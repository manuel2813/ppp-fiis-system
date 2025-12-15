<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

class ValidacionLaboralRecibida extends Notification
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
        // $notifiable es el Asesor
        $studentName = $this->practica->student->name;
        $practicaId = $this->practica->id;

        return (new MailMessage)
                    ->subject('Solicitud de Validación Laboral Recibida')
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line("El estudiante {$studentName} ha enviado una solicitud de Validación Laboral que requiere su dictamen.")
                    ->line('El expediente está listo para su revisión y dictamen F2.')
                    ->action('Revisar Práctica', route('asesor.practicas.show', $practicaId))
                    ->line('Gracias por su colaboración.');
    }

    public function toDatabase(object $notifiable): array
    {
        $studentName = $this->practica->student->name;
        $practicaId = $this->practica->id;

        return [
            'practica_id' => $practicaId,
            'student_name' => $studentName,
            'message' => "{$studentName} ha enviado una Validación Laboral (Dictamen F2).",
            'url' => route('asesor.practicas.show', $practicaId),
        ];
    }
}
