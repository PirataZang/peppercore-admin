<?php

namespace App\Service;

use App\Enums\PaymentMethod;
use App\Models\Client;
use App\Models\IntegrationSetting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Order;

class MercadoPagoService
{
    /**
     * Load stored credentials and point the SDK at them. Throws if the integration
     * isn't active or has no Access Token saved.
     */
    private function configure(): array
    {
        $setting = IntegrationSetting::where('provider', 'mercado_pago')->first();

        if (!$setting || !$setting->is_active || empty($setting->credentials['access_token'])) {
            throw ValidationException::withMessages([
                'integration' => ['A integração com o Mercado Pago não está ativa ou configurada.'],
            ]);
        }

        MercadoPagoConfig::setAccessToken($setting->credentials['access_token']);

        return $setting->credentials;
    }

    /**
     * The Public Key the frontend needs to initialize the Mercado Pago SDK JS
     * (required for tokenizing card data before it ever reaches our backend).
     */
    public function publicKey(): string
    {
        $credentials = $this->configure();

        if (empty($credentials['public_key'])) {
            throw ValidationException::withMessages([
                'integration' => ['Nenhuma Public Key configurada para o Mercado Pago.'],
            ]);
        }

        return $credentials['public_key'];
    }

    /**
     * Create a Pix, Boleto or Card order for a transaction — an actual invoice with a real
     * due date, whose QR code / digitable line / card result comes straight back into our system.
     *
     * @param array{token?: string, payment_method_id?: string, issuer_id?: string, installments?: int} $card
     *   Required only when $method is CreditCard, as returned by the Card Payment Brick.
     */
    public function createCharge(Transaction $transaction, PaymentMethod $method, array $card = []): Transaction
    {
        $this->configure();

        $client = $transaction->client ?? $transaction->project?->client;

        if (!$client || !$client->document) {
            throw ValidationException::withMessages([
                'client' => ['Selecione um cliente com CPF/CNPJ cadastrado para emitir a cobrança.'],
            ]);
        }

        $amount = number_format((float) $transaction->amount, 2, '.', '');

        $request = [
            'type' => 'online',
            'processing_mode' => 'automatic',
            'total_amount' => $amount,
            'external_reference' => "transaction_{$transaction->id}",
            'payer' => $this->buildPayer($client, $method),
            'transactions' => [
                'payments' => [$this->buildPaymentMethod($method, $amount, $card)],
            ],
        ];

        $requestOptions = new RequestOptions();
        $requestOptions->setCustomHeaders(["X-Idempotency-Key: transaction-{$transaction->id}-{$method->value}-" . now()->timestamp]);

        try {
            $order = (new OrderClient())->create($request, $requestOptions);
        } catch (MPApiException $e) {
            $content = $e->getApiResponse()->getContent();

            Log::error('Mercado Pago order creation failed', [
                'transaction_id' => $transaction->id,
                'method' => $method->value,
                'status' => $e->getStatusCode(),
                'content' => $content,
            ]);

            throw ValidationException::withMessages([
                'mercado_pago' => ['Falha ao criar cobrança no Mercado Pago: ' . $this->extractApiErrorMessage($content)],
            ]);
        }

        $this->applyOrderToTransaction($transaction, $order, $method->value);

        return $transaction->fresh();
    }

    /**
     * Fetch the latest state of an order and sync it onto its Transaction (used by the webhook).
     */
    public function syncOrder(string $orderId): ?Transaction
    {
        $this->configure();

        $order = (new OrderClient())->get($orderId);
        $externalReference = $order->external_reference ?? null;

        if (!$externalReference || !str_starts_with($externalReference, 'transaction_')) {
            return null;
        }

        $transactionId = (int) str_replace('transaction_', '', $externalReference);
        $transaction = Transaction::find($transactionId);

        if (!$transaction) {
            return null;
        }

        $method = $transaction->gateway_payload['method'] ?? PaymentMethod::Pix->value;
        $this->applyOrderToTransaction($transaction, $order, $method);

        return $transaction->fresh();
    }

    private function buildPayer(Client $client, PaymentMethod $method): array
    {
        $document = preg_replace('/\D/', '', (string) $client->document);

        $payer = [
            'email' => $client->email ?: 'sem-email@peppercore.local',
            'first_name' => $client->name,
            'identification' => [
                'type' => strlen($document) > 11 ? 'CNPJ' : 'CPF',
                'number' => $document,
            ],
        ];

        if ($method === PaymentMethod::Boleto) {
            foreach (['zip_code', 'street_name', 'street_number', 'neighborhood', 'city', 'state'] as $field) {
                if (empty($client->$field)) {
                    throw ValidationException::withMessages([
                        'client' => ["O cliente precisa ter endereço completo cadastrado para emitir boleto ({$field} ausente)."],
                    ]);
                }
            }

            $payer['address'] = [
                'zip_code' => preg_replace('/\D/', '', $client->zip_code),
                'street_name' => $client->street_name,
                'street_number' => $client->street_number,
                'neighborhood' => $client->neighborhood,
                'city' => $client->city,
                'federal_unit' => $client->state,
            ];
        }

        return $payer;
    }

    private function buildPaymentMethod(PaymentMethod $method, string $amount, array $card): array
    {
        return match ($method) {
            PaymentMethod::Pix => [
                'amount' => $amount,
                'payment_method' => ['id' => 'pix', 'type' => 'bank_transfer'],
                'expiration_time' => 'P1D',
            ],
            PaymentMethod::Boleto => [
                'amount' => $amount,
                'payment_method' => ['id' => 'boleto', 'type' => 'ticket'],
                'expiration_time' => 'P3D',
            ],
            PaymentMethod::CreditCard => $this->buildCardPaymentMethod($amount, $card),
        };
    }

    private function buildCardPaymentMethod(string $amount, array $card): array
    {
        foreach (['token', 'payment_method_id'] as $field) {
            if (empty($card[$field])) {
                throw ValidationException::withMessages([
                    'card' => ["Dados do cartão incompletos ({$field} ausente). Preencha o formulário de cartão novamente."],
                ]);
            }
        }

        $paymentMethod = [
            'id' => $card['payment_method_id'],
            'type' => 'credit_card',
            'token' => $card['token'],
            'installments' => (int) ($card['installments'] ?? 1),
        ];

        if (!empty($card['issuer_id'])) {
            $paymentMethod['issuer_id'] = (string) $card['issuer_id'];
        }

        return [
            'amount' => $amount,
            'payment_method' => $paymentMethod,
        ];
    }

    /**
     * Mercado Pago error responses vary in shape ({message}, {cause:[{description}]}, etc).
     * Pull the most specific human-readable message available instead of a generic fallback.
     */
    private function extractApiErrorMessage(array $content): string
    {
        if (!empty($content['cause'][0]['description'])) {
            return $content['cause'][0]['description'];
        }

        if (!empty($content['message'])) {
            return $content['message'];
        }

        if (!empty($content['error'])) {
            return $content['error'];
        }

        return 'Erro desconhecido. Verifique os logs para detalhes.';
    }

    private function applyOrderToTransaction(Transaction $transaction, Order $order, string $method): void
    {
        $payment = $order->transactions->payments[0] ?? null;
        $paymentMethod = $payment->payment_method ?? null;

        $transaction->gateway = 'mercado_pago';
        $transaction->gateway_id = $order->id;
        $transaction->gateway_status = $order->status;
        $transaction->payment_method = $method;
        $transaction->gateway_payload = [
            'method' => $method,
            'status' => $order->status,
            'status_detail' => $order->status_detail,
            'ticket_url' => $paymentMethod->ticket_url ?? null,
            'qr_code' => $paymentMethod->qr_code ?? null,
            'qr_code_base64' => $paymentMethod->qr_code_base64 ?? null,
            'digitable_line' => $paymentMethod->digitable_line ?? null,
        ];

        if ($order->status === 'processed') {
            $transaction->status = 'paid';
            $transaction->paid_at = $transaction->paid_at ?? now()->toDateString();
        } elseif ($order->status === 'canceled') {
            $transaction->status = 'failed';
        }

        $transaction->save();
    }
}
