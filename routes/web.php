<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\WelcomeController;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('userVerification/{token}', 'UserVerificationController@approve')->name('userVerification');

// Authentication routes (temporarily without rate limiting for testing)
Auth::routes();

// Check-in routes (public access for basic checkin)
Route::middleware(['rate.limit:checkin'])->group(function () {
    Route::get('/checkin', [App\Http\Controllers\Frontend\CheckinController::class, 'index'])->name('frontend.checkins.index');
    Route::match(['get', 'post'], '/checkin/verify', [App\Http\Controllers\Frontend\CheckinController::class, 'verify'])->name('frontend.checkins.verify');
});

// Check-in routes that require user role
Route::middleware(['auth', '2fa'])->group(function () {
    // Redirect /checkin/checkin to /checkin/verify
    Route::get('/checkin/checkin/{booking}', function($booking) {
        return redirect()->route('frontend.checkins.verify');
    })->name('frontend.checkins.show');
    Route::post('/checkin/checkin', [App\Http\Controllers\Frontend\CheckinController::class, 'checkin'])->name('frontend.checkins.checkin');
    Route::post('/checkin/checkout', [App\Http\Controllers\Frontend\CheckinController::class, 'checkout'])->name('frontend.checkins.checkout');
    Route::post('/checkin/quick-checkout', [App\Http\Controllers\Frontend\CheckinController::class, 'quickCheckout'])->name('frontend.checkins.quick-checkout');
    Route::post('/checkin/auto-checkout', [App\Http\Controllers\Frontend\CheckinController::class, 'autoCheckout'])->name('frontend.checkins.auto-checkout');
    Route::get('/checkin/auto-checkout-success', [App\Http\Controllers\Frontend\CheckinController::class, 'autoCheckoutSuccess'])->name('frontend.checkins.auto-checkout-success');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth', '2fa']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/live-dashboard', 'DashboardController@liveDashboard')->name('live-dashboard');
    Route::get('/live-dashboard/data', 'DashboardController@liveDashboardData')->name('live-dashboard.data');
    Route::resource('users', 'UsersController');
    Route::post('/users/{user}/verify-email', [App\Http\Controllers\Admin\UsersController::class, 'verifyEmail'])->name('users.verify-email');
    Route::resource('roles', 'RolesController');
    Route::resource('permissions', 'PermissionsController');
    Route::resource('schedules', 'ScheduleController');
    Route::resource('trainers', 'TrainerController');
    Route::resource('bookings', 'BookingController');
    Route::post('/bookings/{booking}/mark-as-paid', [App\Http\Controllers\Admin\BookingController::class, 'markAsPaid'])->name('bookings.mark-as-paid');

    // Assign student routes
    Route::get('/trainers/{trainer}/assign-student', [App\Http\Controllers\Admin\TrainerController::class, 'showAssignStudentForm'])->name('trainers.assign-student');
    Route::post('/trainers/{trainer}/assign-student', [App\Http\Controllers\Admin\TrainerController::class, 'assignStudent'])->name('trainers.assign-student.store');

    // User Alerts
    Route::delete('user-alerts/destroy', 'UserAlertsController@massDestroy')->name('user-alerts.massDestroy');
    Route::get('user-alerts/read', 'UserAlertsController@read');
    Route::resource('user-alerts', 'UserAlertsController', ['except' => ['edit', 'update']]);

    Route::get('system-calendar', 'SystemCalendarController@index')->name('systemCalendar');
    Route::get('global-search', 'GlobalSearchController@search')->name('globalSearch');
    Route::get('messenger', 'MessengerController@index')->name('messenger.index');
    Route::get('messenger/create', 'MessengerController@createTopic')->name('messenger.createTopic');
    Route::post('messenger', 'MessengerController@storeTopic')->name('messenger.storeTopic');
    Route::get('messenger/inbox', 'MessengerController@showInbox')->name('messenger.showInbox');
    Route::get('messenger/outbox', 'MessengerController@showOutbox')->name('messenger.showOutbox');
    Route::get('messenger/{topic}', 'MessengerController@showMessages')->name('messenger.showMessages');
    Route::delete('messenger/{topic}', 'MessengerController@destroyTopic')->name('messenger.destroyTopic');
    Route::post('messenger/{topic}/reply', 'MessengerController@replyToTopic')->name('messenger.reply');
    Route::get('messenger/{topic}/reply', 'MessengerController@showReply')->name('messenger.showReply');

    // Payment routes
    Route::resource('payments', App\Http\Controllers\Admin\PaymentController::class);
    Route::post('/payments/{payment}/mark-as-paid', [App\Http\Controllers\Admin\PaymentController::class, 'markAsPaid'])->name('payments.mark-as-paid');

    // Category routes
    Route::resource('categories', CategoryController::class);

    // Trainer Availability routes
    Route::get('trainer-availability', [App\Http\Controllers\Admin\TrainerAvailabilityController::class, 'index'])->name('trainer-availability.index');
    Route::get('trainer-availability/{schedule}', [App\Http\Controllers\Admin\TrainerAvailabilityController::class, 'show'])->name('trainer-availability.show');
    Route::get('trainer-availability/{schedule}/calendar', [App\Http\Controllers\Admin\TrainerAvailabilityController::class, 'calendar'])->name('trainer-availability.calendar');
    Route::post('trainer-availability/{schedule}', [App\Http\Controllers\Admin\TrainerAvailabilityController::class, 'store'])->name('trainer-availability.store');
    Route::put('trainer-availability/{availability}', [App\Http\Controllers\Admin\TrainerAvailabilityController::class, 'update'])->name('trainer-availability.update');
    Route::delete('trainer-availability/{availability}', [App\Http\Controllers\Admin\TrainerAvailabilityController::class, 'destroy'])->name('trainer-availability.destroy');
    Route::post('trainer-availability/{schedule}/bulk-update', [App\Http\Controllers\Admin\TrainerAvailabilityController::class, 'bulkUpdate'])->name('trainer-availability.bulk-update');
    Route::post('trainer-availability/{schedule}/create-recurring', [App\Http\Controllers\Admin\TrainerAvailabilityController::class, 'createRecurring'])->name('trainer-availability.create-recurring');
    Route::get('trainer-availability/{schedule}/export', [App\Http\Controllers\Admin\TrainerAvailabilityController::class, 'export'])->name('trainer-availability.export');

    // AJAX: Get all availabilities for a trainer
    Route::get('trainer-availability/ajax/trainer-availabilities', [App\Http\Controllers\Admin\TrainerAvailabilityController::class, 'getTrainerAvailabilities'])->name('trainer-availability.ajax.trainer-availabilities');

    // Site Settings
    Route::get('site-settings', [App\Http\Controllers\Admin\SiteSettingsController::class, 'index'])->name('site-settings.index');
    Route::put('site-settings', [App\Http\Controllers\Admin\SiteSettingsController::class, 'update'])->name('site-settings.update');
});
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'namespace' => 'Auth', 'middleware' => ['auth', '2fa']], function () {
    // Change password
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', 'ChangePasswordController@edit')->name('password.edit');
        Route::post('password', 'ChangePasswordController@update')->name('password.update');
        Route::post('profile', 'ChangePasswordController@updateProfile')->name('password.updateProfile');
        Route::post('profile/destroy', 'ChangePasswordController@destroy')->name('password.destroyProfile');
        Route::post('profile/two-factor', 'ChangePasswordController@toggleTwoFactor')->name('password.toggleTwoFactor');
    }
});
Route::group(['as' => 'frontend.', 'namespace' => 'Frontend', 'middleware' => ['auth']], function () {
    Route::get('/home', 'HomeController@index')->name('home');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

    // User Alerts
    Route::delete('user-alerts/destroy', 'UserAlertsController@massDestroy')->name('user-alerts.massDestroy');
    Route::resource('user-alerts', 'UserAlertsController', ['except' => ['edit', 'update']]);

    // Schedules
    Route::resource('schedules', 'SchedulesController');

    // Contact
    // Route::post('contact', 'ContactController@store')->name('contact.store');

    Route::get('frontend/profile', 'ProfileController@index')->name('profile.index');
    Route::get('frontend/profile/edit', 'ProfileController@edit')->name('profile.edit');
    Route::post('frontend/profile', 'ProfileController@update')->name('profile.update');
    Route::post('frontend/profile/destroy', 'ProfileController@destroy')->name('profile.destroy');
    Route::post('frontend/profile/password', 'ProfileController@password')->name('profile.password');
    Route::post('profile/toggle-two-factor', 'ProfileController@toggleTwoFactor')->name('profile.toggle-two-factor');

    // Profile Settings (SMS Notifications)
    Route::get('/profile/settings', [App\Http\Controllers\Frontend\ProfileSettingsController::class, 'index'])->name('profile.settings');
    Route::put('/profile/settings', [App\Http\Controllers\Frontend\ProfileSettingsController::class, 'update'])->name('profile.settings.update');
    Route::put('/profile/settings/password', [App\Http\Controllers\Frontend\ProfileSettingsController::class, 'updatePassword'])->name('profile.settings.password');

    // Children routes
    Route::resource('children', App\Http\Controllers\Frontend\ChildController::class);

    // Recommendation routes
    Route::resource('recommendations', App\Http\Controllers\Frontend\RecommendationController::class);
    Route::delete('recommendations/attachments/{attachment}', [App\Http\Controllers\Frontend\RecommendationController::class, 'deleteAttachment'])->name('recommendations.delete-attachment');

    // Payment routes
    Route::get('/payments', [App\Http\Controllers\Frontend\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [App\Http\Controllers\Frontend\PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [App\Http\Controllers\Frontend\PaymentController::class, 'store'])->name('payments.store');
    Route::post('/payments/process', [App\Http\Controllers\Frontend\PaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/confirm', [App\Http\Controllers\Frontend\PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::get('/payments/success', [App\Http\Controllers\Frontend\PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/{payment}', [App\Http\Controllers\Frontend\PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/edit', [App\Http\Controllers\Frontend\PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{payment}', [App\Http\Controllers\Frontend\PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [App\Http\Controllers\Frontend\PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/bookings/payment/success', [App\Http\Controllers\Frontend\PaymentController::class, 'success'])->name('bookings.payment.success');


});

// Trainer routes (outside frontend group to avoid namespace conflicts)
Route::middleware(['auth', 'role:Trainer'])->group(function () {
    Route::get('/trainer', [App\Http\Controllers\Frontend\TrainerController::class, 'index'])->name('frontend.trainer.index');
    Route::get('/trainer/class/{schedule}', [App\Http\Controllers\Frontend\TrainerController::class, 'showClassDetails'])->name('frontend.trainer.class-details');
    Route::get('/trainer/student-details', [App\Http\Controllers\Frontend\TrainerController::class, 'showStudentDetails'])->name('frontend.trainer.student-details');
    // COMMENTED OUT: Trainer payment confirmation route
    // Route::post('/trainer/payments/{payment}/confirm', [App\Http\Controllers\Frontend\TrainerController::class, 'confirmPayment'])->name('frontend.trainer.confirm-payment');
    
    // Trainer Availability Management
    Route::resource('trainer/availability', 'Frontend\TrainerAvailabilityController', ['as' => 'frontend.trainer'])->except(['show']);
    Route::get('trainer/availability/calendar', [App\Http\Controllers\Frontend\TrainerAvailabilityController::class, 'calendar'])->name('frontend.trainer.availability.calendar');
    Route::post('trainer/availability/bulk-update', [App\Http\Controllers\Frontend\TrainerAvailabilityController::class, 'bulkUpdate'])->name('frontend.trainer.availability.bulk-update');
    Route::post('trainer/availability/create-recurring', [App\Http\Controllers\Frontend\TrainerAvailabilityController::class, 'createRecurring'])->name('frontend.trainer.availability.create-recurring');
});

// Booking routes (moved outside the frontend group)
Route::middleware(['auth'])->group(function () {
    Route::get('/bookings/create/{schedule}', [App\Http\Controllers\Frontend\BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings/{schedule}', [App\Http\Controllers\Frontend\BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings', [App\Http\Controllers\Frontend\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [App\Http\Controllers\Frontend\BookingController::class, 'show'])->name('bookings.show');
    Route::delete('/bookings/{booking}', [App\Http\Controllers\Frontend\BookingController::class, 'destroy'])->name('bookings.destroy');
});

Route::group(['namespace' => 'Auth', 'middleware' => ['auth', '2fa']], function () {
    // Two Factor Authentication
    if (file_exists(app_path('Http/Controllers/Auth/TwoFactorController.php'))) {
        Route::get('two-factor', 'TwoFactorController@show')->name('twoFactor.show');
        Route::post('two-factor', 'TwoFactorController@check')->name('twoFactor.check');
        Route::get('two-factor/resend', 'TwoFactorController@resend')->name('twoFactor.resend');
    }
});
