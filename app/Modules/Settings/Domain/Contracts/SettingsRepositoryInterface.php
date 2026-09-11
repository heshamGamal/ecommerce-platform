<?php

namespace App\Modules\Settings\Domain\Contracts;

use App\Models\Setting;
use App\Modules\Settings\Application\DTOs\SettingData;
use Illuminate\Support\Collection;

interface SettingsRepositoryInterface
{
    public function findByKey(string $key): ?Setting;
    public function getByGroup(string $group): Collection;
    public function getAll(): Collection;
    public function save(SettingData $data): Setting;
    public function delete(string $key): bool;
}
