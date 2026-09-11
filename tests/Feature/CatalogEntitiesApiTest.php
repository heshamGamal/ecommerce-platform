<?php
namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogEntitiesApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RbacSeeder::class);
        $this->admin = $this->createAdminUser();
    }

    public function test_attribute_and_value_crud_and_nested_404_responses(): void
    {
        $attributeResponse = $this->actingAs($this->admin)->postJson('/api/attributes', ['name' => 'Color'])
            ->assertCreated()->assertJsonPath('data.name', 'Color');
        $attribute = $attributeResponse->json('data.id');
        $valueResponse = $this->postJson("/api/attributes/{$attribute}/values", ['value' => 'Red'])
            ->assertCreated()->assertJsonPath('data.attribute_id', $attribute);
        $value = $valueResponse->json('data.id');

        $this->getJson('/api/attributes')->assertOk()->assertJsonPath('data.0.id', $attribute);
        $this->getJson("/api/attributes/{$attribute}")->assertOk()->assertJsonPath('data.values.0.id', $value);
        $this->getJson("/api/attributes/{$attribute}/values")->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/attributes/{$attribute}/values/{$value}")->assertOk()->assertJsonPath('data.value', 'Red');
        $this->patchJson("/api/attributes/{$attribute}", ['name' => 'Colour'])->assertOk()->assertJsonPath('data.name', 'Colour');
        $this->patchJson("/api/attributes/{$attribute}/values/{$value}", ['value' => 'Blue'])->assertOk()->assertJsonPath('data.value', 'Blue');

        $other = Attribute::query()->create(['name' => 'Size']);
        $this->getJson("/api/attributes/{$other->id}/values/{$value}")->assertNotFound();
        $this->getJson('/api/attributes/999')->assertNotFound();
        $this->getJson('/api/attributes/999/values')->assertNotFound();
        $this->deleteJson("/api/attributes/{$attribute}/values/{$value}")->assertNoContent();
        $this->deleteJson("/api/attributes/{$attribute}")->assertNoContent();
    }

    public function test_attribute_validation_duplicates_and_used_relationships(): void
    {
        $attribute = Attribute::query()->create(['name' => 'Color']);
        $red = $attribute->values()->create(['value' => 'Red']);

        $this->actingAs($this->admin)->postJson('/api/attributes', [])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson('/api/attributes', ['name' => 'Color'])->assertConflict();
        $this->postJson("/api/attributes/{$attribute->id}/values", [])->assertUnprocessable()->assertJsonValidationErrors('value');
        $this->postJson("/api/attributes/{$attribute->id}/values", ['value' => 'Red'])->assertConflict();
        $this->postJson('/api/attributes/999/values', ['value' => 'Ghost'])->assertNotFound();

        $product = Product::query()->create(['name' => 'Variable', 'slug' => 'variable', 'type' => 'variable', 'status' => 'active']);
        $variant = $product->variants()->create([
            'sku' => 'USED', 'price' => 100, 'status' => 'active',
            'combination_hash' => hash('sha256', (string) $red->id),
        ]);
        $variant->attributeValues()->attach($red->id, ['attribute_id' => $attribute->id]);

        $this->deleteJson("/api/attributes/{$attribute->id}/values/{$red->id}")->assertConflict();
        $this->deleteJson("/api/attributes/{$attribute->id}")->assertConflict();
    }

    public function test_brand_crud_validation_duplicate_slug_404_and_delete_relationship_rule(): void
    {
        $create = $this->actingAs($this->admin)->postJson('/api/brands', [
            'name' => 'Acme', 'status' => 'active',
        ])->assertCreated()->assertJsonPath('data.slug', 'acme');
        $id = $create->json('data.id');

        $this->getJson('/api/brands')->assertOk()->assertJsonPath('data.0.id', $id);
        $this->getJson("/api/brands/{$id}")->assertOk();
        $this->patchJson("/api/brands/{$id}", ['name' => 'Acme New', 'slug' => 'acme-new', 'status' => 'inactive'])
            ->assertOk()->assertJsonPath('data.slug', 'acme-new');
        $this->postJson('/api/brands', [])->assertUnprocessable()->assertJsonValidationErrors(['name', 'status']);
        $this->postJson('/api/brands', ['name' => 'Duplicate', 'slug' => 'acme-new', 'status' => 'active'])->assertConflict();
        $this->getJson('/api/brands/999')->assertNotFound();

        Product::query()->create(['name' => 'Branded', 'slug' => 'branded', 'type' => 'simple', 'status' => 'active', 'brand_id' => $id]);
        $this->deleteJson("/api/brands/{$id}")->assertConflict();
        Product::query()->delete();
        $this->deleteJson("/api/brands/{$id}")->assertNoContent();
    }

    public function test_category_crud_validation_duplicate_slug_404_and_relationship_rules(): void
    {
        $parentResponse = $this->actingAs($this->admin)->postJson('/api/categories', [
            'name' => 'Parent', 'is_active' => true,
        ])->assertCreated()->assertJsonPath('data.slug', 'parent');
        $parent = $parentResponse->json('data.id');
        $childResponse = $this->postJson('/api/categories', [
            'name' => 'Child', 'parent_id' => $parent, 'is_active' => true,
        ])->assertCreated()->assertJsonPath('data.parent_id', $parent);
        $child = $childResponse->json('data.id');

        $this->getJson('/api/categories')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson("/api/categories/{$child}")->assertOk()->assertJsonPath('data.parent.id', $parent);
        $this->patchJson("/api/categories/{$child}", [
            'name' => 'Child Updated', 'parent_id' => null, 'is_active' => false,
        ])->assertOk()->assertJsonPath('data.is_active', false);
        $this->postJson('/api/categories', [])->assertUnprocessable()->assertJsonValidationErrors(['name', 'is_active']);
        $this->postJson('/api/categories', ['name' => 'Missing Parent', 'parent_id' => 999, 'is_active' => true])
            ->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $this->postJson('/api/categories', ['name' => 'Duplicate', 'slug' => 'parent', 'is_active' => true])->assertConflict();
        $this->getJson('/api/categories/999')->assertNotFound();

        Product::query()->create(['name' => 'Categorized', 'slug' => 'categorized', 'type' => 'simple', 'status' => 'active', 'category_id' => $child]);
        $this->deleteJson("/api/categories/{$child}")->assertConflict();
        Product::query()->delete();
        $this->deleteJson("/api/categories/{$child}")->assertNoContent();
        $this->deleteJson("/api/categories/{$parent}")->assertNoContent();
    }

    public function test_category_parent_self_and_descendant_cycles_are_rejected(): void
    {
        $parent = Category::query()->create(['name' => 'Parent', 'slug' => 'parent', 'is_active' => true]);
        $child = Category::query()->create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id, 'is_active' => true]);
        $grandchild = Category::query()->create(['name' => 'Grandchild', 'slug' => 'grandchild', 'parent_id' => $child->id, 'is_active' => true]);

        $this->actingAs($this->admin)->patchJson("/api/categories/{$parent->id}", [
            'name' => 'Parent', 'parent_id' => $parent->id, 'is_active' => true,
        ])->assertConflict();
        $this->patchJson("/api/categories/{$parent->id}", [
            'name' => 'Parent', 'parent_id' => $grandchild->id, 'is_active' => true,
        ])->assertConflict();
    }

    public function test_category_with_children_cannot_be_deleted(): void
    {
        $parent = Category::query()->create(['name' => 'Parent', 'slug' => 'parent', 'is_active' => true]);
        Category::query()->create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id, 'is_active' => true]);

        $this->actingAs($this->admin)->deleteJson("/api/categories/{$parent->id}")->assertConflict();
    }
}
