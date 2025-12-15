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

                        <p class="mb-4">Bienvenido. Accede al panel para gestionar las solicitudes pendientes.</p>

                        <a href="{{ route('cppp.dashboard.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">

                            Ir al Dashboard CPPP

                        </a>

                    @endcan

                   

                    @can('isAsesor')

                        <h3 class="text-lg font-semibold">Panel de Asesor</h3>

                        <p class="mb-4">Bienvenido. Accede al panel para gestionar la supervisión (F2) de sus estudiantes.</p>

                        <a href="{{ route('asesor.dashboard.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">

                            Ir al Dashboard Asesor

                        </a>

                    @endcan @can('isJurado')

                        <h3 class="text-lg font-semibold">Panel de Jurado</h3>

                        <p class="mb-4">Bienvenido. Accede al panel para revisar los informes finales asignados.</p>

                        <a href="{{ route('jury.dashboard.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">

                            Ir al Dashboard Jurado

                        </a>

                    @endcan



                    @can('isEstudiante')

                        <h3 class="text-lg font-semibold mb-4">Estado de mis Prácticas Preprofesionales</h3>



                        @if (isset($practica))

                            <div class="border border-gray-200 rounded-lg p-4">

                                <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                    <div>

                                        <dt class="text-sm font-medium text-gray-500">Entidad</dt>

                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $practica->entity_name }}</dd>

                                    </div>

                                    <div>

                                        <dt class="text-sm font-medium text-gray-500">Fecha de Solicitud</dt>

                                        <dd class="mt-1 text-sm text-gray-900">{{ $practica->created_at->format('d/m/Y') }}</dd>

                                    </div>

                                    <div>

                                        <dt class="text-sm font-medium text-gray-500">Estado Actual</dt>

                                        <dd class="mt-1 text-sm font-semibold">

                                           

                                            @if (in_array($practica->status, ['in_review_initial', 'pending_jury_review']))

                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full">En Revisión</span>

                                            @elseif ($practica->status == 'initial_observed')

                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">Solicitud Observada</span>

                                            @elseif ($practica->status == 'initial_approved')

                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">Trámite Aprobado</span>

                                            @elseif ($practica->status == 'pending_advisor_dictamen')

                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full">Pendiente Dictamen Asesor</span>

                                            @elseif ($practica->status == 'final_report_observed' || $practica->status == 'jury_observed')

                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full">Informe Final Observado</span>

                                            @elseif ($practica->status == 'pending_jury_assignment')

                                                <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full">Pendiente Asignar Jurado</span>

                                            @elseif ($practica->status == 'pending_defense_date')

                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full">Aprobado (Esperando Fecha)</span>

                                            @elseif ($practica->status == 'defense_scheduled')

                                                <span class="px-2 py-1 bg-green-700 text-white rounded-full">Sustentación Programada</span>

                                            @elseif ($practica->status == 'completed_approved')

                                                <span class="px-2 py-1 bg-green-700 text-white rounded-full">APROBADO</span>

                                            @elseif ($practica->status == 'completed_failed')

                                                <span class="px-2 py-1 bg-red-700 text-white rounded-full">DESAPROBADO</span>

                                            @elseif ($practica->status == 'completed')

                                                <span class="px-2 py-1 bg-gray-800 text-white rounded-full">Proceso Finalizado</span>

                                            @elseif ($practica->status == 'annulled')

                                                <span class="px-2 py-1 bg-red-800 text-white rounded-full">PRÁCTICA ANULADA</span>

                                            @else

                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full">{{ $practica->status }}</span>

                                            @endif

                                        </dd>

                                    </div>

                                </dl>



                                @if(in_array($practica->status, ['initial_observed', 'initial_approved', 'final_report_observed', 'jury_observed', 'defense_scheduled']))

                                   

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

                                            <div class="mt-4"><a href="{{ route('practicas.final_report.create', $practica) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">Subir Informe Final</a></div>

                                        </div>

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

                                           

                                            @if ($practica->status == 'completed')

                                                <p class="mt-4 text-sm font-bold text-green-700">Su Constancia de Cumplimiento ha sido emitida. Proceso finalizado.</p>

                                            @elseif ($practica->status == 'completed_approved')

                                                <p class="mt-4 text-sm text-green-700">La CPPP emitirá su Constancia de Cumplimiento en breve.</p>

                                            @else

                                                <p class="mt-4 text-sm text-red-700">Según la directiva, tiene una oportunidad de volver a sustentar. Contacte a la CPPP.</p>

                                            @endif

                                        </div>

                                    @endif

                                @endif

                                </div>



                        @else

                            <p class="mb-4">Aún no has iniciado tu trámite de Prácticas Preprofesionales.</p>

                            <a href="{{ route('practicas.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">

                                Iniciar Solicitud de PPP

                            </a>

                        @endif

                    @endcan



                </div>

            </div>

        </div>

    </div>

</x-app-layout>