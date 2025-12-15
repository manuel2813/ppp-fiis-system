<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Nuevo Usuario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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

                    <form method="POST" action="{{ route('cppp.usuarios.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div class="md:col-span-2">
                                <label for="name" class="block font-medium text-sm text-gray-700">Nombre Completo</label>
                                <input id="name" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="text" name="name" value="{{ old('name') }}" required autofocus />
                            </div>

                            <div>
                                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                                <input id="email" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="email" name="email" value="{{ old('email') }}" required />
                            </div>

                            <div>
                                <label for="role_id" class="block font-medium text-sm text-gray-700">Rol del Usuario</label>
                                <select id="role_id" name="role_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>Seleccione un rol...</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="code" class="block font-medium text-sm text-gray-700">Código (Opcional)</label>
                                <input id="code" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="text" name="code" value="{{ old('code') }}" />
                                <p class="text-xs text-gray-500 mt-1">Ej: Código de estudiante o docente.</p>
                            </div>

                            <div>
                                <label for="password" class="block font-medium text-sm text-gray-700">Contraseña</label>
                                <input id="password" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="password" name="password" required />
                            </div>

                            <div>
                                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirmar Contraseña</label>
                                <input id="password_confirmation" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="password" name="password_confirmation" required />
                            </div>
                        </div>

                        <!-- INICIO: BOTONES DE ACCIÓN ACTUALIZADOS -->
                        <div class="flex items-center justify-between mt-8">
                            <!-- Botón de Cancelar -->
                            <a href="{{ route('cppp.usuarios.index') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Cancelar
                            </a>
                            
                            <!-- Botón de Guardar (con color primary) -->
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800">
                                Guardar Usuario
                            </button>
                        </div>
                        <!-- FIN: BOTONES DE ACCIÓN ACTUALIZADOS -->
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>