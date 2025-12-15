<?php

namespace App\Http\Requests\Asesor;

use Illuminate\Foundation\Http\FormRequest;

class UploadF2Request extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // Obtenemos la práctica desde la ruta
        $practica = $this->route('practica');

        // Solo autoriza si el usuario logueado es el asesor DE ESTA práctica
        return $practica && $this->user()->id === $practica->advisor_id;
    }

    /**
     * Define las reglas de validación que aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            'file_f2' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'supervision_notes' => 'nullable|string|max:1000', // Un campo para el texto del F2
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'file_f2.required' => 'Debe adjuntar el archivo Formato F2.',
            'file_f2.mimes' => 'El Formato F2 debe ser un archivo PDF, DOC o DOCX.',
            'supervision_notes.max' => 'Las notas de supervisión son muy largas.',
        ];
    }
}