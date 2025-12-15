<?php

namespace App\Http\Controllers\Jury;

use App\Http\Controllers\Controller;
use App\Models\PracticaPreprofesional;
use App\Models\JuradoAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Jury\SubmitGradeRequest; // <-- Necesario para submitGrade
use Illuminate\Support\Facades\DB; // <-- Necesario para submitGrade
use App\Notifications\NotificacionPractica; // <-- Necesario para submitGrade
use App\Models\User;
use App\Notifications\SustentacionCalificada; // La nueva clase
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\PracticaDocument;
use Illuminate\Support\Facades\Storage;

// Nota: 'Role' y 'Notification' ya no son necesarios aquí si solo se usaban en la función eliminada.

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard del Jurado con sus prácticas asignadas.
     */
    public function index()
    {
        $user = Auth::user();

        $assignments = $user->asignacionesJurado()
            ->with(['practica.student', 'practica.advisor'])
            ->whereHas('practica', function ($query) {
                $query->whereIn('status', [
                    'pending_jury_review',
                    'jury_observed',
                    'pending_defense_date',
                    'defense_scheduled',
                    'completed_approved',
                    'completed_failed'
                ]);
            })
            ->get();

        return view('jury.dashboard.index', [
            'assignments' => $assignments
        ]);
    }

    /**
     * Muestra el detalle de una práctica para que el jurado la revise.
     * (ACTUALIZADO para Votación por Mayoría)
     */
    public function show(PracticaPreprofesional $practica)
    {
        // Cargar relaciones necesarias para la vista
        $practica->load(
            'student',
            'advisor',
            'documents',
            'juradoAssignments.juradoMember' // Carga todas las asignaciones y el usuario (jurado)
        );

        // 1. Obtener todas las asignaciones (para el sidebar)
        // Ordenar: Presidente (1), Miembro (2), Suplente (3)
        $allAssignments = $practica->juradoAssignments
            ->sortBy(function ($item) {
                $order = ['Presidente' => 1, 'Miembro' => 2, 'Suplente' => 3];
                return $order[$item->role] ?? 4;
            });

        // 2. Obtener la asignación específica del usuario logueado
        $myAssignment = $practica->juradoAssignments
            ->where('jurado_member_id', Auth::id())
            ->first();

        return view('jury.dashboard.show', [
            'practica' => $practica,
            'allAssignments' => $allAssignments,
            'myAssignment' => $myAssignment, // Esta variable es crucial
        ]);
    }

    /**
     * Helper para verificar si el usuario actual es el Presidente del jurado.
     */
    private function isJuryPresident(PracticaPreprofesional $practica): bool
    {
        $assignment = $practica->juradoAssignments()
            ->where('jurado_member_id', Auth::id())
            ->first();
            
        // Hacemos la comprobación más segura (ignora mayúsculas/minúsculas y revisa estado)
        return $assignment &&
               strtolower($assignment->role) === 'presidente' &&
               $assignment->estado === 'Activo';
    }


    /**
     * * ¡FUNCIÓN ELIMINADA!
     * * La función 'submitReportVote' ha sido eliminada.
     * Toda la lógica de votación (Visto Bueno / Observar) ahora es
     * manejada por el controlador 'JuradoVotoController'.
     * */
    // public function submitReportVote(...) { ... }


    /**
     * Convierte la nota numérica a la calificación literal según la directiva.
     */
    private function getLiteralGrade(int $grade): string
    {
        if ($grade >= 19) return 'Excelente';
        if ($grade >= 17) return 'Muy bueno';
        if ($grade >= 14) return 'Bueno';
        if ($grade >= 11) return 'Regular';
        return 'Desaprobado';
    }

    /**
     * ====================================================================
     * ¡ESTA ES LA FUNCIÓN QUE RESPONDE A TU PREGUNTA!
     * ====================================================================
     * * Registra la calificación final (solo Presidente) DESPUÉS de la sustentación.
     */
    public function submitGrade(SubmitGradeRequest $request, PracticaPreprofesional $practica)
    {
        // 1. Validamos que el usuario es el Presidente
        if (!$this->isJuryPresident($practica)) {
            abort(403, 'Acción no autorizada. Solo el Presidente del jurado puede registrar la calificación final.');
        }

        // 2. Validamos que la práctica esté en el estado correcto
        if ($practica->status !== 'defense_scheduled') {
             abort(403, 'La calificación solo se puede registrar después de programar la sustentación.');
        }

        $validated = $request->validated();
        $grade = (int) $validated['final_grade'];

        // 3. Determinamos el estado final (Aprobado o Desaprobado)
        $status = ($grade >= 11) ? 'completed_approved' : 'completed_failed';

        try {
            DB::beginTransaction();

            // 4. Guardamos el archivo F5 (Acta)
            $basePath = "practicas/{$practica->id}/actas";
            $f5Path = $request->file('file_f5')->store($basePath);
            $practica->documents()->updateOrCreate(
                ['type' => 'F5_ACTA_EXPOSICION'],
                ['file_path' => $f5Path, 'upload_date' => now()]
            );

            // 5. Actualizamos la práctica con la nota y el estado final
            $practica->update([
                'final_grade' => $grade,
                'status' => $status,
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar la calificación: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar la calificación. Intente de nuevo.');
        }

        // *****************************************************
        // * BLOQUE DE NOTIFICACIÓN CORREGIDO
        // *****************************************************
        try {
            // Cargamos todas las relaciones necesarias
            $practica->load('student', 'advisor', 'jurados');

            // 1. Creamos la colección de destinatarios
            $recipients = collect();
            $recipients->push($practica->student); // Estudiante
            $recipients->push($practica->advisor); // Asesor
            $recipients = $recipients->merge($practica->jurados); // Todos los Jurados

            // 2. Notificar a CPPP
            $cpppUsers = User::whereHas('role', fn($q) => $q->where('name', 'cppp'))->get();
            $recipients = $recipients->merge($cpppUsers); // Añadimos CPPP

            // 3. Filtramos nulos y enviamos
            $recipients = $recipients->filter();
            
            if ($recipients->isNotEmpty()) {
                // Usamos la notificación que ya existe
                Notification::send($recipients, new SustentacionCalificada($practica));
            }

        } catch (\Exception $e) {
            Log::error('Fallo al enviar notificación (SustentacionCalificada): ' . $e->getMessage());
        }
        // *****************************************************

        return redirect()->route('jury.dashboard.index')
                         ->with('success', 'Calificación registrada y acta subida. El proceso ha finalizado.');
    }

/**
     * Maneja la descarga segura de archivos para el Jurado.
     */
    public function downloadDocument(PracticaDocument $document)
    {
        // 1. Cargamos la práctica a la que pertenece el documento
        $practica = $document->practica;

        // 2. VERIFICACIÓN DE PERMISO:
        // ¿El usuario autenticado (Jurado) está asignado a esta práctica?
        $isAssigned = $practica->juradoAssignments()
                               ->where('jurado_member_id', Auth::id())
                               ->exists();
        
        // El Asesor de la práctica también puede descargar los docs desde aquí si es necesario
        $isAdvisor = $practica->advisor_id === Auth::id();

        if (!$isAssigned && !$isAdvisor) {
             abort(403, 'Acción no autorizada.');
        }

        // 3. Verificamos que el archivo exista
        if (!Storage::exists($document->file_path)) {
            abort(404, 'Archivo no encontrado.');
        }

        // 4. Retornamos la descarga
        return Storage::download($document->file_path);
    }
}