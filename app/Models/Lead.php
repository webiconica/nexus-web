<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'origen',
        'name',
        'phone_normalized',
        'email',
        'stage',
        'fbclid',
        'fbc',
        'fbp',
        'datos_formulario',
        'last_interaction_at',
        'gclid',
        'gbraid', 
        'wbraid',
        'google_ads_synced_at',
    ];

    protected $casts = [
        'datos_formulario'    => 'array',
        'last_interaction_at' => 'datetime',
    ];

    public const STAGES = [
        'nuevo'               => 'Nuevo Prospecto',
        'en_seguimiento'      => 'En Seguimiento',
        'cotizado'            => 'Cotizado',
        'anticipo_confirmado' => 'Anticipo Confirmado',
        'cerrado'             => 'Cerrado / Ganado',
        'descartado'          => 'Descartado',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(LeadMessage::class)->orderBy('created_at', 'asc');
    }

    public function stageLogs(): HasMany
    {
        return $this->hasMany(LeadStageLog::class)->orderBy('created_at', 'desc');
    }

    /**
     * Normaliza teléfonos a formato estándar (ej. 52 + 10 dígitos para México)
     * para unificar Formulario Web + Chatbot Web + WhatsApp Cloud API sin duplicados.
     */
    public static function normalizePhone(?string $rawPhone): ?string
    {
        if (empty($rawPhone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $rawPhone);
        if (strlen($digits) < 10) {
            return $digits ?: null;
        }

        // Quitar prefijo '521' de WhatsApp antiguo en México y dejar '52' + 10 dígitos
        if (strlen($digits) === 13 && str_starts_with($digits, '521')) {
            return '52' . substr($digits, 3);
        }

        // Si son 10 dígitos locales de México, anteponer '52'
        if (strlen($digits) === 10) {
            return '52' . $digits;
        }

        return $digits;
    }
}