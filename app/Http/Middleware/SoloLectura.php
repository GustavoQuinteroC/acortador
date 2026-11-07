<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** La cuenta demo puede ver todo el panel, pero no crear, cambiar ni borrar. */
class SoloLectura
{
    public function handle(Request $peticion, Closure $siguiente): Response
    {
        $usuario = $peticion->user();
        if ($usuario instanceof Usuario && $usuario->esDemo() && ! $peticion->isMethodSafe()) {
            $mensaje = 'La cuenta demo es de solo lectura.';
            if ($peticion->expectsJson()) {
                return response()->json(['mensaje' => $mensaje], 403);
            }

            return back()->with('aviso', $mensaje);
        }

        return $siguiente($peticion);
    }
}
