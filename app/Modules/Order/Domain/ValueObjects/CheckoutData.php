<?php

namespace App\Modules\Order\Domain\ValueObjects;

final readonly class CheckoutData
{
    public function __construct(
        public int $addressId,
        public string $currency = 'EGP',
        public ?string $idempotencyKey = null,
        public ?int $shippingMethodId = null,
        public ?string $shippingIdempotencyKey = null,
        public ?string $paymentMethod = null,
        public ?string $paymentIdempotencyKey = null,
    ) {}
}
