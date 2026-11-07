<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarEnlace;
use App\Models\Enlace;
use App\Servicios\CodigosQr;
use App\Servicios\Estadisticas;
use App\Servicios\Slugs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnlaceController extends Controller
{
    public function index(Request $peticion): JsonResponse
    {
        $pagina = $this->usuario($peticion)->enlaces()->latest('creado_en')->latest('id')->paginate(min(100, max(1, $peticion->integer('por_pagina', 25))));

        return response()->json([
            'datos' => collect($pagina->items())->map(fn (Enlace $e) => self::json($e))->all(),
            'pagina' => $pagina->currentPage(),
            'por_pagina' => $pagina->perPage(),
            'total' => $pagina->total(),
            'ultima_pagina' => $pagina->lastPage(),
        ]);
    }

    public function store(GuardarEnlace $peticion, Slugs $slugs): JsonResponse
    {
        $datos = $peticion->validated();
        $datos['slug'] ??= $slugs->nuevo();
        $enlace = $this->usuario($peticion)->enlaces()->create($datos);

        return response()->json(self::json($enlace->refresh()), 201, ['Location' => route('api.enlaces.show', $enlace)]);
    }

    public function show(Request $peticion, Enlace $enlace): JsonResponse
    {
        return response()->json(self::json($this->propio($peticion, $enlace)));
    }

    public function update(GuardarEnlace $peticion, Enlace $enlace): JsonResponse
    {
        $this->propio($peticion, $enlace);
        $datos = $peticion->validated();
        if (array_key_exists('slug', $datos) && $datos['slug'] === null) {
            unset($datos['slug']);
        }
        $enlace->update($datos);

        return response()->json(self::json($enlace));
    }

    public function destroy(Request $peticion, Enlace $enlace): Response
    {
        $this->propio($peticion, $enlace)->delete();

        return response()->noContent();
    }

    public function estadisticas(Request $peticion, Enlace $enlace, Estadisticas $estadisticas): JsonResponse
    {
        $this->propio($peticion, $enlace);
        $dias = min(365, max(1, $peticion->integer('dias', 30)));

        return response()->json(['slug' => $enlace->slug] + $estadisticas->resumen([$enlace->id], $dias));
    }

    public function qr(Request $peticion, Enlace $enlace, CodigosQr $qr): Response
    {
        $this->propio($peticion, $enlace);

        return response($qr->svg($enlace->urlCorta()), 200, ['Content-Type' => 'image/svg+xml']);
    }

    /** @return array<string, mixed> */
    public static function json(Enlace $enlace): array
    {
        return [
            'slug' => $enlace->slug,
            'url_corta' => $enlace->urlCorta(),
            'destino' => $enlace->destino,
            'titulo' => $enlace->titulo,
            'activo' => (bool) $enlace->activo,
            'expira_en' => $enlace->expira_en?->toIso8601String(),
            'visitas' => (int) $enlace->visitas_total,
            'creado_en' => $enlace->creado_en?->toIso8601String(),
        ];
    }
}
