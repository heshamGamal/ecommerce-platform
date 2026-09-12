<?php

namespace App\Modules\Order\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Domain\ValueObjects\CheckoutData;
use App\Modules\Order\Application\UseCases\Checkout;
use App\Modules\Order\Presentation\Http\Requests\CheckoutRequest;
use Illuminate\Http\JsonResponse;

final class CheckoutController extends Controller
{
    public function __invoke(CheckoutRequest $request, Checkout $checkout): JsonResponse
    {
        $data = $request->validated();

        $order = $checkout->execute(new CheckoutData(
            addressId: (int) $data['address_id'],
            currency: strtoupper($data['currency'] ?? 'EGP'),
            idempotencyKey: $data['idempotency_key'] ?? null,
            shippingMethodId: isset($data['shipping_method_id']) ? (int) $data['shipping_method_id'] : null,
            shippingIdempotencyKey: $data['shipping_idempotency_key'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            paymentIdempotencyKey: $data['payment_idempotency_key'] ?? null,
        ));

        return response()->json(['data' => $order], 201);
    }
}
