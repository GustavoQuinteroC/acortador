<?php

namespace Database\Seeders;

use App\Models\Enlace;
use App\Models\Usuario;
use App\Models\Visita;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Cuenta demo de solo lectura con enlaces y visitas de ejemplo. Se puede correr de nuevo:
 * borra y vuelve a crear los datos de la cuenta demo, sin tocar los de otros usuarios.
 */
class DemoSeeder extends Seeder
{
    public const CORREO = 'demo@gustavoquintero.dev';

    public const CONTRASENA = 'demo-acortador';

    public function run(): void
    {
        $demo = Usuario::query()->updateOrCreate(
            ['correo' => self::CORREO],
            ['nombre' => 'Cuenta demo', 'contrasena' => self::CONTRASENA, 'rol' => 'demo'],
        );
        $demo->enlaces()->each(fn (Enlace $e) => $e->delete());

        $ejemplos = [
            ['demo-cv', 'https://gustavoquintero.dev', 'Currículum de Gustavo Quintero', 9],
            ['demo-qr', 'https://proyectos.gustavoquintero.dev/qr/', 'Generador de QR por lotes', 6],
            ['demo-iva', 'https://proyectos.gustavoquintero.dev/iva/', 'Calculadora de IVA y retenciones', 4],
            ['demo-codigo', 'https://github.com/GustavoQuinteroC', 'Repositorios en GitHub', 3],
        ];
        $paises = ['MX' => 70, 'US' => 12, 'CO' => 6, 'ES' => 5, 'AR' => 4, 'CL' => 3];
        $dispositivos = ['celular' => 62, 'escritorio' => 31, 'tableta' => 4, 'bot' => 3];
        $navegadores = ['Chrome' => 58, 'Safari' => 27, 'Edge' => 7, 'Firefox' => 5, 'Samsung Internet' => 3];
        $referentes = [null => 40, 'linkedin.com' => 25, 'l.facebook.com' => 12, 't.co' => 8, 'github.com' => 10, 'web.whatsapp.com' => 5];

        mt_srand(2026);
        foreach ($ejemplos as [$slug, $destino, $titulo, $porDia]) {
            $enlace = $demo->enlaces()->create(['slug' => $slug, 'destino' => $destino, 'titulo' => $titulo]);
            $enlace->forceFill(['creado_en' => now()->subDays(90)])->save();

            $filas = [];
            for ($d = 89; $d >= 0; $d--) {
                $dia = Carbon::today()->subDays($d);
                // Más visitas entre semana y una tendencia que sube poco a poco.
                $base = $porDia * ($dia->isWeekend() ? 0.5 : 1) * (0.6 + (89 - $d) / 150);
                $cuantas = max(0, (int) round($base + mt_rand(-2, 3)));
                for ($i = 0; $i < $cuantas; $i++) {
                    $filas[] = [
                        'enlace_id' => $enlace->id,
                        'fecha' => $dia->copy()->addMinutes(mt_rand(420, 1380)),
                        'visitante' => substr(md5($slug.$d.mt_rand(1, (int) max(1, $cuantas * 0.8))), 0, 16),
                        'pais' => self::sortear($paises),
                        'dispositivo' => self::sortear($dispositivos),
                        'navegador' => self::sortear($navegadores),
                        'referente' => self::sortear($referentes) ?: null,
                    ];
                }
            }
            foreach (array_chunk($filas, 500) as $lote) {
                Visita::query()->insert($lote);
            }
            Enlace::query()->whereKey($enlace->id)->update(['visitas_total' => count($filas)]);
        }
    }

    /** @param array<string|int, int> $pesos */
    private static function sortear(array $pesos): string
    {
        $tiro = mt_rand(1, array_sum($pesos));
        foreach ($pesos as $valor => $peso) {
            $tiro -= $peso;
            if ($tiro <= 0) {
                return (string) $valor;
            }
        }

        return (string) array_key_first($pesos);
    }
}
