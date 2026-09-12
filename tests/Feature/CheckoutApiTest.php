<?php

namespace Tests\Feature;

use App\Models\CustomerAddress;
use App\Models\CustomerCart;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\Role;
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
}
