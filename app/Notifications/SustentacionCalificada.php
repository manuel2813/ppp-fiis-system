<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;
use App\Models\User; // <-- Importante

class SustentacionCalificada extends Notification
{
    use Queueable;

    protected $practica;
    protected $studentName;
    protected $finalGrade;
    protected $finalStatusText;

    public function __construct(PracticaPreprofesional $practica)
    {
        $this->practica = $practica;
        $this->studentName = $practica->student->name ?? 'el estudiante';
        $this->finalGrade = $practica->final_grade;

        // Convertimos el estado a un texto legible
        if ($practica->status == 'completed_approved') {
            $this->finalStatusText = 'APROBADO';
        } else if ($practica->status == 'completed_failed') {
            $this->finalStatusText = 'DESAPROBADO';
        } else {
            $this->finalStatusText = 'FINALIZADO';
        }
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Mensaje para el ESTUDIANTE
        if ($notifiable->id === $this->practica->student_id) {
            return (new MailMessage)
                ->subject('Resultado de tu Sustentación de PPP')
                ->line('¡Hola ' . $notifiable->name . ', tu sustentación ha sido calificada!')
                ->line("Tu nota final es: **{$this->finalGrade} / 20**.")
                ->line("Estado: **{$this->finalStatusText}**.")
                ->line('Puedes revisar tu panel para más detalles.')
                ->action('Ver Dashboard', route('dashboard'));
        }

        // Mensaje para CPPP
        return (new MailMessage)
            ->subject("Proceso Finalizado: {$this->studentName}")
            ->line('El proceso de práctica del estudiante ' . $this->studentName . ' ha concluido.')
            ->line("Resultado: {$this->finalStatusText} (Nota: {$this->finalGrade}/20).")
            ->line('El siguiente paso es la emisión de la Constancia de Cumplimiento.')
            ->action('Ver Práctica', route('cppp.practicas.show', $this->practica->id));
    }

    public function toDatabase(object $notifiable): array
    {
        // Mensaje para el ESTUDIANTE
        if ($notifiable->id === $this->practica->student_id) {
            return [
                'practica_id' => $this->practica->id,
                'message' => "Tu nota final de sustentación es: {$this->finalGrade}/20. Estado: {$this->finalStatusText}.",
                'url' => route('dashboard'),
            ];
        }

        // Mensaje para CPPP
        return [
            'practica_id' => $this->practica->id,
            'message' => "Práctica de {$this->studentName} finalizada ({$this->finalStatusText}). Lista para constancia.",
            'url' => route('cppp.practicas.show', $this->practica->id),
        ];
    }
}