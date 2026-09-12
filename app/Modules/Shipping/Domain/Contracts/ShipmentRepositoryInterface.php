<?php

namespace App\Modules\Shipping\Domain\Contracts;

use App\Models\Shipment;

interface ShipmentRepositoryInterface
{
    public function find(int $id): Shipment;
    public function findForUser(int $userId, int $id): Shipment;
    public function findByIdempotencyKey(string $key): ?Shipment;
    public function listForUserOrder(int $userId, int $orderId): iterable;
    public function create(array $attributes): Shipment;
    public function updateStatus(Shipment $shipment, string $status, ?int $actorId, ?string $note = null): Shipment;
}
