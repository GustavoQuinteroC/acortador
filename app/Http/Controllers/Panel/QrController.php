<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Enlace;
use App\Servicios\CodigosQr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QrController extends Controller
{
    public function __invoke(Request $peticion, Enlace $enlace, string $formato, CodigosQr $qr): Response
    {
        $this->propio($peticion, $enlace);
        $png = $formato === 'png';

        return response($png ? $qr->png($enlace->urlCorta()) : $qr->svg($enlace->urlCorta()), 200, [
            'Content-Type' => $png ? 'image/png' : 'image/svg+xml',
            'Content-Disposition' => ($peticion->boolean('descargar') ? 'attachment' : 'inline').'; filename="qr-'.$enlace->slug.'.'.$formato.'"',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
