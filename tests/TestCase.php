<?php

namespace Tests;

use App\Models\Enlace;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function usuario(string $rol = 'admin', string $correo = 'admin@ejemplo.mx'): Usuario
    {
        return Usuario::query()->create(['nombre' => 'Prueba', 'correo' => $correo, 'contrasena' => 'una-contrasena-larga', 'rol' => $rol]);
    }

    /** @param array<string, mixed> $datos */
    protected function enlace(Usuario $usuario, array $datos = []): Enlace
    {
        return Enlace::factory()->for($usuario)->create($datos);
    }
}
