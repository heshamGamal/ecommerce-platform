<?php

namespace App\Modules\Customer\Domain\Contracts;

use App\Models\User;

interface CustomerRepositoryInterface
{
    public function findById(int $id): ?User;

    public function update(User $customer, string $name, ?string $email, ?string $phone): User;
}
