<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional; // Importamos tu modelo

class SolicitudObservada extends Notification
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
        // $notifiable es el Estudiante
        $observationNotes = $this->practica->observation_notes;

        // La ruta para editar la solicitud inicial
        $editUrl = route('practicas.edit', $this->practica->id); 

        return (new MailMessage)
                    ->subject('Tu Solicitud de PPP tiene Observaciones')
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line('Tu solicitud de práctica preprofesional ha sido observada por la CPPP.')
                    ->line('**Observaciones:**')
                    ->line("*" . nl2br(e($observationNotes)) . "*") // nl2br para saltos de línea
                    ->line('Por favor, corrige las observaciones para continuar con tu trámite.')
                    ->action('Corregir Solicitud', $editUrl);
    }

    /**
     * Define el mensaje para la Base de Datos (Campanita 🔔).
     */
    public function toDatabase(object $notifiable): array
    {
        $practicaId = $this->practica->id;

        // La ruta para editar la solicitud inicial
        $editUrl = route('practicas.edit', $practicaId);

        return [
            'practica_id' => $practicaId,
            'message' => 'Tu solicitud inicial tiene observaciones. ¡Debes corregirla!',
            'url' => $editUrl,
        ];
    }
}