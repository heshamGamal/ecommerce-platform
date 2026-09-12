<?php
namespace Tests\Feature;
use App\Models\Role;use App\Models\User;use Database\Seeders\RbacSeeder;use Illuminate\Foundation\Testing\RefreshDatabase;use Tests\TestCase;
final class StaffApiTest extends TestCase
{
 use RefreshDatabase;
 protected function setUp():void{parent::setUp();$this->seed(RbacSeeder::class);}
 public function test_guest_cannot_access_staff():void{$this->getJson('/api/staff')->assertUnauthorized();$this->postJson('/api/staff',[])->assertUnauthorized();}
 public function test_user_without_staff_permission_is_forbidden():void{$user=$this->userWithRole('product_manager');$this->actingAs($user)->getJson('/api/staff')->assertForbidden();$this->actingAs($user)->postJson('/api/staff',[])->assertForbidden();$this->actingAs($user)->patchJson('/api/staff/1',[])->assertForbidden();$this->actingAs($user)->deleteJson('/api/staff/1')->assertForbidden();}
 public function test_owner_can_create_list_update_and_delete_staff():void{$owner=$this->userWithRole('owner');$payload=['name'=>'Catalog Staff','email'=>'staff@example.com','password'=>'password123','password_confirmation'=>'password123','roles'=>['product_manager']];$response=$this->actingAs($owner)->postJson('/api/staff',$payload)->assertCreated()->assertJsonPath('data.email','staff@example.com')->assertJsonPath('data.roles.0.slug','product_manager');$id=$response->json('data.id');$this->actingAs($owner)->getJson('/api/staff')->assertOk()->assertJsonFragment(['email'=>'staff@example.com']);$this->actingAs($owner)->patchJson("/api/staff/{$id}",['name'=>'Updated Staff','email'=>'staff@example.com','roles'=>['support_agent']])->assertOk()->assertJsonPath('data.name','Updated Staff')->assertJsonPath('data.roles.0.slug','support_agent');$this->actingAs($owner)->deleteJson("/api/staff/{$id}")->assertOk();$this->assertDatabaseMissing('users',['id'=>$id]);}
 public function test_staff_request_validates_duplicate_identifiers_and_customer_role():void{$owner=$this->userWithRole('owner');$existing=User::factory()->create(['email'=>'taken@example.com']);$payload=['name'=>'Invalid','email'=>'taken@example.com','password'=>'password123','password_confirmation'=>'password123','roles'=>['customer']];$this->actingAs($owner)->postJson('/api/staff',$payload)->assertUnprocessable()->assertJsonValidationErrors(['email','roles.0']);}
 public function test_staff_lookup_of_customer_returns_not_found():void{$owner=$this->userWithRole('owner');$customer=$this->userWithRole('customer');$this->actingAs($owner)->patchJson("/api/staff/{$customer->id}",['name'=>'No','email'=>'no@example.com'])->assertNotFound();}
 private function userWithRole(string $role):User{$user=User::factory()->create();$user->roles()->attach(Role::query()->where('slug',$role)->firstOrFail());return $user;}
}
