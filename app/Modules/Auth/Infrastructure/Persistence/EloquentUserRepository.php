<?php

namespace App\Modules\Auth\Infrastructure\Persistence;

use App\Models\User;
use App\Models\Role;
use App\Modules\Auth\Application\DTOs\RegisterUserData;
use App\Modules\Auth\Domain\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByIdentifier(string $identifier): ?User
    {
        return User::query()
            ->where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();
    }

    public function findById(int $id): ?User
    {
        return User::query()->find($id);
    }

    public function create(RegisterUserData $data): User
    {
        $user = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'phone' => $data->phone,
            'password' => Hash::make($data->password),
            'status' => 'active',
        ]);

        $customerRole = Role::query()->where('slug', 'customer')->first();
        if ($customerRole !== null) {
            $user->roles()->syncWithoutDetaching([$customerRole->id]);
        }

        return $user;
    }

    public function updatePassword(User $user, string $password): User
    {
        $user->forceFill(['password' => Hash::make($password)])->save();

        return $user->fresh();
    }
}
