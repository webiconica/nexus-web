<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla maestra de Tenants (Inquilinos / Clientes)
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('api_key', 80)->unique();
            $table->json('allowed_origins')->nullable(); // Dominios permitidos para CORS en widget.js

            // Plan, límites y modo de interfaz
            $table->string('plan')->default('negocio'); // emprendedor, negocio, pro
            $table->unsignedInteger('monthly_chat_limit')->default(1000);
            $table->boolean('crm_only_mode')->default(false); // Oculta Páginas/Blog y prioriza Chat + CRM
            $table->boolean('is_active')->default(true);

            // Webhooks salientes y Meta Conversions API (CAPI)
            $table->string('outbound_webhook_url')->nullable();
            $table->string('outbound_webhook_secret')->nullable();
            $table->string('meta_pixel_id')->nullable();
            $table->text('meta_capi_token')->nullable();

            // WhatsApp Cloud API oficial
            $table->string('whatsapp_phone_number_id')->nullable()->index();
            $table->string('whatsapp_business_account_id')->nullable();
            $table->text('whatsapp_access_token')->nullable();
            $table->string('whatsapp_verify_token')->nullable();

            $table->timestamps();
        });

        // Crear Tenant #1 por defecto para compatibilidad con datos existentes
        DB::table('tenants')->insert([
            'id'                 => 1,
            'name'               => 'Webicónica Principal',
            'slug'               => 'webiconica',
            'api_key'            => 'wk_live_' . Str::random(40),
            'allowed_origins'    => json_encode(['*']),
            'plan'               => 'pro',
            'monthly_chat_limit' => 10000,
            'crm_only_mode'      => false,
            'is_active'          => true,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        // 2. Extender tabla leads con llaves de deduplicación, etapas y atribución Meta
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (!Schema::hasColumn('leads', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
                }
                if (!Schema::hasColumn('leads', 'name')) {
                    $table->string('name')->nullable()->after('origen');
                }
                if (!Schema::hasColumn('leads', 'phone_normalized')) {
                    $table->string('phone_normalized', 25)->nullable()->after('name');
                }
                if (!Schema::hasColumn('leads', 'email')) {
                    $table->string('email')->nullable()->after('phone_normalized');
                }
                if (!Schema::hasColumn('leads', 'stage')) {
                    $table->string('stage', 50)->default('nuevo')->after('email');
                }
                if (!Schema::hasColumn('leads', 'fbclid')) {
                    $table->string('fbclid')->nullable()->after('stage');
                }
                if (!Schema::hasColumn('leads', 'fbc')) {
                    $table->string('fbc')->nullable()->after('fbclid');
                }
                if (!Schema::hasColumn('leads', 'fbp')) {
                    $table->string('fbp')->nullable()->after('fbc');
                }
                if (!Schema::hasColumn('leads', 'last_interaction_at')) {
                    $table->timestamp('last_interaction_at')->nullable()->after('updated_at');
                }

                $table->index(['tenant_id', 'phone_normalized'], 'leads_tenant_phone_idx');
                $table->index(['tenant_id', 'email'], 'leads_tenant_email_idx');
                $table->index(['tenant_id', 'stage'], 'leads_tenant_stage_idx');
            });
        }

        // 3. Historial unificado de conversaciones (Web Chat + WhatsApp + Formularios)
        Schema::create('lead_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1)->index();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('channel', 30); // web_chat, whatsapp, webhook_form, system
            $table->string('direction', 15); // inbound, outbound
            $table->string('sender_type', 20); // contact, ai, agent
            $table->text('content');
            $table->string('external_message_id')->nullable()->index(); // ID de Meta WhatsApp (wamid)
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 4. Bitácora de cambios de etapa y auditoría de Webhooks / Meta CAPI
        Schema::create('lead_stage_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->default(1)->index();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('from_stage', 50)->nullable();
            $table->string('to_stage', 50);
            $table->text('notes')->nullable();
            $table->boolean('webhook_dispatched')->default(false);
            $table->unsignedSmallInteger('webhook_response_status')->nullable();
            $table->timestamps();
        });

        // 5. Agregar tenant_id a las tablas existentes del CMS
        $tablesToScope = ['users', 'pages', 'posts', 'menu_items', 'ai_usage_logs', 'media'];
        foreach ($tablesToScope as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->default(1)->after('id')->index();
                });
            }
        }

        // 6. En settings, permitir la misma 'key' por cada tenant_id
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table) {
                if (!Schema::hasColumn('settings', 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->default(1)->after('id');
                }
            });

            // Intentar reemplazar índice único simple de 'key' por compuesto ['tenant_id', 'key']
            try {
                Schema::table('settings', function (Blueprint $table) {
                    $table->dropUnique(['key']);
                });
            } catch (\Throwable $e) {
                // Ignorar si el índice tenía otro nombre o no existía
            }

            Schema::table('settings', function (Blueprint $table) {
                $table->unique(['tenant_id', 'key'], 'settings_tenant_key_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_stage_logs');
        Schema::dropIfExists('lead_messages');
        Schema::dropIfExists('tenants');
    }
};