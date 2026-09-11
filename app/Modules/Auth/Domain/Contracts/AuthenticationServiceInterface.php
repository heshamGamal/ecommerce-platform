<?php

namespace App\Modules\Auth\Domain\Contracts;

use App\Models\User;

interface AuthenticationServiceInterface
{
    public function attempt(string $identifier, string $password, bool $remember = false): ?User;

    public function login(User $user, bool $remember = false): void;

    public function logout(): void;

    public function user(): ?User;
}
