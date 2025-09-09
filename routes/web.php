<?php

use App\Http\Controllers\Admin\BillsExportController;
use App\Http\Controllers\Admin\ContactExportController;
use App\Http\Controllers\Admin\ReservationsExportController;
use App\Http\Controllers\Admin\RoomsExportController;
use App\Http\Controllers\Admin\UsersExportController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Fnb\CartController as FnbCartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\User\InvoiceController;
use App\Livewire\Admin\AvailabilityCalendar;
use App\Livewire\Admin\ContactMessages as AdminContactMessages;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\FacilityManagement;
use App\Livewire\Admin\GalleryManagement;
use App\Livewire\Admin\GuestManagement;
use App\Livewire\Admin\HousekeepingManagement;
use App\Livewire\Admin\PaymentsReview;
// use App\Livewire\Admin\RoomImages; // legacy, images now managed per room type
use App\Livewire\Admin\PromoManagement;
use App\Livewire\Admin\Reporting;
use App\Livewire\Admin\ReservationManagement;
use App\Livewire\Admin\RoomItemsManagement;
use App\Livewire\Admin\RoomManagement;
// use App\Livewire\Public\RoomsList;
// use App\Livewire\Public\RoomDetail;
use App\Livewire\Admin\RoomTypeImages;
use App\Livewire\Admin\RoomTypeManagement;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\BookingWizard;
use App\Livewire\Fnb\CashierDashboard as FnbCashierDashboard;
use App\Livewire\Fnb\MenuManagement as FnbMenuManagement;
use App\Livewire\Public\Gallery as PublicGallery;
use App\Livewire\Public\RestaurantMenu;
use App\Livewire\User\Bills as UserBills;
use App\Livewire\User\Dashboard as UserDashboard;
use App\Livewire\User\Profile as UserProfile;
use App\Livewire\User\ReservationDetail as UserReservationDetail;
use App\Livewire\User\Reservations as UserReservations;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/email/verify-new', [EmailVerificationController::class, 'verifyNew'])->name('verification.new');
Route::get('/email/verify-current', [EmailVerificationController::class, 'verifyCurrent'])->name('verification.current');
Route::get('/email/resend', [EmailVerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:3,1'])
    ->name('verification.resend');

Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
Route::get('/booking', function () {
    return redirect()->route('booking.hotel');
})->name('booking');
Route::get('/booking/{room}/confirm', function (\App\Models\Room $room) {
    return redirect()->route('booking.hotel', ['room' => $room->id] + request()->only(['checkin', 'checkout']));
})->name('booking.confirm');
// New preferred URL/name
Route::get('/booking-hotel', BookingWizard::class)->middleware('auth')->name('booking.hotel');
// Backward compatible redirect from old URL
Route::get('/booking-wizard', function () {
    return redirect()->route('booking.hotel', request()->all());
});
// Halaman daftar/ detail kamar publik dinonaktifkan sesuai permintaan
// Route::get('/rooms', RoomsList::class)->name('rooms');
// Route::get('/rooms/{room}', RoomDetail::class)->name('rooms.detail');
Route::get('/gallery', PublicGallery::class)->name('gallery');
// Allow guests to browse the menu (checkout still requires auth in component)
Route::get('/menu', RestaurantMenu::class)->name('menu');
Route::post('/fnb/cart/add', [FnbCartController::class, 'add'])->middleware('auth')->name('fnb.cart.add');

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::prefix('admin')->name('admin.')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/usermanagement', UserManagement::class)->name('user.management');
        Route::get('/users/export', [UsersExportController::class, 'csv'])->name('users.export');
        Route::get('/roommanagement', RoomManagement::class)->name('room.management');
        // Legacy route: redirect to room type images manager
        Route::get('/rooms/{room}/images', function (\App\Models\Room $room) {
            return redirect()->route('admin.room-type.images', ['type' => $room->room_type_id]);
        })->name('room.images');
        Route::get('/room-types/{type}/images', RoomTypeImages::class)->name('room-type.images');
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
        Route::get('/gallery', GalleryManagement::class)->name('gallery');
        // Inventori barang per kamar dipindah ke /admin/inventory
        Route::get('/inventory', RoomItemsManagement::class)->name('inventory');
    });

    // Kasir / F&B management
    Route::prefix('cashier')->name('cashier.')->middleware('role:superadmin,cashier')->group(function () {
        Route::get('/fnb', FnbCashierDashboard::class)->name('fnb.orders');
        Route::get('/fnb/menu', FnbMenuManagement::class)->name('fnb.menu');
    });

    Route::prefix('user')->name('user.')->middleware('role:user,users')->group(function () {
        Route::get('/dashboard', UserDashboard::class)->name('dashboard');
        Route::get('/bills', UserBills::class)->name('bills');
        Route::get('/bills/{bill}/invoice', [InvoiceController::class, 'download'])->name('bills.invoice');
        Route::get('/bills/{bill}/proof', [InvoiceController::class, 'proof'])->name('bills.proof');
        Route::get('/profile', UserProfile::class)->name('profile');
        Route::get('/reservations', UserReservations::class)->name('reservations');
        Route::get('/reservations/{reservation}', UserReservationDetail::class)->name('reservations.show');
        Route::get('/fnb/orders', \App\Livewire\User\FnbOrders::class)->name('fnb.orders');
    });
});
