<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role; // <-- 1. Importa el modelo Role
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 2. LLAMAR AL ROLE SEEDER PRIMERO
        // Esto es crucial para que la tabla 'roles' tenga datos
        // antes de que intentemos crear usuarios.
        $this->call([
            RoleSeeder::class,
        ]);

        // 3. OBTENER LOS ROLES CREADOS
        // Buscamos los roles que acabamos de crear para poder asignar sus IDs.
        // Es más seguro que asumir que ID=1, ID=2, etc.
        $cpppRole = Role::where('name', 'cppp')->first();
        $studentRole = Role::where('name', 'estudiante')->first();

        
        // 4. CREAR TU "TEST USER" ASIGNÁNDOLE UN ROL
        // Lo asignaremos como 'cppp' para que sea un administrador de prueba.
        User::factory()->create([
            'name' => 'Test Admin (CPPP)',
            'email' => 'test@example.com',
            'role_id' => $cpppRole->id, // <- Asignación obligatoria
            'code' => 'CPPP-001' // Opcional, pero útil
        ]);

        // 5. (RECOMENDADO) CREAR UN ESTUDIANTE DE PRUEBA
        // Nos será útil tener un estudiante listo para las pruebas del flujo.
        User::factory()->create([
            'name' => 'Test Estudiante',
            'email' => 'estudiante@example.com',
            'role_id' => $studentRole->id, // <- Asignación obligatoria
            'code' => '20201234' // Código de estudiante de ejemplo
        ]);

        // User::factory(10)->create(); // Si quisieras crear 10 usuarios aleatorios, 
                                     // tendrías que asignarles un rol (ej. estudiante)
                                     // de forma similar.
    }
}