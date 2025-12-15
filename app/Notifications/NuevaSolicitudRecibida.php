<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

// --- 1. AÑADE ESTE 'USE' ---
use NotificationChannels\Pushover\PushoverMessage;

class NuevaSolicitudRecibida extends Notification
{
    use Queueable;

    protected $practica;

    public function __construct(PracticaPreprofesional $practica)
    {
        $this->practica = $practica;
    }

    /**
     * --- 2. MODIFICA ESTE MÉTODO ---
     */
    public function via(object $notifiable): array
    {
        // Añadimos 'pushover'
        return ['mail', 'database', 'pushover'];
    }

    /**
     * Define el mensaje de Correo Electrónico.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->practica->student->name;
        $practicaId = $this->practica->id;

        return (new MailMessage)
                    ->subject("Nueva Solicitud de Práctica Recibida")
                    ->line('Hola,')
                    ->line("Se ha recibido una nueva solicitud de práctica del estudiante: {$studentName}.")
                    ->action('Revisar Solicitud', route('cppp.practicas.show', $practicaId))
                    ->line('Por favor, ingrese al panel de CPPP para su revisión.');
    }

    /**
     * Define el mensaje para la Base de Datos (Campanita 🔔).
     */
    public function toDatabase(object $notifiable): array
    {
        $studentName = $this->practica->student->name;
        $practicaId = $this->practica->id;

        return [
            'practica_id' => $practicaId,
            'student_name' => $studentName,
            'message' => "Nueva solicitud de práctica del est. {$studentName}.",
            'url' => route('cppp.practicas.show', $practicaId),
        ];
    }

    /**
     * --- 3. AÑADE ESTE NUEVO MÉTODO ---
     */
    public function toPushover(object $notifiable): PushoverMessage
    {
        $studentName = $this->practica->student->name;
        $url = route('cppp.practicas.show', $this->practica->id);

        return PushoverMessage::create()
            ->title('Nueva Solicitud de Práctica') // Título de la notificación
            ->message("Se ha recibido una nueva solicitud del est. {$studentName}.") // Mensaje
            ->url($url, 'Revisar Solicitud') // Enlace (opcional)
            ->sound('pushover'); // Sonido predeterminado
    }
}