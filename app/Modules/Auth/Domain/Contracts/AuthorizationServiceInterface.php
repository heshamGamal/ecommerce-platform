<?php

namespace App\Modules\Auth\Domain\Contracts;

use App\Models\User;

interface AuthorizationServiceInterface
{
    public function allows(User $user, string $permission): bool;
}
