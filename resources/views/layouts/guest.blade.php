<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'PPP-FIIS') }}</title>
        <link rel="icon" href="{{ asset('images/logo_fiis_ppp.png') }}" type="image/png">
        <linkpreconnect href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .fade-in-up {
                animation: fadeInUp 0.8s ease-out forwards;
                opacity: 0;
                transform: translateY(20px);
            }
            @keyframes fadeInUp {
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen w-full flex flex-col justify-center items-center py-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-600 via-slate-900 to-black relative overflow-y-auto overflow-x-hidden">
            
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full max-w-5xl opacity-50 pointer-events-none">
                <div class="absolute top-[-10%] left-[15%] w-96 h-96 bg-blue-500 rounded-full mix-blend-screen filter blur-[130px]"></div>
                <div class="absolute bottom-[10%] right-[15%] w-80 h-80 bg-indigo-500 rounded-full mix-blend-screen filter blur-[110px]"></div>
            </div>

            <div class="w-full max-w-md px-4 mb-6 text-center z-10 fade-in-up flex flex-col items-center">
                
                <a href="/" class="inline-block mb-4 filter drop-shadow-[0_0_15px_rgba(255,255,255,0.4)] hover:scale-105 transition-transform duration-300">
                    <x-application-logo class="w-24 h-24 sm:w-32 sm:h-32 object-contain mx-auto" />
                </a>
                
                <h1 class="text-4xl sm:text-5xl font-extrabold text-white tracking-wider drop-shadow-md text-center" 
                    style="font-family: 'Figtree', sans-serif; text-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                    <span class="text-blue-200">PPP</span>-FIIS
                </h1>

                <div class="mt-2 w-full flex justify-center">
                    <p class="text-blue-100/90 text-[0.65rem] sm:text-xs font-medium tracking-[0.2em] uppercase border-b border-blue-400/30 pb-2 inline-block text-center">
                        Sistema de Prácticas Preprofesionales
                    </p>
                </div>
            </div>

            <div class="w-full max-w-md px-6 py-8 bg-white/95 backdrop-blur-xl shadow-[0_30px_60px_-10px_rgba(0,0,0,0.7)] overflow-hidden rounded-2xl z-10 fade-in-up border border-white/50 mx-4 box-border" style="animation-delay: 0.2s;">
                {{ $slot }}
            </div>

            <div class="mt-8 text-blue-200/60 text-xs z-10 fade-in-up font-light text-center px-4 pb-4" style="animation-delay: 0.4s;">
                &copy; {{ date('Y') }} UNAS - Facultad de Ingeniería en Informática y Sistemas
            </div>
        </div>
    </body>
</html>