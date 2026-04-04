<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_users');
    }

    public function rules(): array
    {
        return [
            'merchant_id'      => ['nullable', 'string', 'max:50'],
            'server_key'       => ['nullable', 'string', 'max:255'],
            'client_key'       => ['nullable', 'string', 'max:255'],
            'is_production' => ['required', 'in:0,1'],
            'is_3ds'        => ['required', 'in:0,1'],
            'notification_url' => ['nullable', 'url', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'merchant_id'      => 'Merchant ID',
            'server_key'       => 'Server Key',
            'client_key'       => 'Client Key',
            'is_production'    => 'Mode',
            'is_3ds'           => '3DS',
            'notification_url' => 'Notification URL',
        ];
    }
}
