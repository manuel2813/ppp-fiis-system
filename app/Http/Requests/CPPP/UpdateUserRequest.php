<?php
namespace App\Http\Requests\CPPP;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('isCPPP');
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId), // Ignora el email del propio usuario
            ],
            // La contraseña es opcional al actualizar
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role_id' => 'required|exists:roles,id',
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'code')->ignore($userId), // Ignora el código del propio usuario
            ],
        ];
    }
}