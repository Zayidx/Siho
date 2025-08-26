<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\RoomManagement;
use App\Livewire\Admin\GuestManagement;
use App\Livewire\Admin\ReservationManagement;
use App\Livewire\Admin\AvailabilityCalendar;
use App\Livewire\Admin\RoomTypeManagement;
use App\Livewire\Admin\FacilityManagement;
use App\Livewire\Admin\HousekeepingManagement;
use App\Livewire\Admin\Reporting;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Booking;
use App\Livewire\BookingConfirmation;
use Illuminate\Support\Facades\Route;
use App\Livewire\User\Dashboard as UserDashboard;

Route::get('/', fn () => view('welcome'));

Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
Route::get('/booking', Booking::class)->name('booking');
Route::get('/booking/{room}/confirm', BookingConfirmation::class)->name('booking.confirm');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [Login::class, 'logout'])->name('logout');

    // pastikan middleware 'role' sudah terdaftar, atau hapus jika belum pakai Spatie
    Route::prefix('admin')->name('admin.')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/usermanagement', UserManagement::class)->name('user.management');
        Route::get('/roommanagement', RoomManagement::class)->name('room.management');
        Route::get('/guestmanagement', GuestManagement::class)->name('guest.management');
        Route::get('/reservationmanagement', ReservationManagement::class)->name('reservation.management');
        Route::get('/availability-calendar', AvailabilityCalendar::class)->name('availability.calendar');
        Route::get('/calendar-events', [AvailabilityCalendar::class, 'getCalendarEvents'])->name('calendar.events');
        Route::get('/room-type-management', RoomTypeManagement::class)->name('room-type.management');
        Route::get('/facility-management', FacilityManagement::class)->name('facility.management');
        Route::get('/housekeeping', HousekeepingManagement::class)->name('housekeeping.management');
        Route::get('/reporting', Reporting::class)->name('reporting');
    });

    // Dashboard pengguna (role: user/users)
    Route::prefix('user')->name('user.')->middleware('role:user,users')->group(function () {
        Route::get('/dashboard', UserDashboard::class)->name('dashboard');
    });
});
