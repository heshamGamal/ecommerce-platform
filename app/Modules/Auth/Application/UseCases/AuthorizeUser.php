<?php

namespace App\Modules\Auth\Application\UseCases;

use App\Modules\Auth\Domain\Contracts\AuthorizationServiceInterface;
use App\Modules\Auth\Domain\Exceptions\AuthorizationException;

final class AuthorizeUser
{
    public function __construct(private readonly AuthorizationServiceInterface $authorization)
    {
    }

    public function execute(string $permission): bool
    {
        if (!$this->authorization->allows($permission)) {
            throw new AuthorizationException("Missing permission: {$permission}.");
        }

        return true;
    }
}
