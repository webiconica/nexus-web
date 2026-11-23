<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('google_ads_id', 50)->nullable()->after('meta_capi_token'); // Ej. AW-123456789
            $table->string('google_ads_conversion_label', 100)->nullable()->after('google_ads_id'); // Evento lead web
            $table->string('google_ads_offline_action', 150)->nullable()->after('google_ads_conversion_label'); // Nombre exacto de la acción offline en Google Ads
            $table->string('google_ads_feed_token', 64)->nullable()->after('google_ads_offline_action'); // Token para lectura automática de Data Manager
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->string('gclid', 255)->nullable()->after('fbp');
            $table->string('gbraid', 255)->nullable()->after('gclid');
            $table->string('wbraid', 255)->nullable()->after('gbraid');
            $table->timestamp('google_ads_synced_at')->nullable()->after('wbraid');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'google_ads_id',
                'google_ads_conversion_label',
                'google_ads_offline_action',
                'google_ads_feed_token',
            ]);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['gclid', 'gbraid', 'wbraid', 'google_ads_synced_at']);
        });
    }
};