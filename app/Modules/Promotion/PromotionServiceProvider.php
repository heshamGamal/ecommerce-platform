<?php

namespace App\Modules\Promotion;

use App\Modules\Promotion\Domain\Contracts\CouponServiceInterface;
use App\Modules\Promotion\Infrastructure\Persistence\EloquentCouponService;
use Illuminate\Support\ServiceProvider;

final class PromotionServiceProvider extends ServiceProvider
{
    public array $bindings = [CouponServiceInterface::class => EloquentCouponService::class];
}
