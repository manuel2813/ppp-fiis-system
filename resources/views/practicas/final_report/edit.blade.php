<x-app-layout>
    <x-slot name="header">
        <!-- INICIO: HEADER CON BOTÓN DE VOLVER -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Corregir Entrega de Informe Final (F3, F4, Constancia)
            </h2>
        </div>
        <!-- FIN: HEADER CON BOTÓN DE VOLVER -->
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @php
                        $observationTitle = ($practica->status === 'jury_observed') 
                            ? 'Observaciones del Jurado:' 
                            : 'Observaciones del Asesor:';
                    @endphp
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                        <h4 class="text-md font-semibold text-red-800">{{ $observationTitle }}</h4>
                        <p class="mt-2 text-sm text-red-700 whitespace-pre-wrap">{{ $practica->observation_notes }}</p>
                        <p class="mt-4 text-sm font-semibold text-red-900">Por favor, corrija y vuelva a subir los archivos observados.</p>
                    </div>

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

                    <form method="POST" action="{{ route('practicas.final_report.update', $practica) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') 
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Si no sube un archivo nuevo, se conservará el que subió originalmente.
                        </p>

                        <div class="space-y-6">
                            @php
                                $f3 = $practica->documents->where('type', 'F3_INFORME_FINAL')->first();
                                $f4 = $practica->documents->where('type', 'F4_EVALUACION_ENTIDAD')->first();
                                $constancia = $practica->documents->where('type', 'CONSTANCIA_ENTIDAD')->first();
                            @endphp

                            <div>
                                <label for="file_f3" class="block font-medium text-sm text-gray-700">1. Informe Final (Formato F3)</label>
                                <input id="file_f3" class="block mt-1 w-full text-sm" type="file" name="file_f3" />
                                @if($f3)
                                <span class="text-xs text-gray-500">Actual: 
                                    <a href="{{ route('cppp.documentos.download', $f3) }}" class="text-blue-600">Ver F3 actual</a>
                                </span>
                                @endif
                            </div>

                            <div>
                                <label for="file_f4" class="block font-medium text-sm text-gray-700">2. Ficha de Evaluación (Formato F4)</label>
                                <input id="file_f4" class="block mt-1 w-full text-sm" type="file" name="file_f4" />
                                @if($f4)
                                <span class="text-xs text-gray-500">Actual: 
                                    <a href="{{ route('cppp.documentos.download', $f4) }}" class="text-blue-600">Ver F4 actual</a>
                                </span>
                                @endif
                            </div>
                            
                            <div>
                                <label for="file_constancia" class="block font-medium text-sm text-gray-700">3. Certificado o Constancia</label>
                                <input id="file_constancia" class="block mt-1 w-full text-sm" type="file" name="file_constancia" />
                                @if($constancia)
                                <span class="text-xs text-gray-500">Actual: 
                                    <a href="{{ route('cppp.documentos.download', $constancia) }}" class="text-blue-600">Ver Constancia actual</a>
                                </span>
                                @endif
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
                                Enviar Correcciones
                            </button>
                        </div>
                        <!-- FIN: BOTONES DE ACCIÓN ACTUALIZADOS -->
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>