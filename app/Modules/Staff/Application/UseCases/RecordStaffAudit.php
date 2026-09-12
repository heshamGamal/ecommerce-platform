<?php
namespace App\Modules\Staff\Application\UseCases;
use App\Models\User;use App\Modules\Staff\Domain\Contracts\AuditLogRepositoryInterface;
final class RecordStaffAudit{public function __construct(private readonly AuditLogRepositoryInterface $audit){}public function execute(User $actor,string $action,?int $targetId,array $metadata=[]):void{$this->audit->record($actor,$action,User::class,$targetId,$metadata);}}
