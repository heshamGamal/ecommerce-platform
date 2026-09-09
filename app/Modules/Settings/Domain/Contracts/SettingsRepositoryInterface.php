<?php

namespace App\Modules\Settings\Domain\Contracts;

use App\Models\Setting;
use App\Modules\Settings\Application\DTOs\SettingData;
use Illuminate\Support\Collection;

interface SettingRepositoryInterface
{
    /**
     * Find a setting record by its unique key.
     *
     * @param string $key
     * @return Setting|null
     */
    public function findByKey(string $key): ?Setting;

    /**
     * Get all settings belonging to a specific group.
     *
     * @param string $group
     * @return Collection<int, Setting>
     */
    public function getByGroup(string $group): Collection;

    /**
     * Retrieve all settings records.
     *
     * @return Collection<int, Setting>
     */
    public function getAll(): Collection;

    /**
     * Create or update a setting using DTO data.
     *
     * @param SettingData $data
     * @return Setting
     */
    public function save(SettingData $data): Setting;

    /**
     * Delete a setting record by its key.
     *
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;
}
