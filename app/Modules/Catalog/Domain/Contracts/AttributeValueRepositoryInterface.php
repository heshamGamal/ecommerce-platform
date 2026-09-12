<?php

namespace App\Modules\Catalog\Domain\Contracts;

use App\Models\AttributeValue;
use App\Modules\Catalog\Domain\ValueObjects\AttributeValueData;
use Illuminate\Support\Collection;

interface AttributeValueRepositoryInterface
{
    public function forAttribute(int $attributeId): Collection;
    public function findOrFail(int $id, ?int $attributeId = null): AttributeValue;
    public function findMany(array $ids): Collection;
    public function valueExists(int $attributeId, string $value, ?int $exceptId = null): bool;
    public function create(AttributeValueData $data): AttributeValue;
    public function update(AttributeValue $value, AttributeValueData $data): AttributeValue;
    public function isUsed(AttributeValue $value): bool;
    public function delete(AttributeValue $value): void;
}
