<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // <-- LÍNEA FALTANTE
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;
    
    // Desactiva la asignación masiva para el Seeder
    protected $guarded = [];

    /**
     * Define la relación: Un Role tiene muchos Usuarios.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}