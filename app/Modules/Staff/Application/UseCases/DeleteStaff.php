<?php
namespace App\Modules\Staff\Application\UseCases;
use App\Models\User;use App\Modules\Staff\Domain\Contracts\StaffRepositoryInterface;
final class DeleteStaff{public function __construct(private readonly StaffRepositoryInterface $staff){}public function execute(User $user):void{$this->staff->delete($user);}}
