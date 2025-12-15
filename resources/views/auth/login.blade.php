<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Bienvenido</h2>
            <p class="text-sm text-gray-500 mt-1">Ingresa tus credenciales institucionales</p>
        </div>

        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-blue-900 group-focus-within:text-blue-600 transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                </svg>
            </div>
            <x-text-input id="email" class="block w-full pl-10 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-900 focus:border-transparent transition-all duration-200 placeholder-gray-400 text-gray-800 font-medium" 
                          type="email" name="email" :value="old('email')" required autofocus autocomplete="username" 
                          placeholder="Correo Institucional" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="relative group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-blue-900 group-focus-within:text-blue-600 transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <x-text-input id="password" class="block w-full pl-10 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-900 focus:border-transparent transition-all duration-200 placeholder-gray-400 text-gray-800 font-medium" 
                          type="password" name="password" required autocomplete="current-password" 
                          placeholder="Contraseña" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between text-sm">
            <label for="remember_me" class="inline-flex items-center cursor-pointer hover:text-blue-800 transition-colors">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-900 shadow-sm focus:ring-blue-900" name="remember">
                <span class="ms-2 text-gray-600 font-medium">{{ __('Recordarme') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-blue-700 hover:text-blue-900 font-semibold hover:underline" href="{{ route('password.request') }}">
                    {{ __('¿Recuperar contraseña?') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-lg shadow-lg text-sm font-bold text-white bg-gray-900 hover:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-900 transition-all duration-300 transform hover:-translate-y-0.5 uppercase tracking-wider">
                {{ __('Iniciar Sesión') }}
            </button>
        </div>

        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="px-2 bg-white text-gray-400 font-semibold tracking-wide">Nuevo Ingreso</span>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center w-full px-4 py-3 border-2 border-blue-100 rounded-lg text-blue-700 font-bold hover:bg-blue-50 hover:border-blue-200 transition-colors duration-200">
                ¡Regístrate aquí! (SOLO ESTUDIANTES)
            </a>
        </div>
    </form>
</x-guest-layout>