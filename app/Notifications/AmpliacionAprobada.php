<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;
use Carbon\Carbon; // Para formatear la fecha

class AmpliacionAprobada extends Notification
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
        // La práctica ya tiene la nueva 'end_date' actualizada
        $newEndDate = Carbon::parse($this->practica->end_date)->format('d/m/Y');

        return (new MailMessage)
                    ->subject('Tu Solicitud de Ampliación ha sido Aprobada')
                    ->line('¡Buenas noticias, ' . $notifiable->name . '!')
                    ->line('Tu solicitud de ampliación de práctica ha sido aprobada por la CPPP.')
                    ->line("Tu nueva fecha de finalización es: **{$newEndDate}**.")
                    ->action('Ver Dashboard', route('dashboard'));
    }

    public function toDatabase(object $notifiable): array
    {
        $newEndDate = Carbon::parse($this->practica->end_date)->format('d/m/Y');

        return [
            'practica_id' => $this->practica->id,
            'message' => "Ampliación aprobada. Tu nueva fecha de fin es: {$newEndDate}.",
            'url' => route('dashboard'),
        ];
    }
}