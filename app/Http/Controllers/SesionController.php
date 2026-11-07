<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SesionController extends Controller
{
    public function formulario(): View
    {
        return view('publico.entrar');
    }

    public function entrar(Request $peticion): RedirectResponse
    {
        $datos = $peticion->validate([
            'correo' => ['required', 'email', 'max:190'],
            'contrasena' => ['required', 'string', 'max:200'],
        ], [
            'correo.required' => 'Escribe tu correo.',
            'correo.email' => 'Ese correo no es válido.',
            'contrasena.required' => 'Escribe tu contraseña.',
        ]);

        $llave = 'entrar:'.Str::lower($datos['correo']).'|'.$peticion->ip();
        if (RateLimiter::tooManyAttempts($llave, 5)) {
            throw ValidationException::withMessages(['correo' => 'Demasiados intentos. Espera '.RateLimiter::availableIn($llave).' segundos.']);
        }

        if (! Auth::attempt(['correo' => $datos['correo'], 'password' => $datos['contrasena']], $peticion->boolean('recordar'))) {
            RateLimiter::hit($llave, 60);
            throw ValidationException::withMessages(['correo' => 'El correo o la contraseña no coinciden.']);
        }

        RateLimiter::clear($llave);
        $peticion->session()->regenerate();

        return redirect()->intended(route('panel.tablero'));
    }

    public function salir(Request $peticion): RedirectResponse
    {
        Auth::logout();
        $peticion->session()->invalidate();
        $peticion->session()->regenerateToken();

        return redirect()->route('entrar');
    }
}
