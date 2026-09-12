<?php
namespace App\Modules\Inventory\Application\UseCases;
use App\Models\User;use App\Modules\Inventory\Application\DTOs\StockAdjustmentData;use App\Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
final class AdjustInventory{public function __construct(private readonly InventoryRepositoryInterface $inventory){}public function execute(StockAdjustmentData $data,User $actor){return $this->inventory->adjust($data,$actor->id);}}
