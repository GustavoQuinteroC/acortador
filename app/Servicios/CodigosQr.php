<?php

namespace App\Servicios;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/** QR del enlace corto generado en el servidor, sin servicios de terceros. */
class CodigosQr
{
    public function svg(string $texto): string
    {
        return $this->generar($texto, QRMarkupSVG::class);
    }

    public function png(string $texto): string
    {
        return $this->generar($texto, QRGdImagePNG::class);
    }

    private function generar(string $texto, string $salida): string
    {
        $opciones = new QROptions([
            'outputInterface' => $salida,
            'outputBase64' => false,
            'eccLevel' => EccLevel::M,
            'scale' => 12,
            'addQuietzone' => true,
        ]);

        return (string) (new QRCode($opciones))->render($texto);
    }
}
