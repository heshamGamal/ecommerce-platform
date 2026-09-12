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
            'shipping_method_id' => ['nullable', 'integer', 'min:1', 'required_with:shipping_idempotency_key'],
            'shipping_idempotency_key' => ['nullable', 'string', 'max:100', 'required_with:shipping_method_id'],
            'payment_method' => ['nullable', 'string', 'in:cash_on_delivery'],
            'payment_idempotency_key' => ['nullable', 'string', 'max:100', 'required_with:payment_method'],
        ];
    }
}
