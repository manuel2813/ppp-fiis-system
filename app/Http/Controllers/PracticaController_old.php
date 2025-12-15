<?php

namespace App\Http\Controllers;

// 1. Importa los modelos y clases necesarias
use App\Models\User;
use App\Models\Role;
use App\Models\PracticaPreprofesional;
use App\Http\Requests\StorePracticaRequest; // <-- Nuestro FormRequest
use Illuminate\Support\Facades\Auth; // <-- Asegúrate de tener este
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- Y este, para la transacción
use App\Http\Requests\StoreFinalReportRequest; 
use App\Http\Requests\UpdateFinalReportRequest;
use Illuminate\Support\Facades\Storage; // 

use App\Http\Requests\StoreExtensionRequest; // <-- IMPORTA EL NUEVO REQUEST
use App\Http\Requests\StoreWorkValidationRequest; // <-- IMPORTA EL NUEVO REQUEST

class PracticaController extends Controller
{
    /**
     * Muestra el formulario para crear una nueva solicitud de PPP.
     */
    public function create()
    {
        // Necesitamos la lista de Asesores para el dropdown.
        // Buscamos el ID del rol 'asesor'
        $asesorRoleId = Role::where('name', 'asesor')->first()->id;

        // Obtenemos todos los usuarios que tienen ese role_id
        $asesores = User::where('role_id', $asesorRoleId)
                        ->orderBy('name')
                        ->get();

        // Retornamos la vista (que creamos en el Paso 9)
        return view('practicas.create', [
            'asesores' => $asesores
        ]);
    }

    /**
     * Guarda la nueva solicitud de PPP en la base de datos.
     * Usamos StorePracticaRequest para la inyección de dependencias.
     * Laravel validará automáticamente ANTES de ejecutar este método.
     */
    public function store(StorePracticaRequest $request)
    {
        // El $request ya viene validado gracias a StorePracticaRequest
        $validated = $request->validated();
        
        // Obtenemos el ID del estudiante autenticado
        $studentId = Auth::id();

        // Usamos una transacción para asegurar que todo se guarde correctamente.
        // Si falla el guardado de un archivo, se revierte la creación de la práctica.
        try {
            DB::beginTransaction();

            // 1. Crear el registro de la Práctica Preprofesional
            $practica = PracticaPreprofesional::create([
                'student_id' => $studentId,
                'advisor_id' => $validated['advisor_id'],
                'entity_name' => $validated['entity_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'in_review_initial', // Estado inicial según la Directiva
            ]);

            // 2. Guardar los archivos
            // Los archivos se guardarán en: storage/app/practicas/[practica_id]/...
            $basePath = "practicas/{$practica->id}";

            // SUT
            $sutPath = $request->file('file_sut')->store($basePath);
            $practica->documents()->create([
                'type' => 'SUT',
                'file_path' => $sutPath,
                'upload_date' => now()
            ]);

            // Formato F1
            $f1Path = $request->file('file_f1')->store($basePath);
            $practica->documents()->create([
                'type' => 'F1_PLAN',
                'file_path' => $f1Path,
                'upload_date' => now()
            ]);

            // Carta de Aceptación
            $cartaPath = $request->file('file_carta')->store($basePath);
            $practica->documents()->create([
                'type' => 'CARTA_ACEPTACION',
                'file_path' => $cartaPath,
                'upload_date' => now()
            ]);

            // Si todo salió bien, confirmamos la transacción
            DB::commit();

        } catch (\Exception $e) {
            // Si algo falló, revertimos todo
            DB::rollBack();
            
            // (Opcional: Loguear el error $e->getMessage())
            
            // Retornamos al formulario con un mensaje de error
            return back()->with('error', 'Error al guardar la solicitud. Intente de nuevo.');
        }

        // Redirigimos al dashboard (o a 'mis-practicas') con un mensaje de éxito
        return redirect()->route('dashboard')->with('success', 'Solicitud enviada correctamente. Está en proceso de revisión.');
    }

    public function edit(PracticaPreprofesional $practica)
    {
        // Autorización: ¿El estudiante es dueño Y la práctica está observada?
        if (Auth::id() !== $practica->student_id || $practica->status !== 'initial_observed') {
            abort(403, 'Acción no autorizada.');
        }

        // Necesitamos la lista de Asesores para el dropdown
        $asesorRoleId = Role::where('name', 'asesor')->first()->id;
        $asesores = User::where('role_id', $asesorRoleId)
                        ->orderBy('name')
                        ->get();

        // Cargamos los documentos actuales para referencia
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
        // La autorización ya se hizo en UpdatePracticaRequest
        $validated = $request->validated();
        
        try {
            DB::beginTransaction();

            // 1. Actualizar los datos principales de la práctica
            $practica->update([
                'advisor_id' => $validated['advisor_id'],
                'entity_name' => $validated['entity_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'in_review_initial', // <- Se regresa a "En Revisión"
                'observation_notes' => null, // <- Se limpian las observaciones
            ]);

            // 2. Manejar la actualización de archivos (Solo si se subió uno nuevo)
            $basePath = "practicas/{$practica->id}";

            // Revisa y actualiza el SUT
            if ($request->hasFile('file_sut')) {
                // Opcional: Borrar el archivo anterior
                $doc = $practica->documents()->where('type', 'SUT')->first();
                if ($doc) Storage::delete($doc->file_path);

                $sutPath = $request->file('file_sut')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'SUT'], // Busca por tipo
                    ['file_path' => $sutPath, 'upload_date' => now()] // Actualiza o crea
                );
            }

            // Revisa y actualiza el Formato F1
            if ($request->hasFile('file_f1')) {
                $doc = $practica->documents()->where('type', 'F1_PLAN')->first();
                if ($doc) Storage::delete($doc->file_path);

                $f1Path = $request->file('file_f1')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'F1_PLAN'],
                    ['file_path' => $f1Path, 'upload_date' => now()]
                );
            }

            // Revisa y actualiza la Carta de Aceptación
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
            // (Opcional: Loguear $e->getMessage())
            return back()->with('error', 'Error al guardar las correcciones. Intente de nuevo.');
        }

        // Redirigimos al dashboard con un mensaje de éxito
        return redirect()->route('dashboard')->with('success', 'Solicitud corregida y enviada a revisión nuevamente.');
    }

    /**
     * Muestra el formulario para subir el Informe Final (F3, F4, Constancia).
     */
    public function showFinalReportForm(PracticaPreprofesional $practica)
    {
        // Autorización
        if (Auth::id() !== $practica->student_id) {
            abort(403);
        }

        // No debería poder subir si su trámite inicial no está aprobado
        if ($practica->status !== 'initial_approved') {
            return redirect()->route('dashboard')
                             ->with('error', 'Aún no puedes subir tu informe final.');
        }

        return view('practicas.final_report.create', [
            'practica' => $practica
        ]);
    }

    /**
     * Guarda los documentos del Informe Final.
     */
    public function storeFinalReport(StoreFinalReportRequest $request, PracticaPreprofesional $practica)
    {
        // La validación y autorización ya ocurrieron
        $validated = $request->validated();
        
        // La directiva dice que el informe se presenta 30 días post-práctica [cite: 148]
        // (Podríamos añadir una validación de fecha aquí, pero sigamos)

        try {
            DB::beginTransaction();
            
            $basePath = "practicas/{$practica->id}/informe_final";

            // 1. Guardar F3 (Informe Final)
            $f3Path = $request->file('file_f3')->store($basePath);
            $practica->documents()->updateOrCreate(
                ['type' => 'F3_INFORME_FINAL'],
                ['file_path' => $f3Path, 'upload_date' => now()]
            );

            // 2. Guardar F4 (Evaluación Entidad)
            $f4Path = $request->file('file_f4')->store($basePath);
            $practica->documents()->updateOrCreate(
                ['type' => 'F4_EVALUACION_ENTIDAD'],
                ['file_path' => $f4Path, 'upload_date' => now()]
            );

            // 3. Guardar Constancia
            $constanciaPath = $request->file('file_constancia')->store($basePath);
            $practica->documents()->updateOrCreate(
                ['type' => 'CONSTANCIA_ENTIDAD'],
                ['file_path' => $constanciaPath, 'upload_date' => now()]
            );

            // 4. Actualizar el estado de la práctica
            // Pasa a estar pendiente del dictamen del asesor
            $practica->update([
                'status' => 'pending_advisor_dictamen',
                'advisor_dictamen_approved' => false // Resetea por si está corrigiendo
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // (Opcional: Loguear $e->getMessage())
            return back()->with('error', 'Error al guardar los documentos. Intente de nuevo.');
        }

        return redirect()->route('dashboard')
                         ->with('success', 'Informe Final enviado. Pendiente de dictamen del Asesor.');
    }

    /**
     * Muestra el formulario para CORREGIR el Informe Final (F3).
     */
    public function editFinalReport(PracticaPreprofesional $practica)
    {
        // Autorización: ¿El usuario es dueño Y la práctica está observada (por Asesor o Jurado)?
        // LÓGICA DE ESTADO ACTUALIZADA
        if (Auth::id() !== $practica->student_id || !in_array($practica->status, ['final_report_observed', 'jury_observed'])) {
            abort(403, 'Acción no autorizada.');
        }

        // Cargamos los documentos actuales para referencia
        $practica->load('documents');

        return view('practicas.final_report.edit', [
            'practica' => $practica
        ]);
    }

    /**
     * Guarda las correcciones del Informe Final.
     */
    public function updateFinalReport(UpdateFinalReportRequest $request, PracticaPreprofesional $practica)
    {
        // La autorización ya se hizo en el Request
        $validated = $request->validated();
        
        // LÓGICA DE ESTADO ACTUALIZADA
        $previousStatus = $practica->status;

        try {
            DB::beginTransaction();
            
            $basePath = "practicas/{$practica->id}/informe_final";

            // 1. Manejar F3 (si se subió)
            if ($request->hasFile('file_f3')) {
                $doc = $practica->documents()->where('type', 'F3_INFORME_FINAL')->first();
                if ($doc) Storage::delete($doc->file_path);
                $f3Path = $request->file('file_f3')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'F3_INFORME_FINAL'],
                    ['file_path' => $f3Path, 'upload_date' => now()]
                );
            }

            // 2. Manejar F4 (si se subió)
            if ($request->hasFile('file_f4')) {
                $doc = $practica->documents()->where('type', 'F4_EVALUACION_ENTIDAD')->first();
                if ($doc) Storage::delete($doc->file_path);
                $f4Path = $request->file('file_f4')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'F4_EVALUACION_ENTIDAD'],
                    ['file_path' => $f4Path, 'upload_date' => now()]
                );
            }

            // 3. Manejar Constancia (si se subió)
            if ($request->hasFile('file_constancia')) {
                $doc = $practica->documents()->where('type', 'CONSTANCIA_ENTIDAD')->first();
                if ($doc) Storage::delete($doc->file_path);
                $constanciaPath = $request->file('file_constancia')->store($basePath);
                $practica->documents()->updateOrCreate(
                    ['type' => 'CONSTANCIA_ENTIDAD'],
                    ['file_path' => $constanciaPath, 'upload_date' => now()]
                );
            }

            // 4. Actualizar el estado de la práctica
            // LÓGICA DE ESTADO ACTUALIZADA:
            // Si lo observó el Jurado -> vuelve al Jurado
            // Si lo observó el Asesor -> vuelve al Asesor
            $newStatus = ($previousStatus === 'jury_observed') 
                            ? 'pending_jury_review' 
                            : 'pending_advisor_dictamen';

            $practica->update([
                'status' => $newStatus,
                'observation_notes' => null // Limpia las observaciones
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // (Opcional: Loguear $e->getMessage())
            return back()->with('error', 'Error al guardar las correcciones. Intente de nuevo.');
        }

        return redirect()->route('dashboard')
                         ->with('success', 'Informe Final corregido y enviado a revisión.');
    }

    /**
     * Muestra el formulario para solicitar ampliación de PPP.
     */
    public function showExtensionForm(PracticaPreprofesional $practica)
    {
        // Autorización
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
        // La validación y autorización ya ocurrieron en el Request
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // 1. Guardar la carta de ampliación
            // El nombre del archivo incluirá el número de ampliación (1 o 2)
            $extensionNumber = $practica->extension_count + 1;
            $basePath = "practicas/{$practica->id}/ampliaciones";
            $letterPath = $request->file('file_extension_letter')->store($basePath);

            $practica->documents()->create([
                'type' => 'CARTA_AMPLIACION_' . $extensionNumber,
                'file_path' => $letterPath,
                'upload_date' => now()
            ]);

            // 2. Actualizar la práctica para revisión de CPPP
            $practica->update([
                'status' => 'pending_extension', // Nuevo estado
                'pending_extension_date' => $validated['new_end_date']
            ]);
            
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // (Opcional: Loguear $e->getMessage())
            return back()->with('error', 'Error al guardar la solicitud de ampliación.');
        }

        return redirect()->route('dashboard')
                         ->with('success', 'Solicitud de ampliación enviada. Pendiente de revisión por CPPP.');
    }

    /**
     * Muestra el formulario para solicitar Validación Laboral.
     */
    public function showWorkValidationForm()
    {
        // Necesitamos la lista de Asesores para el dropdown
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
        // La validación y autorización ya ocurrieron en el Request
        $validated = $request->validated();
        $studentId = Auth::id();

        try {
            DB::beginTransaction();

            // 1. Crear el registro de la Práctica Preprofesional
            $practica = PracticaPreprofesional::create([
                'student_id' => $studentId,
                'advisor_id' => $validated['advisor_id'],
                'entity_name' => $validated['entity_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'pending_advisor_dictamen', // Salta directo al dictamen del asesor
                'practice_type' => 'validacion_laboral', // Marca como validación
            ]);

            // 2. Guardar los 4 archivos
            $basePath = "practicas/{$practica->id}/validacion";

            // F3 - Informe
            $f3Path = $request->file('file_f3')->store($basePath);
            $practica->documents()->create([ 'type' => 'F3_INFORME_FINAL', 'file_path' => $f3Path, 'upload_date' => now() ]);

            // F4 - Evaluación
            $f4Path = $request->file('file_f4')->store($basePath);
            $practica->documents()->create([ 'type' => 'F4_EVALUACION_ENTIDAD', 'file_path' => $f4Path, 'upload_date' => now() ]);
            
            // Constancia
            $constanciaPath = $request->file('file_constancia')->store($basePath);
            $practica->documents()->create([ 'type' => 'CONSTANCIA_ENTIDAD', 'file_path' => $constanciaPath, 'upload_date' => now() ]);
            
            // Certificación Progresiva
            $certPath = $request->file('file_certificacion')->store($basePath);
            $practica->documents()->create([ 'type' => 'CERTIFICACION_PROGRESIVA', 'file_path' => $certPath, 'upload_date' => now() ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            // (Opcional: Loguear $e->getMessage())
            return back()->with('error', 'Error al guardar la solicitud de validación.');
        }

        return redirect()->route('dashboard')
                         ->with('success', 'Solicitud de Validación Laboral enviada. Pendiente de dictamen del Asesor.');
    }
}