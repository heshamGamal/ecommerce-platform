<?php

namespace App\Modules\Settings\Domain\ValueObjects;

final readonly class SettingData
{
    public function __construct(
        public string $group,
        public string $key,
        public mixed $value,
        public string $type = 'string',
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['group'], $data['key'], $data['value'],
            $data['type'] ?? 'string', $data['description'] ?? null,
        );
    }

    public static function fromModel(object $setting): self
    {
        return new self(
            $setting->group, $setting->key, $setting->getTypedValue(),
            $setting->type, $setting->description,
        );
    }

    public function toArray(): array
    {
        return [
            'group' => $this->group, 'key' => $this->key, 'value' => $this->value,
            'type' => $this->type, 'description' => $this->description,
        ];
    }
}
