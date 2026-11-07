<?php

namespace App\Models;

use App\Servicios\Resolutor;
use Database\Factories\EnlaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $usuario_id
 * @property string $slug
 * @property string $destino
 * @property string|null $titulo
 * @property bool $activo
 * @property Carbon|null $expira_en
 * @property int $visitas_total
 * @property Carbon|null $creado_en
 */
class Enlace extends Model
{
    /** @use HasFactory<EnlaceFactory> */
    use HasFactory;

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    protected $table = 'enlaces';

    protected $fillable = ['slug', 'destino', 'titulo', 'activo', 'expira_en'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'expira_en' => 'datetime',
            'visitas_total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Cualquier cambio del enlace invalida su caché de redirección.
        $olvidar = function (Enlace $enlace): void {
            Resolutor::olvidar($enlace->slug);
            if ($enlace->wasChanged('slug')) {
                Resolutor::olvidar((string) $enlace->getOriginal('slug'));
            }
        };
        static::saved($olvidar);
        static::deleted($olvidar);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return BelongsTo<Usuario, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /** @return HasMany<Visita, $this> */
    public function visitas(): HasMany
    {
        return $this->hasMany(Visita::class);
    }

    public function vencido(): bool
    {
        return $this->expira_en !== null && $this->expira_en->isPast();
    }

    public function urlCorta(): string
    {
        return url($this->slug);
    }
}
