<?php

namespace App\Modules\Shipping\Infrastructure\Persistence;

use App\Models\ShippingMethod;
use App\Modules\Shipping\Domain\Contracts\ShippingMethodRepositoryInterface;
use App\Modules\Shipping\Domain\Exceptions\ShippingRateNotFoundException;

final class EloquentShippingMethodRepository implements ShippingMethodRepositoryInterface
{
    public function listActive(): iterable { return ShippingMethod::query()->where('is_active', true)->orderBy('name')->get(); }
    public function listAll(): iterable { return ShippingMethod::query()->latest()->get(); }
    public function find(int $id): ShippingMethod
    {
        $method = ShippingMethod::query()->find($id);
        if ($method === null) throw new ShippingRateNotFoundException('Shipping method not found.');
        return $method;
    }
    public function create(array $attributes): ShippingMethod { return ShippingMethod::query()->create($attributes); }
    public function update(ShippingMethod $method, array $attributes): ShippingMethod { $method->update($attributes); return $method->fresh(); }
    public function delete(ShippingMethod $method): void { $method->delete(); }
}
