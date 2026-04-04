<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentSettingRequest;
use App\Models\PaymentSetting;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Artisan;

class PaymentSettingController extends Controller
{
    // Definisi semua setting yang dikelola
    private array $definitions = [
        ['key' => 'midtrans_merchant_id',  'label' => 'Merchant ID',         'group' => 'midtrans', 'is_secret' => false],
        ['key' => 'midtrans_server_key',   'label' => 'Server Key',          'group' => 'midtrans', 'is_secret' => true],
        ['key' => 'midtrans_client_key',   'label' => 'Client Key',          'group' => 'midtrans', 'is_secret' => true],
        ['key' => 'midtrans_is_production','label' => 'Mode Produksi',       'group' => 'midtrans', 'is_secret' => false],
        ['key' => 'midtrans_is_3ds',       'label' => '3D Secure (3DS)',     'group' => 'midtrans', 'is_secret' => false],
        ['key' => 'midtrans_notif_url',    'label' => 'Notification URL',    'group' => 'midtrans', 'is_secret' => false],
    ];

    public function index()
    {
        // Pastikan semua key sudah ada di DB (upsert definisi)
        foreach ($this->definitions as $def) {
            PaymentSetting::firstOrCreate(
                ['key' => $def['key']],
                [
                    'label'     => $def['label'],
                    'group'     => $def['group'],
                    'is_secret' => $def['is_secret'],
                    'value'     => $this->defaultValue($def['key']),
                ]
            );
        }

        $settings = PaymentSetting::where('group', 'midtrans')
            ->orderBy('id')
            ->get()
            ->keyBy('key');

        return view('backend.settings.payment', compact('settings'));
    }

    public function update(UpdatePaymentSettingRequest $request)
    {
        $map = [
            'midtrans_merchant_id'   => $request->merchant_id,
            'midtrans_server_key'    => $request->server_key,
            'midtrans_client_key'    => $request->client_key,
            'midtrans_is_production' => $request->is_production,
            'midtrans_is_3ds'        => $request->is_3ds,
            'midtrans_notif_url'     => $request->notification_url
                                        ?? config('app.url') . '/payment/webhook/midtrans',
        ];

        foreach ($map as $key => $value) {
            // Jangan overwrite secret key jika dikosongkan (masked)
            $setting = PaymentSetting::where('key', $key)->first();
            if ($setting?->is_secret && blank($value)) {
                continue;
            }
            PaymentSetting::set($key, $value);
        }

        // Sync ke config runtime agar langsung berlaku tanpa restart
        config([
            'midtrans.merchant_id'   => PaymentSetting::get('midtrans_merchant_id'),
            'midtrans.server_key'    => PaymentSetting::get('midtrans_server_key'),
            'midtrans.client_key'    => PaymentSetting::get('midtrans_client_key'),
            'midtrans.is_production' => (bool) PaymentSetting::get('midtrans_is_production', 0),
            'midtrans.is_3ds'        => (bool) PaymentSetting::get('midtrans_is_3ds', 1),
        ]);

        return redirect()
            ->route('admin.settings.payment')
            ->with('success', 'Pengaturan Midtrans berhasil disimpan.');
    }

    /**
     * Test koneksi ke Midtrans Sandbox menggunakan key yang tersimpan.
     */
    public function testConnection()
    {
        try {
            // Sync config dulu dari DB
            config([
                'midtrans.merchant_id'   => PaymentSetting::get('midtrans_merchant_id', config('midtrans.merchant_id')),
                'midtrans.server_key'    => PaymentSetting::get('midtrans_server_key', config('midtrans.server_key')),
                'midtrans.client_key'    => PaymentSetting::get('midtrans_client_key', config('midtrans.client_key')),
                'midtrans.is_production' => (bool) PaymentSetting::get('midtrans_is_production', 0),
                'midtrans.is_3ds'        => (bool) PaymentSetting::get('midtrans_is_3ds', 1),
            ]);

            $service = new MidtransService();

            // Cek status order dummy — jika key valid, Midtrans return 404 (order not found)
            // bukan 401 (unauthorized). Ini cara aman test koneksi tanpa charge.
            try {
                $service->checkStatus('TEST-CONNECTION-' . time());
            } catch (\Midtrans\Exceptions\MidtransApiException $e) {
                // 404 = key valid, order tidak ditemukan (expected)
                if ($e->getCode() === 404) {
                    return back()->with('success', 'Koneksi ke Midtrans berhasil. Key valid.');
                }
                throw $e;
            }

            return back()->with('success', 'Koneksi ke Midtrans berhasil.');

        } catch (\Exception $e) {
            return back()->with('error', 'Koneksi gagal: ' . $e->getMessage());
        }
    }

    private function defaultValue(string $key): ?string
    {
        return match ($key) {
            'midtrans_merchant_id'   => config('midtrans.merchant_id'),
            'midtrans_server_key'    => config('midtrans.server_key'),
            'midtrans_client_key'    => config('midtrans.client_key'),
            'midtrans_is_production' => config('midtrans.is_production') ? '1' : '0',
            'midtrans_is_3ds'        => config('midtrans.is_3ds') ? '1' : '0',
            'midtrans_notif_url'     => config('app.url') . '/payment/webhook/midtrans',
            default                  => null,
        };
    }
}
