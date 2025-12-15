<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional; // Importamos tu modelo

class SolicitudAprobada extends Notification
{
    use Queueable;

    protected $practica;

    /**
     * Crea una nueva instancia de la notificación.
     */
    public function __construct(PracticaPreprofesional $practica)
    {
        $this->practica = $practica;
    }

    /**
     * Define los canales de envío (BD y Correo).
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Define el mensaje de Correo Electrónico.
     * (Usando los textos de tu controlador)
     */
    public function toMail(object $notifiable): MailMessage
    {
        // $notifiable es el Estudiante
        $resolutionNumber = $this->practica->resolution_number;

        return (new MailMessage)
                    ->subject('Tu Solicitud de PPP ha sido Aprobada') // Tu asunto
                    ->line('¡Felicidades, ' . $notifiable->name . '!')
                    ->line('El Decanato ha emitido tu Resolución de Autorización: ' . $resolutionNumber) // Tu línea
                    ->line('Ya puedes iniciar tus prácticas.')
                    ->action('Ver Dashboard', route('dashboard')) // Tu botón
                    ->line('¡Mucho éxito!');
    }

    /**
     * Define el mensaje para la Base de Datos (Campanita 🔔).
     */
    public function toDatabase(object $notifiable): array
    {
        $resolutionNumber = $this->practica->resolution_number;

        return [
            'practica_id' => $this->practica->id,
            'message' => "¡Aprobada! Tu Resolución de Autorización es: {$resolutionNumber}",
            'url' => route('dashboard'),
        ];
    }
}