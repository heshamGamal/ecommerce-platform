<?php

namespace App\Modules\Payment\Domain\Contracts;

interface PaymentOperationRepositoryInterface
{
    public function start(int $paymentId, string $operation, string $idempotencyKey): void;

    public function successfulResponse(int $paymentId, string $operation): ?array;

    public function complete(int $paymentId, string $operation, string $status, ?string $providerReference, array $response): void;

    public function fail(int $paymentId, string $operation, string $error, bool $retryable = true): void;
}
