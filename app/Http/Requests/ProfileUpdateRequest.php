<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // Validación para el correo de recuperación al editar perfil
            'recovery_email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'different:email', // Debe ser distinto al institucional
            ],
        ];
    }
}