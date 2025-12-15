<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Muestra la página con todas las notificaciones del usuario.
     */
    public function index()
    {
        $user = Auth::user();

        return view('notifications.index', [
            'unreadNotifications' => $user->unreadNotifications,
            'readNotifications'   => $user->readNotifications,
        ]);
    }

    /**
     * Marca una notificación específica como leída y redirige.
     */
    public function read(Request $request, $id)
    {
        $user = Auth::user();

        // Busca la notificación por ID solo para el usuario autenticado
        $notification = $user->notifications()->findOrFail($id);

        // Marca como leída
        if ($notification) {
            $notification->markAsRead();
        }

        // Redirige a la URL almacenada en la notificación (ej. '/practicas/123')
        // Si no hay URL, redirige al dashboard como plan B.
        return redirect($notification->data['url'] ?? '/dashboard');
    }
}