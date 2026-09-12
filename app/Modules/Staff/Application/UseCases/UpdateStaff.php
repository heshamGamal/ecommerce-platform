<?php
namespace App\Modules\Staff\Application\UseCases;
use App\Models\User;use App\Modules\Staff\Application\DTOs\StaffData;use App\Modules\Staff\Domain\Contracts\StaffRepositoryInterface;
final class UpdateStaff{public function __construct(private readonly StaffRepositoryInterface $staff){}public function execute(User $user,StaffData $data){return $this->staff->update($user,$data);}}
