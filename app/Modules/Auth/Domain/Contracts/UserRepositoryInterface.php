<?php

namespace App\Modules\Auth\Domain\Contracts;

use App\Models\User;
use App\Modules\Auth\Domain\ValueObjects\RegisterUserData;

interface UserRepositoryInterface
{
    public function findByIdentifier(string $identifier): ?User;

    public function findById(int $id): ?User;

    public function create(RegisterUserData $data): User;

    public function updatePassword(User $user, string $password): User;
}
