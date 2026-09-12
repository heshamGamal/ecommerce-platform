<?php
namespace App\Modules\Staff\Domain\Contracts;
use App\Models\User;
interface AuditLogRepositoryInterface{public function record(User $actor,string $action,string $targetType,?int $targetId,array $metadata=[]):void;}
