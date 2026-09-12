<?php
namespace App\Modules\Staff\Application\UseCases;
use App\Modules\Staff\Application\DTOs\StaffData;use App\Modules\Staff\Domain\Contracts\StaffRepositoryInterface;
final class CreateStaff{public function __construct(private readonly StaffRepositoryInterface $staff){}public function execute(StaffData $data){return $this->staff->create($data);}}
