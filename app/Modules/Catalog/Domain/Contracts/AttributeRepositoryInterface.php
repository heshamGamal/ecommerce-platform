<?php

namespace App\Modules\Catalog\Domain\Contracts;

use App\Models\Attribute;
use App\Modules\Catalog\Application\DTOs\AttributeData;
use Illuminate\Support\Collection;

interface AttributeRepositoryInterface
{
    public function all(): Collection;
    public function findOrFail(int $id): Attribute;
    public function nameExists(string $name, ?int $exceptId = null): bool;
    public function create(AttributeData $data): Attribute;
    public function update(Attribute $attribute, AttributeData $data): Attribute;
    public function isUsed(Attribute $attribute): bool;
    public function delete(Attribute $attribute): void;
}
