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
use App\Livewire\Admin\PaymentsReview;
use App\Livewire\Admin\ContactMessages as AdminContactMessages;
use App\Http\Controllers\Admin\BillsExportController;
use App\Http\Controllers\Admin\ReservationsExportController;
use App\Http\Controllers\Admin\UsersExportController;
use App\Http\Controllers\Admin\RoomsExportController;
use App\Livewire\Admin\PromoManagement;
use App\Livewire\Admin\RoomImages;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\BookingWizard;
use App\Livewire\Public\RoomsList;
use App\Livewire\Public\RoomDetail;
use Illuminate\Support\Facades\Route;
use App\Livewire\User\Dashboard as UserDashboard;
use App\Livewire\User\Bills as UserBills;
use App\Livewire\User\Profile as UserProfile;
use App\Livewire\User\Reservations as UserReservations;
use App\Livewire\User\ReservationDetail as UserReservationDetail;
use App\Http\Controllers\User\InvoiceController;
use App\Http\Controllers\Admin\ContactExportController;

Route::get('/', fn () => view('welcome'));
Route::get('/email/verify-new', [EmailVerificationController::class, 'verifyNew'])->name('verification.new');
Route::get('/email/verify-current', [EmailVerificationController::class, 'verifyCurrent'])->name('verification.current');
Route::get('/email/resend', [EmailVerificationController::class, 'resend'])->middleware('auth')->name('verification.resend');

Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
Route::get('/booking', function(){
    return redirect()->route('booking.wizard');
})->name('booking');
Route::get('/booking/{room}/confirm', function(\App\Models\Rooms $room){
    return redirect()->route('booking.wizard', ['room'=>$room->id] + request()->only(['checkin','checkout']));
})->name('booking.confirm');
Route::get('/booking-wizard', BookingWizard::class)->name('booking.wizard');
Route::get('/rooms', RoomsList::class)->name('rooms');
Route::get('/rooms/{room}', RoomDetail::class)->name('rooms.detail');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [Login::class, 'logout'])->name('logout');

    // pastikan middleware 'role' sudah terdaftar, atau hapus jika belum pakai Spatie
    Route::prefix('admin')->name('admin.')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/usermanagement', UserManagement::class)->name('user.management');
        Route::get('/users/export', [UsersExportController::class, 'csv'])->name('users.export');
        Route::get('/roommanagement', RoomManagement::class)->name('room.management');
        Route::get('/rooms/{room}/images', RoomImages::class)->name('room.images');
        Route::get('/rooms/export', [RoomsExportController::class, 'csv'])->name('rooms.export');
        Route::get('/guestmanagement', GuestManagement::class)->name('guest.management');
        Route::get('/reservationmanagement', ReservationManagement::class)->name('reservation.management');
        Route::get('/reservations/export', [ReservationsExportController::class, 'csv'])->name('reservations.export');
        Route::get('/availability-calendar', AvailabilityCalendar::class)->name('availability.calendar');
        Route::get('/calendar-events', [AvailabilityCalendar::class, 'getCalendarEvents'])->name('calendar.events');
        Route::get('/room-type-management', RoomTypeManagement::class)->name('room-type.management');
        Route::get('/facility-management', FacilityManagement::class)->name('facility.management');
        Route::get('/housekeeping', HousekeepingManagement::class)->name('housekeeping.management');
        Route::get('/reporting', Reporting::class)->name('reporting');
        Route::get('/payments', PaymentsReview::class)->name('payments');
        Route::get('/payments/export', [BillsExportController::class, 'csv'])->name('payments.export');
        Route::get('/contacts', AdminContactMessages::class)->name('contacts');
        Route::get('/contacts/export', [ContactExportController::class, 'csv'])->name('contacts.export');
        Route::get('/promos', PromoManagement::class)->name('promos');
    });

    // Dashboard pengguna (role: user/users)
    Route::prefix('user')->name('user.')->middleware('role:user,users')->group(function () {
        Route::get('/dashboard', UserDashboard::class)->name('dashboard');
        Route::get('/bills', UserBills::class)->name('bills');
        Route::get('/bills/{bill}/invoice', [InvoiceController::class, 'download'])->name('bills.invoice');
        Route::get('/profile', UserProfile::class)->name('profile');
        Route::get('/reservations', UserReservations::class)->name('reservations');
        Route::get('/reservations/{reservation}', UserReservationDetail::class)->name('reservations.show');
    });
});
