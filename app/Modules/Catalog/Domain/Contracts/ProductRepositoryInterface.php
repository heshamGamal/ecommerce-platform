<?php

namespace App\Modules\Catalog\Domain\Contracts;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Modules\Catalog\Application\DTOs\ProductData;
use App\Modules\Catalog\Application\DTOs\ProductVariantData;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function all(): Collection;
    public function findOrFail(int $id): Product;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    public function create(ProductData $data, string $slug): Product;
    public function update(Product $product, ProductData $data, string $slug): Product;
    public function delete(Product $product): void;
    public function hasVariants(Product $product): bool;
    public function variants(Product $product): Collection;
    public function findVariantOrFail(Product $product, int $variantId): ProductVariant;
    public function skuExists(string $sku, ?int $exceptId = null): bool;
    public function combinationExists(Product $product, string $hash, ?int $exceptId = null): bool;
    public function createVariant(Product $product, ProductVariantData $data, string $hash, Collection $values): ProductVariant;
    public function updateVariant(ProductVariant $variant, ProductVariantData $data, string $hash, Collection $values): ProductVariant;
    public function deleteVariant(ProductVariant $variant): void;
}
