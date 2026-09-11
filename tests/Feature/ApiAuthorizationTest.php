<?php
namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private ProductVariant $variant;
    private Attribute $attribute;
    private AttributeValue $value;
    private Brand $brand;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
        $this->user = User::factory()->create();
        $this->brand = Brand::query()->create(['name' => 'Brand', 'slug' => 'brand', 'status' => 'active']);
        $this->category = Category::query()->create(['name' => 'Category', 'slug' => 'category', 'is_active' => true]);
        $this->product = Product::query()->create([
            'name' => 'Variable', 'slug' => 'variable', 'type' => 'variable', 'status' => 'active',
            'brand_id' => $this->brand->id, 'category_id' => $this->category->id,
        ]);
        $this->attribute = Attribute::query()->create(['name' => 'Color']);
        $this->value = $this->attribute->values()->create(['value' => 'Red']);
        $this->variant = $this->product->variants()->create([
            'sku' => 'AUTH-SKU', 'price' => 100, 'status' => 'active',
            'combination_hash' => hash('sha256', (string) $this->value->id),
        ]);
        $this->variant->attributeValues()->attach($this->value->id, ['attribute_id' => $this->attribute->id]);
    }

    public function test_every_endpoint_requires_authentication(): void
    {
        foreach ($this->endpointRequests() as [$method, $uri, $payload]) {
            $this->request($method, $uri, $payload)->assertUnauthorized();
        }
    }

    public function test_every_endpoint_rejects_an_authenticated_user_without_permission(): void
    {
        $this->actingAs($this->user);

        foreach ($this->endpointRequests() as [$method, $uri, $payload]) {
            $this->request($method, $uri, $payload)->assertForbidden();
        }
    }

    public function test_authenticated_user_with_permission_is_not_rejected_by_authorization(): void
    {
        $admin = Role::query()->where('slug', 'admin')->firstOrFail();
        $this->user->roles()->attach($admin);
        $this->actingAs($this->user);

        foreach ($this->endpointRequests() as [$method, $uri, $payload]) {
            $response = $this->request($method, $uri, $payload);

            self::assertNotSame(401, $response->status(), $method.' '.$uri.' was not authenticated.');
            self::assertNotSame(403, $response->status(), $method.' '.$uri.' was not authorized.');
        }
    }

    /** @return array<int, array{string, string, array<string, mixed>}> */
    private function endpointRequests(): array
    {
        $product = $this->product->id;
        $variant = $this->variant->id;
        $attribute = $this->attribute->id;
        $value = $this->value->id;
        $brand = $this->brand->id;
        $category = $this->category->id;

        return [
            ['GET', '/api/products', []],
            ['POST', '/api/products', []],
            ['GET', "/api/products/{$product}", []],
            ['PATCH', "/api/products/{$product}", []],
            ['DELETE', "/api/products/{$product}", []],
            ['GET', "/api/products/{$product}/variants", []],
            ['POST', "/api/products/{$product}/variants", []],
            ['GET', "/api/products/{$product}/variants/{$variant}", []],
            ['PATCH', "/api/products/{$product}/variants/{$variant}", []],
            ['DELETE', "/api/products/{$product}/variants/{$variant}", []],
            ['GET', '/api/attributes', []],
            ['POST', '/api/attributes', []],
            ['GET', "/api/attributes/{$attribute}", []],
            ['PATCH', "/api/attributes/{$attribute}", []],
            ['DELETE', "/api/attributes/{$attribute}", []],
            ['GET', "/api/attributes/{$attribute}/values", []],
            ['POST', "/api/attributes/{$attribute}/values", []],
            ['GET', "/api/attributes/{$attribute}/values/{$value}", []],
            ['PATCH', "/api/attributes/{$attribute}/values/{$value}", []],
            ['DELETE', "/api/attributes/{$attribute}/values/{$value}", []],
            ['GET', '/api/brands', []],
            ['POST', '/api/brands', []],
            ['GET', "/api/brands/{$brand}", []],
            ['PATCH', "/api/brands/{$brand}", []],
            ['DELETE', "/api/brands/{$brand}", []],
            ['GET', '/api/categories', []],
            ['POST', '/api/categories', []],
            ['GET', "/api/categories/{$category}", []],
            ['PATCH', "/api/categories/{$category}", []],
            ['DELETE', "/api/categories/{$category}", []],
            ['GET', '/api/settings', []],
            ['GET', '/api/settings/groups/store', []],
            ['GET', '/api/settings/store.name', []],
            ['PUT', '/api/settings/store.name', []],
            ['GET', '/api/customer/profile', []],
            ['GET', '/api/customer/addresses', []],
            ['GET', '/api/customer/addresses/default', []],
            ['GET', '/api/customer/orders', []],
            ['GET', '/api/customer/cart', []],
            ['GET', '/api/customer/wishlist', []],
            ['GET', '/api/customer/preferences', []],
            ['GET', '/api/customer/notifications', []],
        ];
    }

    /** @param array<string, mixed> $payload */
    private function request(string $method, string $uri, array $payload): TestResponse
    {
        return $this->json($method, $uri, $payload);
    }
}
