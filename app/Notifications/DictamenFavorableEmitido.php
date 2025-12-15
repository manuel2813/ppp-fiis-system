<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

class DictamenFavorableEmitido extends Notification
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

        return (new MailMessage)
                    ->subject("Dictamen Favorable Recibido: {$studentName}")
                    ->line('Hola,')
                    ->line("El Asesor ha emitido un dictamen favorable (F2) para el informe final del estudiante {$studentName}.")
                    ->line('La práctica está lista para el siguiente paso: Asignación de Jurado.')
                    ->action('Ver Práctica y Asignar Jurado', route('cppp.practicas.show', $practicaId));
    }

    public function toDatabase(object $notifiable): array
    {
        $studentName = $this->practica->student->name;
        $practicaId = $this->practica->id;

        return [
            'practica_id' => $practicaId,
            'student_name' => $studentName,
            'message' => "Dictamen Favorable de {$studentName}. Listo para asignar jurado.",
            'url' => route('cppp.practicas.show', $practicaId),
        ];
    }
}