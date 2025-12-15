<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;     // Para forzar HTTPS
use Illuminate\Support\Facades\View;    // Para el View Composer
use Illuminate\Support\Facades\Auth;    // Para acceder al usuario

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
        // 1. FORZAR HTTPS (Para que funcione Ngrok y el Reset Password)
        if(str_contains(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // 2. COMPARTIR NOTIFICACIONES CON LA BARRA DE NAVEGACIÓN
        View::composer('layouts.navigation', function ($view) {
            if (Auth::check()) {
                $view->with('unreadNotifications', Auth::user()->unreadNotifications);
                $view->with('readNotifications', Auth::user()->readNotifications->take(5)); 
            } else {
                $view->with('unreadNotifications', collect());
                $view->with('readNotifications', collect());
            }
        });
    }
}