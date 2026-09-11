<?php
namespace App\Modules\Catalog\Application\UseCases\Categories;

use App\Models\Category;
use App\Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;

final class GetCategory
{
    public function __construct(private readonly CategoryRepositoryInterface $categories) {}

    public function execute(int $id): Category
    {
        return $this->categories->findOrFail($id);
    }
}
