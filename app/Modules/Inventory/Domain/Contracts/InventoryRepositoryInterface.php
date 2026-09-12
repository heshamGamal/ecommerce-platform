<?php
namespace App\Modules\Inventory\Domain\Contracts;
use App\Models\InventoryItem;use App\Modules\Inventory\Application\DTOs\StockAdjustmentData;
interface InventoryRepositoryInterface{public function list():iterable;public function find(int $id):InventoryItem;public function adjust(StockAdjustmentData $data,?int $actorId):InventoryItem;public function reserve(int $productId,?int $variantId,int $quantity):InventoryItem;public function release(int $productId,?int $variantId,int $quantity):InventoryItem;}
