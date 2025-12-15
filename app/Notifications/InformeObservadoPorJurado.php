<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

class InformeObservadoPorJurado extends Notification
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
                    ->subject('Tu Informe Final tiene Observaciones del Jurado')
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line('Tu informe final ha sido observado por el Jurado Evaluador.')
                    ->line('**Observaciones (Consolidado):**')
                    ->line("*" . nl2br(e($observationNotes)) . "*") // nl2br para saltos de línea
                    ->line('Por favor, corrige las observaciones y vuelve a subir tu informe para una nueva revisión.')
                    ->action('Corregir Informe Final', $editUrl);
    }

    public function toDatabase(object $notifiable): array
    {
        $practicaId = $this->practica->id;
        $editUrl = route('practicas.final_report.edit', $practicaId);

        return [
            'practica_id' => $practicaId,
            'message' => 'Tu informe final fue observado por el Jurado. ¡Debes corregirlo!',
            'url' => $editUrl,
        ];
    }
}