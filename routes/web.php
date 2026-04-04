<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::view('/welcome', 'welcome')->name('welcome');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:Super Admin|Mitra'])
    ->group(function () {
        Route::view('/', 'backend.dashboard')->name('dashboard');

        Route::redirect('/dashboard', '/admin');

        Route::middleware('permission:manage_users')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
            Route::resource('roles', RoleController::class)->except(['show']);
        });

        Route::get('/reports', function () {
            return 'Halaman laporan (butuh permission: view_reports)';
        })->middleware('permission:view_reports')->name('reports.index');
    });

// Customer adalah guest (tidak login)
Route::get('/booking-ticket', function () {
    return 'Halaman booking tiket (customer/guest).';
})->name('ticket.booking');
