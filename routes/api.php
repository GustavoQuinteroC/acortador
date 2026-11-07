<?php

use App\Http\Controllers\Api\EnlaceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['token-api', 'solo-lectura', 'throttle:api'])->name('api.')->group(function () {
    Route::apiResource('enlaces', EnlaceController::class)->parameters(['enlaces' => 'enlace']);
    Route::get('enlaces/{enlace}/estadisticas', [EnlaceController::class, 'estadisticas'])->name('enlaces.estadisticas');
    Route::get('enlaces/{enlace}/qr', [EnlaceController::class, 'qr'])->name('enlaces.qr');
});
