<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional; // Importamos tu modelo

class ResolucionRequerida extends Notification
{
    use Queueable;

    protected $practica;

    /**
     * Crea una nueva instancia de la notificación.
     */
    public function __construct(PracticaPreprofesional $practica)
    {
        $this->practica = $practica;
    }

    /**
     * Define los canales de envío (BD y Correo).
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Define el mensaje de Correo Electrónico.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // $notifiable es el Decano que recibe la notificación
        $studentName = $this->practica->student->name; // Asumimos la relación 'student'

        return (new MailMessage)
                    ->subject('Resolución de Práctica Requerida')
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line('La CPPP ha elevado una solicitud de práctica preprofesional que requiere su resolución.')
                    ->line("Estudiante: {$studentName}.")
                    ->action('Ir al Dashboard de Decano', route('decano.dashboard.index'))
                    ->line('Por favor, revise la solicitud pendiente en su panel.');
    }

    /**
     * Define el mensaje para la Base de Datos (Campanita 🔔).
     */
    public function toDatabase(object $notifiable): array
    {
        $studentName = $this->practica->student->name;

        // La ruta 'decano.dashboard.index' es la más lógica,
        // ya que su panel listará las prácticas pendientes.
        return [
            'practica_id' => $this->practica->id,
            'student_name' => $studentName,
            'message' => "La práctica del est. {$studentName} requiere su resolución.",
            'url' => route('decano.dashboard.index'),
        ];
    }
}