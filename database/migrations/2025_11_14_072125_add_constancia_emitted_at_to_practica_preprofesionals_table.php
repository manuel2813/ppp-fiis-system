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
        // Añadimos la columna que falta
        $table->timestamp('constancia_emitted_at')->nullable()->after('compliance_certificate_issued');
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
