<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Detecta automáticamente si el servidor usa 'public_html' (cPanel/CWP);
        // de lo contrario, mantiene 'public' por defecto (entorno local Laragon).
        if (is_dir(base_path('public_html'))) {
            $this->app->usePublicPath(base_path('public_html'));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('settings')) {
            $globalSettings = Cache::remember('global_settings', 60 * 60 * 24, function () {
                return Setting::pluck('value', 'key')->toArray();
            });
            View::share('globalSettings', $globalSettings);
        }

        // Árbol de menú dinámico para sitios corporativos / de negocio
        $headerMenu = Cache::remember('header_menu_tree', 60 * 60 * 24, function () {
            if (Schema::hasTable('menu_items')) {
                return \App\Models\MenuItem::getTree('header');
            }

            // Estructura universal para cualquier empresa o negocio (con soporte de sub-botones y botón CTA)
            return [
                [
                    'title' => 'Inicio',
                    'url' => '/',
                    'target' => '_self',
                    'is_button' => false,
                    'children' => []
                ],
                [
                    'title' => 'Nosotros',
                    'url' => '/quienes-somos',
                    'target' => '_self',
                    'is_button' => false,
                    'children' => []
                ],
                [
                    'title' => 'Servicios',
                    'url' => '/#servicios',
                    'target' => '_self',
                    'is_button' => false,
                    'children' => [
                        [
                            'title' => 'Consultoría Especializada',
                            'url' => '/servicios/consultoria',
                            'target' => '_self',
                            'is_button' => false,
                            'children' => []
                        ],
                        [
                            'title' => 'Soluciones',
                            'url' => '/servicios/corporativos',
                            'target' => '_self',
                            'is_button' => false,
                            'children' => []
                        ],
                        [
                            'title' => 'Planes y Paquetes',
                            'url' => '/#planes',
                            'target' => '_self',
                            'is_button' => false,
                            'children' => []
                        ],
                    ]
                ],
                [
                    'title' => 'Preguntas Frecuentes',
                    'url' => '/#faq',
                    'target' => '_self',
                    'is_button' => false,
                    'children' => []
                ],
                [
                    'title' => 'Blog',
                    'url' => '/blog',
                    'target' => '_self',
                    'is_button' => false,
                    'children' => []
                ],
                [
                    'title' => 'Contacto',
                    'url' => '/#contacto',
                    'target' => '_self',
                    'is_button' => false,
                    'children' => []
                ],
                [
                    'title' => 'Cotizar Proyecto',
                    'url' => '/#cotizador',
                    'target' => '_self',
                    'is_button' => true, // Marcado como botón destacado para headers que usen CTA
                    'children' => []
                ]
            ];
        });

        View::share('headerMenu', $headerMenu);
    }
}