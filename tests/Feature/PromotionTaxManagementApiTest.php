<?php
namespace Tests\Feature;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
final class PromotionTaxManagementApiTest extends TestCase
{
    use RefreshDatabase;
    public function test_admin_can_manage_coupons_and_tax_rules(): void
    {
        $this->seed(RbacSeeder::class);
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::query()->where('slug', 'admin')->firstOrFail());
        $coupon = $this->actingAs($admin)->postJson('/api/coupons', ['code' => 'save10', 'type' => 'percent', 'value' => 10, 'is_active' => true])->assertCreated()->json('data.id');
        $this->actingAs($admin)->getJson('/api/coupons')->assertOk()->assertJsonPath('data.0.code', 'SAVE10');
        $this->actingAs($admin)->patchJson("/api/coupons/{$coupon}", ['value' => 15])->assertOk()->assertJsonPath('data.value', 15);
        $tax = $this->actingAs($admin)->postJson('/api/tax-rules', ['name' => 'Egypt VAT', 'country' => 'eg', 'rate' => 14])->assertCreated()->json('data.id');
        $this->actingAs($admin)->getJson('/api/tax-rules')->assertOk()->assertJsonPath('data.0.name', 'Egypt VAT');
        $this->actingAs($admin)->deleteJson("/api/tax-rules/{$tax}")->assertNoContent();
        $this->actingAs($admin)->deleteJson("/api/coupons/{$coupon}")->assertNoContent();
    }
    public function test_customer_cannot_manage_promotions_or_taxes(): void
    {
        $this->seed(RbacSeeder::class);
        $customer = User::factory()->create();
        $customer->roles()->attach(Role::query()->where('slug', 'customer')->firstOrFail());
        $this->actingAs($customer)->getJson('/api/coupons')->assertForbidden();
        $this->actingAs($customer)->getJson('/api/tax-rules')->assertForbidden();
    }
}
