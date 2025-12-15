<x-app-layout>
    <x-slot name="header">
        <!-- INICIO: HEADER CON BOTÓN DE VOLVER -->
        <div class="flex items-center space-x-4">
            <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Solicitar Validación de Actividades Laborales
            </h2>
        </div>
        <!-- FIN: HEADER CON BOTÓN DE VOLVER -->
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <h3 class="text-lg font-semibold mb-2">Validación Excepcional</h3>
                    <p class="text-sm text-gray-600 mb-6">
                        Este formulario es para validar actividades laborales (su trabajo) como Práctica Preprofesional,
                        según la Disposición Final TERCERA de la directiva.
                        Debe adjuntar su informe, constancia de trabajo, evaluación de su jefe (F4) y
                        el documento de certificación progresiva vinculado.
                    </p>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>- {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('practicas.validation.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <h3 class="text-lg font-semibold mb-4">1. Datos de la Entidad Laboral</h3>
                        <div>
                            <label for="entity_name" class="block font-medium text-sm text-gray-700">Razón Social (Empresa/Institución)</label>
                            <input id="entity_name" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="text" name="entity_name" value="{{ old('entity_name') }}" required autofocus />
                        </div>

                        <h3 class="text-lg font-semibold mt-6 mb-4">2. Período y Asesor</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="start_date" class="block font-medium text-sm text-gray-700">Fecha de Inicio (Laboral)</label>
                                <input id="start_date" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="date" name="start_date" value="{{ old('start_date') }}" required />
                            </div>
                            <div>
                                <label for="end_date" class="block font-medium text-sm text-gray-700">Fecha de Fin (Laboral)</label>
                                <input id="end_date" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="date" name="end_date" value="{{ old('end_date') }}" required />
                            </div>
                        </div>
                        <div class="mt-4">
                            <label for="advisor_id" class="block font-medium text-sm text-gray-700">Docente Asesor (Para revisión de informe)</label>
                            <select id="advisor_id" name="advisor_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="" disabled selected>Seleccione un asesor...</option>
                                @foreach($asesores as $asesor)
                                    <option value="{{ $asesor->id }}" {{ old('advisor_id') == $asesor->id ? 'selected' : '' }}>
                                        {{ $asesor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <h3 class="text-lg font-semibold mt-6 mb-4">3. Documentos Obligatorios</h3>
                        <div class="space-y-6">
                            <div>
                                <label for="file_f3" class="block font-medium text-sm text-gray-700">1. Informe Correspondiente (Formato F3)</label>
                                <input id="file_f3" class="block mt-1 w-full text-sm" type="file" name="file_f3" required />
                            </div>
                            <div>
                                <label for="file_f4" class="block font-medium text-sm text-gray-700">2. Ficha de Evaluación (Formato F4)</label>
                                <input id="file_f4" class="block mt-1 w-full text-sm" type="file" name="file_f4" required />
                                <p class="text-xs text-gray-500">Llenada por su jefe inmediato en la entidad.</p>
                            </div>
                            <div>
                                <label for="file_constancia" class="block font-medium text-sm text-gray-700">3. Constancia o Certificado de Trabajo</label>
                                <input id="file_constancia" class="block mt-1 w-full text-sm" type="file" name="file_constancia" required />
                            </div>
                            <div>
                                <label for="file_certificacion" class="block font-medium text-sm text-gray-700">4. Documento de Certificación Progresiva</label>
                                <input id="file_certificacion" class="block mt-1 w-full text-sm" type="file" name="file_certificacion" required />
                                <p class="text-xs text-gray-500">Documento que vincula su trabajo a la certificación.</p>
                            </div>
                        </div>

                        <!-- INICIO: BOTONES DE ACCIÓN ACTUALIZADOS -->
                        <div class="flex items-center justify-between mt-8">
                            <!-- Botón de Cancelar -->
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Cancelar
                            </a>
                            
                            <!-- Botón de Enviar (con color primary) -->
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-800">
                                Enviar Solicitud de Validación
                            </button>
                        </div>
                        <!-- FIN: BOTONES DE ACCIÓN ACTUALIZADOS -->
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>