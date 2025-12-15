<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

class AmpliacionSolicitada extends Notification
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
        // $notifiable es el usuario de CPPP
        $studentName = $this->practica->student->name;
        $practicaId = $this->practica->id;
        $newDate = $this->practica->pending_extension_date;

        return (new MailMessage)
                    ->subject('Solicitud de Ampliación de Práctica')
                    ->line('Hola,')
                    ->line("El estudiante {$studentName} ha solicitado una ampliación de su práctica.")
                    ->line("Nueva fecha de finalización propuesta: {$newDate}")
                    ->action('Revisar Solicitud', route('cppp.practicas.show', $practicaId))
                    ->line('Por favor, ingrese al panel de CPPP para su revisión.');
    }

    public function toDatabase(object $notifiable): array
    {
        $studentName = $this->practica->student->name;
        $practicaId = $this->practica->id;

        return [
            'practica_id' => $practicaId,
            'student_name' => $studentName,
            'message' => "El est. {$studentName} ha solicitado una ampliación.",
            'url' => route('cppp.practicas.show', $practicaId),
        ];
    }
}
