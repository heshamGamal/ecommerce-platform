<?php
namespace App\Modules\Catalog\Application\UseCases\Attributes;

use App\Models\AttributeValue;
use App\Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;
use App\Modules\Catalog\Domain\Contracts\AttributeValueRepositoryInterface;

final class GetAttributeValue
{
    public function __construct(
        private readonly AttributeRepositoryInterface $attributes,
        private readonly AttributeValueRepositoryInterface $values,
    ) {}

    public function execute(int $attributeId, int $valueId): AttributeValue
    {
        $this->attributes->findOrFail($attributeId);

        return $this->values->findOrFail($valueId, $attributeId);
    }
}
