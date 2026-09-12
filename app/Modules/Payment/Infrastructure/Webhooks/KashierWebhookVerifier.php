<?php

namespace App\Modules\Payment\Infrastructure\Webhooks;

final class KashierWebhookVerifier
{
    public function verify(array $payload): bool
    {
        $provided = (string) ($payload['signature'] ?? '');
        $key = (string) config('services.kashier.payment_api_key');
        if ($provided === '' || $key === '') {
            return false;
        }

        $fields = ['paymentStatus', 'cardDataToken', 'maskedCard', 'merchantOrderId', 'orderId', 'cardBrand', 'orderReference', 'transactionId', 'amount', 'currency'];
        $body = implode('&', array_map(static fn (string $field): string => $field . '=' . ($payload[$field] ?? 'null'), $fields));
        $calculated = hash_hmac('sha256', $body, $key);

        return hash_equals(strtolower($calculated), strtolower($provided));
    }
}
