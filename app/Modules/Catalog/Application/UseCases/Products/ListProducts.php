<?php
namespace App\Modules\Catalog\Application\UseCases\Products;

use App\Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Collection;

final class ListProducts
{
    public function __construct(private readonly ProductRepositoryInterface $products) {}

    public function execute(): Collection
    {
        return $this->products->all();
    }
}
