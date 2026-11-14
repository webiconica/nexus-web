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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable(); // Para saber a cuál de tus clientes le pertenece este lead
            $table->string('origen')->nullable(); // Ej: "Landing Page Ecosistema", "Formulario Cotizador"
            $table->json('datos_formulario'); // <--- Aquí ocurre la magia
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
