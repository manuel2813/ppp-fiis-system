<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

class PracticaAnulada extends Notification
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
        // $notifiable es el Estudiante (o Asesor)
        $reason = $this->practica->annulment_reason;
        $studentName = $this->practica->student->name ?? 'el estudiante';

        $subject = "Práctica Preprofesional Anulada: {$studentName}";

        // Mensaje para el Estudiante
        if ($notifiable->id === $this->practica->student_id) {
            return (new MailMessage)
                ->subject($subject)
                ->line('Hola ' . $notifiable->name . ',')
                ->line('Lamentamos informarte que tu práctica preprofesional ha sido **ANULADA** por la CPPP.')
                ->line('**Motivo de la Anulación:**')
                ->line("*" . nl2br(e($reason)) . "*")
                ->line('Por favor, contacta a la CPPP para más detalles o consulta tu dashboard.')
                ->action('Ver Dashboard', route('dashboard'));
        }

        // Mensaje para el Asesor
        return (new MailMessage)
            ->subject($subject)
            ->line('Hola ' . $notifiable->name . ',')
            ->line("Se te informa que la práctica del estudiante {$studentName}, a quien asesorabas, ha sido ANULADA por la CPPP.")
            ->line("Motivo: " . $reason)
            ->action('Ver Dashboard de Asesor', route('asesor.dashboard.index'));
    }

    public function toDatabase(object $notifiable): array
    {
        // Mensaje para el Estudiante
        if ($notifiable->id === $this->practica->student_id) {
            return [
                'practica_id' => $this->practica->id,
                'message' => '¡Importante! Tu práctica ha sido ANULADA. Revisa el motivo.',
                'url' => route('dashboard'),
            ];
        }

        // Mensaje para el Asesor
        return [
            'practica_id' => $this->practica->id,
            'message' => "La práctica del est. {$this->practica->student->name} ha sido ANULADA.",
            'url' => route('asesor.dashboard.index'),
        ];
    }
}