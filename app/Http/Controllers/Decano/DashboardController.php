<?php

namespace App\Http\Controllers\Decano;

use App\Http\Controllers\Controller;
use App\Models\PracticaPreprofesional;
use Illuminate\Http\Request;
use App\Notifications\NotificacionPractica;
use App\Notifications\SolicitudAprobada;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard del Decano con las prácticas pendientes de resolución.
     */
    public function index()
    {
        // 1. Buscamos prácticas PENDIENTES (Arriba en la vista)
        // Ordenamos por fecha de actualización ascendente (las que llevan más tiempo esperando primero)
        $pendingInitial = PracticaPreprofesional::where('status', 'pending_dean_resolution')
            ->with(['student', 'advisor'])
            ->orderBy('updated_at', 'asc')
            ->get();
        
        // 2. Buscamos el HISTORIAL de resoluciones emitidas (Abajo en la vista)
        // Filtramos las que tienen número de resolución y están aprobadas o finalizadas
        // Ordenamos por fecha de actualización descendente (lo último que hizo el decano aparece primero)
        $history = PracticaPreprofesional::whereNotNull('resolution_number')
            ->whereNotIn('status', ['pending_dean_resolution', 'in_review_initial', 'annulled']) // Excluir pendientes o anuladas si se desea
            ->with(['student', 'advisor'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('decano.dashboard.index', [
            'pendingInitial' => $pendingInitial,
            'history' => $history
        ]);
    }

    /**
     * Emite la Resolución de Autorización inicial AUTOMÁTICAMENTE.
     */
    public function issueInitialResolution(Request $request, PracticaPreprofesional $practica)
    {
        // Autorización
        if ($practica->status !== 'pending_dean_resolution') {
            abort(403);
        }

        // --- LÓGICA DE GENERACIÓN DE NÚMERO DE RESOLUCIÓN ---
        
        $year = date('Y'); // Año actual (ej. 2025)
        $suffix = "-{$year}-CU-R-UNAS"; // La parte final del formato
        
        // Buscamos todas las resoluciones de ESTE AÑO que coincidan con el formato
        // Hacemos esto para encontrar el último número secuencial usado
        $existingResolutions = PracticaPreprofesional::where('resolution_number', 'LIKE', "%{$suffix}")
            ->pluck('resolution_number');

        $maxSequence = 0;

        foreach ($existingResolutions as $res) {
            // El formato es "N.º XXX-2025-CU-R-UNAS"
            // Intentamos extraer el número XXX. 
            // Usamos expresión regular para sacar los dígitos que están entre "N.º " y el guion del año.
            if (preg_match('/N.º\s*(\d+)-/', $res, $matches)) {
                $sequence = intval($matches[1]);
                if ($sequence > $maxSequence) {
                    $maxSequence = $sequence;
                }
            }
        }

        // El nuevo número es el máximo encontrado + 1
        $newSequence = $maxSequence + 1;
        
        // Formateamos con ceros a la izquierda (pad a 3 dígitos: 1 -> 001, 10 -> 010)
        $newSequenceStr = str_pad($newSequence, 3, '0', STR_PAD_LEFT);
        
        // Construimos la cadena final
        $finalResolutionNumber = "N.º {$newSequenceStr}{$suffix}";

        // ----------------------------------------------------

        // 1. Actualizamos la práctica con el número autogenerado
        $practica->update([
            'status' => 'initial_approved', // ¡Aprobada!
            'resolution_number' => $finalResolutionNumber
        ]);

        // 2. Notificar al estudiante
        try {
            $practica->load('student'); 
            $student = $practica->student;

            if ($student) {
                $student->notify(new SolicitudAprobada($practica));
            }

        } catch (\Exception $e) {
            Log::error('Fallo al enviar notificación de aprobación al estudiante: ' . $e->getMessage());
        }

        // 3. Redirige al Decano
        return redirect()->route('decano.dashboard.index')
                         ->with('success', "Resolución emitida correctamente: {$finalResolutionNumber}");
    }
}