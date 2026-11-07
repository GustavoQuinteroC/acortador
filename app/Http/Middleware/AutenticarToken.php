<?php

namespace App\Http\Middleware;

use App\Models\TokenApi;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** Autentica la API con "Authorization: Bearer gqa_…". */
class AutenticarToken
{
    public function handle(Request $peticion, Closure $siguiente): Response
    {
        $token = TokenApi::buscar((string) $peticion->bearerToken());
        if ($token === null || $token->usuario === null) {
            return response()->json(['mensaje' => 'Falta un token válido en el encabezado Authorization: Bearer.'], 401);
        }

        // Una escritura por minuto basta para saber cuándo se usó por última vez.
        if ($token->ultimo_uso === null || $token->ultimo_uso->lt(now()->subMinute())) {
            $token->forceFill(['ultimo_uso' => now()])->save();
        }
        Auth::setUser($token->usuario);
        $peticion->attributes->set('token_api_id', $token->id);

        return $siguiente($peticion);
    }
}
