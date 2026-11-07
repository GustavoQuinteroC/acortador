<?php

namespace Database\Factories;

use App\Models\Enlace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Enlace> */
class EnlaceFactory extends Factory
{
    protected $model = Enlace::class;

    public function definition(): array
    {
        return [
            'slug' => Str::lower(Str::random(8)),
            'destino' => 'https://ejemplo.mx/'.Str::random(6),
            'titulo' => null,
            'activo' => true,
        ];
    }
}
