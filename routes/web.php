<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use App\Models\Lead;
use App\Models\Page;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Admin\AiContentController;
use App\Http\Controllers\ChatbotController;

use App\Http\Controllers\Api\WebhookLeadController;
use App\Http\Controllers\Api\WidgetController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

use App\Http\Controllers\Admin\CrmController;
use App\Http\Controllers\Admin\IntegrationSettingsController;

use App\Http\Controllers\Api\WhatsAppWebhookController;

use App\Http\Controllers\Admin\TenantController;

use App\Http\Controllers\Api\GoogleAdsFeedController;

// RUTA PÚBLICA PRINCIPAL
Route::get('/', function () {
    $page = Schema::hasTable('pages') 
        ? Page::where('slug', 'inicio')->first() 
        : null;

    return view('welcome', compact('page'));
})->name('home');

// CAPTURA UNIVERSAL DE LEADS PARA FORMULARIOS / SIMULADOR
Route::post('/api/capturar-lead', [LeadController::class, 'store']);

Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])
    ->name('api.webhooks.whatsapp.verify');

Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'handle'])
    ->name('api.webhooks.whatsapp.handle');

// SITEMAP DINÁMICO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// BLOG PÚBLICO
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/demo-blade', function () {
    return view('demo-video');
});

Route::post('/chatbot/message', [ChatbotController::class, 'sendMessage'])
    ->middleware('throttle:15,1')
    ->name('chatbot.message');

// Script embebible para sitios externos
Route::get('/widget.js', [WidgetController::class, 'script'])->name('widget.script');


// Endpoints API públicos para Webhooks externos y Chatbot Widget
Route::prefix('api/v1')
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->group(function () {
        Route::post('/webhooks/leads', [WebhookLeadController::class, 'store'])
            ->middleware('throttle:60,1')
            ->name('api.webhooks.leads');

        Route::post('/widget/chat', [WidgetController::class, 'chat'])
            ->middleware('throttle:20,1')
            ->name('api.widget.chat');


            // En el grupo público /api/v1:
Route::get('/feeds/google-ads', [GoogleAdsFeedController::class, 'offlineConversions'])
->name('api.feeds.google-ads');
    });

// DASHBOARD
Route::get('/dashboard', function () {
    $leads = Lead::latest()->take(50)->get();
    return view('dashboard', compact('leads'));
})->middleware(['auth', 'verified'])->name('dashboard');

// PERFIL DE USUARIO
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



// RUTAS DEL PANEL DE ADMINISTRACIÓN
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Páginas
    Route::get('/paginas', [PageController::class, 'index'])->name('pages.index');
    Route::get('/paginas/crear', [PageController::class, 'create'])->name('pages.create');
    Route::post('/paginas', [PageController::class, 'store'])->name('pages.store');
    Route::get('/paginas/{page}/editar', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('/paginas/{page}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/paginas/{page}', [PageController::class, 'destroy'])->name('pages.destroy');

    // Menú Principal
    Route::get('/menu', [AdminMenuController::class, 'index'])->name('menu.index');
    Route::post('/menu', [AdminMenuController::class, 'store'])->name('menu.store');
    Route::put('/menu/{menuItem}', [AdminMenuController::class, 'update'])->name('menu.update');
    Route::delete('/menu/{menuItem}', [AdminMenuController::class, 'destroy'])->name('menu.destroy');
    Route::post('/menu/reorder', [AdminMenuController::class, 'reorder'])->name('menu.reorder');

    // Blog / Entradas
    Route::resource('posts', AdminPostController::class);

    // Configuración Global
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Gestor de Archivos (Multimedia)
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::get('/api-media', [MediaController::class, 'apiIndex'])->name('media.api');

    Route::post('/settings/test-ai', [SettingController::class, 'testAiConnection'])->name('settings.test-ai');
    Route::post('/settings/reset-ai-usage', [SettingController::class, 'resetAiUsage'])->name('settings.reset-ai-usage');

    Route::post('/ai/generate-content', [AiContentController::class, 'generate'])->name('ai.generate');

    Route::post('/ai/generate-module', [AiContentController::class, 'generateModule'])->name('ai.generate-module');

    Route::post('/ai/refine-module', [AiContentController::class, 'refineModule'])->name('ai.refine-module');

    Route::post('/settings/generate-theme', [App\Http\Controllers\Admin\SettingController::class, 'generateTheme'])->name('settings.generate-theme');

    // Módulo Protagonista: CRM Omnicanal & Embudo
    Route::get('/crm', [CrmController::class, 'index'])->name('crm.index');
    Route::get('/crm/exportar-csv', [CrmController::class, 'exportCsv'])->name('crm.export');
    Route::get('/crm/{lead}', [CrmController::class, 'show'])->name('crm.show');
    Route::patch('/crm/{lead}/etapa', [CrmController::class, 'updateStage'])->name('crm.update-stage');
    Route::post('/crm/{lead}/notas', [CrmController::class, 'storeNote'])->name('crm.store-note');

    // Centro de Integraciones: Widget JS, Webhook Entrada/Salida y Meta CAPI
    Route::get('/integraciones', [IntegrationSettingsController::class, 'index'])->name('integrations.index');
    Route::put('/integraciones', [IntegrationSettingsController::class, 'update'])->name('integrations.update');
    Route::post('/integraciones/regenerar-key', [IntegrationSettingsController::class, 'regenerateKey'])->name('integrations.regenerate-key');

    

    Route::post('/crm/{lead}/whatsapp', [CrmController::class, 'replyWhatsapp'])->name('crm.reply-whatsapp');

    // Gestión de Clientes Multi-Tenant (Solo visible para Tenant #1)
    Route::get('/clientes', [TenantController::class, 'index'])->name('tenants.index');
    Route::post('/clientes', [TenantController::class, 'store'])->name('tenants.store');
    Route::patch('/clientes/{tenant}/toggle', [TenantController::class, 'toggleStatus'])->name('tenants.toggle');

    // Dentro de tu grupo admin autenticado:
    Route::post('/integraciones/regenerar-google-feed', [IntegrationSettingsController::class, 'regenerateGoogleFeedToken'])
    ->name('integrations.regenerate-google-feed');

    // Manual de Uso del CMS
    Route::get('/manual', function () {
        return view('admin.manual.index');
    })->name('manual.index');
    
});

// ELIMINACIÓN DE LEADS
Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('leads.destroy');

require __DIR__.'/auth.php';

// RUTA DINÁMICA CATCH-ALL (Siempre al final absoluto del archivo)
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show');