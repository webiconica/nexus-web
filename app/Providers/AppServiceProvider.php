<?php

namespace App\Providers;

use App\Models\MenuItem;
use App\Models\Setting;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        View::composer('*', function ($view) {
            $tenantId = TenantContext::id();

            if (Schema::hasTable('settings')) {
                $globalSettings = Cache::remember("global_settings_tenant_{$tenantId}", 60 * 60 * 24, function () {
                    return Setting::pluck('value', 'key')->toArray();
                });
                $view->with('globalSettings', $globalSettings);
            }

            $headerMenu = Cache::remember("header_menu_tree_tenant_{$tenantId}", 60 * 60 * 24, function () {
                if (Schema::hasTable('menu_items')) {
                    return MenuItem::getTree('header');
                }

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
                ];
            });

            $view->with('headerMenu', $headerMenu);
        });
    }
}