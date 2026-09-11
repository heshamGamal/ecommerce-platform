<?php
namespace App\Modules\Catalog\Application\UseCases\Attributes;
use App\Models\Attribute;use App\Modules\Catalog\Application\DTOs\AttributeData;use App\Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;use App\Modules\Catalog\Domain\Exceptions\BusinessRuleException;
final class CreateAttribute {public function __construct(private readonly AttributeRepositoryInterface $attributes){} public function execute(AttributeData $d):Attribute{if($this->attributes->nameExists($d->name))throw new BusinessRuleException('The attribute name is already in use.');return $this->attributes->create($d);}}
