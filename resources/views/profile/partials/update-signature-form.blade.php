{{-- resources/views/profile/partials/update-signature-form.blade.php --}}
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Firma Digital
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            Sube tu firma digital. Esta se usará en los documentos oficiales (como las constancias).
            Sube un archivo **.png con fondo transparente**.
        </p>
    </header>

    @if (session('status') === 'signature-updated')
        <p
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 2000)"
            class="text-sm text-green-600 mt-4"
        >Firma actualizada.</p>
    @endif

    <form method="post" action="{{ route('profile.signature.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="signature" value="Archivo de Firma (.png)" />
            <input id="signature" name="signature" type="file" class="mt-1 block w-full text-sm" accept=".png" required />
            <x-input-error class="mt-2" :messages="$errors->get('signature')" />
        </div>

        @if ($user->signature_path)
            <div class="mt-4">
                <p class="text-sm font-medium text-gray-700">Firma Actual:</p>
                <img src="{{ Storage::url($user->signature_path) }}" alt="Firma actual" class="mt-2 h-20 border border-gray-300 p-2 rounded-md">
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>