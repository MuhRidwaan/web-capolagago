<?php

namespace App\Providers;

use App\Models\MailSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();

        Gate::before(function ($user, string $ability) {
            return method_exists($user, 'hasRole') && $user->hasRole('Super Admin') ? true : null;
        });

        // Terapkan konfigurasi email dari DB (jika tabel sudah ada)
        try {
            MailSetting::applyToConfig();
        } catch (\Exception) {
            // Tabel belum ada saat migration pertama kali — abaikan
        }
    }
}
