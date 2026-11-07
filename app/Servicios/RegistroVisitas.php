<?php

namespace App\Servicios;

use App\Models\Enlace;
use App\Models\Visita;
use Illuminate\Http\Request;

/**
 * Registra una visita sin guardar la IP: solo una huella diaria, el tipo de dispositivo,
 * el navegador, el dominio de origen y el país (si Cloudflare lo envía).
 */
class RegistroVisitas
{
    public function registrar(int $enlaceId, Request $peticion): void
    {
        $agente = (string) $peticion->userAgent();

        Visita::query()->create([
            'enlace_id' => $enlaceId,
            'fecha' => now(),
            'visitante' => $this->huella((string) $peticion->ip(), $agente),
            'pais' => $this->pais($peticion->header('CF-IPCountry')),
            'dispositivo' => self::dispositivo($agente),
            'navegador' => self::navegador($agente),
            'referente' => $this->referente($peticion->header('referer')),
        ]);
        Enlace::query()->whereKey($enlaceId)->increment('visitas_total');
    }

    public static function dispositivo(string $agente): string
    {
        return match (true) {
            $agente === '' || (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|whatsapp|telegrambot|preview|curl|wget|python|go-http/i', $agente) => 'bot',
            (bool) preg_match('/ipad|tablet|kindle|silk|(android(?!.*mobile))/i', $agente) => 'tableta',
            (bool) preg_match('/mobile|iphone|ipod|android|blackberry|opera mini|iemobile/i', $agente) => 'celular',
            default => 'escritorio',
        };
    }

    public static function navegador(string $agente): string
    {
        return match (true) {
            (bool) preg_match('/edg(e|a|ios)?\//i', $agente) => 'Edge',
            (bool) preg_match('/opr\/|opera/i', $agente) => 'Opera',
            (bool) preg_match('/samsungbrowser/i', $agente) => 'Samsung Internet',
            (bool) preg_match('/firefox|fxios/i', $agente) => 'Firefox',
            (bool) preg_match('/chrome|crios|chromium/i', $agente) => 'Chrome',
            (bool) preg_match('/safari/i', $agente) => 'Safari',
            default => 'Otro',
        };
    }

    private function huella(string $ip, string $agente): string
    {
        $llave = (string) config('app.key');

        return substr(hash_hmac('sha256', $ip.'|'.$agente.'|'.now()->toDateString(), $llave), 0, 16);
    }

    private function pais(?string $codigo): ?string
    {
        $codigo = strtoupper((string) $codigo);

        // XX = desconocido y T1 = Tor en Cloudflare.
        return preg_match('/^[A-Z]{2}$/', $codigo) && ! in_array($codigo, ['XX', 'T1'], true) ? $codigo : null;
    }

    private function referente(?string $url): ?string
    {
        $host = $url ? parse_url($url, PHP_URL_HOST) : null;
        if (! is_string($host) || $host === '') {
            return null;
        }
        $host = strtolower(preg_replace('/^www\./i', '', $host) ?? $host);

        return $host === request()->getHost() ? null : substr($host, 0, 190);
    }
}
