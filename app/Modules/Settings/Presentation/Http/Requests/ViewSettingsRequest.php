<?php
namespace App\Modules\Settings\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ViewSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('settings.view') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
