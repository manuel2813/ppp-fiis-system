<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard CPPP
            </h2>
            
            <!-- Botón "Gestionar Usuarios" con color primary -->
            <a href="{{ route('cppp.usuarios.index') }}" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Gestionar Usuarios
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- TABLA 1: PENDIENTES DE ACCIÓN -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">Solicitudes Pendientes de Acción ({{ $pendingPracticas->count() }})</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <!-- Encabezado de tabla estilizado -->
                            <thead class="bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($pendingPracticas as $practica)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $practica->student->name }}
                                            <span class="block text-xs text-gray-500">{{ $practica->entity_name }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <!-- Insignias de estado estandarizadas -->
                                            @if($practica->status == 'in_review_initial')
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-yellow-100 text-yellow-800">Revisión Inicial</span>
                                            @elseif($practica->status == 'pending_jury_assignment')
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-purple-100 text-purple-800">Asignar Jurado</span>
                                            @elseif($practica->status == 'pending_defense_date')
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-blue-100 text-blue-800">Programar Fecha</span>
                                            @elseif($practica->status == 'pending_extension')
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-yellow-100 text-yellow-800">Revisión Ampliación</span>
                                            @elseif($practica->status == 'pending_dean_resolution')
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-blue-100 text-blue-800">Pendiente Resolución</span>
                                            @else
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-gray-100 text-gray-800">{{ $practica->status }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <!-- Enlace "Revisar" con color primary -->
                                            <a href="{{ route('cppp.practicas.show', $practica) }}" class="font-semibold text-primary-700 hover:text-primary-900">Revisar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No hay solicitudes pendientes de acción.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TABLA 2: PENDIENTES DE CONSTANCIA -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">Sustentaciones Aprobadas (Pendientes de Constancia) ({{ $certificatePendingPracticas->count() }})</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nota Final</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
 @forelse ($certificatePendingPracticas as $practica)


 @if (!$practica->compliance_certificate_issued)
<tr>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
 {{ $practica->student->name }}
 <span class="block text-xs text-gray-500">{{ $practica->entity_name }}</span>
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-700"> 
    {{ $practica->final_grade }} / 20
 </td>
 <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
 <a href="{{ route('cppp.practicas.show', $practica) }}" class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
 Emitir Constancia
 </a>
 </td>
                                    </tr>
         @endif 
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No hay prácticas pendientes de emitir constancia.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
            <div class="p-6 bg-white border-b border-gray-200">
                <h3 class="text-lg font-semibold mb-4 text-gray-900">Historial de Prácticas Finalizadas ({{ $finishedPracticas->count() }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Final</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($finishedPracticas as $practica)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $practica->student->name }}
                                        <span class="block text-xs text-gray-500">{{ $practica->entity_name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($practica->status == 'completed')
                                            <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-green-100 text-green-800">Finalizada ({{ $practica->final_grade }}/20)</span>
                                        @elseif($practica->status == 'annulled')
                                            <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-red-100 text-red-800">Anulada</span>
                                        @elseif($practica->status == 'completed_failed')
                                             <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-red-100 text-red-800">Desaprobada ({{ $practica->final_grade }}/20)</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('cppp.practicas.show', $practica) }}" class="font-semibold text-primary-700 hover:text-primary-900">Ver Detalles</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        No hay prácticas en el historial.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </div>
        
    </div>
</x-app-layout>