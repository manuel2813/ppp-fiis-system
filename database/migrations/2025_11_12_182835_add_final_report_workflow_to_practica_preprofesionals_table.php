<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('practica_preprofesionals', function (Blueprint $table) {
            
            // La columna 'resolution_number' YA FUE AÑADIDA en la migración de creación.
            // Por eso la borramos de aquí.

            // Para que el Asesor apruebe el informe final (Dictamen)
            $table->boolean('advisor_dictamen_approved')->default(false)->after('observation_notes');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practica_preprofesionals', function (Blueprint $table) {
            // Solo borramos la columna que esta migración crea
            $table->dropColumn('advisor_dictamen_approved');
        });
    }
};