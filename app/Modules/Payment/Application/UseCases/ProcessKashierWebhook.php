<?php

namespace App\Modules\Payment\Application\UseCases;

use App\Models\PaymentWebhookEvent;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Contracts\TransactionManagerInterface;
use App\Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use App\Modules\Payment\Domain\Contracts\PaymentOperationRepositoryInterface;
use App\Modules\Payment\Domain\Exceptions\PaymentException;
use App\Modules\Payment\Domain\Exceptions\PaymentAmountMismatchException;
use App\Modules\Payment\Infrastructure\Webhooks\KashierWebhookVerifier;
use Illuminate\Database\QueryException;

final class ProcessKashierWebhook
{
    public function __construct(
        private readonly PaymentRepositoryInterface $payments,
        private readonly PaymentOperationRepositoryInterface $operations,
        private readonly OrderRepositoryInterface $orders,
        private readonly TransactionManagerInterface $transactions,
        private readonly KashierWebhookVerifier $verifier,
    ) {
    }

    public function execute(array $payload): ?object
    {
        if (! $this->verifier->verify($payload)) {
            throw new PaymentException('Invalid Kashier webhook signature.');
        }

        $eventId = (string) ($payload['transactionId'] ?? $payload['orderId'] ?? '');
        $merchantOrderId = (string) ($payload['merchantOrderId'] ?? $payload['orderReference'] ?? '');
        if ($eventId === '' || $merchantOrderId === '') {
            throw new PaymentException('Kashier webhook is missing its payment reference.');
        }

        $payment = $this->payments->findByIdempotencyKey($merchantOrderId);
        $payment ??= $this->payments->findByProviderReference((string) ($payload['orderId'] ?? ''));
        if ($payment === null) {
            throw new PaymentException('Kashier webhook does not match a local payment.');
        }
        if (abs((float) ($payload['amount'] ?? -1) - (float) $payment->amount) > 0.001) {
            throw new PaymentAmountMismatchException('Kashier webhook amount does not match the local payment.');
        }

        try {
            PaymentWebhookEvent::query()->create([
                'provider' => 'kashier',
                'event_id' => $eventId,
                'event_type' => 'payment',
                'status' => 'received',
                'payment_reference' => (string) ($payload['orderId'] ?? $eventId),
                'payload' => $payload,
            ]);
        } catch (QueryException $exception) {
            $existingEvent = PaymentWebhookEvent::query()->where('provider', 'kashier')->where('event_id', $eventId)->first();
            if ($existingEvent?->status === 'processed') {
                return null;
            }
            if ($existingEvent === null) {
                throw $exception;
            }
        }

        $paid = strtoupper((string) ($payload['paymentStatus'] ?? '')) === 'SUCCESS';
        $status = $paid ? 'confirmed' : 'failed';
        if (in_array($payment->status, ['confirmed', 'paid', 'refunded'], true) && $status !== 'confirmed') {
            PaymentWebhookEvent::query()->where('provider', 'kashier')->where('event_id', $eventId)->update(['status' => 'processed', 'processed_at' => now()]);
            return $payment;
        }
        $metadata = array_merge((array) $payment->metadata, [
            'provider' => 'kashier',
            'transaction_id' => $payload['transactionId'] ?? null,
            'kashier_order_id' => $payload['orderId'] ?? null,
            'webhook' => $payload,
        ]);

        return $this->transactions->run(function () use ($payment, $status, $metadata, $eventId, $payload): object {
            $locked = $this->payments->findForUpdate((int) $payment->id);
            $updated = $this->payments->updateStatus($locked, $status, ['metadata' => $metadata]);
            $this->operations->complete((int) $updated->id, 'create', $status === 'confirmed' ? 'confirmed' : 'failed', (string) ($payload['transactionId'] ?? $payload['orderId'] ?? ''), $payload);
            if ($status === 'confirmed' && $updated->order->status === 'pending') {
                $this->orders->updateStatus((int) $updated->order_id, 'confirmed');
            }
            PaymentWebhookEvent::query()->where('provider', 'kashier')->where('event_id', $eventId)->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);
            return $updated;
        });
    }
}
