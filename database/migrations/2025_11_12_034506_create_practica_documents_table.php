<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practica_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practica_id')->constrained('practica_preprofesionals');
            $table->string('type'); // F1_PLAN, F2_SUPERVISION, F3_INFORME_FINAL, F4_EVALUACION_ENTIDAD, SUT, CARTA_ACEPTACION
            $table->string('file_path'); // Ruta de almacenamiento del archivo
            $table->date('upload_date');
            $table->text('notes')->nullable(); // Comentarios u observaciones
            $table->boolean('is_approved')->nullable(); // Aprobación (Dictamen Favorable, Jurado, etc.)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practica_documents');
    }
};