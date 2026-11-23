<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el Rol Supremo con Spatie
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);

        // 2. Crear o actualizar usuario maestro vinculado al Tenant #1
        $adminUser = User::firstOrCreate(
            ['email' => 'webiconica@gmail.com'],
            [
                'tenant_id' => 1,
                'name'      => 'Gerardo Bolaños',
                'password'  => Hash::make('12345678'), // Cambiar en producción
            ]
        );

        if (empty($adminUser->tenant_id)) {
            $adminUser->update(['tenant_id' => 1]);
        }

        // 3. Asignar el rol al usuario
        if (!$adminUser->hasRole('Super Admin')) {
            $adminUser->assignRole($adminRole);
        }
    }
}