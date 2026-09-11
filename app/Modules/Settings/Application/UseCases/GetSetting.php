<?php

namespace App\Modules\Settings\Application\UseCases;

use App\Modules\Settings\Domain\Contracts\SettingsRepositoryInterface;

final class GetSetting
{
    /**
     * Create a new GetSetting use case instance.
     *
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(
        private readonly SettingsRepositoryInterface $settings,
    ) {
    }

    /**
     * Execute the use case to retrieve a setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function execute(
        string $key,
        mixed $default = null,
    ): mixed {
        $setting = $this->settings->findByKey($key);

        if ($setting === null) {
            return $default;
        }

        return $setting->getTypedValue();
    }
}

