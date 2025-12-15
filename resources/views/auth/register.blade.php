<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 text-center">
        <p>Registro exclusivo para estudiantes. Ingrese su código de matrícula y correo institucional.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nombre Completo')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Code -->
        <div>
            <x-input-label for="code" :value="__('Código de Estudiante')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" name="code" :value="old('code')" required placeholder="Ej: 0020220721" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <!-- Email Institucional -->
        <div>
            <x-input-label for="email" :value="__('Correo Institucional (@unas.edu.pe)')" />
            <x-text-input id="email" class="block mt-1 w-full border-blue-300 focus:border-blue-500 focus:ring-blue-500" type="email" name="email" :value="old('email')" required placeholder="usuario@unas.edu.pe" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Correo de Recuperación (NUEVO) -->
        <div>
            <x-input-label for="recovery_email" :value="__('Correo de Recuperación (Gmail/Hotmail)')" />
            <x-text-input id="recovery_email" class="block mt-1 w-full" type="email" name="recovery_email" :value="old('recovery_email')" required placeholder="tu_correo_personal@gmail.com" />
            <p class="text-xs text-gray-500 mt-1">Usaremos este correo SOLO si olvidas tu contraseña, ya que el institucional suele bloquear correos externos.</p>
            <x-input-error :messages="$errors->get('recovery_email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                {{ __('¿Ya estás registrado?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Registrarse') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>