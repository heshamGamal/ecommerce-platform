<?php

namespace App\Modules\Order\Infrastructure\Persistence;

use App\Models\CustomerOrder;
use App\Models\InventoryItem;
use App\Models\User;
use App\Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use App\Modules\Order\Domain\Exceptions\CheckoutException;
use App\Modules\Order\Domain\Exceptions\InvalidOrderStatusTransitionException;
use App\Modules\Order\Domain\Exceptions\OrderActionNotAllowedException;
use App\Modules\Order\Domain\Exceptions\OrderNotFoundException;
use Illuminate\Support\Facades\DB;

final class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function listForUser(int $userId): iterable
    {
        return CustomerOrder::query()->with('items.product')->where('user_id', $userId)->latest()->get();
    }

    public function listAll(): iterable
    {
        return CustomerOrder::query()->with(['user', 'items.product'])->latest()->get();
    }

    public function findForUser(int $userId, int $orderId): CustomerOrder
    {
        $order = CustomerOrder::query()->with('items.product')->where('user_id', $userId)->find($orderId);
        if ($order === null) {
            throw new OrderNotFoundException('Order not found.');
        }
        return $order;
    }

    public function find(int $orderId): CustomerOrder
    {
        $order = CustomerOrder::query()->with(['user', 'items.product'])->find($orderId);
        if ($order === null) {
            throw new OrderNotFoundException('Order not found.');
        }
        return $order;
    }

    public function updateStatus(int $orderId, string $status): CustomerOrder
    {
        return DB::transaction(function () use ($orderId, $status): CustomerOrder {
            $order = CustomerOrder::query()->lockForUpdate()->find($orderId);
            if ($order === null) {
                throw new OrderNotFoundException('Order not found.');
            }
            $allowed = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['processing', 'cancelled'],
                'processing' => ['shipped', 'cancelled'],
                'shipped' => ['delivered'],
                'delivered' => ['refunded'],
                'cancelled' => [],
                'refunded' => [],
            ];
            if (!in_array($status, $allowed[$order->status] ?? [], true)) {
                throw InvalidOrderStatusTransitionException::from($order->status, $status);
            }
            $order->update(['status' => $status]);
            return $order->fresh(['user', 'items.product']);
        });
    }

    public function cancelForUser(int $userId, int $orderId): CustomerOrder
    {
        return DB::transaction(function () use ($userId, $orderId): CustomerOrder {
            $order = CustomerOrder::query()->where('user_id', $userId)->lockForUpdate()->find($orderId);
            if ($order === null) {
                throw new OrderNotFoundException('Order not found.');
            }
            if (!in_array($order->status, ['pending', 'confirmed', 'processing'], true)) {
                throw new OrderActionNotAllowedException('This order can no longer be cancelled.');
            }
            foreach ($order->items as $item) {
                $inventory = InventoryItem::query()->where('product_id', $item->product_id)->where('variant_id', $item->variant_id)->lockForUpdate()->first();
                if ($inventory !== null && $inventory->reserved >= $item->quantity) {
                    $inventory->decrement('reserved', $item->quantity);
                }
            }
            $order->update(['status' => 'cancelled']);
            return $order->fresh(['items.product', 'items.variant']);
        });
    }

    public function checkout(int $userId, int $addressId, string $currency, ?string $idempotencyKey): CustomerOrder
    {
        return DB::transaction(function () use ($userId, $addressId, $currency, $idempotencyKey): CustomerOrder {
            if ($idempotencyKey !== null) {
                $existing = CustomerOrder::query()->where('idempotency_key', $idempotencyKey)->first();
                if ($existing !== null) return $existing->load('items');
            }
            $user = User::query()->with(['cart.items.product', 'cart.items.variant', 'addresses'])->findOrFail($userId);
            $cart = $user->cart;
            $items = $cart?->items ?? collect();
            if ($items->isEmpty()) throw CheckoutException::emptyCart();
            $address = $user->addresses()->findOrFail($addressId);
            $subtotal = 0;
            $snapshots = [];
            foreach ($items as $cartItem) {
                $product = $cartItem->product;
                if ($product === null || $product->status !== 'active') throw CheckoutException::unavailableProduct($product?->name ?? 'unknown');
                $variant = $cartItem->variant;
                if ($product->type === 'variable' && ($variant === null || $variant->status !== 'active')) throw CheckoutException::unavailableProduct($product->name);
                $unitPrice = $variant?->price ?? $product->price;
                if ($unitPrice === null || $unitPrice < 0) throw CheckoutException::missingPrice($product->name);
                $inventory = InventoryItem::query()->where('product_id', $product->id)->where('variant_id', $variant?->id)->lockForUpdate()->first();
                if ($inventory === null || ($inventory->on_hand - $inventory->reserved) < $cartItem->quantity) throw CheckoutException::unavailableProduct($product->name);
                $lineTotal = $unitPrice * $cartItem->quantity;
                $subtotal += $lineTotal;
                $snapshots[] = ['product_id' => $product->id, 'variant_id' => $variant?->id, 'name' => $product->name, 'sku' => $variant?->sku, 'quantity' => $cartItem->quantity, 'unit_price' => $unitPrice, 'discount_amount' => 0, 'tax_amount' => 0, 'total_amount' => $lineTotal];
                $inventory->increment('reserved', $cartItem->quantity);
            }
            $order = CustomerOrder::query()->create(['user_id' => $userId, 'status' => 'pending', 'total_amount' => $subtotal, 'subtotal_amount' => $subtotal, 'discount_amount' => 0, 'tax_amount' => 0, 'shipping_amount' => 0, 'currency' => $currency, 'shipping_address' => ['recipient_name' => $address->recipient_name, 'phone' => $address->phone, 'address_line1' => $address->address_line1, 'address_line2' => $address->address_line2, 'city' => $address->city, 'state' => $address->state, 'postal_code' => $address->postal_code, 'country' => $address->country], 'idempotency_key' => $idempotencyKey]);
            $order->items()->createMany($snapshots);
            $cart->items()->delete();
            return $order->load('items');
        });
    }
}
