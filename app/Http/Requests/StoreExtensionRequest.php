<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExtensionRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        $practica = $this->route('practica');

        // Autorizado si:
        // 1. Es el dueño
        // 2. La práctica está 'initial_approved' (en curso)
        // 3. Ha tenido menos de 2 ampliaciones aprobadas
        return $practica && 
               $this->user()->id === $practica->student_id &&
               $practica->status === 'initial_approved' &&
               $practica->extension_count < 2;
    }

    /**
     * Define las reglas de validación que aplican a la solicitud.
     */
    public function rules(): array
    {
        $practica = $this->route('practica');
        
        return [
            // La nueva fecha debe ser posterior a la fecha de fin actual
            'new_end_date' => 'required|date|after:' . $practica->end_date,
            
            // La carta de la institución es obligatoria
            'file_extension_letter' => 'required|file|mimes:pdf,doc,docx|max:2048',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'new_end_date.required' => 'Debe proponer una nueva fecha de culminación.',
            'new_end_date.after' => 'La nueva fecha debe ser posterior a la fecha de fin actual.',
            'file_extension_letter.required' => 'Debe adjuntar la carta de la institución que avala la ampliación.',
        ];
    }
}