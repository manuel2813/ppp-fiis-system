<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('asesor.dashboard.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestionar Práctica de: {{ $practica->student->name }}
            </h2>
        </div>
        </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="md:col-span-2 space-y-6">

                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif
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

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Subir Formato F2 (Supervisión)</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Adjunte aquí la ficha de supervisión F2. Según la directiva, esto debe hacerse bimestralmente.
                        </p>
                        
                        <form method="POST" action="{{ route('asesor.practicas.uploadF2', $practica) }}" enctype="multipart/form-data">
                            @csrf
                            <div>
                                <label for="supervision_notes" class="block font-medium text-sm text-gray-700">Comentarios / Observaciones (Formato F2)</label>
                                <textarea id="supervision_notes" name="supervision_notes" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('supervision_notes') }}</textarea>
                            </div>
                            <div class="mt-4">
                                <label for="file_f2" class="block font-medium text-sm text-gray-700">Adjuntar Archivo F2 (Requerido)</label>
                                <input id="file_f2" class="block mt-1 w-full text-sm" type="file" name="file_f2" required />
                            </div>
                            <div class="flex items-center justify-end mt-6">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800">
                                    Subir Ficha F2
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                         <h3 class="text-lg font-semibold mb-4 text-gray-900">Historial de Fichas F2 Subidas</h3>
                         <ul role="list" class="divide-y divide-gray-200">
                            @forelse ($formatosF2_subidos as $file)
                                <li class="py-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        Subido el: {{ $file->upload_date->format('d/m/Y') }}
                                        <a href="{{ route('asesor.documentos.download', $file) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">(Descargar)</a>
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <span class="font-semibold">Notas:</span> {{ $file->notes ?? 'Sin notas.' }}
                                    </p>
                                </li>
                            @empty
                                <li class="py-3 text-sm text-gray-500">Aún no se han subido fichas de supervisión (F2) para esta práctica.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                @if ($practica->status == 'pending_advisor_dictamen')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-primary-500">
                        <div class="p-6 bg-white border-b border-gray-200">
                            
                            <h3 class="text-lg font-semibold mb-4 text-primary-800">Revisión de Informe Final (Dictamen Favorable)</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                El estudiante ha subido sus documentos finales. Por favor, revíselos y
                                emita su dictamen favorable o, de lo contrario, observe el informe.
                            </p>

                            <h4 class="text-md font-semibold mb-2">Documentos Entregados:</h4>
                            <ul role="list" class="divide-y divide-gray-200 border border-gray-200 rounded-md mb-6">
                                @php
                                    $f3 = $practica->documents->where('type', 'F3_INFORME_FINAL')->first();
                                    $f4 = $practica->documents->where('type', 'F4_EVALUACION_ENTIDAD')->first();
                                    $constancia = $practica->documents->where('type', 'CONSTANCIA_ENTIDAD')->first();
                                @endphp
                                
                                <li class="py-3 px-4 flex justify-between items-center text-sm">
                                    <span class="font-medium">1. Informe Final (F3)</span>
                                    @if ($f3)
                                        <a href="{{ route('asesor.documentos.download', $f3) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">Descargar</a>
                                    @else
                                        <span class="text-red-500">No adjuntado</span>
                                    @endif
                                </li>
                                <li class="py-3 px-4 flex justify-between items-center text-sm">
                                    <span class="font-medium">2. Ficha Evaluación (F4)</span>
                                    @if ($f4)
                                        <a href="{{ route('asesor.documentos.download', $f4) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">Descargar</a>
                                    @else
                                        <span class="text-red-500">No adjuntado</span>
                                    @endif
                                </li>
                                <li class="py-3 px-4 flex justify-between items-center text-sm">
                                    <span class="font-medium">3. Constancia de Entidad</span>
                                    @if ($constancia)
                                        <a href="{{ route('asesor.documentos.download', $constancia) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">Descargar</a>
                                    @else
                                        <span class="text-red-500">No adjuntado</span>
                                    @endif
                                </li>
                            </ul>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="p-4 border border-green-200 rounded-lg">
                                    <h5 class="text-md font-semibold text-green-700">Aprobar (Dictamen Favorable)</h5>
                                    <p class="text-xs text-gray-600 mb-3">Confirmo que el informe cumple los requisitos.</p>
                                    <form action="{{ route('asesor.practicas.approveDictamen', $practica) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-green-600 ...">
                                            Emitir Dictamen
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="p-4 border border-red-200 rounded-lg">
                                    <h5 class="text-md font-semibold text-red-700">Observar Informe</h5>
                                    <form action="{{ route('asesor.practicas.observeDictamen', $practica) }}" method="POST">
                                        @csrf
                                        <label for="observation_notes" class="text-xs text-gray-600 mb-1 block">Razón de la observación:</label>
                                        <textarea id="observation_notes" name="observation_notes" rows="3" class="block mt-1 w-full ... " required>{{ old('observation_notes') }}</textarea>
                                        @error('observation_notes')
                                            <span class="text-red-600 text-xs">{{ $message }}</span>
                                        @enderror
                                        <button type="submit" class="w-full mt-3 inline-flex justify-center px-4 py-2 bg-red-600 ...">
                                            Enviar Observación
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                </div>

            <div class="md:col-span-1 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Datos del Practicante</h3>
                        <dl>
                            <div class="mb-2">
                                <dt class="text-sm font-medium text-gray-500">Estudiante</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $practica->student->name }}</dd>
                            </div>
                            <div class="mb-2">
                                <dt class="text-sm font-medium text-gray-500">Entidad</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $practica->entity_name }}</dd>
                            </div>
                            <div class="mb-2">
                                <dt class="text-sm font-medium text-gray-500">Periodo</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($practica->start_date)->format('d/m/Y') }} - 
                                    {{ \Carbon\Carbon::parse($practica->end_date)->format('d/m/Y') }}
                                </dd>
                            </div>
                        </dl>
                        
                        <h4 class="text-md font-semibold mt-6 mb-2 text-gray-900">Documentos de Solicitud</h4>
                        @php
                            $docSUT = $practica->documents->where('type', 'SUT')->first();
                            $docF1 = $practica->documents->where('type', 'F1_PLAN')->first();
                            $docCarta = $practica->documents->where('type', 'CARTA_ACEPTACION')->first();
                        @endphp
                        
                        <ul role="list" class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                            <li class="py-3 px-4 flex justify-between items-center text-sm">
                                <span class="font-medium text-gray-700">1. SUT</span>
                                @if ($docSUT)
                                    <a href="{{ route('asesor.documentos.download', $docSUT) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">Descargar</a>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </li>
                            <li class="py-3 px-4 flex justify-between items-center text-sm">
                                <span class="font-medium text-gray-700">2. Plan F1 (PDF)</span>
                                @if ($docF1)
                                    <a href="{{ route('asesor.documentos.download', $docF1) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">Descargar</a>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </li>
                            <li class="py-3 px-4 flex justify-between items-center text-sm">
                                <span class="font-medium text-gray-700">3. Carta Aceptación</span>
                                @if ($docCarta)
                                    <a href="{{ route('asesor.documentos.download', $docCarta) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">Descargar</a>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </li>
                        </ul>
                        </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>