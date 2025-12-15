<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PracticaDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'practica_id',
        'type',
        'file_path',
        'upload_date',
        'notes', // (Asegúrate de que 'notes' esté en $fillable, lo usamos en el controlador)
    ];
    
    // --- INICIO DE LA CORRECCIÓN ---
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'upload_date' => 'datetime', // <-- ESTA LÍNEA ES LA MAGIA
    ];
    // --- FIN DE LA CORRECCIÓN ---

    public function practica(): BelongsTo
    {
        return $this->belongsTo(PracticaPreprofesional::class, 'practica_id');
    }
}