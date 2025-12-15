<?php

namespace App\Http\Requests\Jury;

use Illuminate\Foundation\Http\FormRequest;

class SubmitGradeRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // El usuario debe ser parte del jurado Y la sustentación debe estar programada
        $practica = $this->route('practica');
        return $practica && 
               $practica->juradoAssignments()->where('jurado_member_id', $this->user()->id)->exists() &&
               $practica->status === 'defense_scheduled';
    }

    /**
     * Define las reglas de validación que aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            // La nota debe ser un número entero entre 0 y 20 [cite: 204]
            'final_grade' => 'required|integer|min:0|max:20',
            
            // El Formato F5 (Acta de Exposición) es requerido [cite: 183]
            'file_f5' => 'required|file|mimes:pdf|max:2048',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'final_grade.required' => 'Debe ingresar la calificación final (0-20).',
            'final_grade.integer' => 'La calificación debe ser un número entero.',
            'final_grade.min' => 'La calificación mínima es 0.',
            'final_grade.max' => 'La calificación máxima es 20.',
            'file_f5.required' => 'Debe adjuntar el Formato F5 (Acta de Exposición).',
            'file_f5.mimes' => 'El Formato F5 debe ser un archivo PDF.',
        ];
    }
}