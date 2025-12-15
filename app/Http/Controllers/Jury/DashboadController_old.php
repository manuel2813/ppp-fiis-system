<?php

namespace App\Http\Controllers\Jury;

use App\Http\Controllers\Controller;
use App\Models\PracticaPreprofesional;
use App\Models\JuradoAssignment; // <-- Importante para la nueva lógica
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Jury\SubmitGradeRequest;
use Illuminate\Support\Facades\DB;
use App\Notifications\NotificacionPractica; // <-- Importar Notificaciones
use Illuminate\Support\Facades\Notification; // <-- Importar Notificaciones
use App\Models\Role; // <-- Importar Role

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
     * (ACTUALIZADO para Votación)
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

    // 3. Documentos (La lógica de F3, F4, etc. ya está en tu vista, está bien)

    return view('jury.dashboard.show', [
        'practica' => $practica,
        'allAssignments' => $allAssignments,
        'myAssignment' => $myAssignment, // Esta variable es crucial
    ]);
}
    
    /**
     * Helper para verificar si el usuario actual es el Presidente del jurado.
     * (Lo mantenemos para el método submitGrade)
     */
    private function isJuryPresident(PracticaPreprofesional $practica): bool
    {
        $assignment = $practica->juradoAssignments()
                               ->where('jurado_member_id', Auth::id())
                               ->first();
        return $assignment && $assignment->role === 'Presidente';
    }


    /**
     * NUEVO: Registra el Voto (V°B° o Observación) de un miembro del jurado.
     * (Reemplaza a 'observeReport' y 'approveReport')
     */
    public function submitReportVote(Request $request, PracticaPreprofesional $practica)
    {
        $validated = $request->validate([
            'vote_type' => 'required|in:approve,observe',
            'observation_notes' => 'nullable|required_if:vote_type,observe|string|min:20',
        ], [
            'observation_notes.required' => 'Debe proveer una razón clara para la observación del informe.'
        ]);

        $assignment = JuradoAssignment::where('practica_id', $practica->id)
                                    ->where('jurado_member_id', Auth::id())
                                    ->first();

        if (!$assignment || $assignment->role === 'Suplente') {
            abort(403, 'Acción no autorizada.');
        }

        // --- Lógica de Observación (Veto) ---
        if ($validated['vote_type'] === 'observe') {
            $assignment->update(['report_approved' => false]);
            
            $practica->update([
                'status' => 'jury_observed',
                'observation_notes' => $validated['observation_notes']
            ]);
            
            // Resetea los votos de los otros miembros
            $practica->juradoAssignments()
                     ->where('jurado_member_id', '!=', Auth::id())
                     ->update(['report_approved' => null]);
            
            // Notificar al estudiante
            $practica->student->notify(new NotificacionPractica(
                'Su Informe de PPP ha sido Observado por el Jurado',
                'El jurado ha registrado observaciones a su informe. Razón: ' . $validated['observation_notes'],
                'Corregir Informe',
                route('dashboard')
            ));

            return back()->with('success', 'Observación registrada. El estudiante deberá corregir.');
        }

        // --- Lógica de Aprobación (V°B°) ---
        if ($validated['vote_type'] === 'approve') {
            $assignment->update(['report_approved' => true]);

            // Revisar si ya todos aprobaron
            $allAssignments = $practica->juradoAssignments()->whereIn('role', ['Presidente', 'Miembro'])->get();
            
            // Contamos solo los miembros principales (Presidente y Miembros)
            $totalMembers = $allAssignments->count();
            $approvedCount = $allAssignments->where('report_approved', true)->count();

            if ($approvedCount === $totalMembers) {
                // ¡Todos aprobaron! Avanza el estado.
                $practica->update([
                    'status' => 'pending_defense_date',
                    'observation_notes' => null
                ]);
                
                // Notificar a CPPP
                $cpppUser = Role::where('name', 'cppp')->first()->users()->first();
                if ($cpppUser) {
                    $cpppUser->notify(new NotificacionPractica(
                        'Informe Aprobado por Jurado - Programar Fecha',
                        'El jurado ha aprobado por unanimidad el informe de ' . $practica->student->name . '. Por favor, programe la fecha de sustentación.',
                        'Programar Fecha',
                        route('cppp.practicas.show', $practica)
                    ));
                }

                return back()->with('success', 'Visto Bueno (V°B°) registrado. ¡El informe ha sido aprobado por unanimidad!');
            }

            return back()->with('success', 'Visto Bueno (V°B°) registrado. Esperando la revisión de los demás miembros.');
        }
        
        return back()->with('error', 'Acción desconocida.');
    }
    
    // --- LOS MÉTODOS 'approveReport' y 'observeReport' SE ELIMINAN ---


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
     * Registra la calificación final (solo Presidente).
     * Esta lógica no cambia.
     */
    public function submitGrade(SubmitGradeRequest $request, PracticaPreprofesional $practica)
    {
        if (!$this->isJuryPresident($practica)) {
            abort(403, 'Acción no autorizada. Solo el Presidente del jurado puede registrar la calificación final.');
        }

        $validated = $request->validated();
        $grade = (int) $validated['final_grade'];
        
        $status = ($grade >= 11) ? 'completed_approved' : 'completed_failed';
        $literalGrade = $this->getLiteralGrade($grade);

        try {
            DB::beginTransaction();

            $basePath = "practicas/{$practica->id}/actas";
            $f5Path = $request->file('file_f5')->store($basePath);
            
            $practica->documents()->updateOrCreate(
                ['type' => 'F5_ACTA_EXPOSICION'],
                ['file_path' => $f5Path, 'upload_date' => now()]
            );

            $practica->update([
                'final_grade' => $grade,
                'status' => $status,
                'defense_approval_date' => now()
            ]);
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar la calificación. Intente de nuevo.');
        }
        
        // Notificar al estudiante
        $practica->student->notify(new NotificacionPractica(
            'Resultado de su Sustentación de PPP',
            'Su sustentación ha sido calificada. Su nota final es: ' . $grade . '/20. Estado: ' . $literalGrade,
            'Ver Resultado',
            route('dashboard')
        ));

        return redirect()->route('jury.dashboard.index')
                         ->with('success', 'Calificación registrada y acta subida. El proceso ha finalizado.');
    }
}