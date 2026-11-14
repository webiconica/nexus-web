<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();

            // --- SEO PROFESIONAL ---
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable(); // Palabras clave secundarias

            // --- BRANDING E IMÁGENES (Rutas de archivos) ---
            $table->string('favicon_path')->nullable(); // Para cambiar el ícono de la pestaña por landing
            $table->string('og_image_path')->nullable(); // Imagen para Redes Sociales (1200x630px recomendado)
            $table->string('twitter_image_path')->nullable(); // Imagen optimizada para X (opcional)

            // --- MARKETING ENGINES & INYECCIÓN ---
            $table->text('head_scripts')->nullable(); // Scripts en el <head> (GTM, Analytics, Meta Pixel)
            $table->text('footer_scripts')->nullable(); // Scripts antes del </body> (Chats, Popups)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
