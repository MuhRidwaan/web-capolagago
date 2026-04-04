<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMailSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_users');
    }

    public function rules(): array
    {
        return [
            'mail_mailer'       => ['required', 'in:smtp,log,sendmail'],
            'mail_host'         => ['required_if:mail_mailer,smtp', 'nullable', 'string', 'max:255'],
            'mail_port'         => ['required_if:mail_mailer,smtp', 'nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username'     => ['nullable', 'string', 'max:255'],
            'mail_password'     => ['nullable', 'string', 'max:255'],
            'mail_encryption'   => ['nullable', 'in:ssl,tls,starttls,'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name'    => ['required', 'string', 'max:150'],
        ];
    }

    public function attributes(): array
    {
        return [
            'mail_mailer'       => 'Driver',
            'mail_host'         => 'SMTP Host',
            'mail_port'         => 'SMTP Port',
            'mail_username'     => 'Username',
            'mail_password'     => 'Password',
            'mail_encryption'   => 'Enkripsi',
            'mail_from_address' => 'From Address',
            'mail_from_name'    => 'From Name',
        ];
    }
}
