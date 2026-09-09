<?php
namespace App\Modules\Settings\Application\DTOs;
final readonly class SettingData
{
/**
* Create a new SettingData DTO instance.
*
* @param string $group
* @param string $key
* @param mixed $value
* @param string $type
* @param string|null $description
*/
public function __construct(
public string $group,
public string $key,
public mixed $value,
public string $type = 'string',
public ?string $description = null,
) {
}
/**
* Create a DTO instance from an associative array.
*
* @param array<string, mixed> $data
* @return self
*/
public static function fromArray(array $data): self
{
return new self(
group: $data['group'],
key: $data['key'],
value: $data['value'],
type: $data['type'] ?? 'string',
description: $data['description'] ?? null,
);
}
/**
* Convert the DTO instance to an array.
*
* @return array<string, mixed>
*/
public function toArray(): array
{
return [
'group'       => $this->group,
'key'         => $this->key,
'value'       => $this->value,
'type'        => $this->type,
'description' => $this->description,
];
}
}
