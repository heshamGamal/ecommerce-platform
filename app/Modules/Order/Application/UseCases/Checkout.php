<?php

namespace App\Modules\Order\Application\UseCases;

use App\Modules\Auth\Domain\Contracts\AuthenticationServiceInterface;
use App\Modules\Auth\Domain\Exceptions\AuthenticationException;
use App\Modules\Order\Domain\ValueObjects\CheckoutData;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
final class Checkout
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authentication,
        private readonly OrderRepositoryInterface $orders,
    ) {}

    public function execute(CheckoutData $data): object
    {
        $user = $this->authentication->user();
        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $this->orders->checkout(
            $user->id,
            $data->addressId,
            $data->currency,
            $data->idempotencyKey,
        );
    }
}
