<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Revisión de Práctica Preprofesional
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="md:col-span-2 space-y-6"> @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <strong class="font-bold">¡Error!</strong>
                        <span class="block sm:inline">Por favor, corrija los siguientes errores:</span>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>- {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Detalles de la Práctica</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Estudiante</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $practica->student->name }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Código de Estudiante</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $practica->student->code ?? 'N/A' }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Entidad Receptora</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $practica->entity_name }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Asesor</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $practica->advisor->name }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Fecha de Inicio</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($practica->start_date)->format('d/m/Y') }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Fecha de Término</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($practica->end_date)->format('d/m/Y') }}</dd>
                            </div>
                            
                            @if ($practica->resolution_number)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Resolución de Autorización</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $practica->resolution_number }}</dd>
                            </div>
                            @endif

                             <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Estado Actual</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full font-semibold">{{ $practica->status }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if ($practica->status == 'in_review_initial')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4 text-red-700">Observar Solicitud Inicial</h3>
                            <form action="{{ route('cppp.practicas.observe', $practica) }}" method="POST">
                                @csrf
                                <div>
                                    <label for="observation_notes" class="block font-medium text-sm text-gray-700">Razón de la Observación (Requerido)</label>
                                    <textarea id="observation_notes" name="observation_notes" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('observation_notes') }}</textarea>
                                    <p class="mt-2 text-sm text-gray-500">El estudiante verá este mensaje y deberá corregir.</p>
                                </div>
                                <div class="flex justify-end mt-4">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                        Enviar Observación
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                @if ($practica->status == 'pending_jury_assignment')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4 text-purple-800">Asignar Jurado Evaluador</h3>
                            <form method="POST" action="{{ route('cppp.practicas.assignJury', $practica) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="presidente_id" class="block font-medium text-sm text-gray-700">Presidente</label>
                                        <select id="presidente_id" name="presidente_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                            <option value="" disabled selected>Seleccione un Presidente...</option>
                                            @foreach($jurados as $docente)
                                                <option value="{{ $docente->id }}" {{ old('presidente_id') == $docente->id ? 'selected' : '' }}>
                                                    {{ $docente->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="suplente_id" class="block font-medium text-sm text-gray-700">Suplente (Opcional)</label>
                                        <select id="suplente_id" name="suplente_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                            <option value="" disabled {{ old('suplente_id') ? '' : 'selected' }}>Seleccione un Suplente...</option>
                                            @foreach($jurados as $docente)
                                                <option value="{{ $docente->id }}" {{ old('suplente_id') == $docente->id ? 'selected' : '' }}>
                                                    {{ $docente->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="miembro1_id" class="block font-medium text-sm text-gray-700">Miembro 1</label>
                                        <select id="miembro1_id" name="miembro1_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                            <option value="" disabled selected>Seleccione un Miembro...</option>
                                            @foreach($jurados as $docente)
                                                <option value="{{ $docente->id }}" {{ old('miembro1_id') == $docente->id ? 'selected' : '' }}>
                                                    {{ $docente->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="miembro2_id" class="block font-medium text-sm text-gray-700">Miembro 2</label>
                                        <select id="miembro2_id" name="miembro2_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                            <option value="" disabled selected>Seleccione un Miembro...</option>
                                            @foreach($jurados as $docente)
                                                <option value="{{ $docente->id }}" {{ old('miembro2_id') == $docente->id ? 'selected' : '' }}>
                                                    {{ $docente->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label for="file_resolution" class="block font-medium text-sm text-gray-700">Resolución de Designación de Jurado (PDF)</label>
                                        <input id="file_resolution" class="block mt-1 w-full text-sm" type="file" name="file_resolution" required />
                                    </div>
                                </div>
                                <div class="flex items-center justify-end mt-8">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700">
                                        Asignar Jurado y Guardar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
                
                @if ($practica->status == 'pending_defense_date')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4 text-blue-800">Programar Sustentación (Acto Público)</h3>
                            <form method="POST" action="{{ route('cppp.practicas.scheduleDefense', $practica) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="defense_date" class="block font-medium text-sm text-gray-700">Fecha y Hora</label>
                                        <input id="defense_date" name="defense_date" type="datetime-local" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required />
                                    </div>
                                    <div>
                                        <label for="defense_place" class="block font-medium text-sm text-gray-700">Lugar (o Enlace Virtual)</label>
                                        <input id="defense_place" name="defense_place" type="text" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" placeholder="Ej: Auditorio FIIS" required />
                                    </div>
                                </div>
                                <div class="flex items-center justify-end mt-8">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                        Programar y Notificar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
                
                @if ($practica->status == 'completed_approved')
    {{-- Muestra el botón SÓLO si aún no se ha emitido --}}
    @if (!$practica->compliance_certificate_issued)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h3 class="text-lg font-semibold mb-4 text-green-800">Emitir Constancia de Cumplimiento</h3>
                <p class="text-sm text-gray-600 mb-6">El estudiante ha APROBADO con nota <span class="font-bold text-lg">{{ $practica->final_grade }}</span>.</p>
                <form method="POST" action="{{ route('cppp.practicas.issueCertificate', $practica) }}">
                    @csrf
                    <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-green-600 ...">
                        Marcar como "Constancia Emitida" y Finalizar Proceso
                    </button>
                </form>
            </div>
        </div>
    @endif
@endif

{{-- Muestra "Proceso Finalizado" si el estado es 'completed' O si la bandera es 'true' --}}
@if ($practica->status == 'completed' || $practica->compliance_certificate_issued)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <h3 class="text-lg font-semibold mb-4 text-gray-900">Proceso Finalizado</h3>
            <p class="text-sm text-gray-600">
                La Constancia de Cumplimiento para este estudiante ya ha sido registrada.
                @if($practica->constancia_emitted_at)
                    (Fecha de emisión: {{ \Carbon\Carbon::parse($practica->constancia_emitted_at)->format('d/m/Y') }})
                @endif
            </p>
        </div>
    </div>
@endif
                
                @if ($practica->status == 'completed_failed')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4 text-red-800">Autorizar Segunda Sustentación</h3>
                            <p class="text-sm text-gray-600 mb-6">El estudiante ha DESAPROBADO con nota <span class="font-bold text-lg">{{ $practica->final_grade }}</span>.</p>
                            <form method="POST" action="{{ route('cppp.practicas.allowSecondAttempt', $practica) }}">
                                @csrf
                                <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                                    Autorizar Segunda Oportunidad
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                @if ($practica->status == 'pending_extension')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-yellow-400">
                        <div class="p-6 bg-white">
                            <h3 class="text-lg font-semibold mb-4 text-yellow-800">Solicitud de Ampliación Pendiente</h3>
                            <p class="text-sm text-gray-600 mb-6">
                                El estudiante solicita ampliar su fecha de fin al 
                                <span class="font-bold">{{ \Carbon\Carbon::parse($practica->pending_extension_date)->format('d/m/Y') }}</span>.
                                (Ampliación N° {{ $practica->extension_count + 1 }})
                            </p>
                            
                            @php
                                $letter = $practica->documents->where('type', 'CARTA_AMPLIACION_' . ($practica->extension_count + 1))->first();
                            @endphp
                            @if ($letter)
                                <a href="{{ route('cppp.documentos.download', $letter) }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                    Descargar Carta de Ampliación
                                </a>
                            @else
                                <span class="text-red-500">Error: No se encontró la carta.</span>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div class="p-4 border border-green-200 rounded-lg">
                                    <h5 class="text-md font-semibold text-green-700">Aprobar Ampliación</h5>
                                    <form action="{{ route('cppp.practicas.approveExtension', $practica) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full inline-flex justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                            Aprobar y Actualizar Fecha
                                        </button>
                                    </form>
                                </div>
                                <div class="p-4 border border-red-200 rounded-lg">
                                    <h5 class="text-md font-semibold text-red-700">Rechazar Ampliación</h5>
                                    <form action="{{ route('cppp.practicas.rejectExtension', $practica) }}" method="POST">
                                        @csrf
                                        <label for="observation_notes" class="text-xs text-gray-600 mb-1 block">Razón del rechazo:</label>
                                        <textarea id="observation_notes" name="observation_notes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm" required>{{ old('observation_notes') }}</textarea>
                                        <button type="submit" class="w-full mt-3 inline-flex justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                            Rechazar Solicitud
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if (!in_array($practica->status, ['completed', 'annulled']))
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-4 border-red-500">
                        <div class="p-6 bg-white">
                            <h3 class="text-lg font-semibold mb-4 text-red-800">Zona de Peligro - Anulación</h3>
                            <p class="text-sm text-gray-600 mb-6">
                                Anulará la práctica por completo (abandono, indisciplina, etc.).
                                Esta acción es irreversible.
                            </p>
                            <form method="POST" action="{{ route('cppp.practicas.annul', $practica) }}" onsubmit="return confirm('¿Está ABSOLUTAMENTE SEGURO de anular esta práctica? Esta acción no se puede deshacer.');">
                                @csrf
                                <label for="annulment_reason" class="block font-medium text-sm text-gray-700">Razón de la Anulación (Requerida)</label>
                                <textarea id="annulment_reason" name="annulment_reason" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('annulment_reason') }}</textarea>
                                <button type="submit" class="w-full mt-4 inline-flex justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-800">
                                    Anular Práctica Permanentemente
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>

            <div class="md:col-span-1 space-y-6"> <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Documentos Adjuntos</h3>
                        
                        @php
                            $documentLabels = [
                                'SUT' => 'Solicitud Única de Trámite',
                                'F1_PLAN' => 'Formato F1 (Plan)',
                                'CARTA_ACEPTACION' => 'Carta de Aceptación',
                                'F2_SUPERVISION' => 'Formato F2 (Supervisión)',
                                'F3_INFORME_FINAL' => 'Informe Final (F3)',
                                'F4_EVALUACION_ENTIDAD' => 'Ficha Evaluación (F4)',
                                'CONSTANCIA_ENTIDAD' => 'Constancia de Entidad',
                                'RESOLUCION_JURADO' => 'Resolución de Jurado',
                                'CARTA_AMPLIACION_1' => '1ª Carta Ampliación',
                                'CARTA_AMPLIACION_2' => '2ª Carta Ampliación',
                                'F5_ACTA_EXPOSICION' => 'Acta de Exposición (F5)',
                                // Documentos de Validación Laboral
                                'CERTIFICACION_PROGRESIVA' => 'Certificación Progresiva',
                            ];
                        @endphp

                        <ul role="list" class="divide-y divide-gray-200">
                            @forelse($practica->documents->sortBy('upload_date') as $document)
                                <li class="py-3 flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-700">
                                        {{ $documentLabels[$document->type] ?? $document->type }}
                                    </span>
                                    <a href="{{ route('cppp.documentos.download', $document) }}" class="ml-4 font-medium text-indigo-600 hover:text-indigo-800">
                                        Descargar
                                    </a>
                                </li>
                            @empty
                                <li class="py-3 text-sm text-gray-500">No hay documentos adjuntos.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                @if ($practica->status == 'in_review_initial')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4 text-green-700">Proponer a Decanato</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Si los 3 documentos son correctos, eleve esta solicitud al Decanato
                                para la emisión de la Resolución de Autorización.
                            </p>
                            
                            <form action="{{ route('cppp.practicas.approve', $practica) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full mt-4 inline-flex justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                    Proponer para Resolución
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>