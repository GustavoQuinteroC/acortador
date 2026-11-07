<?php

namespace App\Http\Controllers;

use App\Models\Enlace;
use App\Models\Usuario;
use Illuminate\Http\Request;

abstract class Controller
{
    protected function usuario(Request $peticion): Usuario
    {
        $usuario = $peticion->user();
        abort_unless($usuario instanceof Usuario, 401);

        return $usuario;
    }

    /** Cada quien ve solo sus enlaces; uno ajeno se trata como inexistente. */
    protected function propio(Request $peticion, Enlace $enlace): Enlace
    {
        abort_unless($enlace->usuario_id === $this->usuario($peticion)->id, 404);

        return $enlace;
    }
}
