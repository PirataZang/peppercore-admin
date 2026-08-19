<?php

namespace App\Http\Controllers;

use App\Models\IntegrationSetting;
use App\Service\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(protected MercadoPagoService $mercadoPagoService)
    {
    }

    /**
     * Receives order notifications from Mercado Pago (Pix/Boleto/Card invoices created via the
     * Orders API) and syncs the related Transaction. Always responds 200 so Mercado Pago doesn't
     * keep retrying, even when we choose to ignore the event.
     */
    public function mercadoPago(Request $request): JsonResponse
    {
        $resourceId = $request->input('data.id');
        $type = $request->input('type', $request->input('topic'));

        // Events other than "order" (e.g. mp-connect, merchant_order) are expected noise —
        // not logged as a problem, just acknowledged so Mercado Pago stops retrying.
        if (!$resourceId || $type !== 'order') {
            return response()->json(['message' => 'ignored'], 200);
        }

        if (!$this->hasValidSignature($request)) {
            Log::warning('Mercado Pago webhook rejected: invalid signature', ['payload' => $request->all()]);
            return response()->json(['message' => 'invalid signature'], 200);
        }

        try {
            $this->mercadoPagoService->syncOrder($resourceId);
        } catch (\Throwable $e) {
            Log::error('Mercado Pago webhook processing failed', ['order_id' => $resourceId, 'error' => $e->getMessage()]);
        }

        return response()->json(['message' => 'ok']);
    }

    private function hasValidSignature(Request $request): bool
    {
        $setting = IntegrationSetting::where('provider', 'mercado_pago')->first();
        $secret = $setting->credentials['webhook_secret'] ?? null;

        // No secret configured yet: accept the notification so the integration keeps working
        // while the user hasn't set one up, instead of silently dropping every event.
        if (!$secret) {
            return true;
        }

        $ts = null;
        $hash = null;
        foreach (explode(',', $request->header('x-signature', '')) as $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, null);
            if (trim((string) $key) === 'ts') $ts = trim((string) $value);
            if (trim((string) $key) === 'v1') $hash = trim((string) $value);
        }

        if (!$ts || !$hash) {
            return false;
        }

        $dataId = strtolower((string) $request->query('data.id', $request->input('data.id', '')));
        $requestId = $request->header('x-request-id', '');

        $parts = [];
        if ($dataId !== '') $parts[] = "id:{$dataId}";
        if ($requestId !== '') $parts[] = "request-id:{$requestId}";
        $parts[] = "ts:{$ts}";

        $manifest = implode(';', $parts) . ';';
        $expectedHash = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expectedHash, $hash);
    }
}
