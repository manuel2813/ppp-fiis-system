<?php

namespace App\Http\Controllers;

use App\Models\JuradoAssignment;
use App\Models\PracticaPreprofesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\InformeObservadoPorJurado;
use App\Notifications\PendienteAgendarSustentacion;
use Illuminate\Support\Facades\Log;
use App\Notifications\SustentacionProgramada; 
use Illuminate\Support\Facades\Notification;  
use App\Notifications\SuplenteActivado; 
use App\Notifications\JuradoReemplazado; 

class JuradoVotoController extends Controller
{
    /**
     * Acción 1: Un jurado (Miembro o Presidente) emite su voto.
     */
    public function emitirVoto(Request $request, PracticaPreprofesional $practica)
    {
        $data = $request->validate([
            'voto' => 'required|in:Visto Bueno,Observacion',
            'observacion_detalle' => 'required_if:voto,Observacion|nullable|string',
        ]);

        $assignment = $practica->juradoAssignments()
            ->where('jurado_member_id', Auth::id())
            ->where('estado', 'Activo')
            ->firstOrFail();

        // Validación extra: No permitir votar si la práctica no está en revisión
        if ($practica->status !== 'pending_jury_review') {
            return back()->with('error', 'La votación para esta práctica ya ha cerrado.');
        }

        $assignment->update([
            'voto' => $data['voto'],
            'observacion_detalle' => ($data['voto'] == 'Observacion') ? $data['observacion_detalle'] : null,
        ]);

        // Revisar si la votación terminó
        $this->revisarEstadoDeVotacion($practica);

        return back()->with('success', 'Voto registrado correctamente.');
    }

    /**
     * Acción 2: La lógica de "revisión por mayoría".
     */
    private function revisarEstadoDeVotacion(PracticaPreprofesional $practica)
{
    // 1. Obtener todos los jurados ACTIVOS
    $activeAssignments = $practica->juradoAssignments()->where('estado', 'Activo')->get();

    // 2. Contar cuántos de ellos ya votaron
    $votesCast = $activeAssignments->whereNotNull('voto');

    // 3. Si aún no votan todos, no hagas nada.
    if ($votesCast->count() < $activeAssignments->count()) {
        return; // Aún faltan votos
    }

    // --- TODOS LOS ACTIVOS VOTARON ---

    // 4. Contar los votos
    $conteo_vb = $votesCast->where('voto', 'Visto Bueno')->count();
    $conteo_obs = $votesCast->where('voto', 'Observacion')->count();

    // 5. Aplicar la regla de mayoría
    if ($conteo_obs >= 2) {
        // Decisión: OBSERVADO

        // Recolectar todas las observaciones
        $observaciones = $votesCast->where('voto', 'Observacion')
                                   ->pluck('observacion_detalle')
                                   ->filter() 
                                   ->implode("\n\n---\n\n");

        $practica->update([
            'status' => 'jury_observed', 
            'observation_notes' => $observaciones,
        ]);

        // Limpiar votos para la siguiente ronda
        $activeAssignments->each(function ($item) {
            $item->update(['voto' => null, 'observacion_detalle' => null]);
        });

        // --- INICIO: Notificar al estudiante ---
        try {
            $practica->load('student');
            if ($practica->student) {
                $practica->student->notify(new InformeObservadoPorJurado($practica));
            }
        } catch (\Exception $e) {
            Log::error('Fallo al notificar (InformeObservadoPorJurado): ' . $e->getMessage());
        }
        // --- FIN: Notificación ---

    } else if ($conteo_vb >= 2) {
        // Decisión: APROBADO POR MAYORÍA. Pasa al Presidente.
        $practica->update([
            'status' => 'pending_defense_date', 
        ]);

        // --- INICIO: Notificar al Presidente ---
        try {
            // Buscamos al presidente activo
            $presidenteAssignment = $activeAssignments->where('role', 'Presidente')->first();

            if ($presidenteAssignment) {
                $presidenteUser = User::find($presidenteAssignment->jurado_member_id);
                $practica->load('student'); // Para el nombre en la notif.

                if ($presidenteUser) {
                    $presidenteUser->notify(new PendienteAgendarSustentacion($practica));
                }
            }
        } catch (\Exception $e) {
            Log::error('Fallo al notificar (PendienteAgendarSustentacion): ' . $e->getMessage());
        }
        // --- FIN: Notificación ---
    }
}

    /**
     * Acción 3: El Presidente da la aprobación final Y PROGRAMA LA DEFENSA.
     */
    public function aprobarSolicitudFinal(Request $request, PracticaPreprofesional $practica)
{
    // 1. Validar que el usuario es el Presidente ACTIVO
    $isPresident = $practica->juradoAssignments()
        ->where('jurado_member_id', Auth::id())
        ->where('role', 'Presidente')
        ->where('estado', 'Activo')
        ->exists();

    // 2. Validar que la práctica esté en el estado 'pending_defense_date'
    if (!$isPresident || $practica->status != 'pending_defense_date') {
        abort(403, 'Acción no autorizada.');
    }

    // 3. Validar los datos del formulario (fecha y lugar)
    $data = $request->validate([
        'defense_date' => 'required|date|after:now',
        'defense_place' => 'required|string|max:255',
    ]);

    try {
        // 4. Aprobar final y guardar fecha/lugar
        $practica->update([
            'status' => 'defense_scheduled', // Usamos tu estado
            'defense_date' => $data['defense_date'],
            'defense_place' => $data['defense_place'],
        ]);

        // --- INICIO: NOTIFICACIÓN MULTI-ROL ---
        // (Esta es la misma lógica que usamos en el CpppDashboardController)
        try {
            // Cargamos todas las relaciones necesarias
            $practica->load('student', 'advisor', 'jurados');

            $recipients = collect();
            $recipients->push($practica->student); // Estudiante
            $recipients->push($practica->advisor); // Asesor
            $recipients = $recipients->merge($practica->jurados); // Todos los jurados

            $recipients = $recipients->filter(); // Filtramos nulos

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new SustentacionProgramada($practica));
            }

        } catch (\Exception $e) {
            Log::error('Fallo al enviar notificación (SustentacionProgramada por Presidente): ' . $e->getMessage());
        }
        // --- FIN: NOTIFICACIÓN ---

    } catch (\Exception $e) {
        Log::error('Error al agendar sustentación (Presidente): ' . $e->getMessage());
        return back()->with('error', 'Error al programar la sustentación.');
    }

    return back()->with('success', 'Sustentación autorizada y programada exitosamente.');
}

    /**
     * Acción 4: Un jurado se recusa (lógica del suplente).
     */
    public function recusarJurado(Request $request, JuradoAssignment $assignment)
{
    // 1. Validar que el usuario actual es el dueño
    if ($assignment->jurado_member_id != Auth::id() || $assignment->estado != 'Activo') {
        abort(403);
    }

    // 2. Encontrar al suplente 'Pendiente'
    $suplente = JuradoAssignment::where('practica_id', $assignment->practica_id)
        ->where('estado', 'Pendiente')
        ->first();

    if (!$suplente) {
        return back()->with('error', 'No se encontró un suplente disponible.');
    }

    // --- INICIO: Preparar datos para notificación ---
    // (Necesitamos los modelos User antes de la transacción)
    $juradoSalienteUser = User::find($assignment->jurado_member_id);
    $suplenteEntranteUser = User::find($suplente->jurado_member_id);
    $practica = $assignment->practica; // Obtenemos la práctica
    $rolAsignado = $assignment->role; // El rol que el suplente tomará
    // --- FIN: Preparar datos ---

    // 3. Hacer el reemplazo (en una transacción)
    try {
        DB::transaction(function () use ($assignment, $suplente, $rolAsignado) {

            // a) El jurado actual pasa a 'Recusado'
            $assignment->update([
                'estado' => 'Recusado',
                'voto' => null, 
                'observacion_detalle' => null,
            ]);

            // b) El suplente pasa a 'Activo' y toma el rol
            $suplente->update([
                'estado' => 'Activo',
                'role' => $rolAsignado, // El suplente ahora es 'Presidente' o 'Miembro'
            ]);
        });
    } catch (\Exception $e) {
        Log::error('Error en transacción de recusación: ' . $e->getMessage());
        return back()->with('error', 'Error al procesar el reemplazo.');
    }

    // --- INICIO: ENVIAR NOTIFICACIONES ---
    try {
        // Cargar datos necesarios
        $practica->load('student');
        $juradoSalienteUser->load('role');
        $cpppUsers = User::whereHas('role', fn($q) => $q->where('name', 'cppp'))->with('role')->get();

        // 1. Notificar al Suplente Entrante
        if ($suplenteEntranteUser) {
            $suplenteEntranteUser->notify(new SuplenteActivado($practica, $rolAsignado));
        }

        // 2. Notificar al Jurado Saliente
        if ($juradoSalienteUser) {
            $juradoSalienteUser->notify(new JuradoReemplazado($practica, $juradoSalienteUser, $suplenteEntranteUser));
        }

        // 3. Notificar a CPPP
        if ($cpppUsers->isNotEmpty()) {
            Notification::send($cpppUsers, new JuradoReemplazado($practica, $juradoSalienteUser, $suplenteEntranteUser));
        }

    } catch (\Exception $e) {
        Log::error('Fallo al enviar notificaciones de recusación: ' . $e->getMessage());
    }
    // --- FIN: NOTIFICACIONES ---

    // 4. Re-revisar la votación
    $this->revisarEstadoDeVotacion($practica);

    return back()->with('success', 'Ha sido reemplazado por el suplente.');
}

    public function observarSolicitudFinal(Request $request, PracticaPreprofesional $practica)
{
    // 1. Validar que el usuario es el Presidente ACTIVO
    $isPresident = $practica->juradoAssignments()
        ->where('jurado_member_id', Auth::id())
        ->where('role', 'Presidente')
        ->where('estado', 'Activo')
        ->exists();

    // 2. Validar que la práctica esté en el estado 'pending_defense_date'
    if (!$isPresident || $practica->status != 'pending_defense_date') {
        abort(403, 'Acción no autorizada.');
    }

    // 3. Validar los datos del formulario (la observación)
    $data = $request->validate([
        'observacion_detalle' => 'required|string|min:20',
    ], [
        'observacion_detalle.required' => 'La razón de la observación es obligatoria.',
        'observacion_detalle.min' => 'La observación debe tener al menos 20 caracteres.'
    ]);

    // 4. Actualizar la práctica (en transacción)
    try {
        DB::transaction(function () use ($practica, $data) {

            // a) Poner la práctica en estado "Observado por Jurado"
            $practica->update([
                'status' => 'jury_observed', // Usamos tu estado
                'observation_notes' => "Observación final del Presidente:\n\n" . $data['observacion_detalle'],
                'defense_date' => null, // Limpiamos datos
                'defense_place' => null,
            ]);

            // b) ¡IMPORTANTE! Limpiar TODOS los votos (de los 3 activos)
            $practica->juradoAssignments()
                ->where('estado', 'Activo')
                ->update([
                    'voto' => null,
                    'observacion_detalle' => null,
                ]);

            // --- INICIO: Notificar al estudiante ---
            // (Lo ponemos dentro de la transacción, pero con su propio try/catch)
            try {
                // $practica ya tiene las 'observation_notes'
                $practica->load('student');
                if ($practica->student) {
                    // Reutilizamos la misma notificación
                    $practica->student->notify(new InformeObservadoPorJurado($practica));
                }
            } catch (\Exception $e) {
                Log::error('Fallo al enviar notificación (InformeObservado - Presidente): ' . $e->getMessage());
                // No rompemos la transacción si solo falla el email
            }
            // --- FIN: Notificación ---

        });
    } catch (\Exception $e) {
        Log::error('Error en transacción de observarSolicitudFinal: ' . $e->getMessage());
        return back()->with('error', 'Error al guardar la observación.');
    }

    return back()->with('success', 'Ha enviado su observación. El estudiante será notificado para corregir.');
}

}