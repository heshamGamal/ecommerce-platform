<?php

namespace App\Modules\Catalog\Domain\Contracts;

use App\Models\Category;
use App\Modules\Catalog\Domain\ValueObjects\CategoryData;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function all(): Collection;
    public function findOrFail(int $id): Category;
    public function slugExists(string $slug, ?int $exceptId = null): bool;
    public function create(CategoryData $data, string $slug): Category;
    public function update(Category $category, CategoryData $data, string $slug): Category;
    public function wouldCreateCycle(Category $category, int $parentId): bool;
    public function hasProductsOrChildren(Category $category): bool;
    public function delete(Category $category): void;
}
