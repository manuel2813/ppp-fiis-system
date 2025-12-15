<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

class AmpliacionRechazada extends Notification
{
    use Queueable;

    protected $practica;

    public function __construct(PracticaPreprofesional $practica)
    {
        $this->practica = $practica;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // $notifiable es el Estudiante
        // La práctica ya tiene las 'observation_notes' del rechazo
        $rejectionReason = $this->practica->observation_notes;

        return (new MailMessage)
                    ->subject('Tu Solicitud de Ampliación ha sido Rechazada')
                    ->line('Hola, ' . $notifiable->name . '.')
                    ->line('Tu solicitud de ampliación de práctica ha sido rechazada por la CPPP.')
                    ->line('**Motivo del Rechazo:**')
                    ->line("*" . nl2br(e($rejectionReason)) . "*")
                    ->line('Tu práctica continuará con la fecha de finalización original.')
                    ->action('Ver Dashboard', route('dashboard'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'practica_id' => $this->practica->id,
            'message' => 'Tu solicitud de ampliación fue rechazada. Revisa los motivos.',
            'url' => route('dashboard'),
        ];
    }
}