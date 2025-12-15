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
            // Para diferenciar 'regular' de 'validacion_laboral'
            $table->string('practice_type')->default('regular')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practica_preprofesionals', function (Blueprint $table) {
            $table->dropColumn('practice_type');
        });
    }
};
