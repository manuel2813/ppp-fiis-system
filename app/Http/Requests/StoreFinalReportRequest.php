<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinalReportRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // El usuario debe ser el dueño
        $practica = $this->route('practica');
        return $practica && $this->user()->id === $practica->student_id;
    }

    /**
     * Define las reglas de validación que aplican a la solicitud.
     */
    public function rules(): array
    {
        // Según la directiva, el estudiante presenta F3, F4 y Certificado [cite: 147]
        return [
            'file_f3' => 'required|file|mimes:pdf,doc,docx|max:10240', // F3 - Informe Final (hasta 10MB)
            'file_f4' => 'required|file|mimes:pdf,doc,docx|max:5120',  // F4 - Ficha de Evaluación
            'file_constancia' => 'required|file|mimes:pdf,doc,docx|max:5120', // Certificado/Constancia
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'file_f3.required' => 'El Informe Final (Formato F3) es obligatorio.',
            'file_f4.required' => 'La Ficha de Evaluación de la Entidad (Formato F4) es obligatoria.',
            'file_constancia.required' => 'El Certificado o Constancia de prácticas es obligatorio.',
            'file_f3.mimes' => 'El archivo F3 debe ser PDF, DOC o DOCX.',
            'file_f3.max' => 'El Informe Final (F3) es muy pesado (máx 10MB).',
        ];
    }
}