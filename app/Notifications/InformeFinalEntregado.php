<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional; // Importamos tu modelo

class InformeFinalEntregado extends Notification
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
        // $notifiable es el Asesor que recibe la notificación
        $studentName = $this->practica->student->name; // Asumimos la relación 'student'
        $practicaId = $this->practica->id;

        return (new MailMessage)
                    ->subject("Informe Final Recibido - {$studentName}")
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line("El estudiante {$studentName} ha entregado su Informe Final.")
                    ->line('La práctica está lista para su revisión y dictamen F2.')
                    ->action('Revisar Práctica', route('asesor.practicas.show', $practicaId))
                    ->line('Gracias por su colaboración.');
    }

    /**
     * Define el mensaje para la Base de Datos (Campanita 🔔).
     */
    public function toDatabase(object $notifiable): array
    {
        $studentName = $this->practica->student->name; // Asumimos la relación 'student'
        $practicaId = $this->practica->id;

        return [
            'practica_id' => $practicaId,
            'student_name' => $studentName,
            'message' => "{$studentName} ha entregado su informe final. Listo para dictamen F2.",
            'url' => route('asesor.practicas.show', $practicaId),
        ];
    }
}