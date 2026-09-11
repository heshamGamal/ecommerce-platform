<?php
namespace App\Modules\Catalog\Application\UseCases\Products;

use App\Models\Product;
use App\Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;

final class GetProduct
{
    public function __construct(private readonly ProductRepositoryInterface $products) {}

    public function execute(int $id): Product
    {
        return $this->products->findOrFail($id);
    }
}
