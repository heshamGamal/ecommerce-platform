<?php

namespace App\Modules\Shipping;

use App\Modules\Shipping\Domain\Contracts\ShippingMethodRepositoryInterface;
use App\Modules\Shipping\Domain\Contracts\ShippingRateCalculatorInterface;
use App\Modules\Shipping\Domain\Contracts\ShipmentRepositoryInterface;
use App\Modules\Shipping\Infrastructure\Persistence\DatabaseShippingRateCalculator;
use App\Modules\Shipping\Infrastructure\Persistence\EloquentShippingMethodRepository;
use App\Modules\Shipping\Infrastructure\Persistence\EloquentShipmentRepository;
use Illuminate\Support\ServiceProvider;

final class ShippingServiceProvider extends ServiceProvider
{
    public array $bindings = [
        ShippingMethodRepositoryInterface::class => EloquentShippingMethodRepository::class,
        ShippingRateCalculatorInterface::class => DatabaseShippingRateCalculator::class,
        ShipmentRepositoryInterface::class => EloquentShipmentRepository::class,
    ];
}
