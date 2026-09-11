<?php

namespace App\Modules\Auth\Domain\Contracts;

interface AuthorizationServiceInterface
{
    public function allows(string $permission): bool;
}
