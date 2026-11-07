<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Autenticable;

/**
 * @property int $id
 * @property string $nombre
 * @property string $correo
 * @property string $contrasena
 * @property string $rol
 */
class Usuario extends Autenticable
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'usuarios';

    protected $fillable = ['nombre', 'correo', 'contrasena', 'rol'];

    protected $hidden = ['contrasena', 'token_recordar'];

    protected $rememberTokenName = 'token_recordar';

    protected function casts(): array
    {
        return ['contrasena' => 'hashed'];
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    public function esDemo(): bool
    {
        return $this->rol === 'demo';
    }

    /** @return HasMany<Enlace, $this> */
    public function enlaces(): HasMany
    {
        return $this->hasMany(Enlace::class);
    }

    /** @return HasMany<TokenApi, $this> */
    public function tokensApi(): HasMany
    {
        return $this->hasMany(TokenApi::class);
    }
}
