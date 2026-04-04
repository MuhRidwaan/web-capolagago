<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'is_secret'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
    }

    /**
     * Terapkan semua setting dari DB ke config Laravel runtime.
     * Dipanggil di AppServiceProvider atau middleware.
     */
    public static function applyToConfig(): void
    {
        $map = [
            'mail_mailer'       => 'mail.default',
            'mail_host'         => 'mail.mailers.smtp.host',
            'mail_port'         => 'mail.mailers.smtp.port',
            'mail_username'     => 'mail.mailers.smtp.username',
            'mail_password'     => 'mail.mailers.smtp.password',
            'mail_encryption'   => 'mail.mailers.smtp.scheme',
            'mail_from_address' => 'mail.from.address',
            'mail_from_name'    => 'mail.from.name',
        ];

        foreach ($map as $dbKey => $configKey) {
            $value = static::get($dbKey);
            if (filled($value)) {
                config([$configKey => $value]);
            }
        }
    }
}
