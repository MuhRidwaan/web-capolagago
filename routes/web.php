<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\MitraController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductSlotController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ActivityTagController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Frontend\BookingController as FrontendBookingController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Payment\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('frontend.home');
Route::view('/welcome', 'welcome')->name('welcome');

Route::get('/booking-ticket', [FrontendBookingController::class, 'index'])
    ->name('ticket.booking');
Route::get('/booking-ticket/availability', [FrontendBookingController::class, 'availability'])
    ->name('ticket.booking.availability');
Route::get('/booking-ticket/estimate', [FrontendBookingController::class, 'estimate'])
    ->name('ticket.booking.estimate');
Route::post('/booking-ticket/checkout', [FrontendBookingController::class, 'checkout'])
    ->name('ticket.booking.checkout');
Route::get('/booking-ticket/status/{token}', [FrontendBookingController::class, 'status'])
    ->name('ticket.booking.status');
Route::post('/booking-ticket/status/{token}/resume-payment', [FrontendBookingController::class, 'resumePayment'])
    ->name('ticket.booking.resume-payment');
Route::get('/booking/finish', [FrontendBookingController::class, 'finish'])
    ->name('ticket.booking.finish');

// Webhook Midtrans — no auth, no CSRF (server-to-server)
Route::post('/payment/webhook/midtrans', [MidtransWebhookController::class, 'handle'])
    ->name('payment.webhook.midtrans');

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:Super Admin|Mitra'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::redirect('/dashboard', '/admin');

        // Pengaturan Sistem (manage_users)
        Route::middleware('permission:manage_users')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
            Route::resource('roles', RoleController::class)->except(['show']);

            Route::get('/settings/payment', [PaymentSettingController::class, 'index'])->name('settings.payment');
            Route::put('/settings/payment', [PaymentSettingController::class, 'update'])->name('settings.payment.update');
            Route::post('/settings/payment/test', [PaymentSettingController::class, 'testConnection'])->name('settings.payment.test');

            Route::get('/settings/mail', [MailSettingController::class, 'index'])->name('settings.mail');
            Route::put('/settings/mail', [MailSettingController::class, 'update'])->name('settings.mail.update');
            Route::post('/settings/mail/test', [MailSettingController::class, 'sendTest'])->name('settings.mail.test');

            // Metode Pembayaran
            Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
            Route::get('/payment-methods/create', [PaymentMethodController::class, 'create'])->name('payment-methods.create');
            Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
            Route::get('/payment-methods/{id}/edit', [PaymentMethodController::class, 'edit'])->name('payment-methods.edit');
            Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
            Route::post('/payment-methods/{id}/toggle', [PaymentMethodController::class, 'toggleActive'])->name('payment-methods.toggle');
            Route::post('/payment-methods/reorder', [PaymentMethodController::class, 'reorder'])->name('payment-methods.reorder');
            Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
        });

        // Produk & Layanan (manage_products)
        Route::middleware('permission:manage_products')->group(function () {
            Route::resource('products', ProductController::class)->except(['show']);
            Route::resource('product-categories', ProductCategoryController::class)
                ->parameters(['product-categories' => 'productCategory'])
                ->except(['show']);
            Route::resource('activity-tags', ActivityTagController::class)
                ->parameters(['activity-tags' => 'activityTag'])
                ->except(['show']);

            Route::get('/slots', [ProductSlotController::class, 'index'])->name('slots.index');
            Route::get('/slots/create', [ProductSlotController::class, 'create'])->name('slots.create');
            Route::post('/slots', [ProductSlotController::class, 'store'])->name('slots.store');
            Route::post('/slots/generate', [ProductSlotController::class, 'generate'])->name('slots.generate');
            Route::get('/slots/{id}/edit', [ProductSlotController::class, 'edit'])->name('slots.edit');
            Route::put('/slots/{id}', [ProductSlotController::class, 'update'])->name('slots.update');
            Route::delete('/slots/{id}', [ProductSlotController::class, 'destroy'])->name('slots.destroy');
            Route::post('/slots/bulk-update', [ProductSlotController::class, 'bulkUpdate'])->name('slots.bulk-update');
            Route::post('/slots/bulk-destroy', [ProductSlotController::class, 'bulkDestroy'])->name('slots.bulk-destroy');
        });

        Route::middleware('permission:manage_users|manage_products')->group(function () {
            Route::resource('mitra', MitraController::class)->parameters(['mitra' => 'mitra'])->except(['show']);
            Route::patch('/mitra/{mitra}/status', [MitraController::class, 'updateStatus'])->name('mitra.status');
        });

        // Laporan (view_reports)
        Route::middleware('permission:view_reports')->group(function () {
            Route::get('/reports', [ReportController::class, 'sales'])->name('reports.index');
            Route::get('/reports/commissions', [ReportController::class, 'commissions'])->name('reports.commissions');
            Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');
        });

        // Booking Management (manage_transactions)
        Route::middleware('permission:manage_transactions')->group(function () {
            Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
            Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
            Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
            Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
            Route::get('/bookings/{id}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
            Route::put('/bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');
            Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');
        });

        // Payment Management (manage_transactions)
        Route::middleware('permission:manage_transactions')->group(function () {
            Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
            Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
            Route::post('/payments/{id}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
            Route::get('/payments/{id}/check-gateway', [PaymentController::class, 'checkGateway'])->name('payments.check-gateway');
        });
    });
