<?php

namespace App\Servicios;

use App\Models\Visita;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Estadisticas
{
    /**
     * Resumen de visitas de uno o varios enlaces en los últimos $dias días.
     *
     * @param  list<int>  $enlaces
     * @return array{dias: int, total: int, unicos: int, por_dia: list<array{fecha: string, visitas: int}>, paises: array<string, int>, dispositivos: array<string, int>, navegadores: array<string, int>, referentes: array<string, int>}
     */
    public function resumen(array $enlaces, int $dias = 30): array
    {
        $desde = Carbon::today()->subDays($dias - 1);
        $base = fn (): Builder => Visita::query()->whereIn('enlace_id', $enlaces)->where('fecha', '>=', $desde);

        /** @var array<string, int> $conteo */
        $conteo = $base()->selectRaw('DATE(fecha) as dia, COUNT(*) as visitas')->groupBy('dia')->pluck('visitas', 'dia')->map(fn ($v) => (int) $v)->all();
        $porDia = [];
        for ($i = 0; $i < $dias; $i++) {
            $dia = $desde->copy()->addDays($i)->toDateString();
            $porDia[] = ['fecha' => $dia, 'visitas' => $conteo[$dia] ?? 0];
        }

        return [
            'dias' => $dias,
            'total' => $base()->count(),
            'unicos' => $base()->distinct()->count('visitante'),
            'por_dia' => $porDia,
            'paises' => $this->top($base(), 'pais', 'Sin dato'),
            'dispositivos' => $this->top($base(), 'dispositivo'),
            'navegadores' => $this->top($base(), 'navegador'),
            'referentes' => $this->top($base(), 'referente', 'Directo'),
        ];
    }

    /**
     * @param  Builder<Visita>  $consulta
     * @return array<string, int>
     */
    private function top(Builder $consulta, string $columna, string $vacio = 'Otro', int $limite = 8): array
    {
        $filas = $consulta->selectRaw("$columna as clave, COUNT(*) as visitas")
            ->groupBy($columna)->orderByDesc('visitas')->limit($limite)->get();

        $resultado = [];
        foreach ($filas as $fila) {
            $resultado[(string) ($fila->getAttribute('clave') ?? $vacio)] = (int) $fila->getAttribute('visitas');
        }

        return $resultado;
    }
}
