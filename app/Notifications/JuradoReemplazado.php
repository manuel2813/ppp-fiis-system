<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;
use App\Models\User; // <-- Importante

class JuradoReemplazado extends Notification
{
    use Queueable;

    protected $practica;
    protected $juradoSaliente;
    protected $suplenteEntrante;

    public function __construct(PracticaPreprofesional $practica, User $juradoSaliente, User $suplenteEntrante)
    {
        $this->practica = $practica;
        $this->juradoSaliente = $juradoSaliente;
        $this->suplenteEntrante = $suplenteEntrante;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $practicaId = $this->practica->id;
        $studentName = $this->practica->student->name ?? 'el estudiante';

        // Mensaje para CPPP
        if ($notifiable->role->name == 'cppp') {
            return (new MailMessage)
                ->subject("Cambio de Jurado en Práctica: {$studentName}")
                ->line('Se ha registrado un cambio de jurado (recusación).')
                ->line("Estudiante: {$studentName}")
                ->line("Jurado Saliente: {$this->juradoSaliente->name}")
                ->line("Jurado Entrante (Suplente): {$this->suplenteEntrante->name}")
                ->action('Ver Práctica', route('cppp.practicas.show', $practicaId));
        }

        // Mensaje para el Jurado Saliente (que es $notifiable)
        return (new MailMessage)
                    ->subject('Confirmación de Recusación')
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line('Se ha procesado tu recusación para la práctica del estudiante ' . $studentName . '.')
                    ->line("Has sido reemplazado exitosamente por el suplente: {$this->suplenteEntrante->name}.")
                    ->action('Volver al Dashboard', route('jury.dashboard.index'));
    }

    public function toDatabase(object $notifiable): array
    {
        $practicaId = $this->practica->id;

        // Mensaje para CPPP
        if ($notifiable->role->name == 'cppp') {
            return [
                'practica_id' => $practicaId,
                'message' => "Cambio de jurado: {$this->juradoSaliente->name} fue reemplazado por {$this->suplenteEntrante->name}.",
                'url' => route('cppp.practicas.show', $practicaId),
            ];
        }

        // Mensaje para el Jurado Saliente
        return [
            'practica_id' => $practicaId,
            'message' => "Te has recusado. Te reemplaza: {$this->suplenteEntrante->name}.",
            'url' => route('jury.dashboard.index'),
        ];
    }
}