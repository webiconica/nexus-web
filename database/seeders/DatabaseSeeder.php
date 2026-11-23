<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar que exista el Tenant #1 (Inquilino Principal)
        $mainTenant = Tenant::firstOrCreate(
            ['id' => 1],
            [
                'name'               => 'Webicónica Principal',
                'slug'               => 'webiconica',
                'api_key'            => 'wk_live_' . Str::random(40),
                'allowed_origins'    => ['*'],
                'plan'               => 'pro',
                'monthly_chat_limit' => 10000,
                'crm_only_mode'      => false,
                'is_active'          => true,
            ]
        );

        TenantContext::set($mainTenant);

        // 2. Ejecutar creación de Roles y Usuario Maestro
        $this->call([
            RoleAndUserSeeder::class,
        ]);

        // 3. Configuraciones Globales Neutras (Marca Blanca + Modelos IA vigentes)
        $defaultSettings = [
            'site_name'          => 'Mi Empresa',
            'site_description'   => 'Soluciones integrales, servicios profesionales y atención personalizada para impulsar tu proyecto.',
            'whatsapp_number'    => '5215500000000',
            'contact_email'      => 'contacto@ejemplo.com',
            'head_scripts'       => '',
            'body_start_scripts' => '',
            'body_end_scripts'   => '',
            'ai_enabled'         => '1',
            'ai_default_model'   => 'gemini-3.8-flash',
            'chatbot_enabled'    => '1',
            'chatbot_model'      => 'gemini-3.5-flash-lite',
        ];

        foreach ($defaultSettings as $key => $value) {
            Setting::updateOrCreate(
                ['tenant_id' => 1, 'key' => $key],
                ['value' => $value]
            );
        }

        // 4. Menú Principal Genérico (aislado al Tenant #1)
        MenuItem::where('tenant_id', 1)->delete();

        MenuItem::create([
            'tenant_id'  => 1,
            'location'   => 'header',
            'title'      => 'Inicio',
            'url'        => '/',
            'target'     => '_self',
            'is_button'  => false,
            'sort_order' => 0,
        ]);

        MenuItem::create([
            'tenant_id'  => 1,
            'location'   => 'header',
            'title'      => 'Nosotros',
            'url'        => '/#nosotros',
            'target'     => '_self',
            'is_button'  => false,
            'sort_order' => 1,
        ]);

        $servicios = MenuItem::create([
            'tenant_id'  => 1,
            'location'   => 'header',
            'title'      => 'Servicios',
            'url'        => '/#servicios',
            'target'     => '_self',
            'is_button'  => false,
            'sort_order' => 2,
        ]);

        // Submenús vinculados a 'Servicios'
        MenuItem::create([
            'tenant_id'  => 1,
            'location'   => 'header',
            'title'      => 'Consultoría',
            'url'        => '/#servicios',
            'target'     => '_self',
            'is_button'  => false,
            'parent_id'  => $servicios->id,
            'sort_order' => 0,
        ]);

        MenuItem::create([
            'tenant_id'  => 1,
            'location'   => 'header',
            'title'      => 'Planes & Paquetes',
            'url'        => '/#planes',
            'target'     => '_self',
            'is_button'  => false,
            'parent_id'  => $servicios->id,
            'sort_order' => 1,
        ]);

        MenuItem::create([
            'tenant_id'  => 1,
            'location'   => 'header',
            'title'      => 'Blog',
            'url'        => '/blog',
            'target'     => '_self',
            'is_button'  => false,
            'sort_order' => 3,
        ]);

        MenuItem::create([
            'tenant_id'  => 1,
            'location'   => 'header',
            'title'      => 'Contacto',
            'url'        => '/#contacto',
            'target'     => '_self',
            'is_button'  => false,
            'sort_order' => 4,
        ]);

        MenuItem::create([
            'tenant_id'  => 1,
            'location'   => 'header',
            'title'      => 'Cotizar',
            'url'        => '/#contacto',
            'target'     => '_self',
            'is_button'  => true,
            'sort_order' => 5,
        ]);

        // 5. Página Inicial en BD (en null para renderizar el fallback del welcome)
        Page::updateOrCreate(
            ['tenant_id' => 1, 'slug' => 'inicio'],
            [
                'title'            => 'Inicio',
                'content'          => null,
                'meta_title'       => 'Inicio | Mi Empresa',
                'meta_description' => 'Bienvenido a nuestro sitio web corporativo.',
            ]
        );
    }
}