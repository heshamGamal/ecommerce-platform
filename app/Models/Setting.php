<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

        /**
             * The attributes that are mass assignable.
                  *
                       * @var array<int, string>
                            */
                                protected $fillable = [
                                        'group',
                                                'key',
                                                        'value',
                                                                'type',
                                                                        'description',
                                                                            ];

                                                                                /**
                                                                                     * Get the value cast to its appropriate PHP type.
                                                                                          */
                                                                                              public function getTypedValue(): mixed
                                                                                                  {
                                                                                                          return match ($this->type) {
                                                                                                                      'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
                                                                                                                                  'integer' => (int) $this->value,
                                                                                                                                              'float'   => (float) $this->value,
                                                                                                                                                          'json'    => json_decode($this->value, true),
                                                                                                                                                                      default   => $this->value,
                                                                                                                                                                              };
                                                                                                                                                                                  }

                                                                                                                                                                                      /**
                                                                                                                                                                                           * Set the value by casting it to a string format for storage.
                                                                                                                                                                                                */
                                                                                                                                                                                                    public function setTypedValue(mixed $value): void
                                                                                                                                                                                                        {
                                                                                                                                                                                                                $this->value = match ($this->type) {
                                                                                                                                                                                                                            'boolean' => $value ? '1' : '0',
                                                                                                                                                                                                                                        'integer',
                                                                                                                                                                                                                                                    'float'   => (string) $value,
                                                                                                                                                                                                                                                                'json'    => json_encode(
                                                                                                                                                                                                                                                                                $value,
                                                                                                                                                                                                                                                                                                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                                                                                                                                                                                                                                                                                                            ),
                                                                                                                                                                                                                                                                                                                        default   => (string) $value,
                                                                                                                                                                                                                                                                                                                                };
                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                    