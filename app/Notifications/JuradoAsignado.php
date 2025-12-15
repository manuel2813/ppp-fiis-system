<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;

class JuradoAsignado extends Notification
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
        // ***** INICIO DE LA CORRECCIÓN *****
        // La URL ahora apunta a la ruta del jurado
        $url = route('jury.practicas.show', $this->practica->id);
        // ***** FIN DE LA CORRECCIÓN *****

        return (new MailMessage)
                    ->subject('Asignación de Jurado de Práctica')
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line('Has sido asignado como jurado para la práctica pre-profesional del estudiante: ' . $this->practica->student->name) 
                    ->action('Ver Detalles de la Práctica', $url) // <-- variable $url usada aquí
                    ->line('Gracias por tu colaboración.');
    }

    public function toDatabase(object $notifiable): array
    {
        // ***** INICIO DE LA CORRECCIÓN *****
        // La URL ahora apunta a la ruta del jurado
        $url = route('jury.practicas.show', $this->practica->id);
        // ***** FIN DE LA CORRECCIÓN *****

        return [
            'practica_id' => $this->practica->id,
            'student_name' => $this->practica->student->name,
            'message' => 'Has sido asignado como jurado para la práctica de ' . $this->practica->student->name,
            'url' => $url, // <-- variable $url usada aquí
        ];
    }
}