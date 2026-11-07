<?php

use App\Http\Middleware\AutenticarToken;
use App\Http\Middleware\CabecerasSeguridad;
use App\Http\Middleware\SoloLectura;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Solo se llega al contenedor a través de Traefik (y Cloudflare, si el proxy está activo).
        $middleware->trustProxies(at: '*');
        $middleware->append(CabecerasSeguridad::class);
        $middleware->alias([
            'token-api' => AutenticarToken::class,
            'solo-lectura' => SoloLectura::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('entrar'));
        $middleware->redirectUsersTo(fn () => route('panel.tablero'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
