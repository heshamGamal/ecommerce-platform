<?php

namespace App\Modules\Shipping\Infrastructure\Persistence;

use App\Models\CustomerOrder;
use App\Models\ShippingMethod;
use App\Modules\Shipping\Domain\Contracts\ShippingRateCalculatorInterface;

final class DatabaseShippingRateCalculator implements ShippingRateCalculatorInterface
{
    public function calculate(CustomerOrder $order, ShippingMethod $method): int
    {
        return $method->base_fee;
    }
}
