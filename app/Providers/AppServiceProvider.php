<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL; // <-- Importación agregada para HTTPS

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar un límite de 191 caracteres para evitar el error de "Key too long" en MySQL
        Schema::defaultStringLength(191); 

        // Forzar HTTPS en producción para evitar el error de Mixed Content (CSS/JS bloqueados)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}