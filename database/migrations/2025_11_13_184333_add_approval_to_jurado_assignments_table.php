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
        Schema::table('jurado_assignments', function (Blueprint $table) {
            // Columna para el Visto Bueno (V°B°) del informe F3
            // null = Pendiente, true = Aprobado, false = Observado
            $table->boolean('report_approved')->nullable()->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurado_assignments', function (Blueprint $table) {
            $table->dropColumn('report_approved');
        });
    }
};
