<?php

namespace App\Http\Requests\CPPP;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AnnulPracticaRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // Solo la CPPP puede anular
        return Gate::allows('isCPPP');
    }

    /**
     * Define las reglas de validación que aplican a la solicitud.
     */
    public function rules(): array
    {
        // La razón de anulación es obligatoria
        return [
            'annulment_reason' => 'required|string|min:20|max:1000',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'annulment_reason.required' => 'Debe especificar una razón clara para la anulación (abandono, indisciplina, etc.).',
            'annulment_reason.min' => 'La razón debe tener al menos 20 caracteres.',
        ];
    }
}