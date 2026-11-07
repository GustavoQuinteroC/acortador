<?php

use App\Http\Controllers\Panel\EnlaceController;
use App\Http\Controllers\Panel\QrController;
use App\Http\Controllers\Panel\TableroController;
use App\Http\Controllers\Panel\TokenController;
use App\Http\Controllers\RedireccionController;
use App\Http\Controllers\SesionController;
use App\Servicios\Slugs;
use Illuminate\Support\Facades\Route;

Route::view('/', 'publico.inicio')->name('inicio');

Route::middleware('guest')->group(function () {
    Route::get('/entrar', [SesionController::class, 'formulario'])->name('entrar');
    Route::post('/entrar', [SesionController::class, 'entrar'])->middleware('throttle:20,1');
});
Route::post('/salir', [SesionController::class, 'salir'])->middleware('auth')->name('salir');

Route::prefix('panel')->name('panel.')->middleware(['auth', 'solo-lectura'])->group(function () {
    Route::get('/', TableroController::class)->name('tablero');
    Route::resource('enlaces', EnlaceController::class)->except(['create', 'edit'])
        ->parameters(['enlaces' => 'enlace']);
    Route::patch('enlaces/{enlace}/estado', [EnlaceController::class, 'alternar'])->name('enlaces.estado');
    Route::get('enlaces/{enlace}/qr.{formato}', QrController::class)->whereIn('formato', ['svg', 'png'])->name('enlaces.qr');
    Route::get('api', [TokenController::class, 'index'])->name('api');
    Route::post('api/tokens', [TokenController::class, 'store'])->name('api.tokens.store');
    Route::delete('api/tokens/{token}', [TokenController::class, 'destroy'])->name('api.tokens.destroy');
});

// Al final: cualquier otro camino de un solo segmento es un enlace corto.
Route::get('/{slug}', RedireccionController::class)
    ->where('slug', trim(Slugs::PATRON, '^$'))
    ->middleware('throttle:redirecciones')
    ->name('redireccion');
