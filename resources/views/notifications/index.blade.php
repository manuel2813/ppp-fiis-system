<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mis Notificaciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900">
                        Nuevas Notificaciones
                    </h3>

                    <div class="mt-4 space-y-3">
                        @forelse ($unreadNotifications as $notification)
                            <div class="p-3 bg-gray-50 rounded-md border border-gray-200">
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $notification->data['message'] }}
                                </p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xs text-gray-500">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                    <a href="{{ route('notifications.read', $notification->id) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                        Ver y Marcar como leído
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No tienes notificaciones nuevas.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900">
                        Historial (Leídas)
                    </h3>

                    <div class="mt-4 space-y-3">
                        @forelse ($readNotifications as $notification)
                            <div class="p-3 bg-white rounded-md border border-gray-100 opacity-75">
                                <p class="text-sm text-gray-600">
                                    {{ $notification->data['message'] }}
                                </p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xs text-gray-400">
                                        {{ $notification->read_at->diffForHumans() }}
                                    </span>
                                    <a href="{{ $notification->data['url'] ?? '#' }}" class="text-sm text-gray-500 hover:text-gray-700">
                                        Ir al enlace
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No tienes notificaciones leídas.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>