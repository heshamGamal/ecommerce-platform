<?php
namespace App\Modules\Catalog\Application\UseCases\Products;
use App\Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
final class DeleteProduct
{
 public function __construct(private readonly ProductRepositoryInterface $products) {}
 public function execute(int $id): void {$this->products->delete($this->products->findOrFail($id));}
}
