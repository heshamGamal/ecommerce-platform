<?php

namespace App\Modules\Shipping\Application\UseCases;

use App\Models\Shipment;
use App\Modules\Auth\Domain\Contracts\AuthenticationServiceInterface;
use App\Modules\Auth\Domain\Exceptions\AuthenticationException;
use App\Modules\Shipping\Domain\Contracts\ShipmentRepositoryInterface;

final class UpdateShipmentStatus
{
    public function __construct(private readonly AuthenticationServiceInterface $authentication, private readonly ShipmentRepositoryInterface $shipments) {}
    public function execute(int $shipmentId, string $status, ?string $note = null): Shipment
    {
        $user = $this->authentication->user();
        if ($user === null) throw new AuthenticationException('Unauthenticated.');
        return $this->shipments->updateStatus($this->shipments->find($shipmentId), $status, $user->id, $note);
    }
}
