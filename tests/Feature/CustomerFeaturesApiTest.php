<?php
namespace Tests\Feature;
use App\Models\CustomerNotification;use App\Models\CustomerOrder;use App\Models\Product;use App\Models\Role;use App\Models\User;use Database\Seeders\RbacSeeder;use Illuminate\Foundation\Testing\RefreshDatabase;use Tests\TestCase;
final class CustomerFeaturesApiTest extends TestCase {
 use RefreshDatabase; private User $customer; private Product $product;
 protected function setUp():void{parent::setUp();$this->seed(RbacSeeder::class);$this->customer=User::factory()->create();$this->customer->roles()->attach(Role::query()->where('slug','customer')->firstOrFail());$this->product=Product::query()->create(['name'=>'Phone','slug'=>'phone','type'=>'simple','status'=>'active']);}
 public function test_addresses_default_address_cart_wishlist_preferences_and_notifications():void{
  $this->actingAs($this->customer)->postJson('/api/customer/addresses',['recipient_name'=>'A','phone'=>'010','address_line1'=>'Street 1','city'=>'Cairo','country'=>'EG','is_default'=>true])->assertCreated();
  $second=$this->actingAs($this->customer)->postJson('/api/customer/addresses',['recipient_name'=>'B','phone'=>'011','address_line1'=>'Street 2','city'=>'Cairo','country'=>'EG','is_default'=>true])->assertCreated();
  $this->assertDatabaseHas('customer_addresses',['user_id'=>$this->customer->id,'is_default'=>0]);$this->actingAs($this->customer)->getJson('/api/customer/addresses/default')->assertOk()->assertJsonPath('data.recipient_name','B');$secondId=$second->json('data.id');$this->actingAs($this->customer)->deleteJson("/api/customer/addresses/{$secondId}")->assertNoContent();
  $this->actingAs($this->customer)->postJson('/api/customer/cart/items',['product_id'=>$this->product->id,'quantity'=>2])->assertCreated();$this->actingAs($this->customer)->getJson('/api/customer/cart')->assertOk()->assertJsonPath('data.items.0.quantity',2);
  $this->actingAs($this->customer)->postJson('/api/customer/wishlist',['product_id'=>$this->product->id])->assertCreated();$this->actingAs($this->customer)->getJson('/api/customer/wishlist')->assertOk()->assertJsonCount(1,'data');
  $this->actingAs($this->customer)->putJson('/api/customer/preferences',['data'=>['locale'=>'ar','marketing'=>false]])->assertOk();$this->actingAs($this->customer)->getJson('/api/customer/preferences')->assertOk()->assertJsonPath('data.data.locale','ar');
  CustomerNotification::query()->create(['user_id'=>$this->customer->id,'type'=>'order','title'=>'Order','body'=>'Ready']);$this->actingAs($this->customer)->getJson('/api/customer/notifications')->assertOk()->assertJsonCount(1,'data');
 }
 public function test_customer_can_list_only_own_orders_and_guests_are_rejected():void{
  $this->getJson('/api/customer/orders')->assertUnauthorized();
  CustomerOrder::query()->create(['user_id'=>$this->customer->id,'status'=>'pending','total_amount'=>100]);$other=User::factory()->create();CustomerOrder::query()->create(['user_id'=>$other->id,'status'=>'pending','total_amount'=>200]);
  $this->actingAs($this->customer)->getJson('/api/customer/orders')->assertOk()->assertJsonCount(1,'data');
 }
}
