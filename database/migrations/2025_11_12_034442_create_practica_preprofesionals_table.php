<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practica_preprofesionals', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // Título de la práctica o informe
            $table->foreignId('student_id')->constrained('users'); // Practicante [cite: 253-254]
            $table->foreignId('advisor_id')->constrained('users'); // Asesor principal (propuesto por el estudiante) [cite: 118]
            $table->string('entity_name'); // Razón Social de la Entidad [cite: 239]
            $table->date('start_date'); // Fecha de inicio de la práctica [cite: 110, 245]
            $table->date('end_date'); // Fecha de término de la práctica [cite: 110, 245]
            $table->integer('total_hours')->nullable(); // Horas mínimas/máximas (máx 30/semana) [cite: 137]
            $table->string('status')->default('in_review_initial'); // Estado del flujo de trabajo
            
            // Campos de documentos obligatorios
            $table->string('resolution_number')->nullable(); // N.º de resolución de autorización [cite: 62, 116]
            $table->string('final_grade')->nullable(); // Calificación final (0-20) [cite: 204]
            $table->boolean('compliance_certificate_issued')->default(false); // Constancia de Cumplimiento [cite: 178]
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practica_preprofesionals');
    }
};