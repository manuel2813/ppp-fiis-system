<?php

namespace App\Http\Controllers\CPPP; // <-- Nota la carpeta CPPP
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\PracticaPreprofesional;
use App\Models\PracticaDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Role; 
use App\Models\User; 
use App\Models\JuradoAssignment; // <-- Importa el modelo de Asignación de Jurado
use App\Http\Requests\CPPP\AssignJuryRequest; // <-- Importa el nuevo Request
use Illuminate\Support\Facades\DB; // <-- Importa DB
use App\Http\Requests\CPPP\ScheduleDefenseRequest; // <-- Importa el nuevo Request
use App\Http\Requests\CPPP\AnnulPracticaRequest; // <-- Importa el nuevo Request
use App\Notifications\ResolucionRequerida;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Notifications\SolicitudObservada; // La nueva clase
use App\Notifications\SustentacionProgramada; // La nueva clase
use App\Notifications\AmpliacionAprobada; // <-- AÑADE ESTA
use App\Notifications\AmpliacionRechazada;
use App\Notifications\PracticaAnulada;
use App\Notifications\JuradoAsignado;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard CPPP con todas las solicitudes que requieren ACCIÓN.
     */
    public function index()
    {
        // 1. Estados que requieren que la CPPP haga algo
        $pendingStatuses = [
            'in_review_initial',
            'pending_jury_assignment',
            'pending_defense_date',
            'pending_extension',
            'pending_dean_resolution', // <-- Asegúrate de que este también esté
        ];

        $pendingPracticas = PracticaPreprofesional::whereIn('status', $pendingStatuses)
            ->with(['student', 'advisor']) 
            ->orderBy('created_at', 'asc')
            ->get();
            
        // 2. Estados que requieren el paso final (Emitir Constancia)
        $certificatePendingPracticas = PracticaPreprofesional::where('status', 'completed_approved')
            ->with(['student', 'advisor'])
            ->orderBy('updated_at', 'asc')
            ->get();
        
        // --- INICIO DE CÓDIGO NUEVO ---
        // 3. Historial de prácticas finalizadas (completadas o anuladas)
        $finishedStatuses = ['completed', 'annulled', 'completed_failed'];
        $finishedPracticas = PracticaPreprofesional::whereIn('status', $finishedStatuses)
            ->with(['student'])
            ->orderBy('updated_at', 'desc') // Más recientes primero
            ->get();
        // --- FIN DE CÓDIGO NUEVO ---
        
        return view('cppp.dashboard.index', [
            'pendingPracticas' => $pendingPracticas,
            'certificatePendingPracticas' => $certificatePendingPracticas,
            'finishedPracticas' => $finishedPracticas // <-- Pasamos la nueva variable
        ]);
    }
    /**
     * Muestra el detalle de una solicitud de práctica.
     */
    public function show(PracticaPreprofesional $practica)
    {
        // Cargamos todas las relaciones necesarias para la vista
        $practica->load(['student', 'advisor', 'documents']);

        // --- AÑADIR ESTA LÓGICA ---
        $juradosDisponibles = [];

        // Solo cargamos la lista de docentes si estamos en la etapa de asignación
        if ($practica->status === 'pending_jury_assignment') {
            // Buscamos roles de 'jurado' Y 'asesor' (ambos son docentes)
            $docenteRoles = Role::whereIn('name', ['jurado', 'asesor'])->pluck('id');
            
            $juradosDisponibles = User::whereIn('role_id', $docenteRoles)
                                    ->orderBy('name')
                                    ->get();
        }
        // --- FIN DE LÓGICA AÑADIDA ---

        return view('cppp.dashboard.show', [
            'practica' => $practica,
            'jurados' => $juradosDisponibles // <-- Pasa la variable a la vista
        ]);
    }

    /**
     * Asigna el Jurado Evaluador a la práctica.
     */
    public function assignJury(AssignJuryRequest $request, PracticaPreprofesional $practica)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // 1. Limpiar asignaciones
            $practica->juradoAssignments()->delete();

            // 2. Crear asignaciones
            $practica->juradoAssignments()->create([
                'jurado_member_id' => $validated['presidente_id'],
                'role' => 'Presidente'
            ]);
            $practica->juradoAssignments()->create([
                'jurado_member_id' => $validated['miembro1_id'],
                'role' => 'Miembro'
            ]);
            $practica->juradoAssignments()->create([
                'jurado_member_id' => $validated['miembro2_id'],
                'role' => 'Miembro'
            ]);

            // 3. Asignar suplente
            if ($request->filled('suplente_id')) {
                $practica->juradoAssignments()->create([
                    'jurado_member_id' => $validated['suplente_id'],
                    'role' => 'Suplente',
                    'estado' => 'Pendiente'
                ]);
            }
            
            // 4. Guardar Resolución
            $basePath = "practicas/{$practica->id}/resoluciones";
            $resPath = $request->file('file_resolution')->store($basePath);
            $practica->documents()->updateOrCreate(
                ['type' => 'RESOLUCION_JURADO'],
                ['file_path' => $resPath, 'upload_date' => now()]
            );

            // 5. Actualizar estado
            $practica->update([
                'status' => 'pending_jury_review'
            ]);
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al asignar el jurado. Intente de nuevo.');
        }

        // *****************************************************
        // * BLOQUE DE NOTIFICACIÓN NUEVO
        // *****************************************************
        try {
            // Usamos la relación 'jurados' (BelongsToMany) que ya tienes
            // en tu modelo PracticaPreprofesional
            $practica->load('student', 'jurados'); 

            if ($practica->jurados->isNotEmpty()) {
                Notification::send($practica->jurados, new JuradoAsignado($practica));
            }
        } catch (\Exception $e) {
            Log::error('Fallo al enviar notificación (JuradoAsignado): ' . $e->getMessage());
        }
        // *****************************************************

        return redirect()->route('cppp.dashboard.index')
                         ->with('success', 'Jurado asignado y notificado correctamente.');
    }
    /**
     * Propone la solicitud inicial al Decanato para su resolución.
     */
   public function approve(Request $request, PracticaPreprofesional $practica)
{
    // Autorización
    if ($practica->status !== 'in_review_initial') {
        abort(403);
    }

    // 1. Actualiza la práctica
    $practica->update([
        'status' => 'pending_dean_resolution', // <-- NUEVO ESTADO
        'observation_notes' => null // Limpiamos observaciones previas
    ]);

    // --- INICIO: ENVIAR NOTIFICACIÓN AL DECANO ---
    try {
        // Buscamos al usuario(s) con el rol 'decano'
        $decanos = User::whereHas('role', function ($query) {
            $query->where('name', 'decano');
        })->get();

        if ($decanos->isNotEmpty()) {
            // Cargamos la relación 'student' para usar el nombre en la notificación
            $practica->load('student'); 

            // Enviamos la notificación a todos los usuarios que sean 'decano'
            Notification::send($decanos, new ResolucionRequerida($practica));
        } else {
            Log::warning('No se encontró un usuario "decano" para notificar.');
        }

    } catch (\Exception $e) {
        // Si la notificación falla, no detenemos la acción principal.
        Log::error('Fallo al enviar notificación al decano: ' . $e->getMessage());
    }
    // --- FIN: NOTIFICACIÓN ---

    // 3. Redirige a CPPP
    return redirect()->route('cppp.dashboard.index')
                     ->with('success', 'Solicitud propuesta y elevada al Decanato para su Resolución.');
}

    /**
     * Observa (rechaza) la solicitud inicial.
     */
    public function observe(Request $request, PracticaPreprofesional $practica)
{
    // 1. Validamos que la observación no esté vacía
    $validated = $request->validate([
        'observation_notes' => 'required|string|min:20',
    ], [
        'observation_notes.required' => 'Debe proveer una razón clara para la observación.',
        'observation_notes.min' => 'La observación debe tener al menos 20 caracteres.'
    ]);

    // 2. Actualizamos el estado y guardamos la observación
    $practica->update([
        'status' => 'initial_observed', // Estado "Observado"
        'observation_notes' => $validated['observation_notes'] // Usamos $validated
    ]);

    // --- INICIO: Notificar al estudiante (NUEVO SISTEMA) ---
    try {
        // Cargamos la relación 'student'
        $practica->load('student'); 
        $student = $practica->student;

        if ($student) {
            // $practica ya contiene las 'observation_notes' del update
            $student->notify(new SolicitudObservada($practica));
        }

    } catch (\Exception $e) {
        // Si la notificación falla, no detenemos la acción de CPPP
        Log::error('Fallo al enviar notificación de observación al estudiante: ' . $e->getMessage());
    }
    // --- FIN: Notificación ---

    // 4. Redirige a CPPP
    return redirect()->route('cppp.dashboard.index')
                     ->with('success', 'Solicitud marcada como "Observada". El estudiante deberá corregirla.');
}
    
    /**
     * Maneja la descarga segura de archivos.
     */
    public function downloadDocument(PracticaDocument $document)
    {
        // El Gate 'isCPPP' ya protegió la ruta, así que sabemos que el usuario es CPPP.
        
        // Verificamos que el archivo exista en el almacenamiento
        if (!Storage::exists($document->file_path)) {
            abort(404, 'Archivo no encontrado.');
        }

        // Retornamos la descarga
        return Storage::download($document->file_path);
    }

    /**
     * Programa la fecha y lugar de la sustentación.
     */
    public function scheduleDefense(ScheduleDefenseRequest $request, PracticaPreprofesional $practica)
{
    // La validación (incluyendo autorización) ya ocurrió en el Request
    $validated = $request->validated();

    try {
        // 1. Actualizamos la práctica (como ya lo tenías)
        $practica->update([
            'defense_date' => $validated['defense_date'],
            'defense_place' => $validated['defense_place'],
            'status' => 'defense_scheduled' // ¡Sustentación Programada!
        ]);

        // --- INICIO: ENVIAR NOTIFICACIÓN MULTI-ROL ---
        try {
            // Asumo que 'jurados' es la relación que definimos
            // para los miembros del jurado (como en la Notif. de JuradoAsignado)
            $practica->load('student', 'advisor', 'jurados');

            // 1. Creamos una colección de destinatarios
            $recipients = collect();
            $recipients->push($practica->student); // Añadimos al Estudiante
            $recipients->push($practica->advisor); // Añadimos al Asesor

            // Añadimos a todos los jurados
            $recipients = $recipients->merge($practica->jurados); 

            // 2. Filtramos nulos (p.ej. si no hay asesor) y enviamos
            $recipients = $recipients->filter();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new SustentacionProgramada($practica));
            }

        } catch (\Exception $e) {
            // Si la notificación falla, no detenemos la acción principal
            Log::error('Fallo al enviar notificación de sustentación: ' . $e->getMessage());
        }
        // --- FIN: NOTIFICACIÓN ---

    } catch (\Exception $e) {
        Log::error('Error al programar la sustentación: ' . $e->getMessage()); // <-- Loguear el error
        return back()->with('error', 'Error al programar la sustentación. Intente de nuevo.');
    }

    return redirect()->route('cppp.dashboard.index')
                     ->with('success', 'Sustentación programada y notificada exitosamente.');
}

    /**
     * Emite la Constancia de Cumplimiento (Paso Final).
     */
    public function issueCertificate(Request $request, PracticaPreprofesional $practica)
    {
        if ($practica->status !== 'completed_approved') {
            abort(403, 'La práctica no está aprobada para emitir constancia.');
        }

        try {
            DB::beginTransaction(); 

            $practica->update([
                'compliance_certificate_issued' => true,
                'status' => 'completed', 
                'constancia_emitted_at' => now(),
                'constancia_issuer_id' => Auth::id() // <-- ¡LA LÍNEA CLAVE!
            ]);
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack(); 
            return back()->with('error', 'Error al emitir la constancia: ' . $e->getMessage());
        }

        return redirect()->route('cppp.dashboard.index')
                         ->with('success', 'Constancia de Cumplimiento registrada. El proceso ha finalizado.');
    }

    /**
     * Autoriza una segunda oportunidad de sustentación.
     */
    public function allowSecondAttempt(Request $request, PracticaPreprofesional $practica)
    {
        // Autorización: ¿Es CPPP y la práctica está desaprobada?
        if ($practica->status !== 'completed_failed') {
            abort(403, 'La práctica no está desaprobada.');
        }

        try {
            DB::beginTransaction();

            // 1. Borrar el Acta (F5) de la sustentación fallida
            $acta_f5 = $practica->documents()->where('type', 'F5_ACTA_EXPOSICION')->first();
            if ($acta_f5) {
                Storage::delete($acta_f5->file_path);
                $acta_f5->delete();
            }

            // 2. Reiniciar el estado de la práctica
            $practica->update([
                'status' => 'pending_defense_date', // Vuelve a "Pendiente de Programar Fecha"
                'final_grade' => null, // Borra la nota desaprobatoria
                'defense_date' => null,
                'defense_place' => null,
                'observation_notes' => 'Segunda oportunidad de sustentación autorizada por CPPP.'
            ]);
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // (Opcional: Loguear $e->getMessage())
            return back()->with('error', 'Error al autorizar la segunda oportunidad.');
        }

        return redirect()->route('cppp.dashboard.index')
                         ->with('success', 'Segunda oportunidad autorizada. La práctica está lista para programar una nueva fecha de sustentación.');
    }

    /**
     * Anula (cancela permanentemente) una práctica.
     */
    public function annul(AnnulPracticaRequest $request, PracticaPreprofesional $practica)
{
    // La validación y autorización ya ocurrieron en el Request
    $validated = $request->validated();

    // No se puede anular una práctica ya completada
    if ($practica->status === 'completed' || $practica->status === 'annulled') {
         return back()->with('error', 'Esta práctica ya está finalizada y no se puede anular.');
    }

    try {
        // 1. Actualizamos la práctica
        $practica->update([
            'status' => 'annulled', // ¡Estado final de anulación!
            'annulment_reason' => $validated['annulment_reason']
        ]);

        // --- INICIO: Notificar al Estudiante y Asesor ---
        try {
            // $practica ya tiene el motivo de anulación
            $practica->load('student', 'advisor');

            $recipients = collect();

            if ($practica->student) {
                $recipients->push($practica->student);
            }
            if ($practica->advisor) {
                $recipients->push($practica->advisor);
            }

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new PracticaAnulada($practica));
            }

        } catch (\Exception $e) {
            Log::error('Fallo al enviar notificación (PracticaAnulada): ' . $e->getMessage());
            // No detenemos el flujo principal
        }
        // --- FIN: Notificación ---

    } catch (\Exception $e) {
        Log::error('Error al anular la práctica: ' . $e->getMessage()); // <-- Loguear error
        return back()->with('error', 'Error al anular la práctica.');
    }

    return redirect()->route('cppp.dashboard.index')
                     ->with('success', 'La práctica ha sido ANULADA exitosamente.');
}

    /**
     * Aprueba la solicitud de ampliación.
     */
    public function approveExtension(Request $request, PracticaPreprofesional $practica)
{
    // Autorización
    if ($practica->status !== 'pending_extension') {
        abort(403);
    }

    try {
        // 1. Actualizamos la práctica
        $practica->update([
            'status' => 'initial_approved', // Vuelve a "En Curso"
            'end_date' => $practica->pending_extension_date, // <-- Aplica la nueva fecha
            'pending_extension_date' => null,
            'extension_count' => $practica->extension_count + 1 // Incrementa el contador
        ]);

        // --- INICIO: Notificar al estudiante ---
        try {
            // $practica ya tiene la nueva 'end_date'
            $practica->load('student');
            if ($practica->student) {
                $practica->student->notify(new AmpliacionAprobada($practica));
            }
        } catch (\Exception $e) {
            Log::error('Fallo al enviar notificación (AmpliacionAprobada): ' . $e->getMessage());
            // No detenemos el flujo principal
        }
        // --- FIN: Notificación ---

    } catch (\Exception $e) {
        Log::error('Error al aprobar la ampliación: ' . $e->getMessage()); // <-- Loguear el error
        return back()->with('error', 'Error al aprobar la ampliación.');
    }

    return redirect()->route('cppp.dashboard.index')
                     ->with('success', 'Ampliación aprobada. La fecha de fin ha sido actualizada.');
}

    /**
     * Rechaza la solicitud de ampliación.
     */
    public function rejectExtension(Request $request, PracticaPreprofesional $practica)
{
    // Autorización
    if ($practica->status !== 'pending_extension') {
        abort(403);
    }

    // Validación
    $validated = $request->validate([
        'observation_notes' => 'required|string|min:10',
    ], [
        'observation_notes.required' => 'Debe proveer una razón para el rechazo.'
    ]);

    try {
        // 1. Actualizamos la práctica
        $practica->update([
            'status' => 'initial_approved', // Vuelve a "En Curso"
            'pending_extension_date' => null,
            'observation_notes' => 'Solicitud de ampliación rechazada: ' . $validated['observation_notes']
        ]);

        // --- INICIO: Notificar al estudiante ---
        try {
            // $practica ya tiene las 'observation_notes'
            $practica->load('student');
            if ($practica->student) {
                $practica->student->notify(new AmpliacionRechazada($practica));
            }
        } catch (\Exception $e) {
            Log::error('Fallo al enviar notificación (AmpliacionRechazada): ' . $e->getMessage());
            // No detenemos el flujo principal
        }
        // --- FIN: Notificación ---

    } catch (\Exception $e) {
        Log::error('Error al rechazar la ampliación: ' . $e->getMessage()); // <-- Loguear el error
        return back()->with('error', 'Error al rechazar la ampliación.');
    }

    return redirect()->route('cppp.dashboard.index')
                     ->with('success', 'Ampliación rechazada y estudiante notificado.');
}

}