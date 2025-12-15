<?php

namespace App\Models;

// 1. --- AÑADE ESTAS IMPORTACIONES ---
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword;




class User extends Authenticatable
{
    use HasFactory, Notifiable; // <-- Asegúrate de tener HasFactory
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id', 
        'code',    
        'signature_path',
        'recovery_email'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Define la relación: Un Usuario pertenece a un Role.
     */
    public function role(): BelongsTo // <-- Ahora PHP sabe que esto es Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Define la relación: Un Usuario (Estudiante) tiene muchas Prácticas.
     */
    public function practicasEstudiante(): HasMany
    {
        return $this->hasMany(PracticaPreprofesional::class, 'student_id');
    }

    /**
     * Define la relación: Un Usuario (Asesor) tiene muchas Prácticas.
     */
    public function practicasAsesor(): HasMany
    {
        return $this->hasMany(PracticaPreprofesional::class, 'advisor_id');
    }

    /**
     * Define la relación: Un Usuario (Jurado) tiene muchas asignaciones.
     */
    public function asignacionesJurado(): HasMany
    {
        return $this->hasMany(JuradoAssignment::class, 'jurado_member_id');
    }

    public function routeNotificationForPushover($notification): ?string
    {
    return $this->pushover_key; // Lee la columna de la BD
    }

    public function routeNotificationForMail($notification)
    {
        // Verificamos si la notificación es la de Restablecer Contraseña
        if ($notification instanceof ResetPassword) {
            return $this->recovery_email ?? $this->email;
        }

        // Para cualquier otra notificación, usa el email principal
        return $this->email;
    }

    /**
     * Restauramos el envío estándar.
     * Al llamar a $this->notify(), Laravel usará el método de arriba (routeNotificationForMail)
     * para decidir el destino, pero MANTENDRÁ el contexto del usuario para generar el link.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }
}