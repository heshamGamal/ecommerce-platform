<?php

namespace App\Modules\Order\Domain\ValueObjects;

final readonly class CheckoutData
{
    public function __construct(
        public int $addressId,
        public string $currency = 'EGP',
        public ?string $idempotencyKey = null,
    ) {}
}
