<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PracticaPreprofesional;
use App\Notifications\NotificacionPractica;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log; // Importante para registrar la tarea
use Carbon\Carbon;

class CheckPracticeDeadlines extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     *
     * @var string
     */
    protected $signature = 'app:check-practice-deadlines';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Verifica plazos de entrega de informes (F3) y anula prácticas vencidas.';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('Iniciando verificación de plazos de PPP...');
        Log::info('Ejecutando CheckPracticeDeadlines...');

        // --- 1. LÓGICA DE ANULACIÓN (Plazo de 30 días vencido) ---
        // "el informe final... deberá presentarse... en un plazo máximo de 30 días... caso contrario se dará por anulada." [cite: 148-150]

        $deadlineDate = Carbon::now()->subDays(30)->toDateString();
        
        // Buscamos prácticas:
        // 1. Cuyo estado sea 'initial_approved' (Aprobadas, pero aún no entregan F3)
        // 2. Cuya fecha de fin ('end_date') sea más antigua que hace 30 días.
        // 3. Que sean 'regulares' (la validación laboral no aplica esta regla)
        $practicasParaAnular = PracticaPreprofesional::where('status', 'initial_approved')
            ->where('practice_type', 'regular')
            ->whereDate('end_date', '<=', $deadlineDate)
            ->get();

        $countAnuladas = 0;
        foreach ($practicasParaAnular as $practica) {
            $practica->update([
                'status' => 'annulled',
                'annulment_reason' => 'Anulada automáticamente: No presentó el informe final 30 días después de la fecha de fin (' . $practica->end_date . ').'
            ]);

            // Notificar al estudiante
            $asunto = 'Práctica Anulada por Plazo Vencido';
            $linea = 'Tu práctica en "' . $practica->entity_name . '" ha sido anulada automáticamente por no presentar el informe final en el plazo de 30 días.';
            $practica->student->notify(new NotificacionPractica($asunto, $linea, 'Ver Dashboard', route('dashboard')));
            
            $countAnuladas++;
        }

        if ($countAnuladas > 0) {
            Log::warning("Se anularon $countAnuladas prácticas por plazo vencido.");
        }

        
        // --- 2. LÓGICA DE ADVERTENCIA (Alerta de 7 días) ---
        // Plazo de 30 días - 7 días de advertencia = 23 días.
        $warningDate = Carbon::now()->subDays(23)->toDateString();

        // Buscamos prácticas:
        // 1. Cuyo estado sea 'initial_approved'
        // 2. Cuya fecha de fin ('end_date') sea EXACTAMENTE hoy hace 23 días.
        $practicasParaAdvertir = PracticaPreprofesional::where('status', 'initial_approved')
            ->where('practice_type', 'regular')
            ->whereDate('end_date', $warningDate)
            ->get();

        $countAdvertidas = 0;
        foreach ($practicasParaAdvertir as $practica) {
            // Notificar al estudiante
            $asunto = 'Alerta de Plazo: 7 Días Restantes para Presentar Informe';
            $linea = 'Te recordamos que solo tienes 7 días (hasta el ' . $practica->end_date->addDays(30)->format('d/m/Y') . ') para presentar tu informe final. De lo contrario, tu práctica será anulada.';
            $practica->student->notify(new NotificacionPractica($asunto, $linea, 'Subir Informe Ahora', route('practicas.final_report.create', $practica)));
            
            $countAdvertidas++;
        }

        if ($countAdvertidas > 0) {
            Log::info("Se enviaron $countAdvertidas advertencias de plazo de 7 días.");
        }


        $this->info("Verificación de plazos completada. Anuladas: $countAnuladas, Advertidas: $countAdvertidas.");
        Log::info("CheckPracticeDeadlines finalizado. Anuladas: $countAnuladas, Advertidas: $countAdvertidas.");
        return 0; // 0 significa éxito
    }
}