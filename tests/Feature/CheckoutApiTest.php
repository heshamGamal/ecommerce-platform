<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\CustomerCart;
use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\Shipment;
use App\Models\ShippingMethod;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CheckoutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_checkout_using_server_side_price_and_snapshot(): void
    {
        $this->seed(RbacSeeder::class);
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Checkout Product', 'slug' => 'checkout-product',
            'type' => 'simple', 'status' => 'active', 'price' => 1250,
        ]);
        $address = CustomerAddress::query()->create([
            'user_id' => $user->id, 'recipient_name' => 'Customer', 'phone' => '01000000000',
            'address_line1' => 'Street 1', 'city' => 'Cairo', 'country' => 'EG', 'is_default' => true,
        ]);
        $cart = CustomerCart::query()->create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);
        InventoryItem::query()->create(['product_id' => $product->id, 'on_hand' => 5, 'reserved' => 0]);

        $response = $this->actingAs($user)->postJson('/api/customer/checkout', [
            'address_id' => $address->id,
            'currency' => 'EGP',
            'idempotency_key' => 'checkout-test-1',
        ]);

        $response->assertCreated()->assertJsonPath('data.total_amount', 2500);
        $this->assertDatabaseHas('customer_order_items', [
            'name' => 'Checkout Product', 'quantity' => 2,
            'unit_price' => 1250, 'total_amount' => 2500,
        ]);
        $this->assertDatabaseHas('inventory_items', ['product_id' => $product->id, 'reserved' => 2]);
        $this->assertDatabaseCount('customer_cart_items', 0);
    }

    public function test_checkout_is_idempotent_for_the_same_key(): void
    {
        $this->seed(RbacSeeder::class);
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Idempotent Product', 'slug' => 'idempotent-product',
            'type' => 'simple', 'status' => 'active', 'price' => 100,
        ]);
        $address = CustomerAddress::query()->create([
            'user_id' => $user->id, 'recipient_name' => 'Customer', 'phone' => '01000000000',
            'address_line1' => 'Street 1', 'city' => 'Cairo', 'country' => 'EG', 'is_default' => true,
        ]);
        $cart = CustomerCart::query()->create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 1]);
        InventoryItem::query()->create(['product_id' => $product->id, 'on_hand' => 1, 'reserved' => 0]);

        $payload = ['address_id' => $address->id, 'idempotency_key' => 'same-key'];
        $first = $this->actingAs($user)->postJson('/api/customer/checkout', $payload);
        $second = $this->actingAs($user)->postJson('/api/customer/checkout', $payload);

        $first->assertCreated();
        $second->assertCreated()->assertJsonPath('data.id', $first->json('data.id'));
        $this->assertDatabaseCount('customer_orders', 1);
    }

    public function test_checkout_orchestrates_shipping_and_payment_after_reserving_inventory(): void
    {
        $this->seed(RbacSeeder::class);
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Full Flow Product', 'slug' => 'full-flow-product',
            'type' => 'simple', 'status' => 'active', 'price' => 1250,
        ]);
        $address = CustomerAddress::query()->create([
            'user_id' => $user->id, 'recipient_name' => 'Customer', 'phone' => '01000000000',
            'address_line1' => 'Street 1', 'city' => 'Cairo', 'country' => 'EG', 'is_default' => true,
        ]);
        $cart = CustomerCart::query()->create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);
        InventoryItem::query()->create(['product_id' => $product->id, 'on_hand' => 5, 'reserved' => 0]);
        $method = ShippingMethod::query()->create([
            'code' => 'standard', 'name' => 'Standard', 'base_fee' => 150,
            'currency' => 'EGP', 'is_active' => true,
        ]);

        $response = $this->actingAs($user)->postJson('/api/customer/checkout', [
            'address_id' => $address->id,
            'currency' => 'EGP',
            'idempotency_key' => 'full-flow-order',
            'shipping_method_id' => $method->id,
            'shipping_idempotency_key' => 'full-flow-shipment',
            'payment_method' => 'cash_on_delivery',
            'payment_idempotency_key' => 'full-flow-payment',
        ]);

        $response->assertCreated()->assertJsonPath('data.total_amount', 2650);
        $orderId = $response->json('data.id');
        $this->assertDatabaseHas('inventory_items', ['product_id' => $product->id, 'reserved' => 2]);
        $this->assertDatabaseHas('shipments', ['order_id' => $orderId, 'fee' => 150, 'status' => 'pending']);
        $this->assertDatabaseHas('payments', ['order_id' => $orderId, 'amount' => 2650, 'status' => 'pending']);
        $this->assertDatabaseCount('customer_orders', 1);
        $this->assertDatabaseCount('shipments', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_checkout_rolls_back_order_and_inventory_when_shipping_creation_fails(): void
    {
        $this->seed(RbacSeeder::class);
        $user = User::factory()->create();
        $product = Product::query()->create([
            'name' => 'Rollback Product', 'slug' => 'rollback-product',
            'type' => 'simple', 'status' => 'active', 'price' => 500,
        ]);
        $address = CustomerAddress::query()->create([
            'user_id' => $user->id, 'recipient_name' => 'Customer', 'phone' => '01000000000',
            'address_line1' => 'Street 1', 'city' => 'Cairo', 'country' => 'EG', 'is_default' => true,
        ]);
        $cart = CustomerCart::query()->create(['user_id' => $user->id]);
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);
        InventoryItem::query()->create(['product_id' => $product->id, 'on_hand' => 5, 'reserved' => 0]);

        $this->actingAs($user)->postJson('/api/customer/checkout', [
            'address_id' => $address->id,
            'currency' => 'EGP',
            'idempotency_key' => 'rollback-order',
            'shipping_method_id' => 999999,
            'shipping_idempotency_key' => 'rollback-shipment',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('customer_orders', 0);
        $this->assertDatabaseCount('shipments', 0);
        $this->assertDatabaseHas('inventory_items', ['product_id' => $product->id, 'on_hand' => 5, 'reserved' => 0]);
        $this->assertDatabaseCount('customer_cart_items', 1);
    }
}
