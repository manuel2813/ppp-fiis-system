<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

class SuplenteActivado extends Notification
{
    use Queueable;

    protected $practica;
    protected $rolAsignado; // El rol que el suplente va a tomar (ej. 'Presidente')

    public function __construct(PracticaPreprofesional $practica, string $rolAsignado)
    {
        $this->practica = $practica;
        $this->rolAsignado = $rolAsignado;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // $notifiable es el Suplente
        $studentName = $this->practica->student->name ?? 'el estudiante';
        $practicaId = $this->practica->id;

        return (new MailMessage)
                    ->subject('Has sido activado como Jurado Suplente')
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line("Has sido activado como jurado suplente para la práctica del estudiante {$studentName}.")
                    ->line("Tu rol asignado en esta terna es: **{$this->rolAsignado}**.")
                    ->action('Ver Práctica Asignada', route('jury.practicas.show', $practicaId));
    }

    public function toDatabase(object $notifiable): array
    {
        $practicaId = $this->practica->id;
        return [
            'practica_id' => $practicaId,
            'message' => "Has sido activado como jurado (Rol: {$this->rolAsignado}).",
            'url' => route('jury.practicas.show', $practicaId),
        ];
    }
}