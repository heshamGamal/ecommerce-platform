<?php

namespace App\Modules\Auth\Infrastructure\Authorization;

use App\Modules\Auth\Domain\Contracts\AuthenticationServiceInterface;
use App\Modules\Auth\Domain\Contracts\AuthorizationServiceInterface;
use App\Modules\Auth\Domain\Contracts\PermissionRepositoryInterface;

final class LaravelAuthorizationService implements AuthorizationServiceInterface
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authentication,
        private readonly PermissionRepositoryInterface $permissions,
    ) {
    }

    public function allows(string $permission): bool
    {
        $user = $this->authentication->user();

        return $user !== null && $this->permissions->userHasPermission($user, $permission);
    }
}
