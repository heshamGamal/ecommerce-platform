<?php
namespace App\Modules\Catalog\Presentation\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreBrandRequest extends FormRequest
{
 public function authorize(): bool { return $this->user()?->hasPermission('brands.create') ?? false; }
 public function rules(): array { return ['name'=>['required','string','max:255'],'slug'=>['nullable','string','max:191'],'status'=>['required','string','max:50']]; }
}
