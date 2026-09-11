<?php

namespace App\Modules\Auth\Domain\Contracts;

use App\Models\User;

interface PermissionRepositoryInterface
{
    public function userHasPermission(User $user, string $permission): bool;
}
