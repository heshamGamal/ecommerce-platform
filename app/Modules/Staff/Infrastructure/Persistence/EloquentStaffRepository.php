<?php
namespace App\Modules\Staff\Infrastructure\Persistence;
use App\Models\Role;use App\Models\User;use App\Modules\Staff\Application\DTOs\StaffData;use App\Modules\Staff\Domain\Exceptions\StaffNotFoundException;use App\Modules\Staff\Domain\Contracts\StaffRepositoryInterface;use Illuminate\Support\Facades\Hash;
final class EloquentStaffRepository implements StaffRepositoryInterface
{
 public function list():mixed{return User::query()->whereDoesntHave('roles',fn($q)=>$q->where('slug','customer'))->with('roles')->latest()->paginate(20);}
 public function create(StaffData $data):User{$user=User::query()->create(['name'=>$data->name,'email'=>$data->email,'phone'=>$data->phone,'status'=>$data->status,'password'=>Hash::make((string)$data->password)]);$this->syncRoles($user,$data->roleSlugs);return $user->load('roles');}
 public function update(User $staff,StaffData $data):User{$staff->update(['name'=>$data->name,'email'=>$data->email,'phone'=>$data->phone,'status'=>$data->status,...($data->password!==null?['password'=>Hash::make($data->password)]:[])]);$this->syncRoles($staff,$data->roleSlugs);return $staff->fresh()->load('roles');}
 public function delete(User $staff):void{if(!$staff->exists)throw new StaffNotFoundException('Staff user not found.');$staff->delete();}
 public function find(int $id):User{$user=User::query()->whereKey($id)->whereDoesntHave('roles',fn($q)=>$q->where('slug','customer'))->first();if($user===null)throw new StaffNotFoundException('Staff user not found.');return $user;}
 private function syncRoles(User $user,array $slugs):void{$ids=Role::query()->whereIn('slug',$slugs)->where('is_active',true)->pluck('id');$user->roles()->sync($ids);}
}
