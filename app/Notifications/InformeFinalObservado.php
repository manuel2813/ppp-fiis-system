<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

class InformeFinalObservado extends Notification
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
        $observationNotes = $this->practica->observation_notes;
        // Ruta para que el estudiante edite su informe final
        $editUrl = route('practicas.final_report.edit', $this->practica->id);

        return (new MailMessage)
                    ->subject('Tu Informe Final tiene Observaciones')
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line('Tu Asesor ha observado tu informe final (F3).')
                    ->line('**Observaciones del Asesor:**')
                    ->line("*" . nl2br(e($observationNotes)) . "*")
                    ->line('Por favor, corrige las observaciones y vuelve a subir tu informe.')
                    ->action('Corregir Informe Final', $editUrl);
    }

    public function toDatabase(object $notifiable): array
    {
        $practicaId = $this->practica->id;
        $editUrl = route('practicas.final_report.edit', $practicaId);

        return [
            'practica_id' => $practicaId,
            'message' => 'Tu informe final (F3) tiene observaciones de tu Asesor.',
            'url' => $editUrl,
        ];
    }
}