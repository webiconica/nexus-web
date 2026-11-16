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

// RUTA PÚBLICA PRINCIPAL
Route::get('/', function () {
    $page = Schema::hasTable('pages') 
        ? Page::where('slug', 'inicio')->first() 
        : null;

    return view('welcome', compact('page'));
})->name('home');

// CAPTURA UNIVERSAL DE LEADS PARA FORMULARIOS / SIMULADOR
Route::post('/api/capturar-lead', [LeadController::class, 'store']);

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