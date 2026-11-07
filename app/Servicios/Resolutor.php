<?php

namespace App\Servicios;

use App\Models\Enlace;
use Illuminate\Support\Facades\Cache;

/**
 * Busca el destino de un slug con caché en Redis: una redirección no toca MariaDB
 * salvo para registrar la visita.
 */
class Resolutor
{
    private const SEGUNDOS = 3600;

    /** @return array{id: int, destino: string, activo: bool, expira: int|null}|null */
    public function resolver(string $slug): ?array
    {
        return Cache::remember(self::clave($slug), self::SEGUNDOS, function () use ($slug) {
            $enlace = Enlace::query()->where('slug', $slug)->first();

            return $enlace === null ? null : [
                'id' => $enlace->id,
                'destino' => $enlace->destino,
                'activo' => $enlace->activo,
                'expira' => $enlace->expira_en?->getTimestamp(),
            ];
        });
    }

    public static function olvidar(string $slug): void
    {
        Cache::forget(self::clave($slug));
    }

    private static function clave(string $slug): string
    {
        return 'enlace:'.$slug;
    }
}
