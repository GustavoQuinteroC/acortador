<?php

namespace App\Http\Controllers;

use App\Servicios\RegistroVisitas;
use App\Servicios\Resolutor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedireccionController extends Controller
{
    public function __invoke(Request $peticion, string $slug, Resolutor $resolutor, RegistroVisitas $visitas): Response
    {
        $enlace = $resolutor->resolver(strtolower($slug));
        if ($enlace === null) {
            return response()->view('publico.estado', ['titulo' => 'Este enlace no existe', 'texto' => 'Revisa que esté bien escrito. Los enlaces cortos solo llevan minúsculas, números y guiones.'], 404);
        }
        if (! $enlace['activo'] || ($enlace['expira'] !== null && $enlace['expira'] <= time())) {
            return response()->view('publico.estado', ['titulo' => 'Este enlace ya no está disponible', 'texto' => 'Quien lo compartió lo desactivó o venció. Pídele uno nuevo.'], 410);
        }

        $visitas->registrar($enlace['id'], $peticion);

        // 302 y sin caché: así cada visita llega aquí y se cuenta.
        return redirect()->away($enlace['destino'], 302)->withHeaders([
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
