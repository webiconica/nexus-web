<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear el Rol Supremo con Spatie
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);

        // 2. Crear tu usuario maestro
        $adminUser = User::firstOrCreate(
            ['email' => 'webiconica@gmail.com'],
            [
                'name' => 'Gerardo Bolaños',
                'password' => Hash::make('12345678'), // Cambia esta contraseña en entornos de producción
            ]
        );

        // 3. Asignar el rol al usuario
        if (!$adminUser->hasRole('Super Admin')) {
            $adminUser->assignRole($adminRole);
        }
    }
}