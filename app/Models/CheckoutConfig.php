<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value'])]
class CheckoutConfig extends Model
{
    /**
     * Get a config value by key, with a fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $config = static::where('key', $key)->first();

        if (! $config) {
            return value($default);
        }

        return $config->value;
    }

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }
}
