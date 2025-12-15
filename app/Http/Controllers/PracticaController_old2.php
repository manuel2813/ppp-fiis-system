<?php

namespace App\Http\Controllers;

// 1. --- IMPORTACIONES DE MODELOS Y FACADES ---
use App\Models\User;
use App\Models\Role;
use App\Models\PracticaPreprofesional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// 2. --- IMPORTACIONES DE FORMREQUESTS (Están correctas) ---
use App\Http\Requests\StorePracticaRequest;
use App\Http\Requests\UpdatePracticaRequest;
use App\Http\Requests\StoreFinalReportRequest;
use App\Http\Requests\UpdateFinalReportRequest;
use App\Http\Requests\StoreExtensionRequest;
use App\Http\Requests\StoreWorkValidationRequest;
use App\Notifications\InformeFinalEntregado; // 
use Illuminate\Support\Facades\Log; // (Opcional, para logs)
use App\Notifications\AmpliacionSolicitada;
use App\Notifications\NuevaSolicitudRecibida;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ValidacionLaboralRecibida;
use Barryvdh\DomPDF\Facade\Pdf;

class PracticaController extends Controller
{
    /**
     * Muestra el formulario para crear una nueva solicitud de PPP.
     */
    public function create()
    {
        $asesorRoleId = Role::where('name', 'asesor')->first()->id;
        $asesores = User::where('role_id', $asesorRoleId)
                        ->orderBy('name')
                        ->get();

        return view('practicas.create', [
            'asesores' => $asesores
        ]);
    }

    /**
     * Guarda la nueva solicitud de PPP en la base de datos.
     */
    public function store(StorePracticaRequest $request)
    {
        $validated = $request->validated();
        $studentId = Auth::id();
        $practica = null; // Definimos $practica aquí para que esté disponible fuera del try

        try {
            DB::beginTransaction();

            $practica = PracticaPreprofesional::create([
                'student_id' => $studentId,
                'advisor_id' => $validated['advisor_id'],
                'entity_name' => $validated['entity_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'in_review_initial',
                
                // --- INICIO: NUEVOS CAMPOS DEL F1 ---
                // (Asumiendo que los nombres en el Request serán así)
                'title' => $validated['f1_title'],
                'practice_area' => $validated['f1_area'],
                'entity_details' => $validated['f1_entity_details'],
                'practice_objectives' => $validated['f1_objectives'],
                'practice_activities' => $validated['f1_activities'],
                'practice_schedule' => $validated['f1_schedule'],
                // --- FIN: NUEVOS CAMPOS DEL F1 ---
            ]);

            $basePath = "practicas/{$practica->id}";

            // SUT sigue siendo un archivo
            $sutPath = $request->file('file_sut')->store($basePath);
            $practica->documents()->create(['type' => 'SUT', 'file_path' => $sutPath, 'upload_date' => now()]);

            // CARTA DE ACEPTACIÓN sigue siendo un archivo
            $cartaPath = $request->file('file_carta')->store($basePath);
            $practica->documents()->create(['type' => 'CARTA_ACEPTACION', 'file_path' => $cartaPath, 'upload_date' => now()]);
            
            // --- ELIMINADO ---
            // Ya no procesamos 'file_f1'
            // $f1Path = $request->file('file_f1')->store($basePath);
            // $practica->documents()->create([ 'type' => 'F1_PLAN', 'file_path' => $f1Path, 'upload_date' => now() ]);
            // --- FIN ELIMINADO ---

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar solicitud de práctica: ' . $e->getMessage()); // Log del error
            return back()->with('error', 'Error al guardar la solicitud. Intente de nuevo.')->withInput(); // withInput para retener datos del modal
        }

        // --- INICIO: NOTIFICAR A CPPP ---
        if ($practica) { // Nos aseguramos que la práctica se creó
            try {
                // Buscamos a todos los usuarios con el rol 'cppp'
                $cpppUsers = User::whereHas('role', function ($query) {
                    $query->where('name', 'cppp');
                })->get();

                if ($cpppUsers->isNotEmpty()) {
                    // Cargamos la relación 'student' para usar el nombre
                    $practica->load('student'); 

                    // Enviamos la notificación a todos los usuarios de CPPP
                    Notification::send($cpppUsers, new NuevaSolicitudRecibida($practica));
                } else {
                    Log::warning('No se encontró un usuario "cppp" para notificar.');
                }

            } catch (\Exception $e) {
                // Si la notificación falla, no detenemos al estudiante
                Log::error('Fallo al enviar notificación a CPPP: ' . $e->getMessage());
            }
        }
        // --- FIN: NOTIFICACIÓN ---

        return redirect()->route('dashboard')->with('success', 'Solicitud enviada correctamente. Está en proceso de revisión.');
    }

    /**
     * Muestra el formulario para editar/corregir una práctica observada.
     */
    public function edit(PracticaPreprofesional $practica)
    {
        if (Auth::id() !== $practica->student_id || $practica->status !== 'initial_observed') {
            abort(403, 'Acción no autorizada.');
        }

        $asesorRoleId = Role::where('name', 'asesor')->first()->id;
        $asesores = User::where('role_id', $asesorRoleId)
                        ->orderBy('name')
                        ->get();

        $practica->load('documents');

        return view('practicas.edit', [
            'practica' => $practica,
            'asesores' => $asesores
        ]);
    }

    /**
     * Guarda las correcciones (actualiza) la práctica.
     */
    public function update(UpdatePracticaRequest $request, PracticaPreprofesional $practica)
    {
        $validated = $request->validated();
        
        try {
            DB::beginTransaction();

            $practica->update([
                'advisor_id' => $validated['advisor_id'],
                'entity_name' => $validated['entity_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'in_review_initial',
                'observation_notes' => null,
            ]);

            $basePath = "practicas/{$practica->id}";

            if ($request->hasFile('file_sut')) {
                $doc = $practica->documents()->where('type', 'SUT')->first();
                if ($doc) Storage::delete($doc->file_path);
                $sutPath = $request->file('file_sut')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'SUT'],
                    ['file_path' => $sutPath, 'upload_date' => now()]
                );
            }

            if ($request->hasFile('file_f1')) {
                $doc = $practica->documents()->where('type', 'F1_PLAN')->first();
                if ($doc) Storage::delete($doc->file_path);
                $f1Path = $request->file('file_f1')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'F1_PLAN'],
                    ['file_path' => $f1Path, 'upload_date' => now()]
                );
            }

            if ($request->hasFile('file_carta')) {
                $doc = $practica->documents()->where('type', 'CARTA_ACEPTACION')->first();
                if ($doc) Storage::delete($doc->file_path);
                $cartaPath = $request->file('file_carta')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'CARTA_ACEPTACION'],
                    ['file_path' => $cartaPath, 'upload_date' => now()]
                );
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar las correcciones. Intente de nuevo.');
        }

        return redirect()->route('dashboard')->with('success', 'Solicitud corregida y enviada a revisión nuevamente.');
    }

    /**
     * Muestra el formulario para subir el Informe Final (F3, F4, Constancia).
     */
    public function showFinalReportForm(PracticaPreprofesional $practica)
    {
        if (Auth::id() !== $practica->student_id) { abort(403); }

        if ($practica->status !== 'initial_approved') {
            return redirect()->route('dashboard')
                             ->with('error', 'Aún no puedes subir tu informe final.');
        }

        return view('practicas.final_report.create', [ 'practica' => $practica ]);
    }

    /**
     * Guarda los documentos del Informe Final.
     */
    public function storeFinalReport(StoreFinalReportRequest $request, PracticaPreprofesional $practica)
{
    $validated = $request->validated();

    try {
        DB::beginTransaction();
        $basePath = "practicas/{$practica->id}/informe_final";

        // ... (Tu código para guardar F3)
        $f3Path = $request->file('file_f3')->store($basePath);
        $practica->documents()->updateOrCreate(
            ['type' => 'F3_INFORME_FINAL'],
            ['file_path' => $f3Path, 'upload_date' => now()]
        );

        // ... (Tu código para guardar F4)
        $f4Path = $request->file('file_f4')->store($basePath);
        $practica->documents()->updateOrCreate(
            ['type' => 'F4_EVALUACION_ENTIDAD'],
            ['file_path' => $f4Path, 'upload_date' => now()]
        );

        // ... (Tu código para guardar Constancia)
        $constanciaPath = $request->file('file_constancia')->store($basePath);
        $practica->documents()->updateOrCreate(
            ['type' => 'CONSTANCIA_ENTIDAD'],
            ['file_path' => $constanciaPath, 'upload_date' => now()]
        );

        $practica->update([
            'status' => 'pending_advisor_dictamen',
            'advisor_dictamen_approved' => false
        ]);

        DB::commit(); // Transacción completada

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Error al guardar informe final: " . $e->getMessage()); // <-- Es bueno loguear
        return back()->with('error', 'Error al guardar los documentos. Intente de nuevo.');
    }

    // --- INICIO: ENVIAR NOTIFICACIÓN (DESPUÉS DEL COMMIT) ---
    try {
        // Cargamos las relaciones 'asesor' y 'student' de la práctica
        // Asumo que tu relación en el modelo PracticaPreprofesional se llama 'asesor'
        $practica->load('asesor', 'student'); 

        $asesor = $practica->asesor;

        // Solo enviamos si existe un asesor asignado
        if ($asesor) {
            $asesor->notify(new InformeFinalEntregado($practica));
        }

    } catch (\Exception $e) {
        // Si la notificación falla, no detenemos al usuario,
        // pero sí lo registramos en el log.
        Log::error('Fallo al enviar notificación de informe final: ' . $e->getMessage());
    }
    // --- FIN: NOTIFICACIÓN ---

    return redirect()->route('dashboard')
                     ->with('success', 'Informe Final enviado. Pendiente de dictamen del Asesor.');
}

    /**
     * Muestra el formulario para CORREGIR el Informe Final (F3).
     */
    public function editFinalReport(PracticaPreprofesional $practica)
    {
        if (Auth::id() !== $practica->student_id || !in_array($practica->status, ['final_report_observed', 'jury_observed'])) {
            abort(403, 'Acción no autorizada.');
        }
        $practica->load('documents');
        return view('practicas.final_report.edit', [ 'practica' => $practica ]);
    }

    /**
     * Guarda las correcciones del Informe Final.
     * (ACTUALIZADO CON LÓGICA DE V°B°)
     */
    public function updateFinalReport(UpdateFinalReportRequest $request, PracticaPreprofesional $practica)
    {
        $validated = $request->validated();
        $previousStatus = $practica->status;

        try {
            DB::beginTransaction();
            $basePath = "practicas/{$practica->id}/informe_final";

            if ($request->hasFile('file_f3')) {
                $doc = $practica->documents()->where('type', 'F3_INFORME_FINAL')->first();
                if ($doc) Storage::delete($doc->file_path);
                $f3Path = $request->file('file_f3')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'F3_INFORME_FINAL'],
                    ['file_path' => $f3Path, 'upload_date' => now()]
                );
            }
            if ($request->hasFile('file_f4')) {
                $doc = $practica->documents()->where('type', 'F4_EVALUACION_ENTIDAD')->first();
                if ($doc) Storage::delete($doc->file_path);
                $f4Path = $request->file('file_f4')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'F4_EVALUACION_ENTIDAD'],
                    ['file_path' => $f4Path, 'upload_date' => now()]
                );
            }
            if ($request->hasFile('file_constancia')) {
                $doc = $practica->documents()->where('type', 'CONSTANCIA_ENTIDAD')->first();
                if ($doc) Storage::delete($doc->file_path);
                $constanciaPath = $request->file('file_constancia')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'CONSTANCIA_ENTIDAD'],
                    ['file_path' => $constanciaPath, 'upload_date' => now()]
                );
            }

            $newStatus = ($previousStatus === 'jury_observed') 
                            ? 'pending_jury_review' 
                            : 'pending_advisor_dictamen';

            $practica->update([
                'status' => $newStatus,
                'observation_notes' => null
            ]);

            // --- INICIO: LÓGICA DE V°B° AÑADIDA ---
            // Si la corrección fue por el jurado, reseteamos todos los votos
            if ($previousStatus === 'jury_observed') {
                $practica->juradoAssignments()->update(['report_approved' => null]);
            }
            // --- FIN: LÓGICA DE V°B° AÑADIDA ---

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar las correcciones. Intente de nuevo.');
        }

        return redirect()->route('dashboard')->with('success', 'Informe Final corregido y enviado a revisión.');
    }

    /**
     * Muestra el formulario para solicitar ampliación de PPP.
     */
    public function showExtensionForm(PracticaPreprofesional $practica)
    {
        if (Auth::id() !== $practica->student_id || 
            $practica->status !== 'initial_approved' || 
            $practica->extension_count >= 2) {
            abort(403, 'No está autorizado para solicitar una ampliación.');
        }

        return view('practicas.extension.create', [
            'practica' => $practica
        ]);
    }

    /**
     * Guarda la solicitud de ampliación y la envía a CPPP.
     */
    public function storeExtensionRequest(StoreExtensionRequest $request, PracticaPreprofesional $practica)
{
    $validated = $request->validated();

    try {
        DB::beginTransaction();
        $extensionNumber = $practica->extension_count + 1;
        $basePath = "practicas/{$practica->id}/ampliaciones";
        $letterPath = $request->file('file_extension_letter')->store($basePath);

        $practica->documents()->create([
            'type' => 'CARTA_AMPLIACION_' . $extensionNumber,
            'file_path' => $letterPath,
            'upload_date' => now()
        ]);

        $practica->update([
            'status' => 'pending_extension',
            'pending_extension_date' => $validated['new_end_date']
        ]);

        DB::commit();

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al guardar solicitud de ampliación: ' . $e->getMessage()); // <-- Log del error
        return back()->with('error', 'Error al guardar la solicitud de ampliación.');
    }

    // --- INICIO: NOTIFICAR A CPPP ---
    try {
        // Buscamos a todos los usuarios con el rol 'cppp'
        $cpppUsers = User::whereHas('role', function ($query) {
            $query->where('name', 'cppp');
        })->get();

        if ($cpppUsers->isNotEmpty()) {
            // Cargamos la relación 'student' para usar el nombre
            $practica->load('student'); 

            // Enviamos la notificación a todos los usuarios de CPPP
            Notification::send($cpppUsers, new AmpliacionSolicitada($practica));
        } else {
            Log::warning('No se encontró un usuario "cppp" para notificar ampliación.');
        }

    } catch (\Exception $e) {
        Log::error('Fallo al enviar notificación de ampliación a CPPP: ' . $e->getMessage());
    }
    // --- FIN: NOTIFICACIÓN ---

    return redirect()->route('dashboard')
                     ->with('success', 'Solicitud de ampliación enviada. Pendiente de revisión por CPPP.');
}

    /**
     * Muestra el formulario para solicitar Validación Laboral.
     */
    public function showWorkValidationForm()
    {
        $asesorRoleId = Role::where('name', 'asesor')->first()->id;
        $asesores = User::where('role_id', $asesorRoleId)
                        ->orderBy('name')
                        ->get();

        return view('practicas.validation.create', [
            'asesores' => $asesores
        ]);
    }

    /**
     * Guarda la solicitud de Validación Laboral (salta a revisión de Asesor).
     */
    public function storeWorkValidation(StoreWorkValidationRequest $request)
{
    $validated = $request->validated();
    $studentId = Auth::id();
    $practica = null; // Definimos $practica aquí

    try {
        DB::beginTransaction();

        $practica = PracticaPreprofesional::create([
            'student_id' => $studentId,
            'advisor_id' => $validated['advisor_id'], // <-- Clave: el asesor ya está aquí
            'entity_name' => $validated['entity_name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => 'pending_advisor_dictamen', // <-- El estado correcto
            'practice_type' => 'validacion_laboral',
        ]);

        $basePath = "practicas/{$practica->id}/validacion";

        // ... (Tu código para guardar F3, F4, Constancia, Certificación)
        $f3Path = $request->file('file_f3')->store($basePath);
        $practica->documents()->create([ 'type' => 'F3_INFORME_FINAL', 'file_path' => $f3Path, 'upload_date' => now() ]);
        $f4Path = $request->file('file_f4')->store($basePath);
        $practica->documents()->create([ 'type' => 'F4_EVALUACION_ENTIDAD', 'file_path' => $f4Path, 'upload_date' => now() ]);
        $constanciaPath = $request->file('file_constancia')->store($basePath);
        $practica->documents()->create([ 'type' => 'CONSTANCIA_ENTIDAD', 'file_path' => $constanciaPath, 'upload_date' => now() ]);
        $certPath = $request->file('file_certificacion')->store($basePath);
        $practica->documents()->create([ 'type' => 'CERTIFICACION_PROGRESIVA', 'file_path' => $certPath, 'upload_date' => now() ]);

        DB::commit();

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error al guardar solicitud de validación: ' . $e->getMessage()); // <-- Log
        return back()->with('error', 'Error al guardar la solicitud de validación.');
    }

    // --- INICIO: NOTIFICAR AL ASESOR ---
    if ($practica) { // Nos aseguramos que la práctica se creó
        try {
            // Cargamos las relaciones 'asesor' y 'student'
            $practica->load('asesor', 'student'); 
            $asesor = $practica->asesor;

            // Solo enviamos si existe un asesor
            if ($asesor) {
                $asesor->notify(new ValidacionLaboralRecibida($practica));
            } else {
                Log::warning("No se encontró asesor (ID: {$practica->advisor_id}) para notificar validación.");
            }

        } catch (\Exception $e) {
            Log::error('Fallo al enviar notificación de validación al asesor: ' . $e->getMessage());
        }
    }
    // --- FIN: NOTIFICACIÓN ---

    return redirect()->route('dashboard')
                     ->with('success', 'Solicitud de Validación Laboral enviada. Pendiente de dictamen del Asesor.');
}

    public function downloadConstancia(PracticaPreprofesional $practica)
    {
        // 1. Validaciones de seguridad
        if (Auth::id() !== $practica->student_id) {
            abort(403, 'Acción no autorizada.');
        }
        if (!$practica->compliance_certificate_issued) {
            return back()->with('error', 'La constancia aún no ha sido emitida por la CPPP.');
        }

        // 2. Cargar relaciones de la práctica
        $practica->load('student', 'advisor');
        
        // 3. --- LÓGICA DEFINITIVA ---
        // Buscamos al usuario (Director) exacto que emitió la constancia
        $director = User::find($practica->constancia_issuer_id);
        // --- FIN DE LA LÓGICA ---

        // 4. Cargar la vista Blade del PDF (esto ya está bien)
        $pdf = Pdf::loadView('pdfs.constancia_cumplimiento', compact('practica', 'director'));

        // 5. Descargar el PDF
        return $pdf->download('Constancia_PPP_' . $practica->student->name . '.pdf');
    }

}