<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class PaymentSetting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'group', 'is_secret'];

    protected static ?bool $settingsTableExists = null;

    /**
     * Ambil value berdasarkan key, dengan fallback ke config/env.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (! static::hasSettingsTable()) {
            return $default;
        }

        $setting = static::where('key', $key)->first();
        return $setting?->value ?? $default;
    }

    /**
     * Simpan atau update value berdasarkan key.
     */
    public static function set(string $key, mixed $value): void
    {
        if (! static::hasSettingsTable()) {
            return;
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    protected static function hasSettingsTable(): bool
    {
        if (static::$settingsTableExists !== null) {
            return static::$settingsTableExists;
        }

        try {
            return static::$settingsTableExists = Schema::hasTable((new static())->getTable());
        } catch (\Throwable) {
            return static::$settingsTableExists = false;
        }
    }
}
