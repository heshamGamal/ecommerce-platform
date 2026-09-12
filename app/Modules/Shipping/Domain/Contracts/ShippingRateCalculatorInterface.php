<?php

namespace App\Modules\Shipping\Domain\Contracts;

use App\Models\CustomerOrder;
use App\Models\ShippingMethod;

interface ShippingRateCalculatorInterface
{
    public function calculate(CustomerOrder $order, ShippingMethod $method): int;
}
