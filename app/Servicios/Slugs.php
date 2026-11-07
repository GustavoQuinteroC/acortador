<?php

namespace App\Servicios;

use App\Models\Enlace;

class Slugs
{
    /** Sin 0/o, 1/l/i: el slug se puede dictar y copiar a mano sin confusiones. */
    private const ALFABETO = 'abcdefghjkmnpqrstuvwxyz23456789';

    /** Rutas propias de la app que un slug no puede ocupar. */
    public const RESERVADOS = ['api', 'panel', 'entrar', 'salir', 'up', 'vendor', 'robots', 'favicon', 'index', 'admin', 'login'];

    public const PATRON = '^[a-z0-9](?:[a-z0-9-]{0,62}[a-z0-9])?$';

    public function nuevo(int $largo = 6): string
    {
        $ultimo = strlen(self::ALFABETO) - 1;
        do {
            $slug = '';
            for ($i = 0; $i < $largo; $i++) {
                $slug .= self::ALFABETO[random_int(0, $ultimo)];
            }
        } while ($this->ocupado($slug));

        return $slug;
    }

    public function ocupado(string $slug): bool
    {
        return in_array($slug, self::RESERVADOS, true) || Enlace::query()->where('slug', $slug)->exists();
    }
}
