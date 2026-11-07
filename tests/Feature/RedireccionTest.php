<?php

namespace Tests\Feature;

use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedireccionTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirige_y_registra_la_visita_sin_guardar_la_ip(): void
    {
        $enlace = $this->enlace($this->usuario(), ['slug' => 'promo', 'destino' => 'https://ejemplo.mx/oferta?x=1']);

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 Version/18.0 Mobile/15E148 Safari/604.1',
            'Referer' => 'https://www.linkedin.com/feed/',
            'CF-IPCountry' => 'MX',
        ])->get('/promo')
            ->assertStatus(302)
            ->assertRedirect('https://ejemplo.mx/oferta?x=1')
            ->assertHeader('Cache-Control', 'no-store, private');

        $visita = Visita::query()->sole();
        $this->assertSame('celular', $visita->dispositivo);
        $this->assertSame('Safari', $visita->navegador);
        $this->assertSame('MX', $visita->pais);
        $this->assertSame('linkedin.com', $visita->referente);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{16}$/', $visita->visitante);
        $this->assertArrayNotHasKey('ip', $visita->getAttributes());
        $this->assertSame(1, $enlace->refresh()->visitas_total);
    }

    public function test_un_slug_que_no_existe_da_404(): void
    {
        $this->get('/no-existe')->assertNotFound()->assertSee('Este enlace no existe');
        $this->assertSame(0, Visita::query()->count());
    }

    public function test_pausado_o_vencido_da_410(): void
    {
        $usuario = $this->usuario();
        $this->enlace($usuario, ['slug' => 'pausado', 'activo' => false]);
        $this->enlace($usuario, ['slug' => 'vencido', 'expira_en' => now()->subMinute()]);

        $this->get('/pausado')->assertStatus(410)->assertSee('ya no está disponible');
        $this->get('/vencido')->assertStatus(410);
    }

    public function test_cambiar_el_destino_invalida_la_cache(): void
    {
        $enlace = $this->enlace($this->usuario(), ['slug' => 'cambia', 'destino' => 'https://uno.mx']);
        $this->get('/cambia')->assertRedirect('https://uno.mx');

        $enlace->update(['destino' => 'https://dos.mx']);
        $this->get('/cambia')->assertRedirect('https://dos.mx');

        $enlace->update(['slug' => 'nuevo']);
        $this->get('/cambia')->assertNotFound();
        $this->get('/nuevo')->assertRedirect('https://dos.mx');
    }

    public function test_paginas_publicas(): void
    {
        $this->get('/')->assertOk()->assertSee('Acortador de enlaces con QR y estadísticas')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/up')->assertOk();
        $this->get('/entrar')->assertOk()->assertSee('Cuenta demo');
    }
}
