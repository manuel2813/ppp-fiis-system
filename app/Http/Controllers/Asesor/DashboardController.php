<?php

namespace App\Http\Controllers\Asesor; // <-- Nota la carpeta Asesor

use App\Http\Controllers\Controller;
use App\Models\PracticaPreprofesional;
use App\Http\Requests\Asesor\UploadF2Request; // <-- Nuestro FormRequest
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\DictamenFavorableEmitido;
use App\Notifications\InformeFinalObservado; // (Para el siguiente paso)
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Models\PracticaDocument;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard del Asesor con sus prácticas asignadas.
     */
    public function index()
    {
        $user = Auth::user();
        $practicasAsignadas = $user->practicasAsesor()
                                        ->with('student')
                                        ->orderBy('status', 'asc')
                                        ->get();

        return view('asesor.dashboard.index', [
            'practicas' => $practicasAsignadas
        ]);
    }

    /**
     * Muestra el detalle de una práctica para subir F2.
     */
    public function show(PracticaPreprofesional $practica)
    {
        if ($practica->advisor_id !== Auth::id()) {
            abort(403, 'No está autorizado para ver esta práctica.');
        }

        $practica->load('student', 'documents');
        $formatosF2 = $practica->documents->where('type', 'F2_SUPERVISION');

        return view('asesor.dashboard.show', [
            'practica' => $practica,
            'formatosF2_subidos' => $formatosF2
        ]);
    }

    /**
     * Guarda el Formato F2 (Ficha de Supervisión).
     */
    public function uploadF2(UploadF2Request $request, PracticaPreprofesional $practica)
    {
        $validated = $request->validated();
        $basePath = "practicas/{$practica->id}/formatos_f2";
        
        try {
            $f2Path = $request->file('file_f2')->store($basePath);
            $practica->documents()->create([
                'type' => 'F2_SUPERVISION',
                'file_path' => $f2Path,
                'upload_date' => now(),
                'notes' => $validated['supervision_notes'] ?? null
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al guardar el Formato F2. Intente de nuevo.');
        }

        return back()->with('success', 'Formato F2 (Supervisión) subido correctamente.');
    }

    /**
     * Aprueba el Informe Final (Emite Dictamen Favorable).
     */
    public function approveDictamen(Request $request, PracticaPreprofesional $practica)
    {
        if ($practica->advisor_id !== Auth::id()) {
            abort(403);
        }

        try {
            $practica->update([
                'advisor_dictamen_approved' => true,
                'status' => 'pending_jury_assignment',
                'observation_notes' => null
            ]);

            try {
                $cpppUsers = User::whereHas('role', function ($query) {
                    $query->where('name', 'cppp');
                })->get();

                if ($cpppUsers->isNotEmpty()) {
                    $practica->load('student');
                    Notification::send($cpppUsers, new DictamenFavorableEmitido($practica));
                } else {
                    Log::warning('No se encontró un usuario "cppp" para notificar dictamen favorable.');
                }
            } catch (\Exception $e) {
                Log::error('Fallo al enviar notificación (DictamenFavorable): ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error al aprobar dictamen: ' . $e->getMessage());
            return back()->with('error', 'Error al emitir el dictamen.');
        }

        return redirect()->route('asesor.dashboard.index')
                         ->with('success', 'Dictamen Favorable emitido. La CPPP asignará un jurado.');
    }

    /**
     * Observa (rechaza) el Informe Final del estudiante.
     */
    public function observeDictamen(Request $request, PracticaPreprofesional $practica)
    {
        if ($practica->advisor_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'observation_notes' => 'required|string|min:20',
        ], [
            'observation_notes.required' => 'Debe proveer una razón clara para la observación del informe final.',
            'observation_notes.min' => 'La observación debe tener al menos 20 caracteres.'
        ]);

        try {
            $practica->update([
                'status' => 'final_report_observed',
                'advisor_dictamen_approved' => false,
                'observation_notes' => $validated['observation_notes']
            ]);

            try {
                $practica->load('student');
                if ($practica->student) {
                    $practica->student->notify(new InformeFinalObservado($practica));
                }
            } catch (\Exception $e) {
                Log::error('Fallo al enviar notificación (InformeFinalObservado): ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error('Error al observar dictamen: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar la observación.');
        }

        return redirect()->route('asesor.dashboard.index')
                         ->with('success', 'Informe Final Observado. El estudiante deberá corregirlo.');
    }

    /**
     * Maneja la descarga segura de archivos para el Asesor.
     */
    public function downloadDocument(PracticaDocument $document)
    {
        // 1. Cargamos la práctica asociada al documento
        $document->load('practica');

        // 2. VERIFICACIÓN DE PERMISO:
        // ¿El usuario autenticado es el asesor de la práctica a la que
        // pertenece este documento?
        if ($document->practica->advisor_id !== Auth::id()) {
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