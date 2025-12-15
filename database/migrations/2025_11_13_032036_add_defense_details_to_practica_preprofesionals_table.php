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
            // Fecha y Hora de la Sustentación
            $table->dateTime('defense_date')->nullable()->after('advisor_dictamen_approved');
            // Lugar (físico o virtual)
            $table->string('defense_place')->nullable()->after('defense_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practica_preprofesionals', function (Blueprint $table) {
            $table->dropColumn('defense_date');
            $table->dropColumn('defense_place');
        });
    }
};
