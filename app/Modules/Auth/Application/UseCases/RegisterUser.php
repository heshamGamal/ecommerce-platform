<?php

namespace App\Modules\Auth\Application\UseCases;

use App\Models\User;
use App\Modules\Auth\Application\DTOs\RegisterUserData;
use App\Modules\Auth\Domain\Contracts\UserRepositoryInterface;

final class RegisterUser
{
    public function __construct(private readonly UserRepositoryInterface $users)
    {
    }

    public function execute(RegisterUserData $data): User
    {
        return $this->users->create($data);
    }
}
