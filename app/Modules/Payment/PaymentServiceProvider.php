<?php

namespace App\Modules\Payment;

use App\Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use App\Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use App\Modules\Payment\Infrastructure\Gateways\PaymentGatewayRouter;
use App\Modules\Payment\Infrastructure\Persistence\EloquentPaymentRepository;
use Illuminate\Support\ServiceProvider;

final class PaymentServiceProvider extends ServiceProvider
{
    public array $bindings = [
        PaymentRepositoryInterface::class => EloquentPaymentRepository::class,
        PaymentGatewayInterface::class => PaymentGatewayRouter::class,
    ];
}
