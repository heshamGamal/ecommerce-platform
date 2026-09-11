<?php
namespace App\Modules\Catalog\Application\UseCases\Attributes;
use App\Models\AttributeValue;use App\Modules\Catalog\Application\DTOs\AttributeValueData;use App\Modules\Catalog\Domain\Contracts\AttributeValueRepositoryInterface;use App\Modules\Catalog\Domain\Exceptions\BusinessRuleException;
final class UpdateAttributeValue {public function __construct(private readonly AttributeValueRepositoryInterface $values){} public function execute(int $attributeId,int $id,AttributeValueData $d):AttributeValue{$v=$this->values->findOrFail($id,$attributeId);if($this->values->valueExists($attributeId,$d->value,$id))throw new BusinessRuleException('The attribute value already exists.');return $this->values->update($v,$d);}}
