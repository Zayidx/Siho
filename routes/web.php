<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\RoomManagement;
use App\Livewire\Admin\GuestManagement;
use App\Livewire\Admin\ReservationManagement;
use App\Livewire\Auth\Login;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::get('/login', Login::class)->name('login');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [Login::class, 'logout'])->name('logout');

    // pastikan middleware 'role' sudah terdaftar, atau hapus jika belum pakai Spatie
    Route::prefix('admin')->name('admin.')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/usermanagement', UserManagement::class)->name('user.management');
        Route::get('/roommanagement', RoomManagement::class)->name('room.management');
        Route::get('/guestmanagement', GuestManagement::class)->name('guest.management');
        Route::get('/reservationmanagement', ReservationManagement::class)->name('reservation.management');
    });
});


