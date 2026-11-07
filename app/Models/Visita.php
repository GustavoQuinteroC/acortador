<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $enlace_id
 * @property string $visitante
 * @property string|null $pais
 * @property string $dispositivo
 * @property string $navegador
 * @property string|null $referente
 */
class Visita extends Model
{
    public $timestamps = false;

    protected $table = 'visitas';

    protected $fillable = ['enlace_id', 'fecha', 'visitante', 'pais', 'dispositivo', 'navegador', 'referente'];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }

    /** @return BelongsTo<Enlace, $this> */
    public function enlace(): BelongsTo
    {
        return $this->belongsTo(Enlace::class);
    }
}
