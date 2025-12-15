<?php

namespace App\Http\Requests\CPPP;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ScheduleDefenseRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        // Solo la CPPP puede hacer esto
        // Y solo si la práctica está esperando fecha
        return Gate::allows('isCPPP') &&
               $this->route('practica')->status === 'pending_defense_date';
    }

    /**
     * Define las reglas de validación que aplican a la solicitud.
     */
    public function rules(): array
    {
        return [
            // Usamos datetime-local en el input
            'defense_date' => 'required|date|after:now',
            'defense_place' => 'required|string|min:5|max:255',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'defense_date.required' => 'Debe seleccionar una fecha y hora.',
            'defense_date.after' => 'La fecha de sustentación debe ser en el futuro.',
            'defense_place.required' => 'Debe especificar el lugar (ej. Auditorio FIIS o enlace virtual).',
            'defense_place.min' => 'El lugar debe tener al menos 5 caracteres.',
        ];
    }
}