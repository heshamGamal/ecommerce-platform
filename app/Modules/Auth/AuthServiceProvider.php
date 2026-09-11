<?php

namespace App\Modules\Auth;

use App\Modules\Auth\Domain\Contracts\AuthenticationServiceInterface;
use App\Modules\Auth\Domain\Contracts\PasswordServiceInterface;
use App\Modules\Auth\Domain\Contracts\UserRepositoryInterface;
use App\Modules\Auth\Infrastructure\Authentication\LaravelPasswordService;
use App\Modules\Auth\Infrastructure\Authentication\LaravelSessionAuthenticationService;
use App\Modules\Auth\Infrastructure\Persistence\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UserRepositoryInterface::class => EloquentUserRepository::class,
        AuthenticationServiceInterface::class => LaravelSessionAuthenticationService::class,
        PasswordServiceInterface::class => LaravelPasswordService::class,
    ];

    public function boot(): void
    {
        RateLimiter::for('auth-login', static function (Request $request): Limit {
            $identifier = strtolower((string) $request->input('identifier'));

            return Limit::perMinute(5)->by($identifier.'|'.$request->ip());
        });
    }
}
