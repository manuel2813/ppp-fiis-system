<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Jurado - Mis Prácticas Asignadas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">Prácticas para Evaluar ({{ $assignments->count() }})</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mi Rol</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado de la Práctica</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($assignments as $assignment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $assignment->practica->student->name }}
                                            <span class="block text-xs text-gray-500">{{ $assignment->practica->entity_name }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span class="font-semibold">{{ $assignment->role }}</span>
                                        </td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
    @if($assignment->practica->status == 'pending_jury_review')
        <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-yellow-100 text-yellow-800">Pendiente Revisión (F3)</span>
    
    @elseif($assignment->practica->status == 'jury_observed')
        <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-red-100 text-red-800">Informe Observado</span>
    
    @elseif($assignment->practica->status == 'pending_defense_date')
        <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-blue-100 text-blue-800">Aprobado (Esperando Fecha)</span>
    
    @elseif($assignment->practica->status == 'defense_scheduled')
        <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-primary-600 text-white">Sustentación Programada</span>
    
    @elseif($assignment->practica->status == 'completed')
        <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-green-100 text-green-800">Completado</span>
    @else
        <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-gray-100 text-gray-800">{{ $assignment->practica->status }}</span>
    @endif
</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('jury.practicas.show', $assignment->practica) }}" class="font-semibold text-primary-700 hover:text-primary-900">Revisar Informe</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No tiene prácticas asignadas para evaluar.
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