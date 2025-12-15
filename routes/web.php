<?php

use Illuminate\Support\Facades\Route;

// --- CONTROLADORES ---
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PracticaController;
use App\Http\Controllers\CPPP\DashboardController as CpppDashboardController;
use App\Http\Controllers\Asesor\DashboardController as AsesorDashboardController;
use App\Http\Controllers\Jury\DashboardController as JuryDashboardController;
use App\Http\Controllers\CPPP\UserController;
use App\Http\Controllers\Decano\DashboardController as DecanoDashboardController;
use App\Http\Controllers\JuradoVotoController;
use App\Http\Controllers\NotificationController; // <-- AQUÍ FALTABA EL PUNTO Y COMA

// --- FACADES Y MODELOS ---
use Illuminate\Support\Facades\Auth;
use App\Models\PracticaPreprofesional;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN Y PERFIL (Generadas por Breeze)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = Auth::user();
    $user->load('role'); 
    
    $data = [];
    
    if ($user->role->name === 'estudiante') {
        
        // Obtenemos TODAS sus prácticas
        $practicas = PracticaPreprofesional::where('student_id', $user->id)
            ->orderByRaw("CASE WHEN status IN ('completed', 'annulled', 'completed_failed') THEN 1 ELSE 0 END") // 0 = Activas, 1 = Finalizadas
            ->orderBy('created_at', 'desc')
            ->get();
            
        // --- INICIO DE LÓGICA NUEVA ---
        
        // ¿Puede el estudiante iniciar un nuevo trámite?
        // NO PUEDE si tiene CUALQUIER práctica que NO esté 'annulled'.
        // (Ej: 'in_review', 'initial_approved', 'completed', 'completed_failed')
        $cannotStartNew = $practicas->contains(function ($practica) {
            return $practica->status !== 'annulled';
        });

        $data['practicas'] = $practicas;
        $data['can_start_new_practica'] = !$cannotStartNew;
        
        // --- FIN DE LÓGICA NUEVA ---
    }
    
    return view('dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/signature', [ProfileController::class, 'updateSignature'])->name('profile.signature.update');
});

require __DIR__.'/auth.php';


/*
|--------------------------------------------------------------------------
| RUTAS DE NUESTRA APLICACIÓN (SISTEMA DE PPP)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | RUTAS DE NOTIFICACIONES (Generales para todos los usuarios)
    |--------------------------------------------------------------------------
    */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/read/{id}', [NotificationController::class, 'read'])->name('notifications.read');


    /*
    |--------------------------------------------------------------------------
    | ROL: ESTUDIANTE
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isEstudiante')->group(function () {
        Route::get('/practicas/solicitar', [PracticaController::class, 'create'])->name('practicas.create');
        Route::post('/practicas', [PracticaController::class, 'store'])->name('practicas.store');
        Route::get('/practicas/{practica}/editar', [PracticaController::class, 'edit'])->name('practicas.edit');
        Route::put('/practicas/{practica}', [PracticaController::class, 'update'])->name('practicas.update');
        Route::get('/practicas/{practica}/informe-final', [PracticaController::class, 'showFinalReportForm'])->name('practicas.final_report.create');
        Route::post('/practicas/{practica}/informe-final', [PracticaController::class, 'storeFinalReport'])->name('practicas.final_report.store');
        Route::get('/practicas/{practica}/informe-final/editar', [PracticaController::class, 'editFinalReport'])->name('practicas.final_report.edit');
        Route::put('/practicas/{practica}/informe-final', [PracticaController::class, 'updateFinalReport'])->name('practicas.final_report.update');
        Route::get('/practicas/{practica}/ampliacion/solicitar', [PracticaController::class, 'showExtensionForm'])->name('practicas.extension.create');
        Route::post('/practicas/{practica}/ampliacion', [PracticaController::class, 'storeExtensionRequest'])->name('practicas.extension.store');
        Route::get('/practicas/validacion/solicitar', [PracticaController::class, 'showWorkValidationForm'])->name('practicas.validation.create');
        Route::post('/practicas/validacion', [PracticaController::class, 'storeWorkValidation'])->name('practicas.validation.store');
       Route::get('/practicas/{practica}/descargar-constancia', [PracticaController::class, 'downloadConstancia'])
        ->name('practicas.downloadConstancia');
        
    });

    
    /*
    | ROL: CPPP (Comisión)
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isCPPP')->prefix('cppp')->name('cppp.')->group(function () {
        
        Route::get('/dashboard', [CpppDashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/practicas/{practica}', [CpppDashboardController::class, 'show'])->name('practicas.show');
        Route::get('/documentos/{document}', [CpppDashboardController::class, 'downloadDocument'])->name('documentos.download');
        
        Route::post('/practicas/{practica}/approve', [CpppDashboardController::class, 'approve'])->name('practicas.approve');
        Route::post('/practicas/{practica}/observar', [CpppDashboardController::class, 'observe'])->name('practicas.observe');
        Route::post('/practicas/{practica}/assign-jury', [CpppDashboardController::class, 'assignJury'])->name('practicas.assignJury');
        Route::post('/practicas/{practica}/schedule-defense', [CpppDashboardController::class, 'scheduleDefense'])->name('practicas.scheduleDefense');
        Route::post('/practicas/{practica}/issue-certificate', [CpppDashboardController::class, 'issueCertificate'])->name('practicas.issueCertificate');
        
        Route::post('/practicas/{practica}/allow-second-attempt', [CpppDashboardController::class, 'allowSecondAttempt'])->name('practicas.allowSecondAttempt');
        Route::post('/practicas/{practica}/annul', [CpppDashboardController::class, 'annul'])->name('practicas.annul');
        Route::post('/practicas/{practica}/approve-extension', [CpppDashboardController::class, 'approveExtension'])->name('practicas.approveExtension');
        Route::post('/practicas/{practica}/reject-extension', [CpppDashboardController::class, 'rejectExtension'])->name('practicas.rejectExtension');

        Route::resource('usuarios', UserController::class)->parameters(['usuarios' => 'user']);
    });
    
    /*
    |--------------------------------------------------------------------------
    | ROL: ASESOR
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isAsesor')->prefix('asesor')->name('asesor.')->group(function () {
        Route::get('/dashboard', [AsesorDashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/practicas/{practica}', [AsesorDashboardController::class, 'show'])->name('practicas.show');
        Route::post('/practicas/{practica}/upload-f2', [AsesorDashboardController::class, 'uploadF2'])->name('practicas.uploadF2');
        Route::post('/practicas/{practica}/approve-dictamen', [AsesorDashboardController::class, 'approveDictamen'])->name('practicas.approveDictamen');
        Route::post('/practicas/{practica}/observe-dictamen', [AsesorDashboardController::class, 'observeDictamen'])->name('practicas.observeDictamen');
        Route::get('/documentos/{document}', [AsesorDashboardController::class, 'downloadDocument'])->name('documentos.download');
    
    });

    
    /*
    |--------------------------------------------------------------------------
    | ROL: JURADO EVALUADOR (ACTUALIZADO)
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isJurado')->prefix('jurado')->name('jury.')->group(function () {
    Route::get('/dashboard', [JuryDashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/practicas/{practica}', [JuryDashboardController::class, 'show'])->name('practicas.show');

    // --- INICIO DE NUEVAS RUTAS DE VOTACIÓN ---
    
    // Ruta para que Miembro/Presidente emita su V/B u Observación
    Route::post('/practicas/{practica}/votar', [JuradoVotoController::class, 'emitirVoto'])
        ->name('voto.emitir');

    // Ruta para que un jurado se recuse (entre el suplente)
    Route::post('/assignments/{assignment}/recusar', [JuradoVotoController::class, 'recusarJurado'])
        ->name('voto.recusar');

    // Ruta para que el Presidente dé la Aprobación Final y programe la fecha
    Route::post('/practicas/{practica}/aprobar-final', [JuradoVotoController::class, 'aprobarSolicitudFinal'])
        ->name('voto.aprobar');
    
    // Ruta para que el Presidente OBSERVE en la decisión final
    Route::post('/practicas/{practica}/observar-final', [JuradoVotoController::class, 'observarSolicitudFinal'])
        ->name('voto.observar');      
        
    // --- FIN DE NUEVAS RUTAS ---

    // Esta ruta se mantiene (solo para Presidente, para SUBIR LA NOTA después de sustentar)
    Route::post('/practicas/{practica}/submit-grade', [JuryDashboardController::class, 'submitGrade'])
        ->name('practicas.submitGrade');
        
    // --- RUTA ANTIGUA (Desactivada) ---
    // Route::post('/practicas/{practica}/vote', [JuryDashboardController::class, 'submitReportVote'])->name('practicas.submitReportVote');
    
    Route::get('/documentos/{document}', [JuryDashboardController::class, 'downloadDocument'])->name('documentos.download');

    
});

    /*
    |--------------------------------------------------------------------------
    | ROL: DECANO
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isDecano')->prefix('decano')->name('decano.')->group(function () {
        Route::get('/dashboard', [DecanoDashboardController::class, 'index'])->name('dashboard.index');
        Route::post('/practicas/{practica}/issue-resolution', [DecanoDashboardController::class, 'issueInitialResolution'])->name('practicas.issueInitialResolution');
    });

});