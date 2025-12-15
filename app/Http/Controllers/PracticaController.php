<?php

namespace App\Http\Controllers;

// 1. --- IMPORTACIONES (Sin cambios) ---
use App\Models\User;
use App\Models\Role;
use App\Models\PracticaPreprofesional;
use Illuminate\Http\Request; // <-- Revertido
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StorePracticaRequest; // <-- Revertido
use App\Http\Requests\UpdatePracticaRequest;
use App\Http\Requests\StoreFinalReportRequest;
use App\Http\Requests\UpdateFinalReportRequest;
use App\Http\Requests\StoreExtensionRequest;
use App\Http\Requests\StoreWorkValidationRequest;
use App\Notifications\InformeFinalEntregado; 
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
        // Esta variable $asesores está bien, es local, no es la relación
        $asesorRoleId = Role::where('name', 'asesor')->first()->id;
        $asesores = User::where('role_id', $asesorRoleId)
                            ->orderBy('name')
                            ->get();

        return view('practicas.create', [
            'asesores' => $asesores // Pasar la variable local está bien
        ]);
    }

    /**
     * Guarda la nueva solicitud de PPP en la base de datos.
     */
    public function store(StorePracticaRequest $request)
    {
        $validated = $request->validated(); 
        $studentId = Auth::id();
        $practica = null; 

        try {
            DB::beginTransaction();
            
            $practica = PracticaPreprofesional::create([
                'student_id' => $studentId,
                'advisor_id' => $validated['advisor_id'],
                'entity_name' => $validated['entity_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'in_review_initial',
                'entity_ruc' => $validated['entity_ruc'],
                'entity_phone' => $validated['entity_phone'],
                'entity_address' => $validated['entity_address'],
                'entity_manager' => $validated['entity_manager'],
                'entity_department' => $validated['entity_department'],
                'entity_province' => $validated['entity_province'],
                'entity_district' => $validated['entity_district'],
                'supervisor_name' => $validated['supervisor_name'],
                'supervisor_email' => $validated['supervisor_email'],
                'title' => $validated['f1_title'],
                'practice_area' => $validated['f1_area'],
                'entity_details' => $validated['f1_entity_details'],
                'practice_objectives' => $validated['f1_objectives'],
                'practice_activities' => $validated['f1_activities'],
                'practice_schedule' => $validated['f1_schedule'],
            ]);

            // CORREGIDO: Usamos 'advisor' (inglés) para la relación
            $practica->load('student', 'advisor'); 
            
            $basePath = "practicas/{$practica->id}";
            $pdfF1 = Pdf::loadView('pdfs.f1_plan', ['practica' => $practica]);
            $f1FileName = 'F1_Plan_de_Practica_' . Str::slug($practica->student->name) . '.pdf';
            $f1Path = "{$basePath}/{$f1FileName}";
            Storage::put($f1Path, $pdfF1->output());
            $practica->documents()->create(['type' => 'F1_PLAN', 'file_path' => $f1Path, 'upload_date' => now()]);
            $sutPath = $request->file('file_sut')->store($basePath);
            $practica->documents()->create(['type' => 'SUT', 'file_path' => $sutPath, 'upload_date' => now()]);
            $cartaPath = $request->file('file_carta')->store($basePath);
            $practica->documents()->create(['type' => 'CARTA_ACEPTACION', 'file_path' => $cartaPath, 'upload_date' => now()]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar solicitud de práctica: ' . $e->getMessage() . ' Línea: ' . $e->getLine());
            return back()->with('error', 'Error al guardar la solicitud. Intente de nuevo.')->withInput();
        }

        if ($practica) {
            try {
                $cpppUsers = User::whereHas('role', function ($query) { $query->where('name', 'cppp'); })->get();
                if ($cpppUsers->isNotEmpty()) {
                    $practica->load('student'); 
                    Notification::send($cpppUsers, new NuevaSolicitudRecibida($practica));
                } else {
                    Log::warning('No se encontró un usuario "cppp" para notificar.');
                }
            } catch (\Exception $e) {
                Log::error('Fallo al enviar notificación a CPPP: ' . $e->getMessage());
            }
        }
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
        $asesores = User::where('role_id', $asesorRoleId)->orderBy('name')->get();
        $practica->load('documents');
        return view('practicas.edit', [
            'practica' => $practica,
            'asesores' => $asesores // Esto está bien, es la variable local
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
                'entity_ruc' => $validated['entity_ruc'],
                'entity_phone' => $validated['entity_phone'],
                'entity_address' => $validated['entity_address'],
                'entity_manager' => $validated['entity_manager'],
                'entity_department' => $validated['entity_department'],
                'entity_province' => $validated['entity_province'],
                'entity_district' => $validated['entity_district'],
                'supervisor_name' => $validated['supervisor_name'],
                'supervisor_email' => $validated['supervisor_email'],
                'title' => $validated['f1_title'],
                'practice_area' => $validated['f1_area'],
                'entity_details' => $validated['f1_entity_details'],
                'practice_objectives' => $validated['f1_objectives'],
                'practice_activities' => $validated['f1_activities'],
                'practice_schedule' => $validated['f1_schedule'],
            ]);

            $basePath = "practicas/{$practica->id}";
            if ($request->hasFile('file_sut')) { /* ... */ }
            if ($request->hasFile('file_carta')) { /* ... */ }

            $docF1 = $practica->documents()->where('type', 'F1_PLAN')->first();
            if ($docF1) Storage::delete($docF1->file_path);

            // CORREGIDO: Usamos 'advisor' (inglés) para la relación
            $practica->load('student', 'advisor');
            
            $pdfF1 = Pdf::loadView('pdfs.f1_plan', ['practica' => $practica]);
            $f1FileName = 'F1_Plan_de_Practica_' . Str::slug($practica->student->name) . '_v' . ($practica->id) . '.pdf';
            $f1Path = "{$basePath}/{$f1FileName}";
            Storage::put($f1Path, $pdfF1->output());
            $practica->documents()->updateOrCreate(['type' => 'F1_PLAN'], ['file_path' => $f1Path, 'upload_date' => now()]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar la solicitud: ' . $e->getMessage() . ' Línea: ' . $e->getLine());
            return back()->with('error', 'Error al guardar las correcciones. Intente de nuevo.')->withInput();
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
            return redirect()->route('dashboard')->with('error', 'Aún no puedes subir tu informe final.');
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
            // ... (lógica de guardado de F3, F4, Constancia) ...
            $f3Path = $request->file('file_f3')->store($basePath);
            $practica->documents()->updateOrCreate(['type' => 'F3_INFORME_FINAL'], ['file_path' => $f3Path, 'upload_date' => now()]);
            $f4Path = $request->file('file_f4')->store($basePath);
            $practica->documents()->updateOrCreate(['type' => 'F4_EVALUACION_ENTIDAD'], ['file_path' => $f4Path, 'upload_date' => now()]);
            $constanciaPath = $request->file('file_constancia')->store($basePath);
            $practica->documents()->updateOrCreate(['type' => 'CONSTANCIA_ENTIDAD'], ['file_path' => $constanciaPath, 'upload_date' => now()]);
            $practica->update(['status' => 'pending_advisor_dictamen', 'advisor_dictamen_approved' => false]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al guardar informe final: " . $e->getMessage()); 
            return back()->with('error', 'Error al guardar los documentos. Intente de nuevo.');
        }

        try {
            // *****************************************************
            // * CORRECCIÓN 1: 'asesor' se cambia por 'advisor'
            // *****************************************************
            $practica->load('advisor', 'student'); 
            $asesor = $practica->advisor; // <-- CORREGIDO
            // *****************************************************
            
            if ($asesor) {
                $asesor->notify(new InformeFinalEntregado($practica));
            }
        } catch (\Exception $e) {
            Log::error('Fallo al enviar notificación de informe final: ' . $e->getMessage());
        }
        return redirect()->route('dashboard')->with('success', 'Informe Final enviado. Pendiente de dictamen del Asesor.');
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
     */
    public function updateFinalReport(UpdateFinalReportRequest $request, PracticaPreprofesional $practica)
    {
        $validated = $request->validated();
        $previousStatus = $practica->status;
        try {
            DB::beginTransaction();
            $basePath = "practicas/{$practica->id}/informe_final";
            if ($request->hasFile('file_f3')) {
                // ... (lógica de update F3) ...
            }
            $newStatus = ($previousStatus === 'jury_observed') ? 'pending_jury_review' : 'pending_advisor_dictamen';
            $practica->update(['status' => $newStatus, 'observation_notes' => null]);
            if ($previousStatus === 'jury_observed') {
                $practica->juradoAssignments()->update(['report_approved' => null]);
            }
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
        if (Auth::id() !== $practica->student_id || $practica->status !== 'initial_approved' || $practica->extension_count >= 2) {
            abort(403, 'No está autorizado para solicitar una ampliación.');
        }
        return view('practicas.extension.create', ['practica' => $practica]);
    }

    /**
     * Guarda la solicitud de ampliación y la envía a CPPP.
     */
    public function storeExtensionRequest(StoreExtensionRequest $request, PracticaPreprofesional $practica)
    {
        // ... (lógica de storeExtensionRequest sin cambios) ...
        return redirect()->route('dashboard')->with('success', 'Solicitud de ampliación enviada. Pendiente de revisión por CPPP.');
    }

    /**
     * Muestra el formulario para solicitar Validación Laboral.
     */
    public function showWorkValidationForm()
    {
        $asesorRoleId = Role::where('name', 'asesor')->first()->id;
        $asesores = User::where('role_id', $asesorRoleId)->orderBy('name')->get();
        return view('practicas.validation.create', ['asesores' => $asesores]);
    }

    /**
     * Guarda la solicitud de Validación Laboral (salta a revisión de Asesor).
     */
    public function storeWorkValidation(StoreWorkValidationRequest $request)
    {
        $validated = $request->validated();
        $studentId = Auth::id();
        $practica = null; 
        try {
            DB::beginTransaction();
            $practica = PracticaPreprofesional::create([
                'student_id' => $studentId,
                'advisor_id' => $validated['advisor_id'], 
                'entity_name' => $validated['entity_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'pending_advisor_dictamen', 
                'practice_type' => 'validacion_laboral',
            ]);
            $basePath = "practicas/{$practica->id}/validacion";
            // ... (lógica de guardado de documentos de validación) ...
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar solicitud de validación: ' . $e->getMessage()); 
            return back()->with('error', 'Error al guardar la solicitud de validación.');
        }

        if ($practica) { 
            try {
                // *****************************************************
                // * CORRECCIÓN 2: 'asesor' se cambia por 'advisor'
                // *****************************************************
                $practica->load('advisor', 'student'); 
                $asesor = $practica->advisor; // <-- CORREGIDO
                // *****************************************************

                if ($asesor) {
                    $asesor->notify(new ValidacionLaboralRecibida($practica));
                } else {
                    Log::warning("No se encontró asesor (ID: {$practica->advisor_id}) para notificar validación.");
                }
            } catch (\Exception $e) {
                Log::error('Fallo al enviar notificación de validación al asesor: ' . $e->getMessage());
            }
        }
        return redirect()->route('dashboard')->with('success', 'Solicitud de Validación Laboral enviada. Pendiente de dictamen del Asesor.');
    }

    /**
     * Descarga la constancia de cumplimiento.
     */
    public function downloadConstancia(PracticaPreprofesional $practica)
    {
        if (Auth::id() !== $practica->student_id) {
            abort(403, 'Acción no autorizada.');
        }
        if (!$practica->compliance_certificate_issued) {
            return back()->with('error', 'La constancia aún no ha sido emitida por la CPPP.');
        }

        // *****************************************************
        // * CORRECCIÓN 3: 'asesor' se cambia por 'advisor'
        // *****************************************************
        $practica->load('student', 'advisor');
        // *****************************************************
        
        $director = User::find($practica->constancia_issuer_id);
        $pdf = Pdf::loadView('pdfs.constancia_cumplimiento', compact('practica', 'director'));
        return $pdf->download('Constancia_PPP_' . $practica->student->name . '.pdf');
    }
}