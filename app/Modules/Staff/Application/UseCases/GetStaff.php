<?php

namespace App\Modules\Staff\Application\UseCases;

use App\Models\User;
use App\Modules\Staff\Domain\Contracts\StaffRepositoryInterface;

final class GetStaff
{
    public function __construct(private readonly StaffRepositoryInterface $staff)
    {
    }

    public function execute(int $id): User
    {
        return $this->staff->find($id);
    }
}
