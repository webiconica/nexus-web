<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'custom_domain',
        'api_key',
        'allowed_origins',
        'plan',
        'monthly_chat_limit',
        'crm_only_mode',
        'is_active',
        'outbound_webhook_url',
        'outbound_webhook_secret',
        'meta_pixel_id',
        'meta_capi_token',
        'meta_capi_events',
        'google_ads_id',
        'google_ads_conversion_label',
        'google_ads_feed_token',
        'google_ads_events',
        'whatsapp_phone_number_id',
        'whatsapp_business_account_id',
        'whatsapp_access_token',
        'whatsapp_verify_token',
    ];

    protected $casts = [
        'allowed_origins'       => 'array',
        'meta_capi_events'      => 'array',
        'google_ads_events'     => 'array',
        'crm_only_mode'         => 'boolean',
        'is_active'             => 'boolean',
        'meta_capi_token'       => 'encrypted',
        'whatsapp_access_token' => 'encrypted',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (empty($tenant->api_key)) {
                $tenant->api_key = 'wk_live_' . Str::random(40);
            }
            if (empty($tenant->whatsapp_verify_token)) {
                $tenant->whatsapp_verify_token = 'wk_wa_' . Str::random(24);
            }
            if (empty($tenant->google_ads_feed_token)) {
                $tenant->google_ads_feed_token = 'wk_gads_' . Str::random(32);
            }
        });
    }
}