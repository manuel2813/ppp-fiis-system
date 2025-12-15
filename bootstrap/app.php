<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
// --- 1. IMPORTA LA CLASE SCHEDULE ---
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    // --- 2. AÑADE ESTA SECCIÓN COMPLETA ---
    ->withSchedule(function (Schedule $schedule) {
        
        // Ejecuta nuestro comando de plazos todos los días a las 4:00 AM
        $schedule->command('app:check-practice-deadlines')->dailyAt('04:00');
        
        // (Aquí puedes añadir más tareas en el futuro)
    })
    // -------------------------------------
    ->create();
