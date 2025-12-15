<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use App\Models\User; // <-- Importante: Importamos el modelo User

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // --- LÓGICA INTELIGENTE (NUEVO) ---
        
        // 1. Verificamos si el correo ingresado es un "Correo de Recuperación" (Gmail/Outlook personal)
        $userByRecovery = User::where('recovery_email', $request->email)->first();

        if ($userByRecovery) {
            // ¡Encontrado! El usuario ingresó su correo personal.
            // TRUCO: Cambiamos silenciosamente el email del request por el INSTITUCIONAL del usuario encontrado.
            // Esto es necesario porque Laravel usa el email institucional como "llave" para generar el token.
            $request->merge(['email' => $userByRecovery->email]);
        }

        // ----------------------------------

        // 2. Laravel procesa la solicitud (Ahora ya tiene el email institucional correcto)
        // Nota: Gracias a la modificación que hicimos antes en User.php, aunque aquí
        // le pasemos el institucional, el correo real se enviará al de recuperación.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withInput($request->only('email'))
                    ->withErrors(['email' => __($status)]);
    }
}