<?php

namespace App\Modules\Order;

use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Contracts\TransactionManagerInterface;
use App\Modules\Order\Infrastructure\Persistence\DatabaseTransactionManager;
use App\Modules\Order\Infrastructure\Persistence\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

final class OrderServiceProvider extends ServiceProvider
{
    public array $bindings = [
        OrderRepositoryInterface::class => EloquentOrderRepository::class,
        TransactionManagerInterface::class => DatabaseTransactionManager::class,
    ];
}
