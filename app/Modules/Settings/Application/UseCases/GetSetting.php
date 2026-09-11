<?php

namespace App\Modules\Settings\Application\UseCases;

use App\Modules\Settings\Domain\Contracts\SettingRepositoryInterface;

final class GetSetting
{
    /**
     * Create a new GetSetting use case instance.
     *
     * @param SettingRepositoryInterface $settings
     */
    public function __construct(
        private readonly SettingRepositoryInterface $settings,
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

