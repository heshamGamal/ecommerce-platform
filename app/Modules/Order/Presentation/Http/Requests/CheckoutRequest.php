<?php

namespace App\Modules\Order\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'min:1'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'idempotency_key' => ['nullable', 'string', 'max:100'],
        ];
    }
}
