<?php

namespace App\Modules\Staff\Application\UseCases;

use App\Models\User;
use App\Modules\Staff\Domain\Contracts\StaffRepositoryInterface;
use App\Modules\Staff\Domain\Exceptions\StaffActionNotAllowedException;

final class DeleteStaff
{
    public function __construct(private readonly StaffRepositoryInterface $staff)
    {
    }

    public function execute(User $staff, User $actor): void
    {
        if ($staff->is($actor)) {
            throw new StaffActionNotAllowedException('A staff user cannot delete their own account.');
        }

        if ($staff->hasRole('owner')) {
            throw new StaffActionNotAllowedException('The owner account cannot be deleted.');
        }

        $this->staff->delete($staff);
    }
}
