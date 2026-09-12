<?php

namespace App\Modules\Shipping\Application\DTOs;

final readonly class CreateShipmentData
{
    public function __construct(
        public int $shippingMethodId,
        public string $idempotencyKey,
    ) {}
}
