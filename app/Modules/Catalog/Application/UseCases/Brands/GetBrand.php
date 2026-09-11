<?php
namespace App\Modules\Catalog\Application\UseCases\Brands;

use App\Models\Brand;
use App\Modules\Catalog\Domain\Contracts\BrandRepositoryInterface;

final class GetBrand
{
    public function __construct(private readonly BrandRepositoryInterface $brands) {}

    public function execute(int $id): Brand
    {
        return $this->brands->findOrFail($id);
    }
}
