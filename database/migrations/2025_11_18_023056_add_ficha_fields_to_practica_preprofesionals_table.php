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
            // Campos de la "Ficha F1" 
            $table->string('entity_ruc', 11)->nullable()->after('entity_name');
            $table->string('entity_phone')->nullable()->after('entity_ruc');
            $table->string('entity_address')->nullable()->after('entity_phone');
            $table->string('entity_manager')->nullable()->after('entity_address'); // Gerente/Representante
            $table->string('entity_department')->nullable()->after('entity_manager'); // Departamento (Ubicación)
            $table->string('entity_province')->nullable()->after('entity_department');
            $table->string('entity_district')->nullable()->after('entity_province');
            
            // Datos del Supervisor en la Entidad [cite: 248-250]
            $table->string('supervisor_name')->nullable()->after('practice_schedule'); // Jefe inmediato / Supervisor
            $table->string('supervisor_email')->nullable()->after('supervisor_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practica_preprofesionals', function (Blueprint $table) {
            //
        });
    }
};
