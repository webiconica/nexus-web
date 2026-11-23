<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->tenant_id)) {
                $model->tenant_id = TenantContext::id();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            // Permitir que un Super Admin consulte sin filtro cuando use ->withoutGlobalScope('tenant')
            $tenantId = TenantContext::id();
            $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}