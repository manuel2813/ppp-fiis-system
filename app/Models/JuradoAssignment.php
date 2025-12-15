<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JuradoAssignment extends Model
{
    use HasFactory;

    /**
     * Esto permite la asignación masiva de CUALQUIER campo.
     * Es perfecto para nuestro caso, no necesitas $fillable.
     */
    protected $guarded = [];

    /**
     * Relación con la Práctica
     */
    public function practica(): BelongsTo
    {
        return $this->belongsTo(PracticaPreprofesional::class, 'practica_id');
    }

    /**
     * Relación con el Usuario (Jurado)
     */
    public function juradoMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'jurado_member_id');
    }
}