<?php

use Illuminate\Support\Facades\Route;

// --- CONTROLADORES ---
// Controladores de Breeze
use App\Http\Controllers\ProfileController;
// Controladores de nuestra aplicación
use App\Http\Controllers\PracticaController;
use App\Http\Controllers\CPPP\DashboardController as CpppDashboardController;
use App\Http\Controllers\Asesor\DashboardController as AsesorDashboardController;
use App\Http\Controllers\Jury\DashboardController as JuryDashboardController;
use App\Http\Controllers\CPPP\UserController;
use App\Http\Controllers\Decano\DashboardController as DecanoDashboardController; // <-- NUEVO CONTROLADOR
// --- FACADES Y MODELOS ---
use Illuminate\Support\Facades\Auth;
use App\Models\PracticaPreprofesional;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta de Bienvenida (Pública)
Route::get('/', function () {
    // Redirige al login si no está autenticado, o al dashboard si ya lo está
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
// Ruta de Dashboard personalizada (combina Breeze con nuestra lógica)
Route::get('/dashboard', function () {
    $user = Auth::user();
    $user->load('role'); // Carga la relación 'role'
    
    $data = [];
    
    if ($user->role->name === 'estudiante') {
        
        // --- INICIO DE LA CORRECCIÓN (Lógica de Anulación) ---
        // Definimos los estados que se consideran "finalizados"
        $finishedStates = ['completed', 'annulled', 'completed_failed'];

        // Buscamos la última práctica del estudiante que NO esté en un estado finalizado
        $data['practica'] = PracticaPreprofesional::where('student_id', $user->id)
                            ->whereNotIn('status', $finishedStates) // <-- Lógica Corregida
                            ->orderBy('created_at', 'desc')
                            ->first();
        // --- FIN DE LA CORRECCIÓN ---
    }
    
    return view('dashboard', $data);
})->middleware(['auth', 'verified'])->name('dashboard'); // 'verified' es opcional pero bueno

// Rutas de Perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Incluye las rutas de login, register, password reset, etc.
require __DIR__.'/auth.php';


/*
|--------------------------------------------------------------------------
| RUTAS DE NUESTRA APLICACIÓN (SISTEMA DE PPP)
|--------------------------------------------------------------------------
| Todas protegidas por el middleware 'auth'
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | ROL: ESTUDIANTE
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isEstudiante')->group(function () {
        
        // --- Solicitud Inicial ---
        Route::get('/practicas/solicitar', [PracticaController::class, 'create'])
            ->name('practicas.create');
        Route::post('/practicas', [PracticaController::class, 'store'])
            ->name('practicas.store');
        Route::get('/practicas/{practica}/editar', [PracticaController::class, 'edit'])
            ->name('practicas.edit');
        Route::put('/practicas/{practica}', [PracticaController::class, 'update'])
            ->name('practicas.update');

        // --- Informe Final ---
        Route::get('/practicas/{practica}/informe-final', [PracticaController::class, 'showFinalReportForm'])
            ->name('practicas.final_report.create');
        Route::post('/practicas/{practica}/informe-final', [PracticaController::class, 'storeFinalReport'])
            ->name('practicas.final_report.store');
        Route::get('/practicas/{practica}/informe-final/editar', [PracticaController::class, 'editFinalReport'])
            ->name('practicas.final_report.edit');
        Route::put('/practicas/{practica}/informe-final', [PracticaController::class, 'updateFinalReport'])
            ->name('practicas.final_report.update');

        // --- Ampliación ---
        Route::get('/practicas/{practica}/ampliacion/solicitar', [PracticaController::class, 'showExtensionForm'])
            ->name('practicas.extension.create');
        Route::post('/practicas/{practica}/ampliacion', [PracticaController::class, 'storeExtensionRequest'])
            ->name('practicas.extension.store');
        
        // --- Validación Laboral ---
        Route::get('/practicas/validacion/solicitar', [PracticaController::class, 'showWorkValidationForm'])
            ->name('practicas.validation.create');
        Route::post('/practicas/validacion', [PracticaController::class, 'storeWorkValidation'])
            ->name('practicas.validation.store');
    });

    
    /*
    |--------------------------------------------------------------------------
    | ROL: CPPP (Comisión)
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isCPPP')->prefix('cppp')->name('cppp.')->group(function () {
        
        Route::get('/dashboard', [CpppDashboardController::class, 'index'])
            ->name('dashboard.index');
        Route::get('/practicas/{practica}', [CpppDashboardController::class, 'show'])
            ->name('practicas.show');
        Route::get('/documentos/{document}', [CpppDashboardController::class, 'downloadDocument'])
            ->name('documentos.download');

        // Flujo de Práctica
        Route::post('/practicas/{practica}/approve', [CpppDashboardController::class, 'approve'])
            ->name('practicas.approve');
        Route::post('/practicas/{practica}/observar', [CpppDashboardController::class, 'observe'])
            ->name('practicas.observe');
        Route::post('/practicas/{practica}/assign-jury', [CpppDashboardController::class, 'assignJury'])
            ->name('practicas.assignJury');
        Route::post('/practicas/{practica}/schedule-defense', [CpppDashboardController::class, 'scheduleDefense'])
            ->name('practicas.scheduleDefense');
        Route::post('/practicas/{practica}/issue-certificate', [CpppDashboardController::class, 'issueCertificate'])
            ->name('practicas.issueCertificate');
        
        // Flujos Excepcionales
        Route::post('/practicas/{practica}/allow-second-attempt', [CpppDashboardController::class, 'allowSecondAttempt'])
            ->name('practicas.allowSecondAttempt');
        Route::post('/practicas/{practica}/annul', [CpppDashboardController::class, 'annul'])
            ->name('practicas.annul');
        Route::post('/practicas/{practica}/approve-extension', [CpppDashboardController::class, 'approveExtension'])
            ->name('practicas.approveExtension');
        Route::post('/practicas/{practica}/reject-extension', [CpppDashboardController::class, 'rejectExtension'])
            ->name('practicas.rejectExtension');

        // Gestión de Usuarios
        Route::resource('usuarios', UserController::class)->parameters([
            'usuarios' => 'user'
        ]);
        

    });

    // Route::middleware(['auth', 'can:isCPPP_or_isDecano'])->prefix('admin')->name('admin.')->group(function () {
        
        // Gestión de Usuarios
       //  Route::resource('usuarios', UserController::class)->parameters([
          //   'usuarios' => 'user'
      //   ]);

   //  });
    
    /*
    |--------------------------------------------------------------------------
    | ROL: ASESOR
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isAsesor')->prefix('asesor')->name('asesor.')->group(function () {
        
        Route::get('/dashboard', [AsesorDashboardController::class, 'index'])
            ->name('dashboard.index');
        Route::get('/practicas/{practica}', [AsesorDashboardController::class, 'show'])
            ->name('practicas.show');

        // Flujo
        Route::post('/practicas/{practica}/upload-f2', [AsesorDashboardController::class, 'uploadF2'])
            ->name('practicas.uploadF2');
        Route::post('/practicas/{practica}/approve-dictamen', [AsesorDashboardController::class, 'approveDictamen'])
            ->name('practicas.approveDictamen');
        Route::post('/practicas/{practica}/observe-dictamen', [AsesorDashboardController::class, 'observeDictamen'])
            ->name('practicas.observeDictamen');
    });

    
    /*
    |--------------------------------------------------------------------------
    | ROL: JURADO EVALUADOR
    |--------------------------------------------------------------------------
    */
    Route::middleware('can:isJurado')->prefix('jurado')->name('jury.')->group(function () {
        
        Route::get('/dashboard', [JuryDashboardController::class, 'index'])
            ->name('dashboard.index');
        Route::get('/practicas/{practica}', [JuryDashboardController::class, 'show'])
            ->name('practicas.show');
        
        // Flujo
        Route::post('/practicas/{practica}/observe', [JuryDashboardController::class, 'observeReport'])
            ->name('practicas.observe');
        Route::post('/practicas/{practica}/approve', [JuryDashboardController::class, 'approveReport'])
            ->name('practicas.approve');
        Route::post('/practicas/{practica}/submit-grade', [JuryDashboardController::class, 'submitGrade'])
            ->name('practicas.submitGrade');
    });

    Route::middleware('can:isDecano')->prefix('decano')->name('decano.')->group(function () {
    
    // GET: Muestra el dashboard del Decano
    Route::get('/dashboard', [DecanoDashboardController::class, 'index'])
        ->name('dashboard.index');

    // POST: Emite la resolución inicial
    Route::post('/practicas/{practica}/issue-resolution', [DecanoDashboardController::class, 'issueInitialResolution'])
        ->name('practicas.issueInitialResolution');
    });
    

});