<?php

namespace App\Modules\Settings\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('settings.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'group' => ['required', 'string', 'max:100'],
            'key' => ['required', 'string', 'max:191'],
            'value' => [
                'present',
                Rule::when($this->input('type') === 'string', ['nullable', 'string']),
                Rule::when($this->input('type') === 'boolean', ['boolean']),
                Rule::when($this->input('type') === 'integer', ['integer']),
                Rule::when($this->input('type') === 'float', ['numeric']),
                Rule::when($this->input('type') === 'json', ['array']),
            ],
            'type' => ['required', Rule::in(['string', 'boolean', 'integer', 'float', 'json'])],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('key') !== null) {
            $this->merge(['key' => $this->route('key')]);
        }
    }
}
