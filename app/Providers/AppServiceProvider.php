<?php

namespace App\Providers;

use App\Models\Usuario;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Carbon::setLocale((string) config('app.locale'));

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Los contadores viven en el almacén de caché (Redis en producción).
        RateLimiter::for('api', function (Request $peticion) {
            $usuario = $peticion->user();

            return Limit::perMinute(60)->by($usuario instanceof Usuario ? 'usuario:'.$usuario->id : 'ip:'.$peticion->ip())
                ->response(fn () => response()->json(['mensaje' => 'Demasiadas solicitudes. Espera un minuto.'], 429));
        });
        RateLimiter::for('redirecciones', fn (Request $peticion) => Limit::perMinute(120)->by((string) $peticion->ip()));
    }
}
