<?php
namespace App\Modules\Catalog\Infrastructure\Persistence;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Modules\Catalog\Domain\ValueObjects\ProductData;
use App\Modules\Catalog\Domain\ValueObjects\ProductVariantData;
use App\Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Catalog\Domain\Exceptions\DuplicateSkuException;
use App\Modules\Catalog\Domain\Exceptions\DuplicateSlugException;
use App\Modules\Catalog\Domain\Exceptions\InvalidVariantCombinationException;
use App\Modules\Catalog\Domain\Exceptions\ProductNotFoundException;
use App\Modules\Catalog\Domain\Exceptions\VariantNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
class EloquentProductRepository implements ProductRepositoryInterface
{
    public function all(): Collection { return Product::query()->with(['brand', 'category'])->orderByDesc('id')->get(); }
    public function findOrFail(int $id): Product
    {
        $model = Product::query()->with(['brand', 'category', 'variants.attributeValues.attribute'])->find($id);
        if ($model === null) throw new ProductNotFoundException($id);
        return $model;
    }
    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        return Product::query()->where('slug', $slug)->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))->exists();
    }
    public function create(ProductData $data, string $slug): Product
    {
        try { return Product::query()->create(array_merge($data->toArray(), ['slug' => $slug]))->load(['brand', 'category']); }
        catch (QueryException $e) { if ($this->slugExists($slug)) throw new DuplicateSlugException($slug); throw $e; }
    }
    public function update(Product $product, ProductData $data, string $slug): Product
    {
        try { $product->update(array_merge($data->toArray(), ['slug' => $slug])); return $product->refresh()->load(['brand', 'category', 'variants.attributeValues.attribute']); }
        catch (QueryException $e) { if ($this->slugExists($slug, $product->id)) throw new DuplicateSlugException($slug); throw $e; }
    }
    public function delete(Product $product): void { $product->delete(); }
    public function hasVariants(Product $product): bool { return $product->variants()->exists(); }
    public function variants(Product $product): Collection { return $product->variants()->with('attributeValues.attribute')->orderBy('id')->get(); }
    public function findVariantOrFail(Product $product, int $variantId): ProductVariant
    {
        $model = $product->variants()->with('attributeValues.attribute')->find($variantId);
        if ($model === null) throw new VariantNotFoundException($variantId);
        return $model;
    }
    public function skuExists(string $sku, ?int $exceptId = null): bool
    {
        return ProductVariant::query()->where('sku', $sku)->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))->exists();
    }
    public function combinationExists(Product $product, string $hash, ?int $exceptId = null): bool
    {
        return $product->variants()->where('combination_hash', $hash)->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))->exists();
    }
    public function createVariant(Product $product, ProductVariantData $data, string $hash, Collection $values): ProductVariant
    {
        try {
            return DB::transaction(function () use ($product, $data, $hash, $values) {
                $variant = $product->variants()->create(array_merge($data->persistenceData(), ['combination_hash' => $hash]));
                $variant->attributeValues()->attach($this->pivotData($values));
                return $variant->load('attributeValues.attribute');
            });
        } catch (QueryException $e) { $this->throwVariantConflict($product, $data->sku, $hash); throw $e; }
    }
    public function updateVariant(ProductVariant $variant, ProductVariantData $data, string $hash, Collection $values): ProductVariant
    {
        try {
            return DB::transaction(function () use ($variant, $data, $hash, $values) {
                $variant->update(array_merge($data->persistenceData(), ['combination_hash' => $hash]));
                $variant->attributeValues()->sync($this->pivotData($values));
                return $variant->refresh()->load('attributeValues.attribute');
            });
        } catch (QueryException $e) { $this->throwVariantConflict($variant->product, $data->sku, $hash, $variant->id); throw $e; }
    }
    public function deleteVariant(ProductVariant $variant): void { $variant->delete(); }
    private function pivotData(Collection $values): array
    {
        return $values->mapWithKeys(fn ($value) => [$value->id => ['attribute_id' => $value->attribute_id]])->all();
    }
    private function throwVariantConflict(Product $product, string $sku, string $hash, ?int $exceptId = null): void
    {
        if ($this->skuExists($sku, $exceptId)) throw new DuplicateSkuException($sku);
        if ($this->combinationExists($product, $hash, $exceptId)) throw InvalidVariantCombinationException::duplicate();
    }
}
