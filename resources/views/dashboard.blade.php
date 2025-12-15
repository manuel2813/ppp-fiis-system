<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @can('isCPPP')
                        <h3 class="text-lg font-semibold">Panel de Administración (CPPP)</h3>
                        <p class="mb-4 text-gray-600">Bienvenido. Accede al panel para gestionar las solicitudes pendientes.</p>
                        <a href="{{ route('cppp.dashboard.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Ir al Dashboard CPPP
                        </a>
                    @endcan
                    
                    @can('isAsesor')
                        <h3 class="text-lg font-semibold">Panel de Asesor</h3>
                        <p class="mb-4 text-gray-600">Bienvenido. Accede al panel para gestionar la supervisión (F2) de sus estudiantes.</p>
                        <a href="{{ route('asesor.dashboard.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Ir al Dashboard Asesor
                        </a>
                    @endcan

                    @can('isJurado')
                        <h3 class="text-lg font-semibold">Panel de Jurado</h3>
                        <p class="mb-4 text-gray-600">Bienvenido. Accede al panel para revisar los informes finales asignados.</p>
                        <a href="{{ route('jury.dashboard.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Ir al Dashboard Jurado
                        </a>
                    @endcan

                    @can('isDecano')
                        <h3 class="text-lg font-semibold">Panel de Decanatura</h3>
                        <p class="mb-4 text-gray-600">Bienvenido. Desde aquí puede acceder al panel para emitir resoluciones pendientes.</p>
                        <div class="flex space-x-4">
                            <a href="{{ route('decano.dashboard.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Emitir Resoluciones
                            </a>
                        </div>
                    @endcan

                    @can('isEstudiante')
                            <h3 class="text-lg font-semibold mb-4">Estado de mis Prácticas Preprofesionales</h3>

                            
                            @if (isset($practicas) && $practicas->isNotEmpty())
                                
                                @foreach($practicas as $practica)
                                <div class="border border-gray-200 rounded-lg p-4 {{ !$loop->first ? 'mt-6' : '' }}">
                                    <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Entidad</dt>
                                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $practica->entity_name }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">
                                                @if($practica->practice_type == 'validacion_laboral')
                                                    Tipo de Trámite
                                                @else
                                                    Fecha de Solicitud
                                                @endif
                                            </dt>
                                            <dd class="mt-1 text-sm font-semibold text-gray-900">
                                                @if($practica->practice_type == 'validacion_laboral')
                                                    Validación Laboral
                                                @else
                                                    {{ $practica->created_at->format('d/m/Y') }}
                                                @endif
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">Estado Actual</dt>
                                            <dd class="mt-1 text-sm font-semibold">
                                                
                                                @php $status = $practica->status; @endphp
                                                
                                                {{-- Aquí van todas tus insignias de estado --}}
                                                @if ($status == 'in_review_initial' || $status == 'pending_jury_review')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-yellow-100 text-yellow-800">En Revisión</span>
                                                @elseif ($status == 'pending_extension')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-yellow-100 text-yellow-800">Ampliación en Revisión</span>
                                                @elseif ($status == 'initial_observed' || $status == 'final_report_observed' || $status == 'jury_observed')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-red-100 text-red-800">Observado</span>
                                                @elseif ($status == 'initial_approved')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-green-100 text-green-800">En Curso
                                                        @if($practica->extension_count > 0)
                                                            ({{ $practica->extension_count }}ª Amp.)
                                                        @endif
                                                    </span>
                                                @elseif ($status == 'pending_advisor_dictamen')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-blue-100 text-blue-800">Pendiente Dictamen Asesor</span>
                                                @elseif ($status == 'pending_dean_resolution')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-blue-100 text-blue-800">Pendiente Resolución</span>
                                                @elseif ($status == 'pending_jury_assignment')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-purple-100 text-purple-800">Pendiente Asignar Jurado</span>
                                                @elseif ($status == 'pending_defense_date')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-green-100 text-green-800">Aprobado (Esperando Fecha)</span>
                                                @elseif ($status == 'defense_scheduled')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-primary-600 text-white">Sustentación Programada</span>
                                                @elseif ($status == 'completed_approved')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-primary-700 text-white">APROBADO</span>
                                                @elseif ($status == 'completed_failed')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-red-700 text-white">DESAPROBADO</span>
                                                @elseif ($status == 'completed')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-gray-800 text-white">Proceso Finalizado</span>
                                                @elseif ($status == 'annulled')
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-red-800 text-white">PRÁCTICA ANULADA</span>
                                                @else
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-gray-100 text-gray-800">{{ $status }}</span>
                                                @endif
                                                </dd>
                                        </div>
                                    </dl>

                                    
                                    @if(in_array($practica->status, ['initial_observed', 'initial_approved', 'final_report_observed', 'jury_observed', 'defense_scheduled', 'pending_extension']))
                                        
                                        @if ($practica->status == 'initial_observed')
                                            <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-md">
                                                <h4 class="text-md font-semibold text-red-800">Observaciones de la CPPP:</h4>
                                                <p class="mt-2 text-sm text-red-700 whitespace-pre-wrap">{{ $practica->observation_notes }}</p>
                                                <div class="mt-4"><a href="{{ route('practicas.edit', $practica) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Corregir Solicitud</a></div>
                                            </div>
                                        @endif

                                        @if ($practica->status == 'initial_approved')
                                            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-md">
                                                <h4 class="text-md font-semibold text-green-800">¡Trámite Aprobado!</h4>
                                                <p class="mt-2 text-sm text-green-700">Siguiente paso: Subir su Informe Final (F3), Ficha de Evaluación (F4) y Constancia.</p>
                                                <div class="mt-4"><a href="{{ route('practicas.final_report.create', $practica) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700">Subir Informe Final</a></div>
                                            </div>
                                            
                                            @if ($practica->extension_count < 2)
                                                <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-md">
                                                    <h4 class="text-md font-semibold text-gray-800">Solicitar Ampliación</h4>
                                                    <p class="mt-2 text-sm text-gray-700">
                                                        Si necesita extender su período de prácticas (máx. 2 veces), puede solicitarlo aquí.
                                                        (Solicitudes realizadas: {{ $practica->extension_count }} de 2)
                                                    </p>
                                                    <div class="mt-4">
                                                        <a href="{{ route('practicas.extension.create', $practica) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                                            Solicitar Ampliación
                                                        </a>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                        
                                        @if ($practica->status == 'final_report_observed' || $practica->status == 'jury_observed')
                                            <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-md">
                                                <h4 class="text-md font-semibold text-red-800">
                                                    {{ $practica->status == 'jury_observed' ? 'Observaciones del Jurado (Informe Final):' : 'Observaciones del Asesor (Informe Final):' }}
                                                </h4>
                                                <p class="mt-2 text-sm text-red-700 whitespace-pre-wrap">{{ $practica->observation_notes }}</p>
                                                <div class="mt-4"><a href="{{ route('practicas.final_report.edit', $practica) }}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Corregir Informe Final</a></div>
                                            </div>
                                        @endif

                                        @if ($practica->status == 'defense_scheduled')
                                            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                                                <h4 class="text-md font-semibold text-blue-800">¡Sustentación Programada!</h4>
                                                <p class="mt-2 text-sm text-blue-700">Su acto público de sustentación ha sido programado:</p>
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
                                        @endif
                                    
                                    
                                    @elseif (in_array($practica->status, ['completed_approved', 'completed_failed', 'completed', 'annulled']))
                                        
                                        @if ($practica->status == 'annulled')
                                            <div class="mt-6 p-4 bg-red-800 border border-red-900 rounded-md text-white">
                                                <h4 class="text-md font-semibold">Práctica Anulada</h4>
                                                <p class="mt-2 text-sm">La CPPP ha anulado esta práctica por la siguiente razón:</p>
                                                <p class="mt-2 text-sm font-semibold whitespace-pre-wrap">{{ $practica->annulment_reason }}</p>
                                                <p class="mt-4 text-sm">Debe iniciar un nuevo trámite de prácticas.</p>
                                            </div>
                                        
                                        @else
                                            <div class="mt-6 p-4 {{ $practica->status == 'completed_failed' ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' }} rounded-md">
                                                
                                                @if ($practica->status == 'completed_failed')
                                                    <h4 class="text-md font-semibold text-red-800">Resultado: Desaprobado</h4>
                                                @else
                                                    <h4 class="text-md font-semibold text-green-800">¡Felicidades! Ha Aprobado</h4>
                                                @endif

                                                <dl class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <dt class="text-sm font-medium text-gray-500">Calificación Final</dt>
                                                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $practica->final_grade }} / 20</dd>
                                                    </div>
                                                    <div>
                                                        <dt class="text-sm font-medium text-gray-500">Fecha de Sustentación</dt>
                                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($practica->defense_date)->format('d/m/Y') }}</dd>
                                                    </div>
                                                </dl>
                                                
                                               @if ($practica->status == 'completed' || $practica->compliance_certificate_issued)
 <p class="mt-4 text-sm font-bold text-green-700">¡Constancia Disponible!</p>
 <a href="{{ route('practicas.downloadConstancia', $practica) }}" 
  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 mt-2">
  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
 Descargar Constancia
</a>
 @elseif ($practica->status == 'completed_approved')
 <p class="mt-4 text-sm text-green-700">La CPPP emitirá su Constancia de Cumplimiento en breve.</p>
 @else
 <p class="mt-4 text-sm text-red-700">Según la directiva, tiene una oportunidad de volver a sustentar. Contacte a la CPPP.</p>
 @endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                                @endforeach

                            @endif 


                            
                            @if (!isset($can_start_new_practica) || $can_start_new_practica)
                                
                                <div class="{{ (isset($practicas) && $practicas->isNotEmpty()) ? 'mt-8' : '' }}">
                                    
                                    @if (!isset($practicas) || $practicas->isEmpty())
                                        <p class="mb-4 text-gray-600">Seleccione el tipo de trámite que desea iniciar:</p>
                                    @else
                                        {{-- Este texto se muestra si tiene puros anulados --}}
                                        <p class="mb-4 text-gray-600">Puede iniciar un nuevo trámite:</p>
                                    @endif

                                    <div class="flex space-x-4">
                                        <a href="{{ route('practicas.create') }}" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Iniciar Solicitud (Regular)
                                        </a>
                                        <a href="{{ route('practicas.validation.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Solicitar Validación Laboral
                                        </a>
                                    </div>
                                </div>

                            @endif
                          

                        @endcan

                </div>
            </div>
        </div>
    </div>
</x-app-layout>