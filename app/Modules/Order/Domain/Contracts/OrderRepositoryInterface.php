<?php

namespace App\Modules\Order\Domain\Contracts;

use App\Models\CustomerOrder;

interface OrderRepositoryInterface
{
    public function checkout(int $userId, int $addressId, string $currency, ?string $idempotencyKey): CustomerOrder;

    public function listForUser(int $userId): iterable;

    public function listAll(): iterable;

    public function findForUser(int $userId, int $orderId): CustomerOrder;

    public function find(int $orderId): CustomerOrder;

    public function updateStatus(int $orderId, string $status): CustomerOrder;

    public function cancelForUser(int $userId, int $orderId): CustomerOrder;
}
