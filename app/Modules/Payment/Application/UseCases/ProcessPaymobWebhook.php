<?php

namespace App\Modules\Payment\Application\UseCases;

use App\Models\PaymentWebhookEvent;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Contracts\TransactionManagerInterface;
use App\Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use App\Modules\Payment\Domain\Contracts\PaymentOperationRepositoryInterface;
use App\Modules\Payment\Domain\Exceptions\PaymentException;
use App\Modules\Payment\Infrastructure\Webhooks\PaymobWebhookVerifier;
use Illuminate\Database\QueryException;

final class ProcessPaymobWebhook
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly PaymentOperationRepositoryInterface $operations,
        private readonly OrderRepositoryInterface $orders,
        private readonly TransactionManagerInterface $transactions,
        private readonly PaymobWebhookVerifier $verifier,
    ) {
    }

    public function execute(array $payload, string $hmac): ?object
    {
        if (! $this->verifier->verify($payload, $hmac)) {
            throw new PaymentException('Invalid Paymob webhook signature.');
        }

        $object = (array) ($payload['obj'] ?? $payload);
        $eventId = (string) ($object['id'] ?? $payload['id'] ?? '');
        $reference = (string) ($object['id'] ?? '');
        if ($eventId === '' || $reference === '') {
            throw new PaymentException('Paymob webhook is missing its event reference.');
        }

        $merchantReference = (string) data_get($object, 'order.merchant_order_id', '');
        $payment = $merchantReference !== ''
            ? $this->payments->findByIdempotencyKey($merchantReference)
            : null;
        $payment ??= $this->payments->findByProviderReference($reference);
        if ($payment === null) {
            throw new PaymentException('Paymob webhook does not match a local payment.');
        }

        try {
            PaymentWebhookEvent::query()->create([
                'provider' => 'paymob',
                'event_id' => $eventId,
                'event_type' => (string) ($payload['type'] ?? 'TRANSACTION'),
                'status' => 'received',
                'payment_reference' => $reference,
                'payload' => $payload,
            ]);
        } catch (QueryException $exception) {
            $existingEvent = PaymentWebhookEvent::query()->where('provider', 'paymob')->where('event_id', $eventId)->first();
            if ($existingEvent?->status === 'processed') {
                return null;
            }
            if ($existingEvent === null) {
                throw $exception;
            }
        }

        $pending = (bool) ($object['pending'] ?? false);
        $status = $pending ? 'pending' : ((bool) ($object['success'] ?? false) ? 'confirmed' : 'failed');
        $metadata = array_merge((array) $payment->metadata, [
            'provider' => 'paymob',
            'transaction_id' => $reference,
            'webhook' => $payload,
        ]);

        $result = $this->transactions->run(function () use ($payment, $status, $metadata, $eventId, $reference, $payload): object {
            $locked = $this->payments->findForUpdate((int) $payment->id);
            $updated = $this->payments->updateStatus($locked, $status, ['metadata' => $metadata]);
            $this->operations->complete((int) $updated->id, 'create', $status === 'confirmed' ? 'confirmed' : ($status === 'failed' ? 'failed' : 'processing'), $reference, $payload);
            if ($status === 'confirmed' && $updated->order->status === 'pending') {
                $this->orders->updateStatus((int) $updated->order_id, 'confirmed');
            }
            PaymentWebhookEvent::query()->where('provider', 'paymob')->where('event_id', $eventId)->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);
            return $updated;
        });

        return $result;
    }
}
