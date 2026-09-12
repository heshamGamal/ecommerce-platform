<?php

namespace App\Modules\Shipping\Domain\Contracts;

use App\Models\CustomerOrder;

interface ShippingRateCalculatorInterface
{
    public function calculate(CustomerOrder $order): int;
}
