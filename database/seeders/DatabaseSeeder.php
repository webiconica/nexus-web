<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\MenuItem;
use App\Models\Page;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Ejecutar la creación de Roles y Usuario Maestro
        $this->call([
            RoleAndUserSeeder::class,
        ]);

        // 2. Configuraciones Globales Neutras (Marca Blanca)
        $defaultSettings = [
            'site_name' => 'Mi Empresa',
            'site_description' => 'Soluciones integrales, servicios profesionales y atención personalizada para impulsar tu proyecto.',
            'whatsapp_number' => '5215500000000',
            'contact_email' => 'contacto@ejemplo.com',
            'tenant_id' => '1',
            'head_scripts' => '',
            'body_start_scripts' => '',
            'body_end_scripts' => '',
        ];

        foreach ($defaultSettings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // 3. Menú Principal Genérico
        MenuItem::truncate();

        $inicio = MenuItem::create([
            'location' => 'header',
            'title' => 'Inicio',
            'url' => '/',
            'target' => '_self',
            'is_button' => false,
            'sort_order' => 0
        ]);

        $nosotros = MenuItem::create([
            'location' => 'header',
            'title' => 'Nosotros',
            'url' => '/#nosotros',
            'target' => '_self',
            'is_button' => false,
            'sort_order' => 1
        ]);

        $servicios = MenuItem::create([
            'location' => 'header',
            'title' => 'Servicios',
            'url' => '/#servicios',
            'target' => '_self',
            'is_button' => false,
            'sort_order' => 2
        ]);

        // Submenús vinculados al botón 'Servicios'
        MenuItem::create([
            'location' => 'header',
            'title' => 'Consultoría',
            'url' => '/#servicios',
            'target' => '_self',
            'is_button' => false,
            'parent_id' => $servicios->id,
            'sort_order' => 0
        ]);

        MenuItem::create([
            'location' => 'header',
            'title' => 'Planes & Paquetes',
            'url' => '/#planes',
            'target' => '_self',
            'is_button' => false,
            'parent_id' => $servicios->id,
            'sort_order' => 1
        ]);

        MenuItem::create([
            'location' => 'header',
            'title' => 'Blog',
            'url' => '/blog',
            'target' => '_self',
            'is_button' => false,
            'sort_order' => 3
        ]);

        MenuItem::create([
            'location' => 'header',
            'title' => 'Contacto',
            'url' => '/#contacto',
            'target' => '_self',
            'is_button' => false,
            'sort_order' => 4
        ]);

        MenuItem::create([
            'location' => 'header',
            'title' => 'Cotizar',
            'url' => '/#contacto',
            'target' => '_self',
            'is_button' => true,
            'sort_order' => 5
        ]);

        // 4. Página Inicial en BD (en null para renderizar el fallback del welcome)
        Page::updateOrCreate(
            ['slug' => 'inicio'],
            [
                'title' => 'Inicio',
                'content' => null,
                'meta_title' => 'Inicio | Mi Empresa',
                'meta_description' => 'Bienvenido a nuestro sitio web corporativo.',
            ]
        );
    }
}