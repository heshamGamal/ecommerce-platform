<?php

namespace App\Modules\Customer\Domain\Contracts;

use App\Models\CustomerCart;

interface CartRepositoryInterface
{
    public function get(int $userId): CustomerCart;
    public function addItem(int $userId, int $productId, ?int $variantId, int $quantity): CustomerCart;
    public function updateItem(int $userId, int $productId, ?int $variantId, int $quantity): CustomerCart;
    public function removeItem(int $userId, int $productId, ?int $variantId): CustomerCart;
    public function clear(int $userId): CustomerCart;
}
