<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

class PendienteAgendarSustentacion extends Notification
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
        // $notifiable es el Presidente del Jurado
        $studentName = $this->practica->student->name ?? 'el estudiante';
        $practicaId = $this->practica->id;

        return (new MailMessage)
                    ->subject("Acción Requerida: Agendar Sustentación de {$studentName}")
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line("El informe final del estudiante {$studentName} ha sido aprobado por mayoría de votos del jurado.")
                    ->line('Como Presidente, por favor ingresa al panel para programar la fecha y lugar de la sustentación final.')
                    ->action('Programar Sustentación', route('jury.practicas.show', $practicaId));
    }

    public function toDatabase(object $notifiable): array
    {
        $studentName = $this->practica->student->name ?? 'el estudiante';
        $practicaId = $this->practica->id;

        return [
            'practica_id' => $practicaId,
            'message' => "Aprobado por mayoría. Debes agendar la sustentación de {$studentName}.",
            'url' => route('jury.practicas.show', $practicaId),
        ];
    }
}