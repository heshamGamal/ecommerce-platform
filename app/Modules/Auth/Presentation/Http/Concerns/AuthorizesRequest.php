<?php

namespace App\Modules\Auth\Presentation\Http\Concerns;

use App\Modules\Auth\Application\UseCases\AuthorizeUser;

trait AuthorizesRequest
{
    protected function authorizePermission(string $permission): bool
    {
        return app(AuthorizeUser::class)->execute($permission);
    }
}
