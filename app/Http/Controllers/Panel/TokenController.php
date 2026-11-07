<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\TokenApi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TokenController extends Controller
{
    public function index(Request $peticion): View
    {
        return view('panel.api', ['tokens' => $this->usuario($peticion)->tokensApi()->latest('creado_en')->get()]);
    }

    public function store(Request $peticion): RedirectResponse
    {
        $datos = $peticion->validate(['nombre' => ['required', 'string', 'max:80']], ['nombre.required' => 'Ponle un nombre al token para saber dónde lo usas.']);
        [, $claro] = TokenApi::emitir($this->usuario($peticion), $datos['nombre']);

        // Se muestra una sola vez; en la base solo queda su SHA-256.
        return redirect()->route('panel.api')->with('token_nuevo', $claro);
    }

    public function destroy(Request $peticion, TokenApi $token): RedirectResponse
    {
        abort_unless($token->usuario_id === $this->usuario($peticion)->id, 404);
        $token->delete();

        return redirect()->route('panel.api')->with('exito', 'Token revocado. Las llamadas con él ya no funcionan.');
    }
}
