<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\PracticaPreprofesional;
use App\Models\User; // <-- Importante: necesitamos el modelo User
use Carbon\Carbon;   // <-- Importante: para formatear la fecha

class SustentacionProgramada extends Notification
{
    use Queueable;

    protected $practica;
    protected $studentName;
    protected $defenseDateTime;
    protected $defensePlace;

    /**
     * Crea una nueva instancia de la notificación.
     */
    public function __construct(PracticaPreprofesional $practica)
    {
        $this->practica = $practica;
        $this->studentName = $practica->student->name ?? 'el estudiante';

        // Formateamos la fecha y hora para que sea legible
        $this->defenseDateTime = Carbon::parse($practica->defense_date)
                                     ->format('d \de F \d\e\l Y \a \l\a\s H:i'); // ej: 14 de Noviembre del 2025 a las 09:40

        $this->defensePlace = $practica->defense_place;
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
        // $notifiable es el usuario que recibe (Estudiante, Asesor o Jurado)
        return (new MailMessage)
                    ->subject("Programación de Sustentación: {$this->studentName}")
                    ->line('Hola ' . $notifiable->name . ',')
                    ->line("Se ha programado la sustentación de práctica preprofesional del estudiante {$this->studentName}.")
                    ->line("**Fecha y Hora:** " . $this->defenseDateTime)
                    ->line("**Lugar/Enlace:** " . $this->defensePlace)
                    ->action('Ver Detalles', $this->getNotificationUrl($notifiable))
                    ->line('Se espera su puntual asistencia.');
    }

    /**
     * Define el mensaje para la Base de Datos (Campanita 🔔).
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'practica_id' => $this->practica->id,
            'message' => "Sustentación ({$this->studentName}) programada: {$this->defenseDateTime}.",
            'url' => $this->getNotificationUrl($notifiable), // <-- URL inteligente
        ];
    }

    /**
     * Método auxiliar "inteligente" para obtener la URL correcta
     * según el rol del usuario que recibe la notificación.
     */
    protected function getNotificationUrl(User $notifiable): string
    {
        $practicaId = $this->practica->id;

        // Asumimos que el usuario tiene la relación 'role' cargada
        $roleName = $notifiable->role->name ?? ''; 

        switch ($roleName) {
            case 'asesor':
                return route('asesor.practicas.show', $practicaId);
            case 'jurado':
                return route('jury.practicas.show', $practicaId);
            case 'estudiante':
                return route('dashboard'); // El dashboard del estudiante
            default:
                // Como fallback, el panel de CPPP (o dashboard genérico)
                return route('cppp.practicas.show', $practicaId);
        }
    }
}