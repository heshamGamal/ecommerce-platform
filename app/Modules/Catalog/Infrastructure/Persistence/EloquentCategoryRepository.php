<?php
namespace App\Modules\Catalog\Infrastructure\Persistence;
use App\Models\Category;
use App\Modules\Catalog\Application\DTOs\CategoryData;
use App\Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use App\Modules\Catalog\Domain\Exceptions\CategoryNotFoundException;
use App\Modules\Catalog\Domain\Exceptions\DuplicateSlugException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function all(): Collection { return Category::query()->with('parent')->withCount(['children', 'products'])->orderBy('name')->get(); }
    public function findOrFail(int $id): Category { $model = Category::query()->with(['parent', 'children'])->withCount('products')->find($id); if ($model === null) throw new CategoryNotFoundException($id); return $model; }
    public function slugExists(string $slug, ?int $exceptId = null): bool { return Category::query()->where('slug', $slug)->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))->exists(); }
    public function create(CategoryData $data, string $slug): Category
    {
        try { return Category::query()->create(array_merge($data->toArray(), ['slug' => $slug]))->load('parent'); }
        catch (QueryException $e) { if ($this->slugExists($slug)) throw new DuplicateSlugException($slug); throw $e; }
    }
    public function update(Category $category, CategoryData $data, string $slug): Category
    {
        try { $category->update(array_merge($data->toArray(), ['slug' => $slug])); return $category->refresh()->load(['parent', 'children'])->loadCount('products'); }
        catch (QueryException $e) { if ($this->slugExists($slug, $category->id)) throw new DuplicateSlugException($slug); throw $e; }
    }
    public function wouldCreateCycle(Category $category, int $parentId): bool
    {
        if ($category->id === $parentId) return true;
        $candidate = Category::query()->find($parentId);
        while ($candidate !== null) {
            if ($candidate->id === $category->id) return true;
            $candidate = $candidate->parent_id === null ? null : Category::query()->find($candidate->parent_id);
        }
        return false;
    }
    public function hasProductsOrChildren(Category $category): bool { return $category->products()->exists() || $category->children()->exists(); }
    public function delete(Category $category): void { $category->delete(); }
}
