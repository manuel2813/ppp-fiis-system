<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if ($user && !$user->relationLoaded('role')) {
                $user->load('role');
            }
        });

        Gate::define('isEstudiante', function (User $user) {
            return $user->role && $user->role->name === 'estudiante';
        });
        
        Gate::define('isAsesor', function (User $user) {
            return $user->role && $user->role->name === 'asesor';
        });
        
        Gate::define('isCPPP', function (User $user) {
            return $user->role && $user->role->name === 'cppp';
        });
        
        Gate::define('isJurado', function (User $user) {
            return $user->role && $user->role->name === 'jurado';
        });
        
        Gate::define('isDecano', function (User $user) {
            return $user->role && $user->role->name === 'decano';
        });

        Gate::define('isDocente', function (User $user) {
            return $user->role && in_array($user->role->name, ['asesor', 'jurado']);
        });

        // El gate 'isCPPP_or_isDecano' ha sido eliminado.
    }
}