<x-app-layout>
    <x-slot name="header">
        <!-- INICIO: HEADER CON BOTÓN DE VOLVER -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('cppp.usuarios.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Usuario: {{ $user->name }}
            </h2>
        </div>
        <!-- FIN: HEADER CON BOTÓN DE VOLVER -->
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                
                <form method="POST" action="{{ route('cppp.usuarios.update', $user) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-6 bg-white border-b border-gray-200">
                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>- {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="name" class="block font-medium text-sm text-gray-700">Nombre Completo</label>
                                <input id="name" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus />
                            </div>
                            <div>
                                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                                <input id="email" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="email" name="email" value="{{ old('email', $user->email) }}" required />
                            </div>
                            <div>
                                <label for="role_id" class="block font-medium text-sm text-gray-700">Rol del Usuario</label>
                                <select id="role_id" name="role_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled>Seleccione un rol...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ (old('role_id', $user->role_id) == $role->id) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label for="code" class="block font-medium text-sm text-gray-700">Código (Opcional)</label>
                                <input id="code" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="text" name="code" value="{{ old('code', $user->code) }}" />
                                <p class="text-xs text-gray-500 mt-1">Ej: Código de estudiante o docente.</p>
                            </div>
                            <div>
                                <label for="password" class="block font-medium text-sm text-gray-700">Nueva Contraseña</label>
                                <input id="password" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="password" name="password" />
                                <p class="text-xs text-gray-500 mt-1">Dejar en blanco para no cambiar.</p>
                            </div>
                            <div>
                                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirmar Nueva Contraseña</label>
                                <input id="password_confirmation" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="password" name="password_confirmation" />
                            </div>
                        </div>

                        <!-- INICIO: BOTONES DE ACCIÓN (ACTUALIZADO) -->
                        <div class="flex items-center justify-between mt-8">
                            <!-- Botón de Cancelar -->
                            <a href="{{ route('cppp.usuarios.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Cancelar
                            </a>
                            
                            <!-- Botón de Actualizar (con color primary) -->
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800">
                                Actualizar Usuario
                            </button>
                        </div>
                        <!-- FIN: BOTONES DE ACCIÓN (ACTUALIZADO) -->
                    </div>
                </form>
                
                <!-- INICIO: ZONA DE PELIGRO (MOVIDA) -->
                <div class="p-6 bg-red-50 border-t border-red-200">
                    <h3 class="text-lg font-semibold text-red-800">Zona de Peligro</h3>
                    <p class="mt-1 text-sm text-gray-600">Esta acción no se puede deshacer. Esto eliminará permanentemente al usuario y puede causar errores si tiene prácticas asociadas.</p>
                    <form method="POST" action="{{ route('cppp.usuarios.destroy', $user) }}" onsubmit="return confirm('¿Está seguro de que desea eliminar este usuario?');" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-800">
                            Eliminar Usuario
                        </button>
                    </form>
                </div>
                <!-- FIN: ZONA DE PELIGRO -->
                
            </div>
        </div>
    </div>
</x-app-layout>