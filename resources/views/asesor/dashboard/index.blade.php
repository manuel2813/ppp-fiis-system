<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Asesor - Mis Estudiantes
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">Prácticas Asignadas ({{ $practicas->count() }})</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entidad</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($practicas as $practica)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $practica->student->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $practica->entity_name }}</td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if ($practica->status == 'in_review_initial' || $practica->status == 'initial_observed')
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-gray-100 text-gray-800">Trámite en Revisión</span>
                                            
                                            @elseif ($practica->status == 'initial_approved')
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-green-100 text-green-800">En Curso</span>
                                            
                                            @elseif ($practica->status == 'pending_advisor_dictamen')
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-yellow-100 text-yellow-800">Pendiente Dictamen (F3)</span>
                                            
                                            @elseif ($practica->status == 'final_report_observed')
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-red-100 text-red-800">Informe Observado (F3)</span>

                                            @elseif (in_array($practica->status, ['pending_jury_assignment', 'pending_jury_review', 'pending_defense_date', 'defense_scheduled']))
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-purple-100 text-purple-800">En Proceso (Jurado)</span>
                                            
                                            @elseif (in_array($practica->status, ['completed_approved', 'completed', 'completed_failed', 'annulled']))
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-gray-800 text-white">Finalizado</span>
                                                
                                            @else
                                                <span class="px-3 py-1 text-xs font-medium rounded-full inline-block bg-gray-100 text-gray-800">{{ $practica->status }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('asesor.practicas.show', $practica) }}" class="font-semibold text-primary-700 hover:text-primary-900">Gestionar Práctica</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No tiene estudiantes asignados para supervisión.
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