<?php

namespace App\Modules\Staff\Application\UseCases;

use App\Models\User;
use App\Modules\Staff\Application\DTOs\StaffData;
use App\Modules\Staff\Domain\Contracts\StaffRepositoryInterface;
use App\Modules\Staff\Domain\Exceptions\StaffActionNotAllowedException;

final class UpdateStaff
{
    public function __construct(private readonly StaffRepositoryInterface $staff)
    {
    }

    public function execute(User $staff, StaffData $data, User $actor): User
    {
        if ($staff->is($actor) && $data->status === 'inactive') {
            throw new StaffActionNotAllowedException('A staff user cannot deactivate their own account.');
        }

        if ($staff->hasRole('owner')) {
            throw new StaffActionNotAllowedException('The owner account cannot be modified through Staff management.');
        }

        return $this->staff->update($staff, $data);
    }
}
