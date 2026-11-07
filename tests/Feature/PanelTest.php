<?php

namespace Tests\Feature;

use App\Models\Enlace;
use App\Models\TokenApi;
use App\Models\Visita;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_panel_pide_sesion(): void
    {
        $this->get('/panel')->assertRedirect('/entrar');
    }

    public function test_entrar_y_salir(): void
    {
        $this->usuario();
        $this->post('/entrar', ['correo' => 'admin@ejemplo.mx', 'contrasena' => 'otra'])->assertSessionHasErrors('correo');
        $this->assertGuest();

        $this->post('/entrar', ['correo' => 'admin@ejemplo.mx', 'contrasena' => 'una-contrasena-larga'])->assertRedirect('/panel');
        $this->assertAuthenticated();
        $this->post('/salir')->assertRedirect('/entrar');
        $this->assertGuest();
    }

    public function test_bloquea_tras_cinco_intentos_fallidos(): void
    {
        $this->usuario();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/entrar', ['correo' => 'admin@ejemplo.mx', 'contrasena' => 'mal']);
        }
        $this->post('/entrar', ['correo' => 'admin@ejemplo.mx', 'contrasena' => 'una-contrasena-larga'])
            ->assertSessionHasErrors('correo');
        $this->assertStringStartsWith('Demasiados intentos', (string) session('errors')->first('correo'));
        $this->assertGuest();
    }

    public function test_crea_enlaces_con_slug_propio_o_generado(): void
    {
        $usuario = $this->usuario();
        $this->actingAs($usuario)->post('/panel/enlaces', ['destino' => 'https://ejemplo.mx/a', 'slug' => 'Mi-Promo'])
            ->assertRedirect('/panel/enlaces')->assertSessionHas('creado', 'mi-promo');
        $this->actingAs($usuario)->post('/panel/enlaces', ['destino' => 'https://ejemplo.mx/b'])->assertSessionHasNoErrors();

        $generado = Enlace::query()->where('destino', 'https://ejemplo.mx/b')->sole();
        $this->assertMatchesRegularExpression('/^[a-hj-km-np-z2-9]{6}$/', $generado->slug);
        $this->actingAs($usuario)->get('/panel/enlaces')->assertOk()->assertSee('/mi-promo')->assertSee('/'.$generado->slug);
    }

    public function test_valida_destino_y_slug(): void
    {
        $usuario = $this->usuario();
        $this->enlace($usuario, ['slug' => 'ocupado']);

        $this->actingAs($usuario)->post('/panel/enlaces', ['destino' => 'javascript:alert(1)'])->assertSessionHasErrors('destino');
        $this->actingAs($usuario)->post('/panel/enlaces', ['destino' => 'http://localhost/otro'])
            ->assertSessionHasErrors(['destino' => 'El destino no puede ser otro enlace de este acortador.']);
        $this->actingAs($usuario)->post('/panel/enlaces', ['destino' => 'https://ejemplo.mx', 'slug' => 'ocupado'])
            ->assertSessionHasErrors(['slug' => 'Ese enlace corto ya existe. Elige otro.']);
        $this->actingAs($usuario)->post('/panel/enlaces', ['destino' => 'https://ejemplo.mx', 'slug' => 'panel'])->assertSessionHasErrors('slug');
        $this->actingAs($usuario)->post('/panel/enlaces', ['destino' => 'https://ejemplo.mx', 'slug' => '-mal-'])->assertSessionHasErrors('slug');
        $this->assertSame(1, Enlace::query()->count());
    }

    public function test_editar_pausar_y_eliminar(): void
    {
        $usuario = $this->usuario();
        $enlace = $this->enlace($usuario, ['slug' => 'viejo']);
        Visita::query()->create(['enlace_id' => $enlace->id, 'fecha' => now(), 'visitante' => str_repeat('a', 16), 'dispositivo' => 'escritorio', 'navegador' => 'Chrome']);

        $this->actingAs($usuario)->put('/panel/enlaces/viejo', ['destino' => 'https://ejemplo.mx/nuevo', 'slug' => 'nuevo'])
            ->assertRedirect('/panel/enlaces/nuevo');
        $this->actingAs($usuario)->patch('/panel/enlaces/nuevo/estado');
        $this->assertFalse($enlace->refresh()->activo);

        $this->actingAs($usuario)->delete('/panel/enlaces/nuevo')->assertRedirect('/panel/enlaces');
        $this->assertSame(0, Enlace::query()->count());
        $this->assertSame(0, Visita::query()->count());
    }

    public function test_nadie_ve_enlaces_ajenos(): void
    {
        $ajeno = $this->enlace($this->usuario('admin', 'otra@ejemplo.mx'), ['slug' => 'ajeno']);
        $yo = $this->usuario();

        $this->actingAs($yo)->get('/panel/enlaces/ajeno')->assertNotFound();
        $this->actingAs($yo)->delete('/panel/enlaces/ajeno')->assertNotFound();
        $this->actingAs($yo)->get('/panel/enlaces/ajeno/qr.svg')->assertNotFound();
        $this->assertTrue($ajeno->exists());
    }

    public function test_qr_en_svg_y_png(): void
    {
        $usuario = $this->usuario();
        $this->enlace($usuario, ['slug' => 'qr']);

        $svg = $this->actingAs($usuario)->get('/panel/enlaces/qr/qr.svg')->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', (string) $svg->getContent());
        $png = $this->actingAs($usuario)->get('/panel/enlaces/qr/qr.png?descargar=1')->assertOk()->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Content-Disposition', 'attachment; filename="qr-qr.png"');
        $this->assertStringStartsWith("\x89PNG", (string) $png->getContent());
    }

    public function test_la_cuenta_demo_ve_todo_pero_no_escribe(): void
    {
        $this->seed(DemoSeeder::class);
        $this->post('/entrar', ['correo' => DemoSeeder::CORREO, 'contrasena' => DemoSeeder::CONTRASENA])->assertRedirect('/panel');

        $this->get('/panel')->assertOk()->assertSee('Demo de solo lectura')->assertSee('/demo-cv');
        $this->get('/panel/enlaces')->assertOk()->assertDontSee('Nuevo enlace');
        $this->get('/panel/enlaces/demo-cv?dias=90')->assertOk()->assertSee('Países');
        $this->get('/panel/api')->assertOk();

        $antes = Enlace::query()->count();
        $this->from('/panel/enlaces')->post('/panel/enlaces', ['destino' => 'https://ejemplo.mx'])
            ->assertRedirect('/panel/enlaces')->assertSessionHas('aviso', 'La cuenta demo es de solo lectura.');
        $this->delete('/panel/enlaces/demo-cv');
        $this->post('/panel/api/tokens', ['nombre' => 'x']);
        $this->assertSame($antes, Enlace::query()->count());
        $this->assertSame(0, TokenApi::query()->count());

        $this->post('/salir')->assertRedirect('/entrar');
    }

    public function test_la_semilla_demo_se_puede_correr_dos_veces(): void
    {
        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);
        $this->assertSame(4, Enlace::query()->count());
        $this->assertGreaterThan(500, Visita::query()->count());
    }

    public function test_comando_para_crear_administrador(): void
    {
        $this->artisan('acortador:admin', ['correo' => 'Jefa@Ejemplo.mx'])
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'corta')
            ->assertExitCode(1);
        $this->artisan('acortador:admin', ['correo' => 'Jefa@Ejemplo.mx'])
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'una-clave-bastante-larga')
            ->assertExitCode(0);

        $this->post('/entrar', ['correo' => 'jefa@ejemplo.mx', 'contrasena' => 'una-clave-bastante-larga'])->assertRedirect('/panel');
    }
}
