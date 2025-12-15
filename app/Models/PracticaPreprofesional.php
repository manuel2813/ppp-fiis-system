<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticaPreprofesional extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * **** SECCIÓN CORREGIDA CON TODOS LOS CAMPOS DEL F1 ****
     */
    protected $fillable = [
        // --- Campos Originales ---
        'student_id',
        'advisor_id',
        'entity_name',
        'start_date',
        'end_date',
        'status',
        'practice_type',
        'observation_notes',
        'resolution_number',
        'advisor_dictamen_approved',
        'defense_date',
        'defense_place',
        'extension_count',
        'pending_extension_date',
        'annulment_reason',
        'final_grade',
        'compliance_certificate_issued',
        'constancia_emitted_at',
        'constancia_issuer_id',

        // --- INICIO: CAMPOS AÑADIDOS (FICHA F1) ---
        'entity_ruc',
        'entity_phone',
        'entity_address',
        'entity_manager',
        'entity_department',
        'entity_province',
        'entity_district',
        'supervisor_name',
        'supervisor_email',
        // --- FIN: CAMPOS AÑADIDOS (FICHA F1) ---
        
        // --- INICIO: CAMPOS AÑADIDOS (PLAN F1 - MODAL) ---
        'title',
        'practice_area',
        'entity_details',
        'practice_objectives',
        'practice_activities',
        'practice_schedule',
        // --- FIN: CAMPOS AÑADIDOS (PLAN F1 - MODAL) ---
    ];

    /**
     * Define la relación: Una Práctica pertenece a un Estudiante (User).
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Define la relación: Una Práctica pertenece a un Asesor (User).
     */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    /**
     * Define la relación: Una Práctica tiene muchos Documentos.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(PracticaDocument::class, 'practica_id');
    }

    /**
     * Define la relación: Una Práctica tiene muchas asignaciones de Jurado.
     */
    public function juradoAssignments(): HasMany
    {
        return $this->hasMany(JuradoAssignment::class, 'practica_id');
    }

    /**
     * Define la relación: Una Práctica tiene muchos Jurados (Usuarios)
     * a través de las asignaciones.
     */
    public function jurados(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'jurado_assignments', 'practica_id', 'jurado_member_id');
    }
}