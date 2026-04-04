<?php

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
    ->middleware(['auth', 'role:Super Admin|Admin Operasional|Admin Marketing'])
    ->group(function () {
        Route::view('/', 'backend.dashboard')->name('dashboard');

        Route::get('/users', function () {
            return 'Halaman manajemen user (butuh permission: manage_users)';
        })->middleware('permission:manage_users')->name('users.index');

        Route::get('/reports', function () {
            return 'Halaman laporan (butuh permission: view_reports)';
        })->middleware('permission:view_reports')->name('reports.index');
    });

Route::middleware(['auth', 'role:Wisatawan'])->group(function () {
    Route::get('/booking-ticket', function () {
        return 'Halaman booking tiket (butuh permission: booking_ticket)';
    })->middleware('permission:booking_ticket')->name('ticket.booking');
});
