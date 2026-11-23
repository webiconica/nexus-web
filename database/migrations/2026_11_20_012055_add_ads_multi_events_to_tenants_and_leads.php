<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Tenant;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'google_ads_id')) {
                $table->string('google_ads_id', 50)->nullable()->after('meta_capi_token');
            }
            if (!Schema::hasColumn('tenants', 'google_ads_conversion_label')) {
                $table->string('google_ads_conversion_label', 100)->nullable()->after('google_ads_id');
            }
            if (!Schema::hasColumn('tenants', 'google_ads_feed_token')) {
                $table->string('google_ads_feed_token', 64)->nullable()->after('google_ads_conversion_label');
            }
            if (!Schema::hasColumn('tenants', 'google_ads_events')) {
                $table->json('google_ads_events')->nullable()->after('google_ads_feed_token');
            }
            if (!Schema::hasColumn('tenants', 'meta_capi_events')) {
                $table->json('meta_capi_events')->nullable()->after('meta_capi_token');
            }
        });

        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'gclid')) {
                $table->string('gclid', 255)->nullable()->after('fbp');
            }
            if (!Schema::hasColumn('leads', 'gbraid')) {
                $table->string('gbraid', 255)->nullable()->after('gclid');
            }
            if (!Schema::hasColumn('leads', 'wbraid')) {
                $table->string('wbraid', 255)->nullable()->after('gbraid');
            }
        });

        // Generar token inicial de Feed Google Ads para los tenants existentes
        Tenant::whereNull('google_ads_feed_token')->each(function (Tenant $tenant) {$tenant->update(['google_ads_feed_token' => 'wk_gads_' . Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {$cols = array_filter([
                'google_ads_id',
                'google_ads_conversion_label',
                'google_ads_feed_token',
                'google_ads_events',
                'meta_capi_events',
            ], fn ($c) => Schema::hasColumn('tenants',$c));

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('leads', function (Blueprint $table) {$cols = array_filter(['gclid', 'gbraid', 'wbraid'], fn ($c) => Schema::hasColumn('leads',$c));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};