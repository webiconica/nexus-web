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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            
            // 'key' guardará el atributo 'name' del input (ej: 'head_scripts', 'whatsapp_number', 'header_logo')
            $table->string('key')->unique(); 
            
            // 'value' guardará el contenido. Usamos 'longText' en lugar de 'text' para asegurar que 
            // no haya problemas si en el futuro pegas scripts muy pesados de GTM, píxeles o CSS personalizado.
            $table->longText('value')->nullable(); 
            
            // 'type' nos ayuda a identificar el tipo de dato (ej: 'text', 'image', 'code') 
            // para saber cómo tratarlo al momento de actualizar o borrar.
            $table->string('type')->default('text'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};