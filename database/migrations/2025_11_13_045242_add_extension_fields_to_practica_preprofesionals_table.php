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
            // Contador de ampliaciones aprobadas
            $table->integer('extension_count')->default(0)->after('defense_place');
            
            // Fecha de ampliación solicitada (temporal)
            $table->date('pending_extension_date')->nullable()->after('extension_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practica_preprofesionals', function (Blueprint $table) {
            $table->dropColumn('extension_count');
            $table->dropColumn('pending_extension_date');
        });
    }
};
