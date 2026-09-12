<?php

namespace Tests\Feature;

use App\Models\CustomerOrder;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class OrderCrudApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_and_show_only_owned_orders(): void
    {
        $this->seed(RbacSeeder::class);
        $customer = $this->userWithRole('customer');
        $other = User::factory()->create();
        $owned = CustomerOrder::query()->create(['user_id' => $customer->id, 'status' => 'pending', 'total_amount' => 100, 'currency' => 'EGP']);
        $foreign = CustomerOrder::query()->create(['user_id' => $other->id, 'status' => 'pending', 'total_amount' => 200, 'currency' => 'EGP']);

        $this->actingAs($customer)->getJson('/api/customer/orders')->assertOk()->assertJsonCount(1, 'data');
        $this->actingAs($customer)->getJson("/api/customer/orders/{$owned->id}")->assertOk()->assertJsonPath('data.id', $owned->id);
        $this->actingAs($customer)->getJson("/api/customer/orders/{$foreign->id}")->assertNotFound();
    }

    public function test_order_manager_can_update_status_and_invalid_transition_is_conflict(): void
    {
        $this->seed(RbacSeeder::class);
        $manager = $this->userWithRole('order_manager');
        $order = CustomerOrder::query()->create(['user_id' => User::factory()->create()->id, 'status' => 'pending', 'total_amount' => 100, 'currency' => 'EGP']);

        $this->actingAs($manager)->patchJson("/api/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertOk()->assertJsonPath('data.status', 'confirmed');
        $this->actingAs($manager)->patchJson("/api/orders/{$order->id}/status", ['status' => 'delivered'])
            ->assertConflict();
    }

    public function test_customer_can_cancel_pending_order_but_cannot_cancel_delivered_order(): void
    {
        $this->seed(RbacSeeder::class);
        $customer = $this->userWithRole('customer');
        $pending = CustomerOrder::query()->create(['user_id' => $customer->id, 'status' => 'pending', 'total_amount' => 100, 'currency' => 'EGP']);
        $delivered = CustomerOrder::query()->create(['user_id' => $customer->id, 'status' => 'delivered', 'total_amount' => 100, 'currency' => 'EGP']);

        $this->actingAs($customer)->postJson("/api/customer/orders/{$pending->id}/cancel")
            ->assertOk()->assertJsonPath('data.status', 'cancelled');
        $this->actingAs($customer)->postJson("/api/customer/orders/{$delivered->id}/cancel")
            ->assertConflict();
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::query()->where('slug', $role)->firstOrFail());
        return $user;
    }
}
