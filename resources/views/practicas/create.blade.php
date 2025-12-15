<x-app-layout>
    <div x-data="{ 
        f1DataFilled: {{ (old('f1_title') || $errors->has('f1_title')) ? 'true' : 'false' }},
        
        f1_title: '{{ old('f1_title', '') }}',
        f1_area: '{{ old('f1_area', '') }}',
        f1_entity_details: '{{ old('f1_entity_details', '') }}',
        f1_objectives: '{{ old('f1_objectives', '') }}',
        f1_activities: '{{ old('f1_activities', '') }}',
        f1_schedule: '{{ old('f1_schedule', '') }}'
    }">

        <x-slot name="header">
            <div class="flex items-center space-x-4">
                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Solicitud de Inicio de Prácticas Preprofesionales
                </h2>
            </div>
        </x-slot>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        @if ($errors->any())
                            <div id="validation-error-box" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                <strong class="font-bold">¡Error!</strong>
                                <span class="block sm:inline">Por favor, corrija los siguientes errores:</span>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>- {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('practicas.store') }}" enctype="multipart/form-data">
                            @csrf

                            <h3 class="text-lg font-semibold mb-4">1. Datos de la Entidad (Ficha F1)</h3>
                            
                            <div class="mt-4">
                                <x-input-label for="entity_name" value="Razón Social de la Entidad (Empresa/Institución)" />
                                <x-text-input id="entity_name" class="block mt-1 w-full" type="text" name="entity_name" :value="old('entity_name')" required autofocus />
                                <x-input-error :messages="$errors->get('entity_name')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <x-input-label for="entity_ruc" value="RUC" />
                                    <x-text-input id="entity_ruc" class="block mt-1 w-full" type="text" name="entity_ruc" :value="old('entity_ruc')" required placeholder="Ej: 10203040501" />
                                    <x-input-error :messages="$errors->get('entity_ruc')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="entity_phone" value="Teléfono Fijo o Celular" />
                                    <x-text-input id="entity_phone" class="block mt-1 w-full" type="text" name="entity_phone" :value="old('entity_phone')" required placeholder="Ej: 987654321" />
                                    <x-input-error :messages="$errors->get('entity_phone')" class="mt-2" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-input-label for="entity_address" value="Dirección" />
                                <x-text-input id="entity_address" class="block mt-1 w-full" type="text" name="entity_address" :value="old('entity_address')" required placeholder="Ej: Av. Universitaria Km. 2" />
                                <x-input-error :messages="$errors->get('entity_address')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                                <div>
                                    <x-input-label for="entity_department" value="Departamento" />
                                    <x-text-input id="entity_department" class="block mt-1 w-full" type="text" name="entity_department" :value="old('entity_department')" required placeholder="Ej: Huánuco" />
                                    <x-input-error :messages="$errors->get('entity_department')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="entity_province" value="Provincia" />
                                    <x-text-input id="entity_province" class="block mt-1 w-full" type="text" name="entity_province" :value="old('entity_province')" required placeholder="Ej: Leoncio Prado" />
                                    <x-input-error :messages="$errors->get('entity_province')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="entity_district" value="Distrito" />
                                    <x-text-input id="entity_district" class="block mt-1 w-full" type="text" name="entity_district" :value="old('entity_district')" required placeholder="Ej: Rupa Rupa" />
                                    <x-input-error :messages="$errors->get('entity_district')" class="mt-2" />
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <x-input-label for="entity_manager" value="Gerente / Representante Legal" />
                                <x-text-input id="entity_manager" class="block mt-1 w-full" type="text" name="entity_manager" :value="old('entity_manager')" required />
                                <x-input-error :messages="$errors->get('entity_manager')" class="mt-2" />
                            </div>


                            <h3 class="text-lg font-semibold mt-6 mb-4">2. Datos de la Práctica (Ficha F1)</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="supervisor_name" value="Nombre del Supervisor / Jefe Inmediato" />
                                    <x-text-input id="supervisor_name" class="block mt-1 w-full" type="text" name="supervisor_name" :value="old('supervisor_name')" required />
                                    <x-input-error :messages="$errors->get('supervisor_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="supervisor_email" value="Email del Supervisor / Jefe Inmediato" />
                                    <x-text-input id="supervisor_email" class="block mt-1 w-full" type="email" name="supervisor_email" :value="old('supervisor_email')" required placeholder="ejemplo@empresa.com" />
                                    <x-input-error :messages="$errors->get('supervisor_email')" class="mt-2" />
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <x-input-label for="start_date" value="Fecha de Inicio" />
                                    <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" required />
                                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="end_date" value="Fecha de Término" />
                                    <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" required />
                                    <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-input-label for="advisor_id" value="Docente Asesor Propuesto (Académico)" />
                                <select id="advisor_id" name="advisor_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled {{ old('advisor_id') ? '' : 'selected' }}>Seleccione un asesor...</option>
                                    @foreach($asesores as $asesor)
                                        <option value="{{ $asesor->id }}" {{ old('advisor_id') == $asesor->id ? 'selected' : '' }}>
                                            {{ $asesor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('advisor_id')" class="mt-2" />
                            </div>


                            <h3 class="text-lg font-semibold mt-6 mb-4">3. Documentos Obligatorios</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Adjunte los documentos requeridos. El Plan de Práctica (F1) se llenará en el siguiente paso.
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="file_sut" class="block font-medium text-sm text-gray-700">Solicitud Única de Trámite (SUT)</label>
                                    <input id="file_sut" class="block mt-1 w-full text-sm" type="file" name="file_sut" required />
                                    <x-input-error :messages="$errors->get('file_sut')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <label class="block font-medium text-sm text-gray-700">Plan de Práctica (F1)</label>
                                    <x-secondary-button type="button" 
                                                        @click.prevent="$dispatch('open-modal', 'f1-form-modal')"
                                                        class="mt-1 w-full justify-center">
                                        Llenar Plan de Práctica
                                    </x-secondary-button>
                                    
                                    <span x-show="f1_title.trim().length > 0 && f1_area.trim().length > 0" class="flex items-center text-sm text-green-600 mt-2">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"></path></svg>
                                        Plan de Práctica Llenado
                                    </span>
                                    <x-input-error :messages="$errors->get('f1_title')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="file_carta" class="block font-medium text-sm text-gray-700">Carta de Aceptación</label>
                                    <input id="file_carta" class="block mt-1 w-full text-sm" type="file" name="file_carta" required />
                                    <x-input-error :messages="$errors->get('file_carta')" class="mt-2" />
                                </div>
                            </div>
                            
                            <input type="hidden" name="f1_title" x-bind:value="f1_title">
            <input type="hidden" name="f1_area" x-bind:value="f1_area">
            <input type="hidden" name="f1_entity_details" x-bind:value="f1_entity_details">
            <input type="hidden" name="f1_objectives" x-bind:value="f1_objectives">
            <input type="hidden" name="f1_activities" x-bind:value="f1_activities">
            <input type="hidden" name="f1_schedule" x-bind:value="f1_schedule">

                            <div class="flex items-center justify-between mt-8">
                                <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 underline">
                                    Cancelar
                                </a>
                                <x-primary-button type="submit">
                                    Enviar Solicitud
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <x-modal name="f1-form-modal" 
                     :show="$errors->has('f1_title') || $errors->has('f1_area') || $errors->has('f1_objectives') || $errors->has('f1_entity_details') || $errors->has('f1_activities') || $errors->has('f1_schedule')">
                
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900">
                        Formato F1: Plan de Práctica Preprofesional
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Complete todos los campos de su plan de práctica.
                    </V>

                    
                    <div class="mt-6 space-y-4">
                        <div>
                            <x-input-label for="modal_f1_title" value="Título de la Práctica" />
                            <x-text-input id="modal_f1_title" x-model="f1_title" class="block mt-1 w-full" type="text" />
                            <x-input-error :messages="$errors->get('f1_title')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="modal_f1_area" value="Área donde desarrollará la práctica" />
                            <x-text-input id="modal_f1_area" x-model="f1_area" class="block mt-1 w-full" type="text" />
                            <x-input-error :messages="$errors->get('f1_area')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="modal_f1_entity_details" value="Aspectos generales de la entidad" />
                            <textarea id="modal_f1_entity_details" x-model="f1_entity_details" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" rows="3"></textarea>
                            <x-input-error :messages="$errors->get('f1_entity_details')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="modal_f1_objectives" value="Objetivos de la práctica" />
                            <textarea id="modal_f1_objectives" x-model="f1_objectives" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" rows="3"></textarea>
                            <x-input-error :messages="$errors->get('f1_objectives')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="modal_f1_activities" value="Actividades por ejecutarse" />
                            <textarea id="modal_f1_activities" x-model="f1_activities" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" rows="5"></textarea>
                            <x-input-error :messages="$errors->get('f1_activities')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="modal_f1_schedule" value="Cronograma de actividades" />
                            <textarea id="modal_f1_schedule" x-model="f1_schedule" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" rows="3"></textarea>

                            <x-input-error :messages="$errors->get('f1_schedule')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-secondary-button @click="$dispatch('close')">
                            Cancelar
                        </x-secondary-button>
                        <x-primary-button class="ml-3" 
                            ::disabled="f1_title.trim() === '' || f1_area.trim() === ''"
                            ::class="{ 'opacity-50 cursor-not-allowed': f1_title.trim() === '' || f1_area.trim() === '' }"
                            @click="f1DataFilled = true; $dispatch('close')">
                            Guardar Plan
                        </x-primary-button>
                    </div>
                </div>
            </x-modal>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Busca la caja de errores por el ID que le dimos
            const errorBox = document.getElementById('validation-error-box');
            
            if (errorBox) {
                // Si la caja existe, haz scroll suave hacia ella
                errorBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    </script>
</x-app-layout>