<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('jury.dashboard.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Revisión de Informe Final: {{ $practica->student->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="md:col-span-2 space-y-6">

                @if(session('success'))
                    <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded">
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
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Documentos Finales para Revisión</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Revise los siguientes documentos (F3, F4, Constancia) para aprobar
                            el informe y autorizar la sustentación.
                        </p>
                        
                        @php
                            $f3 = $practica->documents->where('type', 'F3_INFORME_FINAL')->first();
                            $f4 = $practica->documents->where('type', 'F4_EVALUACION_ENTIDAD')->first();
                            $constancia = $practica->documents->where('type', 'CONSTANCIA_ENTIDAD')->first();
                            $resJurado = $practica->documents->where('type', 'RESOLUCION_JURADO')->first();
                        @endphp

                        <ul role="list" class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                            <li class="py-3 px-4 flex justify-between items-center text-sm">
                                <span class="font-medium text-gray-700">1. Informe Final (F3)</span>
                                @if ($f3) <a href="{{ route('cppp.documentos.download', $f3) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">Descargar</a> @endif
                            </li>
                            <li class="py-3 px-4 flex justify-between items-center text-sm">
                                <span class="font-medium text-gray-700">2. Ficha Evaluación (F4)</span>
                                @if ($f4) <a href="{{ route('cppp.documentos.download', $f4) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">Descargar</a> @endif
                            </li>
                            <li class="py-3 px-4 flex justify-between items-center text-sm">
                                <span class="font-medium text-gray-700">3. Constancia de Entidad</span>
                                @if ($constancia) <a href="{{ route('cppp.documentos.download', $constancia) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">Descargar</a> @endif
                            </li>
                             <li class="py-3 px-4 flex justify-between items-center text-sm bg-gray-50">
                                <span class="font-medium text-gray-700">Resolución de Jurado</span>
                                @if ($resJurado) <a href="{{ route('cppp.documentos.download', $resJurado) }}" class="ml-4 font-medium text-primary-700 hover:text-primary-900">Descargar</a> @endif
                            </li>
                        </ul>
                    </div>
                </div>
                
                @if ($practica->status == 'defense_scheduled')
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg overflow-hidden shadow-sm">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-blue-800">Sustentación Programada</h3>
                            <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Fecha y Hora</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($practica->defense_date)->format('d/m/Y \a \l\a\s h:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Lugar</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $practica->defense_place }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    @if ($myAssignment->role === 'Presidente')
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-green-500">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-semibold mb-4 text-green-800">Registrar Calificación (Formato F5)</h3>
                                <p class="text-sm text-gray-600 mb-6">
                                    Como Presidente del Jurado, registre la calificación final (0-20)
                                    y adjunte el Formato F5 (Acta de Exposición) firmado.
                                </p>

                                <form method="POST" action="{{ route('jury.practicas.submitGrade', $practica) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                        <div class="md:col-span-1">
                                            <label for="final_grade" class="block font-medium text-sm text-gray-700">Calificación Final (0-20)</label>
                                            <input id="final_grade" name="final_grade" type="number" min="0" max="20" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required />
                                            <p class="text-xs text-gray-500 mt-1">Mínimo aprobatorio: 11</p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label for="file_f5" class="block font-medium text-sm text-gray-700">Formato F5 (Acta de Exposición)</label>
                                            <input id="file_f5" name="file_f5" type="file" class="block mt-1 w-full text-sm" required />
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end mt-8">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                            Guardar Calificación y Finalizar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-semibold mb-4 text-gray-900">Esperando Calificación</h3>
                                <p class="text-sm text-gray-600">El Presidente del jurado es el encargado de registrar la calificación final y subir el Acta (Formato F5) después de la sustentación.</p>
                            </div>
                        </div>
                    @endif
                @endif
                @if ($practica->status == 'pending_jury_review')
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4 text-gray-900">Estado de la Revisión del Jurado</h3>
                            <ul class="divide-y divide-gray-200">
                                @foreach ($allAssignments as $assignment)
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $assignment->juradoMember->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $assignment->role }}</p>
                                    </div>
                                    <div>
                                        @if ($assignment->report_approved === true)
                                            <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-green-100 text-green-800">Aprobado (V°B°)</span>
                                        @elseif ($assignment->report_approved === false)
                                            <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-red-100 text-red-800">Observado</span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-gray-100 text-gray-800">Pendiente</span>
                                        @endif
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    @if ($myAssignment->report_approved === null)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-yellow-500">
                            <div class="p-6 bg-white">
                                <h3 class="text-lg font-semibold mb-4 text-yellow-800">Emitir Visto Bueno (V°B°) u Observación</h3>
                                <p class="text-sm text-gray-600 mb-4">Por favor, revise los documentos y registre su decisión. Si usted observa el informe, el proceso se detendrá para el estudiante.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div class="p-4 border border-green-200 rounded-lg">
                                        <h5 class="text-md font-semibold text-green-700">Aprobar (V°B°)</h5>
                                        <p class="text-xs text-gray-600 mb-3">Confirmo que el informe es correcto y doy mi Visto Bueno.</p>
                                        <form action="{{ route('jury.practicas.submitReportVote', $practica) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="vote_type" value="approve">
                                            <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                                Dar Visto Bueno
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <div class="p-4 border border-red-200 rounded-lg">
                                        <h5 class="text-md font-semibold text-red-700">Observar Informe</h5>
                                        <form action="{{ route('jury.practicas.submitReportVote', $practica) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="vote_type" value="observe">
                                            <label for="observation_notes" class="text-xs text-gray-600 mb-1 block">Razón de la observación (Requerido):</label>
                                            <textarea id="observation_notes" name="observation_notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm" required>{{ old('observation_notes') }}</textarea>
                                            <button type="submit" class="w-full mt-3 inline-flex justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                                Enviar Observación
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 bg-white border-b border-gray-200">
                                <h3 class="text-lg font-semibold mb-4 text-gray-900">Voto Registrado</h3>
                                <p class="text-sm text-gray-600">
                                    Usted ya ha emitido su voto para este informe. Esperando a los demás miembros del jurado.
                                </p>
                            </div>
                        </div>
                    @endif
                    
                @endif
                </div>

            <div class="md:col-span-1 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Composición del Jurado</h3>
                        <dl>
                            @foreach ($allAssignments as $assignment)
                                <div class="mb-3 pb-3 border-b border-gray-100 last:border-b-0">
                                    <dt class="text-sm font-medium text-gray-500">{{ $assignment->role }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900 @if($assignment->role == 'Presidente') font-bold @endif">
                                        {{ $assignment->juradoMember->name }}
                                        @if ($assignment->juradoMember->id == Auth::id())
                                            <span class="ml-2 text-xs text-primary-600 font-semibold">(Usted)</span>
                                        @endif
                                    </dd>
                                    <dd class="mt-1 text-sm">
                                        @if ($assignment->report_approved === true)
                                            <span class="font-medium text-green-600">V°B° Registrado</span>
                                        @elseif ($assignment->report_approved === false)
                                            <span class="font-medium text-red-600">Observado</span>
                                        @elseif ($practica->status == 'pending_jury_review')
                                            <span class="text-gray-500">Voto Pendiente</span>
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                            </dl>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Datos del Estudiante</h3>
                        <dl>
                            <div class="mb-2">
                                <dt class="text-sm font-medium text-gray-500">Estudiante</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $practica->student->name }}</dd>
                            </div>
                            <div class="mb-2">
                                <dt class="text-sm font-medium text-gray-500">Asesor</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $practica->advisor->name }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>