<?php

namespace App\Modules\Auth\Application\UseCases;

use App\Models\User;
use App\Modules\Auth\Domain\Contracts\AuthenticationServiceInterface;
use App\Modules\Auth\Domain\Exceptions\AuthenticationException;

final class AuthenticateUser
{
    public function __construct(private readonly AuthenticationServiceInterface $authentication)
    {
    }

    public function execute(): User
    {
        $user = $this->authentication->user();

        if ($user === null) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $user;
    }
}
