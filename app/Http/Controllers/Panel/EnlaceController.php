<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarEnlace;
use App\Models\Enlace;
use App\Servicios\Estadisticas;
use App\Servicios\Slugs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnlaceController extends Controller
{
    public function index(Request $peticion): View
    {
        $buscar = trim((string) $peticion->query('buscar', ''));
        $enlaces = $this->usuario($peticion)->enlaces()
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('slug', 'like', "%{$buscar}%")
                ->orWhere('destino', 'like', "%{$buscar}%")
                ->orWhere('titulo', 'like', "%{$buscar}%")))
            ->latest('creado_en')->latest('id')
            ->paginate(15)->withQueryString();

        return view('panel.enlaces', ['enlaces' => $enlaces, 'buscar' => $buscar]);
    }

    public function store(GuardarEnlace $peticion, Slugs $slugs): RedirectResponse
    {
        $datos = $peticion->validated();
        $datos['slug'] ??= $slugs->nuevo();
        $enlace = $this->usuario($peticion)->enlaces()->create($datos);

        return redirect()->route('panel.enlaces.index')
            ->with('creado', $enlace->slug)
            ->with('exito', 'Enlace creado: '.$enlace->urlCorta());
    }

    public function show(Request $peticion, Enlace $enlace, Estadisticas $estadisticas): View
    {
        $this->propio($peticion, $enlace);
        $dias = in_array((int) $peticion->query('dias'), [7, 30, 90], true) ? (int) $peticion->query('dias') : 30;

        return view('panel.enlace', ['enlace' => $enlace, 'resumen' => $estadisticas->resumen([$enlace->id], $dias)]);
    }

    public function update(GuardarEnlace $peticion, Enlace $enlace): RedirectResponse
    {
        $this->propio($peticion, $enlace);
        $datos = $peticion->validated();
        if (array_key_exists('slug', $datos) && $datos['slug'] === null) {
            unset($datos['slug']);
        }
        $enlace->update($datos);

        return redirect()->route('panel.enlaces.show', $enlace)->with('exito', 'Cambios guardados.');
    }

    public function alternar(Request $peticion, Enlace $enlace): RedirectResponse
    {
        $this->propio($peticion, $enlace)->update(['activo' => ! $enlace->activo]);

        return back()->with('exito', $enlace->activo ? 'Enlace activado.' : 'Enlace desactivado: ahora muestra "ya no está disponible".');
    }

    public function destroy(Request $peticion, Enlace $enlace): RedirectResponse
    {
        $this->propio($peticion, $enlace)->delete();

        return redirect()->route('panel.enlaces.index')->with('exito', 'Enlace eliminado con sus estadísticas.');
    }
}
