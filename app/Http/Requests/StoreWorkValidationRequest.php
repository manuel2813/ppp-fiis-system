<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkValidationRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // Solo un estudiante puede hacer esto
        return $this->user()->role->name === 'estudiante';
    }

    /**
     * Define las reglas de validación que aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            'entity_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'advisor_id' => 'required|exists:users,id',
            
            // Documentos requeridos por la directiva
            'file_f3' => 'required|file|mimes:pdf,doc,docx|max:10240', // Informe 
            'file_constancia' => 'required|file|mimes:pdf,doc,docx|max:5120', // Constancia de trabajo
            'file_f4' => 'required|file|mimes:pdf,doc,docx|max:5120', // Evaluación del jefe (F4)
            'file_certificacion' => 'required|file|mimes:pdf,doc,docx|max:5120', // Prueba de Certificación Progresiva 
        ];
    }
    
    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'advisor_id.required' => 'Debe seleccionar un Asesor para que revise su informe.',
            'file_f3.required' => 'El Informe Correspondiente (Formato F3) es obligatorio.',
            'file_constancia.required' => 'La Constancia o Certificado de Trabajo es obligatorio.',
            'file_f4.required' => 'La Ficha de Evaluación (F4) llenada por su jefe es obligatoria.',
            'file_certificacion.required' => 'El documento de Certificación Progresiva es obligatorio.',
        ];
    }
}