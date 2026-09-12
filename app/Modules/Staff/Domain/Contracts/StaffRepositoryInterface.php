<?php
namespace App\Modules\Staff\Domain\Contracts;
use App\Models\User;
use App\Modules\Staff\Application\DTOs\StaffData;
interface StaffRepositoryInterface
{
 public function list(): mixed;
 public function create(StaffData $data): User;
 public function update(User $staff,StaffData $data): User;
 public function delete(User $staff): void;
 public function find(int $id): User;
 public function activeOwnerCount(): int;
}
