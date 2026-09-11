<?php
namespace App\Modules\Catalog\Application\UseCases\Products;
use App\Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
final class DeleteProductVariant
{
 public function __construct(private readonly ProductRepositoryInterface $products) {}
 public function execute(int $productId,int $variantId): void {$p=$this->products->findOrFail($productId);$this->products->deleteVariant($this->products->findVariantOrFail($p,$variantId));}
}
