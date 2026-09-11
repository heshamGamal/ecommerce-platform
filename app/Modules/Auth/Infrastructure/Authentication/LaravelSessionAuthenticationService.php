<?php

namespace App\Modules\Auth\Infrastructure\Authentication;

use App\Models\User;
use App\Modules\Auth\Domain\Contracts\AuthenticationServiceInterface;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;

final class LaravelSessionAuthenticationService implements AuthenticationServiceInterface
{
    private function guard(): StatefulGuard
    {
        /** @var StatefulGuard $guard */
        $guard = Auth::guard('web');

        return $guard;
    }

    public function attempt(string $identifier, string $password, bool $remember = false): ?User
    {
        if (!$this->guard()->attempt([
            'email' => $identifier,
            'password' => $password,
            'status' => 'active',
        ], $remember) && !$this->guard()->attempt([
            'phone' => $identifier,
            'password' => $password,
            'status' => 'active',
        ], $remember)) {
            return null;
        }

        /** @var User|null $user */
        $user = $this->guard()->user();

        return $user;
    }

    public function login(User $user, bool $remember = false): void
    {
        $this->guard()->login($user, $remember);
    }

    public function logout(): void
    {
        $this->guard()->logout();
    }

    public function user(): ?User
    {
        /** @var User|null $user */
        $user = $this->guard()->user();

        return $user;
    }
}
