<?php

namespace App\Modules\Catalog\Domain\Contracts;

use App\Models\Brand;
use App\Modules\Catalog\Application\DTOs\BrandData;
use Illuminate\Support\Collection;

interface BrandRepositoryInterface
{
    public function all(): Collection;
    public function findOrFail(int $id): Brand;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    public function create(BrandData $data, string $slug): Brand;
    public function update(Brand $brand, BrandData $data, string $slug): Brand;
    public function hasProducts(Brand $brand): bool;
    public function delete(Brand $brand): void;
}
