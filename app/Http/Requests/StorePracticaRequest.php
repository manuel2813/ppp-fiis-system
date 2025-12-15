<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StorePracticaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('isEstudiante');
    }

    public function rules(): array
    {
        return [
            // --- DATOS PRINCIPALES ---
            'entity_name' => 'required|string|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'advisor_id' => 'required|exists:users,id',
            
            // --- ARCHIVOS REQUERIDOS ---
            'file_sut' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'file_carta' => 'required|file|mimes:pdf,doc,docx|max:5120',

            // --- INICIO: NUEVOS CAMPOS (FICHA F1)  ---
            'entity_ruc' => 'required|numeric|digits:11',
            'entity_phone' => 'required|string|min:7|max:15',
            'entity_address' => 'required|string|max:255',
            'entity_manager' => 'required|string|max:255',
            'entity_department' => 'required|string|max:100',
            'entity_province' => 'required|string|max:100',
            'entity_district' => 'required|string|max:100',
            'supervisor_name' => 'required|string|max:255',
            'supervisor_email' => 'required|email|max:255',
            // --- FIN: NUEVOS CAMPOS (FICHA F1) ---

            // --- CAMPOS DEL PLAN F1 (Modal) ---
            'f1_title' => 'required|string|max:255',
            'f1_area' => 'required|string|max:255',
            'f1_entity_details' => 'required|string|min:20',
            'f1_objectives' => 'required|string|min:20',
            'f1_activities' => 'required|string|min:20',
            'f1_schedule' => 'required|string|min:20',
        ];
    }
    
    public function messages(): array
    {
        return [
            // --- Mensajes existentes ---
            'advisor_id.required' => 'Debe seleccionar un docente asesor.',
            'file_sut.required' => 'La Solicitud Única de Trámite (SUT) es obligatoria.',
            'file_carta.required' => 'La Carta de Aceptación es obligatoria.',
            'start_date.after_or_equal' => 'La fecha de inicio no puede ser en el pasado.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',

            // --- INICIO: NUEVOS MENSAJES (FICHA F1) ---
            'entity_ruc.required' => 'El RUC de la entidad es obligatorio.',
            'entity_ruc.digits' => 'El RUC debe tener 11 dígitos numéricos.',
            'entity_phone.required' => 'El teléfono de la entidad es obligatorio.',
            'entity_address.required' => 'La dirección de la entidad es obligatoria.',
            'entity_manager.required' => 'El nombre del Gerente o Representante es obligatorio.',
            'entity_department.required' => 'El Departamento (ubicación) es obligatorio.',
            'entity_province.required' => 'La Provincia (ubicación) es obligatoria.',
            'entity_district.required' => 'El Distrito (ubicación) es obligatorio.',
            'supervisor_name.required' => 'El nombre de su Supervisor/Jefe Inmediato es obligatorio.',
            'supervisor_email.required' => 'El email de su Supervisor/Jefe Inmediato es obligatorio.',
            'supervisor_email.email' => 'El email del supervisor no es válido.',
            // --- FIN: NUEVOS MENSAJES (FICHA F1) ---

            // --- Mensajes del Plan F1 (Modal) ---
            'f1_title.required' => 'El Título del Plan (F1) es obligatorio.',
            'f1_area.required' => 'El Área (F1) es obligatoria.',
            'f1_entity_details.required' => 'Los Aspectos Generales (F1) son obligatorios.',
            'f1_objectives.required' => 'Los Objetivos (F1) son obligatorios.',
            'f1_activities.required' => 'Las Actividades (F1) son obligatorias.',
            'f1_schedule.required' => 'El Cronograma (F1) es obligatorio.',
        ];
    }
}