<?php
namespace App\Modules\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use LogicException;

final class CatalogActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = match ($this->route()?->getName()) {
            'products.index', 'products.show',
            'products.variants.index', 'products.variants.show',
            'attributes.index', 'attributes.show',
            'attributes.values.index', 'attributes.values.show' => 'products.view',
            'products.destroy', 'products.variants.destroy',
            'attributes.destroy', 'attributes.values.destroy' => 'products.delete',
            'brands.index', 'brands.show' => 'brands.view',
            'brands.destroy' => 'brands.delete',
            'categories.index', 'categories.show' => 'categories.view',
            'categories.destroy' => 'categories.delete',
            default => throw new LogicException('Catalog route is missing an authorization mapping.'),
        };

        return $this->user()?->hasPermission($permission) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
