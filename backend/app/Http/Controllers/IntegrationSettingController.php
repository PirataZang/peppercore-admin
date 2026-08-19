<?php

namespace App\Http\Controllers;

use App\Enums\GatewayProvider;
use App\Models\IntegrationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationSettingController extends Controller
{
    /**
     * List all known providers with their current configuration state.
     * Credentials are never returned, only whether they're set.
     */
    public function index(): JsonResponse
    {
        $providers = array_map(fn (GatewayProvider $p) => $p->value, GatewayProvider::cases());
        $settings = IntegrationSetting::whereIn('provider', $providers)->get()->keyBy('provider');

        $result = collect(GatewayProvider::cases())->map(function (GatewayProvider $provider) use ($settings) {
            $setting = $settings->get($provider->value);

            return [
                'provider' => $provider->value,
                'label' => $provider->label(),
                'is_active' => $setting?->is_active ?? false,
                'has_credentials' => !empty($setting?->credentials),
                'updated_at' => $setting?->updated_at,
            ];
        });

        return response()->json($result->values());
    }

    /**
     * Create or update credentials/active state for a provider.
     */
    public function update(Request $request, string $provider): JsonResponse
    {
        if (!GatewayProvider::tryFrom($provider)) {
            return response()->json(['message' => 'Integração desconhecida.'], 404);
        }

        $validated = $request->validate([
            'is_active' => 'required|boolean',
            'credentials' => 'sometimes|array',
        ] + $this->credentialRules($provider));

        $setting = IntegrationSetting::firstOrNew(['provider' => $provider]);

        $setting->is_active = $validated['is_active'];

        if ($request->has('credentials')) {
            $setting->credentials = $validated['credentials'];
        }

        $setting->save();

        return response()->json([
            'provider' => $setting->provider,
            'is_active' => $setting->is_active,
            'has_credentials' => !empty($setting->credentials),
            'updated_at' => $setting->updated_at,
        ]);
    }

    private function credentialRules(string $provider): array
    {
        if ($provider !== 'mercado_pago') {
            return [];
        }

        return [
            'credentials.access_token' => 'required_with:credentials|string',
            'credentials.public_key' => 'sometimes|nullable|string',
            'credentials.webhook_secret' => 'sometimes|nullable|string',
        ];
    }
}
