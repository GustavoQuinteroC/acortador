<?php

namespace Tests\Feature;

use App\Models\Enlace;
use App\Models\TokenApi;
use App\Models\Usuario;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function cabeceras(Usuario $usuario): array
    {
        [, $claro] = TokenApi::emitir($usuario, 'pruebas');

        return ['Authorization' => 'Bearer '.$claro, 'Accept' => 'application/json'];
    }

    public function test_sin_token_responde_401_en_json(): void
    {
        $this->getJson('/api/v1/enlaces')->assertUnauthorized()->assertJsonPath('mensaje', fn ($m) => str_contains($m, 'Bearer'));
        $this->getJson('/api/v1/enlaces', ['Authorization' => 'Bearer gqa_falso'])->assertUnauthorized();
    }

    public function test_el_token_solo_se_guarda_como_hash(): void
    {
        [$token, $claro] = TokenApi::emitir($this->usuario(), 'ventas');

        $this->assertStringStartsWith('gqa_', $claro);
        $this->assertSame(hash('sha256', $claro), $token->hash);
        $this->assertDatabaseMissing('tokens_api', ['hash' => $claro]);
    }

    public function test_ciclo_completo_de_un_enlace(): void
    {
        $usuario = $this->usuario();
        $h = $this->cabeceras($usuario);

        $this->postJson('/api/v1/enlaces', ['destino' => 'https://ejemplo.mx/promocion', 'slug' => 'promo', 'titulo' => 'Promoción'], $h)
            ->assertCreated()
            ->assertHeader('Location', 'http://localhost/api/v1/enlaces/promo')
            ->assertJson(['slug' => 'promo', 'url_corta' => 'http://localhost/promo', 'activo' => true, 'visitas' => 0]);

        $this->getJson('/api/v1/enlaces', $h)->assertOk()->assertJsonPath('total', 1)->assertJsonPath('datos.0.slug', 'promo');
        $this->getJson('/api/v1/enlaces/promo', $h)->assertOk()->assertJsonPath('titulo', 'Promoción');

        $this->patchJson('/api/v1/enlaces/promo', ['activo' => false], $h)->assertOk()->assertJsonPath('activo', false)->assertJsonPath('destino', 'https://ejemplo.mx/promocion');
        $this->get('/promo')->assertStatus(410);
        $this->patchJson('/api/v1/enlaces/promo', ['activo' => true], $h)->assertOk();
        $this->get('/promo')->assertRedirect('https://ejemplo.mx/promocion');

        $this->getJson('/api/v1/enlaces/promo/estadisticas?dias=7', $h)->assertOk()
            ->assertJsonPath('total', 1)->assertJsonPath('dias', 7)->assertJsonCount(7, 'por_dia');
        $this->get('/api/v1/enlaces/promo/qr', $h)->assertOk()->assertHeader('Content-Type', 'image/svg+xml');

        $this->deleteJson('/api/v1/enlaces/promo', [], $h)->assertNoContent();
        $this->assertSame(0, Enlace::query()->count());
        $this->assertSame(0, Visita::query()->count());
    }

    public function test_errores_de_validacion_en_espanol(): void
    {
        $this->postJson('/api/v1/enlaces', ['destino' => 'ftp://ejemplo.mx'], $this->cabeceras($this->usuario()))
            ->assertUnprocessable()
            ->assertJsonPath('errors.destino.0', 'El destino debe ser una dirección completa que empiece con http:// o https://.');
    }

    public function test_no_toca_enlaces_ajenos(): void
    {
        $this->enlace($this->usuario('admin', 'otra@ejemplo.mx'), ['slug' => 'ajeno']);
        $h = $this->cabeceras($this->usuario());

        $this->getJson('/api/v1/enlaces/ajeno', $h)->assertNotFound();
        $this->deleteJson('/api/v1/enlaces/ajeno', [], $h)->assertNotFound();
        $this->getJson('/api/v1/enlaces', $h)->assertJsonPath('total', 0);
    }

    public function test_token_de_demo_es_de_solo_lectura_y_un_token_revocado_deja_de_servir(): void
    {
        $demo = $this->usuario('demo', 'demo@ejemplo.mx');
        $h = $this->cabeceras($demo);
        $this->getJson('/api/v1/enlaces', $h)->assertOk();
        $this->postJson('/api/v1/enlaces', ['destino' => 'https://ejemplo.mx'], $h)->assertForbidden();

        TokenApi::query()->delete();
        $this->getJson('/api/v1/enlaces', $h)->assertUnauthorized();
    }

    public function test_tokens_desde_el_panel(): void
    {
        $usuario = $this->usuario();
        $this->actingAs($usuario)->post('/panel/api/tokens', ['nombre' => 'ventas'])->assertRedirect('/panel/api')->assertSessionHas('token_nuevo');
        $token = TokenApi::query()->sole();
        $this->actingAs($usuario)->get('/panel/api')->assertOk()->assertSee('ventas');
        $this->actingAs($usuario)->delete('/panel/api/tokens/'.$token->id)->assertRedirect('/panel/api');
        $this->assertSame(0, TokenApi::query()->count());
    }
}
