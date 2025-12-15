<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Información del Perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Actualice la información de su cuenta y configure su correo de recuperación.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <!-- Nombre -->
        <div>
            <x-input-label for="name" :value="__('Nombre Completo')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Correo Institucional (SOLO LECTURA) -->
        <!-- Lo bloqueamos para evitar errores, ya que es el ID del usuario -->
        <div>
            <x-input-label for="email" :value="__('Correo Institucional (Usuario)')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full bg-gray-100 text-gray-600 cursor-not-allowed" :value="old('email', $user->email)" required readonly />
            <p class="text-xs text-gray-500 mt-1">
                <span class="font-bold text-red-500">*</span> El correo institucional es su identificador y no debe modificarse.
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Correo de Recuperación (EDITABLE) -->
        <div class="p-4 bg-blue-50 border border-blue-100 rounded-md">
            <x-input-label for="recovery_email" :value="__('Correo de Recuperación (Gmail / Outlook Personal)')" class="text-blue-800 font-bold" />
            
            <x-text-input id="recovery_email" name="recovery_email" type="email" class="mt-1 block w-full border-blue-300 focus:border-blue-500 focus:ring-blue-500" :value="old('recovery_email', $user->recovery_email)" placeholder="ejemplo@gmail.com" required />
            
            <div class="flex items-start mt-2">
                <svg class="w-4 h-4 text-blue-600 mr-1 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-xs text-blue-600">
                    <strong>IMPORTANTE:</strong> Si olvida su contraseña, el enlace de restablecimiento se enviará a este correo para evitar bloqueos del servidor institucional.
                </p>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('recovery_email')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar Cambios') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Información actualizada correctamente.') }}</p>
            @endif
        </div>
    </form>
</section>