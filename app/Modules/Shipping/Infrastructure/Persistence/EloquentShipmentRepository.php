<?php

namespace App\Modules\Shipping\Infrastructure\Persistence;

use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Modules\Shipping\Domain\Contracts\ShipmentRepositoryInterface;
use App\Modules\Shipping\Domain\Exceptions\ShipmentNotFoundException;
use App\Modules\Shipping\Domain\Exceptions\InvalidShipmentTransitionException;
use Illuminate\Support\Facades\DB;

final class EloquentShipmentRepository implements ShipmentRepositoryInterface
{
    public function find(int $id): object
    {
        $shipment = Shipment::query()->with(['order', 'method', 'events'])->find($id);
        if ($shipment === null) throw new ShipmentNotFoundException('Shipment not found.');
        return $shipment;
    }
    public function findForUser(int $userId, int $id): object
    {
        $shipment = Shipment::query()->with(['order', 'method', 'events'])->where('user_id', $userId)->find($id);
        if ($shipment === null) throw new ShipmentNotFoundException('Shipment not found.');
        return $shipment;
    }
    public function findByIdempotencyKey(string $key): ?object { return Shipment::query()->with(['order', 'method'])->where('idempotency_key', $key)->first(); }
    public function listForUserOrder(int $userId, int $orderId): iterable { return Shipment::query()->with(['method', 'events'])->where('user_id', $userId)->where('order_id', $orderId)->latest()->get(); }
    public function create(array $attributes): object { return Shipment::query()->create($attributes)->load(['order', 'method', 'events']); }
    public function updateStatus(object $shipment, string $status, ?int $actorId, ?string $note = null): object
    {
        return DB::transaction(function () use ($shipment, $status, $actorId, $note): Shipment {
            $locked = Shipment::query()->lockForUpdate()->find($shipment->id);
            if ($locked === null) throw new ShipmentNotFoundException('Shipment not found.');
            $allowed = ['pending' => ['picked_up', 'cancelled'], 'picked_up' => ['in_transit', 'cancelled'], 'in_transit' => ['out_for_delivery', 'cancelled'], 'out_for_delivery' => ['delivered'], 'delivered' => [], 'cancelled' => []];
            if (!in_array($status, $allowed[$locked->status] ?? [], true)) throw InvalidShipmentTransitionException::from($locked->status, $status);
            $from = $locked->status;
            $locked->update(['status' => $status]);
            ShipmentEvent::query()->create(['shipment_id' => $locked->id, 'from_status' => $from, 'to_status' => $status, 'actor_id' => $actorId, 'note' => $note]);
            return $locked->fresh(['order', 'method', 'events']);
        });
    }
}
