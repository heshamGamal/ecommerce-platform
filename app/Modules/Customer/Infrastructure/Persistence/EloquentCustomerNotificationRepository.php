<?php

namespace App\Modules\Customer\Infrastructure\Persistence;

use App\Models\CustomerNotification;
use App\Modules\Customer\Domain\Contracts\CustomerNotificationRepositoryInterface;

final class EloquentCustomerNotificationRepository implements CustomerNotificationRepositoryInterface
{
    public function listForUser(int $userId): iterable
    {
        return CustomerNotification::query()->where('user_id', $userId)->latest()->get();
    }

    public function markAsRead(int $userId, int $notificationId): object
    {
        $notification = CustomerNotification::query()->where('user_id', $userId)->findOrFail($notificationId);
        $notification->update(['read_at' => now()]);

        return $notification->fresh();
    }
}
