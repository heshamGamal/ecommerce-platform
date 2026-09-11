<?php
namespace App\Modules\Catalog\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreProductRequest extends FormRequest
{
 public function authorize(): bool { return $this->user()?->hasPermission('products.create') ?? false; }
 public function rules(): array { return ['name'=>['required','string','max:255'],'slug'=>['nullable','string','max:191'],'description'=>['nullable','string'],'type'=>['required',Rule::in(['simple','variable'])],'status'=>['required','string','max:50'],'brand_id'=>['nullable','integer','exists:brands,id'],'category_id'=>['nullable','integer','exists:categories,id']]; }
}
