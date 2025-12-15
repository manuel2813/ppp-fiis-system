<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFinalReportRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool // <-- Corregido (decía publicS)
    {
        // El usuario debe ser el dueño Y la práctica debe estar observada
        $practica = $this->route('practica');
        return $practica && 
               $this->user()->id === $practica->student_id && 
               in_array($practica->status, ['final_report_observed', 'jury_observed']); // <-- LÓGICA ACTUALIZADA
    }

    /**
     * Define las reglas de validación que aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            // Los archivos son opcionales
            'file_f3' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // F3 - Informe Final (hasta 10MB)
            'file_f4' => 'nullable|file|mimes:pdf,doc,docx|max:5120',  // F4 - Ficha de Evaluación
            'file_constancia' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // Certificado/Constancia
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'file_f3.mimes' => 'El archivo F3 debe ser PDF, DOC o DOCX.',
            'file_f3.max' => 'El Informe Final (F3) es muy pesado (máx 10MB).',
        ];
    }
}