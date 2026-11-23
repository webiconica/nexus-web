<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeadStageLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GoogleAdsFeedController extends Controller
{
    public function offlineConversions(Request $request): StreamedResponse
    {
        $token = (string) $request->query('token', '');
        abort_if(strlen($token) < 20, 403, 'Token inválido');

        $tenant = Tenant::where('google_ads_feed_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        $eventMap = array_filter($tenant->google_ads_events ?? []);

        $logs = empty($eventMap)
            ? collect()
            : LeadStageLog::withoutGlobalScope('tenant')
                ->with('lead')
                ->where('tenant_id', $tenant->id)
                ->whereIn('to_stage', array_keys($eventMap))
                ->where('created_at', '>=', now()->subDays(90))
                ->orderBy('created_at')
                ->get();

        return response()->streamDownload(function () use ($logs, $eventMap) {
            $out = fopen('php://output', 'w');

            fwrite($out, "Parameters:TimeZone=-0600\n");

            fputcsv($out, [
                'Google Click ID',
                'GBRAID',
                'WBRAID',
                'Conversion Name',
                'Conversion Time',
                'Conversion Value',
                'Conversion Currency',
                'Email',
                'Phone Number',
                'Order ID',
            ]);

            foreach ($logs as $log) {
                $lead = $log->lead;
                if (!$lead) {
                    continue;
                }

                $hasIdentifier = !empty($lead->gclid)
                    || !empty($lead->gbraid)
                    || !empty($lead->wbraid)
                    || !empty($lead->email)
                    || !empty($lead->phone_normalized);

                if (!$hasIdentifier) {
                    continue;
                }

                $conversionName = $eventMap[$log->to_stage];

                $emailHash = !empty($lead->email)
                    ? hash('sha256', strtolower(trim($lead->email)))
                    : '';

                $phoneE164 = !empty($lead->phone_normalized)
                    ? '+' . ltrim($lead->phone_normalized, '+')
                    : '';
                $phoneHash = $phoneE164 !== ''
                    ? hash('sha256', $phoneE164)
                    : '';

                $value = in_array($log->to_stage, ['anticipo_confirmado', 'cerrado'], true)
                    ? (float) (data_get($lead->datos_formulario, 'monto_anticipo')
                        ?? data_get($lead->datos_formulario, 'valor_cotizacion')
                        ?? 1)
                    : 0;

                fputcsv($out, [
                    $lead->gclid ?? '',
                    $lead->gbraid ?? '',
                    $lead->wbraid ?? '',
                    $conversionName,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $value,
                    'MXN',
                    $emailHash,
                    $phoneHash,
                    "LEAD-{$lead->id}-{$log->to_stage}",
                ]);
            }

            fclose($out);
        }, 'google-ads-conversions.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}