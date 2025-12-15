<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'estudiante', 'description' => 'Estudiante de la EPIIS / Practicante'],
            ['name' => 'asesor', 'description' => 'Docente Asesor de Prácticas Preprofesionales'],
            ['name' => 'cppp', 'description' => 'Miembro de la Comisión de Prácticas Preprofesionales (Presidente/Secretario/Vocal)'],
            ['name' => 'jurado', 'description' => 'Docente Asignado como Miembro del Jurado Evaluador'],
            ['name' => 'decano', 'description' => 'Decano de la Facultad (Autoriza Resoluciones)'],
        ];

        DB::table('roles')->insert($roles);
    }
}