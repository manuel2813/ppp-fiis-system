<x-app-layout>
    <x-slot name="header">
        <!-- INICIO: HEADER CON BOTÓN DE VOLVER -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Solicitar Ampliación de Prácticas
            </h2>
        </div>
        <!-- FIN: HEADER CON BOTÓN DE VOLVER -->
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <h3 class="text-lg font-semibold mb-2">Solicitud de Ampliación</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Según la directiva, puede solicitar hasta 2 ampliaciones. Esta sería su
                        <span class="font-bold">{{ $practica->extension_count + 1 }}ª</span> solicitud.
                        Debe adjuntar la carta de la institución y la nueva fecha de fin.
                    </p>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>- {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('practicas.extension.store', $practica) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Fecha de Fin Actual</label>
                                <input class="block mt-1 w-full border-gray-300 rounded-md shadow-sm bg-gray-100" type="date" value="{{ $practica->end_date }}" disabled />
                            </div>
                            
                            <div>
                                <label for="new_end_date" class="block font-medium text-sm text-gray-700">Nueva Fecha de Fin Propuesta</label>
                                <input id="new_end_date" name="new_end_date" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="date" value="{{ old('new_end_date') }}" required />
                            </div>

                            <div class="md:col-span-2">
                                <label for="file_extension_letter" class="block font-medium text-sm text-gray-700">Carta de la Institución (Avalando ampliación)</label>
                                <input id="file_extension_letter" name="file_extension_letter" class="block mt-1 w-full text-sm" type="file" required />
                            </div>
                        </div>

                        <!-- INICIO: BOTONES DE ACCIÓN ACTUALIZADOS -->
                        <div class="flex items-center justify-between mt-8">
                            <!-- Botón de Cancelar -->
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Cancelar
                            </a>
                            
                            <!-- Botón de Enviar (con color primary) -->
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800">
                                Enviar Solicitud de Ampliación
                            </button>
                        </div>
                        <!-- FIN: BOTONES DE ACCIÓN ACTUALIZADOS -->
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>