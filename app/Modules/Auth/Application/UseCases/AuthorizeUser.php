<?php

namespace App\Modules\Auth\Application\UseCases;

use App\Modules\Auth\Domain\Contracts\AuthorizationServiceInterface;

final class AuthorizeUser
{
    public function __construct(private readonly AuthorizationServiceInterface $authorization)
    {
    }

    public function execute(string $permission): bool
    {
        return $this->authorization->allows($permission);
    }
}
