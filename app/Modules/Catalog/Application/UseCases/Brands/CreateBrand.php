<?php
namespace App\Modules\Catalog\Application\UseCases\Brands;
use App\Models\Brand;use App\Modules\Catalog\Application\DTOs\BrandData;use App\Modules\Catalog\Domain\Contracts\BrandRepositoryInterface;use App\Modules\Catalog\Domain\Exceptions\DuplicateSlugException;use Illuminate\Support\Str;
final class CreateBrand {public function __construct(private readonly BrandRepositoryInterface $brands){} public function execute(BrandData $d):Brand{$s=Str::slug($d->slug?:$d->name);if($this->brands->slugExists($s))throw new DuplicateSlugException($s);return $this->brands->create($d,$s);}}
