<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CabecerasSeguridad
{
    private const CSP = "default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src https://cdn.jsdelivr.net https://fonts.gstatic.com; img-src 'self' data: https://gustavoquintero.dev; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'";

    public function handle(Request $peticion, Closure $siguiente): Response
    {
        $respuesta = $siguiente($peticion);
        $respuesta->headers->set('X-Content-Type-Options', 'nosniff');
        $respuesta->headers->set('X-Frame-Options', 'DENY');
        if (! $respuesta->headers->has('Referrer-Policy')) {
            $respuesta->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }
        if (str_contains((string) $respuesta->headers->get('Content-Type'), 'text/html')) {
            $respuesta->headers->set('Content-Security-Policy', self::CSP);
        }

        return $respuesta;
    }
}
