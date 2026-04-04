<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMailSettingRequest;
use App\Mail\BookingPaymentConfirmed;
use App\Models\MailSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailSettingController extends Controller
{
    private array $definitions = [
        ['key' => 'mail_mailer',       'label' => 'Driver / Mailer',    'is_secret' => false],
        ['key' => 'mail_host',         'label' => 'SMTP Host',          'is_secret' => false],
        ['key' => 'mail_port',         'label' => 'SMTP Port',          'is_secret' => false],
        ['key' => 'mail_username',     'label' => 'Username / Email',   'is_secret' => false],
        ['key' => 'mail_password',     'label' => 'Password / App Key', 'is_secret' => true],
        ['key' => 'mail_encryption',   'label' => 'Enkripsi',           'is_secret' => false],
        ['key' => 'mail_from_address', 'label' => 'From Address',       'is_secret' => false],
        ['key' => 'mail_from_name',    'label' => 'From Name',          'is_secret' => false],
    ];

    public function index()
    {
        foreach ($this->definitions as $def) {
            MailSetting::firstOrCreate(
                ['key' => $def['key']],
                [
                    'label'     => $def['label'],
                    'is_secret' => $def['is_secret'],
                    'value'     => $this->defaultValue($def['key']),
                ]
            );
        }

        $settings = MailSetting::orderBy('id')->get()->keyBy('key');

        return view('backend.settings.mail', compact('settings'));
    }

    public function update(UpdateMailSettingRequest $request)
    {
        $fields = [
            'mail_mailer', 'mail_host', 'mail_port',
            'mail_username', 'mail_encryption',
            'mail_from_address', 'mail_from_name',
        ];

        foreach ($fields as $key) {
            MailSetting::set($key, $request->input($key));
        }

        // Password: jangan overwrite jika dikosongkan
        if (filled($request->mail_password)) {
            MailSetting::set('mail_password', $request->mail_password);
        }

        // Terapkan ke config runtime
        MailSetting::applyToConfig();

        return redirect()
            ->route('admin.settings.mail')
            ->with('success', 'Pengaturan email berhasil disimpan.');
    }

    /**
     * Kirim email test ke alamat yang ditentukan.
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        // Terapkan config dari DB sebelum kirim
        MailSetting::applyToConfig();

        try {
            // Buat dummy data untuk preview email
            $booking = (object) [
                'booking_code' => 'CAP-TEST-0001',
                'user_name'    => 'Test User',
                'visit_date'   => now()->addDays(3)->toDateString(),
                'total_guests' => 2,
            ];

            $payment = (object) [
                'amount'              => 850000,
                'payment_method_name' => 'QRIS',
                'paid_at'             => now(),
            ];

            Mail::to($request->test_email)
                ->send(new BookingPaymentConfirmed($booking, $payment));

            return back()->with('success', 'Email test berhasil dikirim ke ' . $request->test_email);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal kirim email: ' . $e->getMessage());
        }
    }

    private function defaultValue(string $key): ?string
    {
        return match ($key) {
            'mail_mailer'       => env('MAIL_MAILER', 'log'),
            'mail_host'         => env('MAIL_HOST', ''),
            'mail_port'         => env('MAIL_PORT', '587'),
            'mail_username'     => env('MAIL_USERNAME', ''),
            'mail_encryption'   => env('MAIL_SCHEME', 'tls'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', ''),
            'mail_from_name'    => env('MAIL_FROM_NAME', config('app.name')),
            default             => null,
        };
    }
}
