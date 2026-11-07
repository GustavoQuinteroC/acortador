<?php

use App\Models\Usuario;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

Artisan::command('acortador:admin {correo} {--nombre=Administrador}', function (string $correo) {
    /** @var Illuminate\Console\Command $this */
    $contrasena = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
    $validador = Validator::make(['correo' => $correo, 'contrasena' => $contrasena], [
        'correo' => ['required', 'email', 'max:190'],
        'contrasena' => ['required', 'string', 'min:12'],
    ]);
    if ($validador->fails()) {
        $this->error($validador->errors()->first());

        return 1;
    }

    Usuario::query()->updateOrCreate(
        ['correo' => strtolower($correo)],
        ['nombre' => (string) $this->option('nombre'), 'contrasena' => $contrasena, 'rol' => 'admin'],
    );
    $this->info("Administrador listo: {$correo}");

    return 0;
})->purpose('Crea o actualiza un administrador; la contraseña se pide sin mostrarla');
