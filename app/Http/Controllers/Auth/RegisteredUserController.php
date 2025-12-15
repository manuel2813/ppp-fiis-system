<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role; // Importante: Importar el modelo Role
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'size:10', 'regex:/^00(19|20)\d{2}\d{4}$/', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'regex:/^[a-zA-Z0-9._%+-]+@unas\.edu\.pe$/i', 'unique:'.User::class],
            // Validamos que el correo de recuperación sea Gmail (opcional, pero recomendado para evitar bloqueos)
            'recovery_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'different:email'], 
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'code.regex' => 'El código debe seguir el formato: 00 + Año + 4 dígitos (Ej: 0020221234).',
            'email.regex' => 'Solo se permiten correos institucionales (@unas.edu.pe).',
            'recovery_email.different' => 'El correo de recuperación debe ser distinto al institucional.',
        ]);

        // Buscamos el ID del rol 'estudiante'
        // Asumimos que el nombre en la BD es 'estudiante'. Si no existe, fallará.
        $studentRole = Role::where('name', 'estudiante')->firstOrFail();

        $user = User::create([
            'name' => $request->name,
            'code' => $request->code,
            'email' => $request->email,
            'recovery_email' => $request->recovery_email, // <--- GUARDAMOS
            'password' => Hash::make($request->password),
            'role_id' => $studentRole->id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}