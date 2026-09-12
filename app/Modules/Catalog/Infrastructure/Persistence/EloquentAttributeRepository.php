<?php
namespace App\Modules\Catalog\Infrastructure\Persistence;
use App\Models\Attribute;
use App\Modules\Catalog\Domain\ValueObjects\AttributeData;
use App\Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;
use App\Modules\Catalog\Domain\Exceptions\AttributeNotFoundException;
use Illuminate\Support\Collection;
class EloquentAttributeRepository implements AttributeRepositoryInterface
{
    public function all(): Collection { return Attribute::query()->with('values')->orderBy('name')->get(); }
    public function findOrFail(int $id): Attribute
    {
        $model = Attribute::query()->with('values')->find($id);
        if ($model === null) throw new AttributeNotFoundException($id);
        return $model;
    }
    public function nameExists(string $name, ?int $exceptId = null): bool
    {
        return Attribute::query()->where('name', $name)->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))->exists();
    }
    public function create(AttributeData $data): Attribute { return Attribute::query()->create($data->toArray())->load('values'); }
    public function update(Attribute $attribute, AttributeData $data): Attribute { $attribute->update($data->toArray()); return $attribute->refresh()->load('values'); }
    public function isUsed(Attribute $attribute): bool { return $attribute->values()->whereHas('variants')->exists(); }
    public function delete(Attribute $attribute): void { $attribute->delete(); }
}
