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
        
        // Columna 1: El voto del jurado
        // Visto Bueno, Observacion, o NULL si aún no vota.
        $table->string('voto')->nullable()->after('role');

        // Columna 2: El texto de la observación (si la hay)
        $table->text('observacion_detalle')->nullable()->after('voto');

        // Columna 3: El estado del jurado en esta práctica
        // 'Activo' = Debe votar (Presidente, Miembros)
        // 'Pendiente' = Es el suplente en espera
        // 'Recusado' = Fue reemplazado por el suplente
        $table->string('estado')->default('Activo')->after('observacion_detalle');
    });

    // Cuando asignes jurados, asegúrate de poner al Suplente con estado 'Pendiente'
    // DB::table('jurado_assignments')
    //     ->where('role', 'Suplente')
    //     ->update(['estado' => 'Pendiente']);
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurado_assignments', function (Blueprint $table) {
            //
        });
    }
};
