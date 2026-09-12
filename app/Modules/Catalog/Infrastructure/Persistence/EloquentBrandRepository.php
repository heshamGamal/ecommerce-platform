<?php
namespace App\Modules\Catalog\Infrastructure\Persistence;
use App\Models\Brand;
use App\Modules\Catalog\Domain\ValueObjects\BrandData;
use App\Modules\Catalog\Domain\Contracts\BrandRepositoryInterface;
use App\Modules\Catalog\Domain\Exceptions\BrandNotFoundException;
use App\Modules\Catalog\Domain\Exceptions\DuplicateSlugException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
class EloquentBrandRepository implements BrandRepositoryInterface
{
    public function all(): Collection { return Brand::query()->withCount('products')->orderBy('name')->get(); }
    public function findOrFail(int $id): Brand { $model = Brand::query()->withCount('products')->find($id); if ($model === null) throw new BrandNotFoundException($id); return $model; }
    public function slugExists(string $slug, ?int $exceptId = null): bool { return Brand::query()->where('slug', $slug)->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))->exists(); }
    public function create(BrandData $data, string $slug): Brand
    {
        try { return Brand::query()->create(array_merge($data->toArray(), ['slug' => $slug])); }
        catch (QueryException $e) { if ($this->slugExists($slug)) throw new DuplicateSlugException($slug); throw $e; }
    }
    public function update(Brand $brand, BrandData $data, string $slug): Brand
    {
        try { $brand->update(array_merge($data->toArray(), ['slug' => $slug])); return $brand->refresh()->loadCount('products'); }
        catch (QueryException $e) { if ($this->slugExists($slug, $brand->id)) throw new DuplicateSlugException($slug); throw $e; }
    }
    public function hasProducts(Brand $brand): bool { return $brand->products()->exists(); }
    public function delete(Brand $brand): void { $brand->delete(); }
}
