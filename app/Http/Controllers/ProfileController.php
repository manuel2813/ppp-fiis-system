<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage; // <-- AÑADIR ESTE 'use'

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    // --- ¡AQUÍ ESTÁ LA NUEVA FUNCIÓN! ---
    /**
     * Update the user's digital signature.
     */
    public function updateSignature(Request $request): RedirectResponse
    {
        $user = $request->user();

        // 1. Validar la entrada
        $request->validateWithBag('updateSignature', [
            'signature' => [
                'required',
                'image',
                'mimes:png', // Forzar PNG para fondo transparente
                'max:1024', // 1MB Max
            ],
        ]);

        // 2. Eliminar la firma antigua (si existe)
        if ($user->signature_path && Storage::disk('public')->exists($user->signature_path)) {
            Storage::disk('public')->delete($user->signature_path);
        }

        // 3. Subir la nueva firma a 'storage/app/public/signatures'
        $path = $request->file('signature')->store('signatures', 'public');

        // 4. Guardar la ruta en la base de datos
        $user->forceFill([
            'signature_path' => $path,
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'signature-updated');
    }
}