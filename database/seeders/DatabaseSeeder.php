<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Machine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario personal
        User::factory()->create([
            'name' => 'Personal Lavanderia',
            'email' => 'staff@quickwash.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        // Crear estudiante de prueba
        User::factory()->create([
            'name' => 'Estudiante Uno',
            'email' => 'student@quickwash.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // Crear máquinas
        for ($i = 1; $i <= 5; $i++) {
            Machine::create([
                'name' => "Lavadora $i",
                'status' => 'available',
            ]);
        }
        for ($i = 1; $i <= 3; $i++) {
            Machine::create([
                'name' => "Secadora $i",
                'status' => 'available',
            ]);
        }
    }
}
