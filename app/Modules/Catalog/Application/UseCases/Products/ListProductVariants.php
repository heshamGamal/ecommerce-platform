<?php
namespace App\Modules\Catalog\Application\UseCases\Products;

use App\Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Collection;

final class ListProductVariants
{
    public function __construct(private readonly ProductRepositoryInterface $products) {}

    public function execute(int $productId): Collection
    {
        $product = $this->products->findOrFail($productId);

        return $this->products->variants($product);
    }
}
