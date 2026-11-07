<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Servicios\Estadisticas;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TableroController extends Controller
{
    public function __invoke(Request $peticion, Estadisticas $estadisticas): View
    {
        $usuario = $this->usuario($peticion);
        /** @var list<int> $ids */
        $ids = $usuario->enlaces()->pluck('id')->all();

        return view('panel.tablero', [
            'resumen' => $estadisticas->resumen($ids, 30),
            'totalEnlaces' => count($ids),
            'activos' => $usuario->enlaces()->where('activo', true)->count(),
            'mejores' => $usuario->enlaces()->orderByDesc('visitas_total')->limit(5)->get(),
        ]);
    }
}
