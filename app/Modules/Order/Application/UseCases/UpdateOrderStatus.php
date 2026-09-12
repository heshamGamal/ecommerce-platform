<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\CustomerOrder;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;

final class UpdateOrderStatus
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function execute(int $orderId, string $status): CustomerOrder
    {
        return $this->orders->updateStatus($orderId, $status);
    }
}
