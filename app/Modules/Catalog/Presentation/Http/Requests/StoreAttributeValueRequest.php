<?php
namespace App\Modules\Catalog\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreAttributeValueRequest extends FormRequest
{
 public function authorize(): bool { return $this->user()?->hasPermission('products.create') ?? false; }
 public function rules(): array { return ['value'=>['required','string','max:255']]; }
}
