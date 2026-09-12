<?php

namespace App\Modules\Order\Application\UseCases;

use App\Models\CustomerOrder;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;

final class GetOrder
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function execute(int $orderId): CustomerOrder
    {
        return $this->orders->find($orderId);
    }
}
