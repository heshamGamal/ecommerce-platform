<?php

namespace App\Modules\Shipping\Application\UseCases;

use App\Models\ShippingMethod;
use App\Modules\Shipping\Domain\Contracts\ShippingMethodRepositoryInterface;

final class UpdateShippingMethod
{
    public function __construct(private readonly ShippingMethodRepositoryInterface $methods) {}
    public function execute(int $id, array $data): ShippingMethod { return $this->methods->update($this->methods->find($id), $data); }
}
