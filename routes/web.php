<?php

use App\Http\Controllers\Admin\ActivityTagController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\MitraController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductSlotController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Frontend\BookingController as FrontendBookingController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Models\Product;
use App\Http\Controllers\Payment\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /login',
        'Disallow: /register',
        'Disallow: /logout',
        'Disallow: /profile',
        'Disallow: /booking-ticket',
        'Disallow: /booking',
        'Disallow: /payment',
        'Sitemap: ' . route('sitemap'),
    ];

    return response(implode("\n", $lines) . "\n", 200)
        ->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots');

Route::get('/sitemap.xml', function () {
    $staticPages = collect([
        [
            'loc' => route('frontend.home'),
            'lastmod' => now()->toDateString(),
            'changefreq' => 'daily',
            'priority' => '1.0',
        ],
        [
            'loc' => route('frontend.about'),
            'lastmod' => now()->toDateString(),
            'changefreq' => 'monthly',
            'priority' => '0.6',
        ],
        [
            'loc' => route('frontend.wisata'),
            'lastmod' => now()->toDateString(),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ],
    ]);

    $products = Product::query()
        ->where('is_active', true)
        ->whereHas('category', fn ($query) => $query->where('type', 'internal'))
        ->orderByDesc('updated_at')
        ->get(['slug', 'updated_at'])
        ->map(function (Product $product) {
            return [
                'loc' => route('frontend.wisata.show', ['slug' => $product->slug]),
                'lastmod' => optional($product->updated_at)->toDateString() ?? now()->toDateString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        });

    return response()
        ->view('sitemap.xml', ['urls' => $staticPages->concat($products)])
        ->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

// ── Auth ──────────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('frontend.home');
Route::view('/about', 'frontend.about')->name('frontend.about');
Route::get('/wisata', [HomeController::class, 'wisata'])->name('frontend.wisata');
Route::get('/wisata/{slug}', [HomeController::class, 'wisataDetail'])->name('frontend.wisata.show');
Route::view('/welcome', 'welcome')->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('frontend.profile');
    Route::get('/profile/orders', [ProfileController::class, 'orders'])
        ->name('frontend.orders');
    Route::patch('/profile', [ProfileController::class, 'updateProfile'])
        ->name('frontend.profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('frontend.profile.password.update');
    Route::patch('/profile/reviews/{review}', [ProfileController::class, 'updateReview'])
        ->name('frontend.profile.reviews.update');
    Route::get('/booking-ticket', [FrontendBookingController::class, 'index'])
        ->name('ticket.booking');
    Route::get('/booking-ticket/product/{slug}', [FrontendBookingController::class, 'product'])
        ->name('ticket.booking.product');
    Route::get('/booking-ticket/product/{slug}/calendar', [FrontendBookingController::class, 'productCalendar'])
        ->name('ticket.booking.product.calendar');
    Route::get('/booking-ticket/availability', [FrontendBookingController::class, 'availability'])
        ->name('ticket.booking.availability');
    Route::get('/booking-ticket/estimate', [FrontendBookingController::class, 'estimate'])
        ->name('ticket.booking.estimate');
    Route::post('/booking-ticket/checkout', [FrontendBookingController::class, 'checkout'])
        ->name('ticket.booking.checkout');
    Route::get('/booking-ticket/status/{token}', [FrontendBookingController::class, 'status'])
        ->name('ticket.booking.status');
    Route::get('/booking-ticket/status/{token}/invoice', [FrontendBookingController::class, 'invoice'])
        ->name('ticket.booking.invoice');
    Route::post('/booking-ticket/status/{token}/reviews', [FrontendBookingController::class, 'storeReview'])
        ->name('ticket.booking.review.store');
    Route::post('/booking-ticket/status/{token}/resume-payment', [FrontendBookingController::class, 'resumePayment'])
        ->name('ticket.booking.resume-payment');
    Route::post('/booking-ticket/status/{token}/sync-payment', [FrontendBookingController::class, 'syncPaymentStatus'])
        ->name('ticket.booking.sync-payment');
    Route::get('/booking/finish', [FrontendBookingController::class, 'finish'])
        ->name('ticket.booking.finish');
});

// Debug routes hanya untuk local development.
if (app()->environment('local')) {
    Route::get('/debug/midtrans-key', function () {
        $serverKey = \App\Models\PaymentSetting::get('midtrans_server_key', '');
        $clientKey = \App\Models\PaymentSetting::get('midtrans_client_key', '');
        return response()->json([
            'server_key_length' => strlen($serverKey),
            'server_key_prefix' => substr($serverKey, 0, 15),
            'server_key_suffix' => substr($serverKey, -5),
            'client_key_length' => strlen($clientKey),
            'client_key_prefix' => substr($clientKey, 0, 15),
            'is_production'     => \App\Models\PaymentSetting::get('midtrans_is_production'),
        ]);
    });

    Route::prefix('/debug/errors')->group(function () {
        Route::get('/419', fn () => response()->view('errors.419', [], 419))->name('debug.errors.419');
        Route::get('/404', fn () => response()->view('errors.404', [], 404))->name('debug.errors.404');
        Route::get('/500', fn () => response()->view('errors.500', [], 500))->name('debug.errors.500');
        Route::get('/503', fn () => response()->view('errors.503', [], 503))->name('debug.errors.503');
    });
}

// Webhook Midtrans — no auth, no CSRF (server-to-server)
Route::post('/payment/webhook/midtrans', [MidtransWebhookController::class, 'handle'])
    ->name('payment.webhook.midtrans');

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin.access', 'role:Super Admin|Mitra'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::redirect('/dashboard', '/admin');

        // ── Pengaturan Sistem (manage_users) ──────────────────────────────────
        Route::middleware('permission:manage_users')->group(function () {
            Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::resource('users', UserController::class)->except(['show']);
            Route::resource('roles', RoleController::class)->except(['show']);

            Route::get('/settings/payment', [PaymentSettingController::class, 'index'])->name('settings.payment');
            Route::put('/settings/payment', [PaymentSettingController::class, 'update'])->name('settings.payment.update');
            Route::post('/settings/payment/test', [PaymentSettingController::class, 'testConnection'])->name('settings.payment.test');

            Route::get('/settings/mail', [MailSettingController::class, 'index'])->name('settings.mail');
            Route::put('/settings/mail', [MailSettingController::class, 'update'])->name('settings.mail.update');
            Route::post('/settings/mail/test', [MailSettingController::class, 'sendTest'])->name('settings.mail.test');

            Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
            Route::get('/payment-methods/create', [PaymentMethodController::class, 'create'])->name('payment-methods.create');
            Route::post('/payment-methods', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
            Route::post('/payment-methods/reorder', [PaymentMethodController::class, 'reorder'])->name('payment-methods.reorder');
            Route::get('/payment-methods/{id}/edit', [PaymentMethodController::class, 'edit'])->name('payment-methods.edit');
            Route::put('/payment-methods/{id}', [PaymentMethodController::class, 'update'])->name('payment-methods.update');
            Route::post('/payment-methods/{id}/toggle', [PaymentMethodController::class, 'toggleActive'])->name('payment-methods.toggle');
            Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
        });

        // ── Produk & Layanan (manage_products) ────────────────────────────────
        Route::middleware('permission:manage_products')->group(function () {
            Route::resource('products', ProductController::class)->except(['show']);
            Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
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
            Route::post('/slots/bulk-update', [ProductSlotController::class, 'bulkUpdate'])->name('slots.bulk-update');
            Route::post('/slots/bulk-destroy', [ProductSlotController::class, 'bulkDestroy'])->name('slots.bulk-destroy');
            Route::get('/slots/{id}/edit', [ProductSlotController::class, 'edit'])->name('slots.edit');
            Route::put('/slots/{id}', [ProductSlotController::class, 'update'])->name('slots.update');
            Route::delete('/slots/{id}', [ProductSlotController::class, 'destroy'])->name('slots.destroy');

            Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews.index');
            Route::post('/reviews/bulk-toggle', [ReviewController::class, 'bulkToggle'])->name('reviews.bulk-toggle');
            Route::get('/reviews/{id}', [ReviewController::class, 'show'])->name('reviews.show');
            Route::post('/reviews/{id}/toggle', [ReviewController::class, 'togglePublish'])->name('reviews.toggle');
            Route::delete('/reviews/{id}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        });

        // ── Mitra (manage_users|manage_products) ──────────────────────────────
        Route::middleware('permission:manage_users|manage_products')->group(function () {
            Route::resource('mitra', MitraController::class)->parameters(['mitra' => 'mitra'])->except(['show']);
            Route::patch('/mitra/{mitra}/status', [MitraController::class, 'updateStatus'])->name('mitra.status');
        });

        // ── Promosi (manage_products) ─────────────────────────────────────────
        Route::middleware('permission:manage_products')->group(function () {
            Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
            Route::get('/promotions/create', [PromotionController::class, 'create'])->name('promotions.create');
            Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');
            Route::get('/promotions/{id}/edit', [PromotionController::class, 'edit'])->name('promotions.edit');
            Route::put('/promotions/{id}', [PromotionController::class, 'update'])->name('promotions.update');
            Route::post('/promotions/{id}/toggle', [PromotionController::class, 'toggleActive'])->name('promotions.toggle');
            Route::delete('/promotions/{id}', [PromotionController::class, 'destroy'])->name('promotions.destroy');
            Route::get('/promo-types', [PromotionController::class, 'types'])->name('promo-types.index');
        });

        // ── Laporan (view_reports) ────────────────────────────────────────────
        Route::middleware('permission:view_reports')->group(function () {
            Route::get('/reports', [ReportController::class, 'sales'])->name('reports.index');
            Route::get('/reports/commissions', [ReportController::class, 'commissions'])->name('reports.commissions');
            Route::get('/reports/products', [ReportController::class, 'products'])->name('reports.products');
        });

        // ── Transaksi (manage_transactions) ──────────────────────────────────
        Route::middleware('permission:manage_transactions')->group(function () {

            // Komisi — static routes dulu sebelum {id}
            Route::get('/commissions', [CommissionController::class, 'index'])->name('commissions.index');
            Route::get('/commissions/tiers', [CommissionController::class, 'tiers'])->name('commissions.tiers');
            Route::post('/commissions/tiers', [CommissionController::class, 'storeTier'])->name('commissions.tiers.store');
            Route::post('/commissions/bulk-settle', [CommissionController::class, 'bulkSettle'])->name('commissions.bulk-settle');
            Route::put('/commissions/tiers/{id}', [CommissionController::class, 'updateTier'])->name('commissions.tiers.update');
            Route::delete('/commissions/tiers/{id}', [CommissionController::class, 'destroyTier'])->name('commissions.tiers.destroy');
            Route::get('/commissions/{id}', [CommissionController::class, 'show'])->name('commissions.show');
            Route::post('/commissions/{id}/settle', [CommissionController::class, 'settle'])->name('commissions.settle');
            Route::post('/commissions/{id}/cancel', [CommissionController::class, 'cancel'])->name('commissions.cancel');

            // Booking
            Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
            Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
            Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
            Route::get('/bookings/{id}', [BookingController::class, 'show'])->name('bookings.show');
            Route::get('/bookings/{id}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
            Route::put('/bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');
            Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.update-status');

            // Payment
            Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('/payments/{id}', [PaymentController::class, 'show'])->name('payments.show');
            Route::post('/payments/{id}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
            Route::post('/payments/{id}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
            Route::get('/payments/{id}/check-gateway', [PaymentController::class, 'checkGateway'])->name('payments.check-gateway');
        });
    });
