<?php

namespace App\Modules\Shipping\Domain\Contracts;

use App\Models\ShippingMethod;

interface ShippingMethodRepositoryInterface
{
    public function listActive(): iterable;
    public function listAll(): iterable;
    public function find(int $id): ShippingMethod;
    public function create(array $attributes): ShippingMethod;
    public function update(ShippingMethod $method, array $attributes): ShippingMethod;
    public function delete(ShippingMethod $method): void;
}
