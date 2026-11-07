<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $usuario_id
 * @property string $nombre
 * @property string $hash
 * @property Carbon|null $ultimo_uso
 * @property Carbon|null $creado_en
 */
class TokenApi extends Model
{
    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = 'actualizado_en';

    /** Prefijo visible: ayuda a reconocer el token y a que los escáneres de secretos lo detecten. */
    public const PREFIJO = 'gqa_';

    protected $table = 'tokens_api';

    protected $fillable = ['nombre', 'hash'];

    protected $hidden = ['hash'];

    protected function casts(): array
    {
        return ['ultimo_uso' => 'datetime'];
    }

    /** @return BelongsTo<Usuario, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Crea un token y devuelve el texto en claro, que no se vuelve a mostrar.
     *
     * @return array{0: TokenApi, 1: string}
     */
    public static function emitir(Usuario $usuario, string $nombre): array
    {
        $claro = self::PREFIJO.Str::random(40);
        $token = $usuario->tokensApi()->create(['nombre' => $nombre, 'hash' => hash('sha256', $claro)]);

        return [$token, $claro];
    }

    public static function buscar(string $claro): ?self
    {
        if (! str_starts_with($claro, self::PREFIJO)) {
            return null;
        }

        return self::query()->where('hash', hash('sha256', $claro))->first();
    }
}
