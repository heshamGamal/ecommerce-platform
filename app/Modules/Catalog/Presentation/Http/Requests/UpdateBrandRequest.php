<?php
namespace App\Modules\Catalog\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateBrandRequest extends FormRequest
{
 public function authorize(): bool { return $this->user()?->hasPermission('brands.update') ?? false; }
 public function rules(): array { return ['name'=>['required','string','max:255'],'slug'=>['nullable','string','max:191'],'status'=>['required','string','max:50']]; }
}
