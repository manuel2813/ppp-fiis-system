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
            // El 'title' ya existe en tu DB (según el dump), así que no lo añadimos.
            // Solo añadimos los campos que faltan del F1.
            $table->string('practice_area')->nullable()->after('title');
            $table->text('entity_details')->nullable()->after('practice_area');
            $table->text('practice_objectives')->nullable()->after('entity_details');
            $table->text('practice_activities')->nullable()->after('practice_objectives');
            $table->text('practice_schedule')->nullable()->after('practice_activities');
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
