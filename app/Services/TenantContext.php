<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantContext
{
    protected static ?Tenant $currentTenant = null;

    public static function set(?Tenant $tenant): void
    {
        self::$currentTenant = $tenant;
    }

    public static function current(): ?Tenant
    {
        if (self::$currentTenant !== null) {
            return self::$currentTenant;
        }

        if (Auth::check() && !empty(Auth::user()->tenant_id)) {
            self::$currentTenant = Tenant::find(Auth::user()->tenant_id);
            return self::$currentTenant;
        }

        // Fallback seguro para instalaciones dedicadas de 1 solo cliente
        self::$currentTenant = Tenant::find(1);
        return self::$currentTenant;
    }

    public static function id(): int
    {
        return self::current()?->id ?? 1;
    }

    /**
     * Resuelve el tenant desde una petición pública (API Key, token de widget o dominio)
     */
    public static function resolveFromRequest(Request $request): ?Tenant
    {
        $apiKey = $request->header('X-API-Key')
            ?? $request->bearerToken()
            ?? $request->query('key')
            ?? $request->input('api_key');

        if (!empty($apiKey)) {
            $tenant = Tenant::where('api_key', trim($apiKey))
                ->where('is_active', true)
                ->first();

            if ($tenant) {
                self::set($tenant);
                return $tenant;
            }
        }

        // Resolución por subdominio o dominio personalizado
        $host = $request->getHost();
        $tenantByDomain = Tenant::where('custom_domain', $host)
            ->orWhere('slug', explode('.', $host)[0])
            ->where('is_active', true)
            ->first();

        if ($tenantByDomain) {
            self::set($tenantByDomain);
            return $tenantByDomain;
        }

        return self::current();
    }
}