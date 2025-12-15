<x-app-layout>
    <x-slot name="header">
        <!-- INICIO: HEADER CON BOTÓN DE VOLVER -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Entrega de Informe Final (F3, F4, Constancia)
            </h2>
        </div>
        <!-- FIN: HEADER CON BOTÓN DE VOLVER -->
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <h3 class="text-lg font-semibold mb-2">Práctica en: {{ $practica->entity_name }}</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Según la directiva, al término de su práctica, debe adjuntar los siguientes 3 documentos
                        para que su Asesor emita el dictamen favorable.
                    </p>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <strong class="font-bold">¡Error!</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>- {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('practicas.final_report.store', $practica) }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="space-y-6">

                            <div>
                                <label for="file_f3" class="block font-medium text-sm text-gray-700">1. Informe Final (Formato F3)</label>
                                <input id="file_f3" class="block mt-1 w-full text-sm" type="file" name="file_f3" required />
                                <p class="text-xs text-gray-500">Este es su informe redactado (Máx 10MB).</p>
                            </div>

                            <div>
                                <label for="file_f4" class="block font-medium text-sm text-gray-700">2. Ficha de Evaluación (Formato F4)</label>
                                <input id="file_f4" class="block mt-1 w-full text-sm" type="file" name="file_f4" required />
                                <p class="text-xs text-gray-500">Documento llenado y firmado por la entidad receptora.</p>
                            </div>
                            
                            <div>
                                <label for="file_constancia" class="block font-medium text-sm text-gray-700">3. Certificado o Constancia de Prácticas</label>
                                <input id="file_constancia" class="block mt-1 w-full text-sm" type="file" name="file_constancia" required />
                                <p class="text-xs text-gray-500">Emitido por la institución donde realizó las prácticas.</p>
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
                                Enviar Informe y Documentos
                            </button>
                        </div>
                        <!-- FIN: BOTONES DE ACCIÓN ACTUALIZADOS -->
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>