<?php

namespace Tests\Unit;

use App\Servicios\RegistroVisitas;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DeteccionTest extends TestCase
{
    /** @return array<string, array{string, string, string}> */
    public static function agentes(): array
    {
        return [
            'Chrome en Windows' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'escritorio', 'Chrome'],
            'Edge' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 'escritorio', 'Edge'],
            'Safari en iPhone' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1', 'celular', 'Safari'],
            'Android celular' => ['Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'celular', 'Chrome'],
            'Samsung en tableta' => ['Mozilla/5.0 (Linux; Android 14; SM-X710) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/27.0 Chrome/125.0.0.0 Safari/537.36', 'tableta', 'Samsung Internet'],
            'Firefox' => ['Mozilla/5.0 (X11; Linux x86_64; rv:144.0) Gecko/20100101 Firefox/144.0', 'escritorio', 'Firefox'],
            'Vista previa de WhatsApp' => ['WhatsApp/2.24.1 A', 'bot', 'Otro'],
            'curl' => ['curl/8.11.0', 'bot', 'Otro'],
            'Sin agente' => ['', 'bot', 'Otro'],
        ];
    }

    #[DataProvider('agentes')]
    public function test_detecta_dispositivo_y_navegador(string $agente, string $dispositivo, string $navegador): void
    {
        $this->assertSame($dispositivo, RegistroVisitas::dispositivo($agente));
        $this->assertSame($navegador, RegistroVisitas::navegador($agente));
    }
}
