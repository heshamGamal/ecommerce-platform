<?php

namespace App\Modules\Settings\Application\UseCases;

use App\Models\Setting;
use App\Modules\Settings\Application\DTOs\SettingData;
use App\Modules\Settings\Domain\Contracts\SettingsRepositoryInterface;

final class UpdateSetting
{
    /**
     * Create a new UpdateSetting use case instance.
     *
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(
        private readonly SettingsRepositoryInterface $settings,
    ) {
    }

    /**
     * Execute the use case to create or update a setting.
     *
     * @param SettingData $data
     * @return Setting
     */
    public function execute(SettingData $data): Setting
    {
        return $this->settings->save($data);
    }
}

