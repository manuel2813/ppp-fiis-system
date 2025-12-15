<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurado_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practica_id')->constrained('practica_preprofesionals');
            $table->foreignId('jurado_member_id')->constrained('users'); // Miembro del Jurado
            $table->string('role'); // Presidente, Miembro, Suplente [cite: 171]
            $table->decimal('score', 4, 2)->nullable(); // Calificación individual [cite: 204]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurado_assignments');
    }
};