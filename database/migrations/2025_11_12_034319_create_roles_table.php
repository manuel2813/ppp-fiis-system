<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // estudiante, asesor, cppp, decano, jurado
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Añadir una columna de clave foránea de rol a la tabla 'users'
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->after('email');
            $table->string('code')->nullable()->after('name'); // Código del estudiante/docente
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
            $table->dropColumn('code');
        });
        Schema::dropIfExists('roles');
    }
};