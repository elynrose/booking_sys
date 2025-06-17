<?php

use App\Http\Controllers\Admin\CategoryController;

Route::view('/', 'welcome');
Route::get('userVerification/{token}', 'UserVerificationController@approve')->name('userVerification');
Auth::routes();

// Check-in routes (public access for basic checkin)
Route::get('/checkin', [App\Http\Controllers\Frontend\CheckinController::class, 'index'])->name('frontend.checkins.index');
Route::match(['get', 'post'], '/checkin/verify', [App\Http\Controllers\Frontend\CheckinController::class, 'verify'])->name('frontend.checkins.verify');

// Check-in routes that require user role
Route::middleware(['auth', '2fa'])->group(function () {
    Route::get('/checkin/checkin/{booking}', [App\Http\Controllers\Frontend\CheckinController::class, 'showCheckin'])->name('frontend.checkins.show');
    Route::post('/checkin/checkin', [App\Http\Controllers\Frontend\CheckinController::class, 'checkin'])->name('frontend.checkins.checkin');
    Route::post('/checkin/checkout', [App\Http\Controllers\Frontend\CheckinController::class, 'checkout'])->name('frontend.checkins.checkout');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Admin', 'middleware' => ['auth', '2fa', 'admin']], function () {
    Route::get('/', 'HomeController@index')->name('home');
    // Permissions
    Route::delete('permissions/destroy', 'PermissionsController@massDestroy')->name('permissions.massDestroy');
    Route::resource('permissions', 'PermissionsController');

    // Roles
    Route::delete('roles/destroy', 'RolesController@massDestroy')->name('roles.massDestroy');
    Route::resource('roles', 'RolesController');

    // Users
    Route::delete('users/destroy', 'UsersController@massDestroy')->name('users.massDestroy');
    Route::resource('users', 'UsersController');

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

    // Trainer routes
    Route::resource('trainers', App\Http\Controllers\Admin\TrainerController::class);

    // Category routes
    Route::resource('categories', CategoryController::class);
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
    Route::post('frontend/profile', 'ProfileController@update')->name('profile.update');
    Route::post('frontend/profile/destroy', 'ProfileController@destroy')->name('profile.destroy');
    Route::post('frontend/profile/password', 'ProfileController@password')->name('profile.password');
    Route::post('profile/toggle-two-factor', 'ProfileController@toggleTwoFactor')->name('profile.toggle-two-factor');

    // Children routes
    Route::resource('children', App\Http\Controllers\Frontend\ChildController::class);

    // Payment routes
    Route::get('/payments', [App\Http\Controllers\Frontend\PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [App\Http\Controllers\Frontend\PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [App\Http\Controllers\Frontend\PaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [App\Http\Controllers\Frontend\PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/edit', [App\Http\Controllers\Frontend\PaymentController::class, 'edit'])->name('payments.edit');
    Route::put('/payments/{payment}', [App\Http\Controllers\Frontend\PaymentController::class, 'update'])->name('payments.update');
    Route::delete('/payments/{payment}', [App\Http\Controllers\Frontend\PaymentController::class, 'destroy'])->name('payments.destroy');
    Route::get('/bookings/payment/success', [App\Http\Controllers\Frontend\PaymentController::class, 'success'])->name('bookings.payment.success');

    // Trainer routes
    Route::middleware(['auth', 'role:trainer'])->group(function () {
        Route::get('/trainer', [App\Http\Controllers\Frontend\TrainerController::class, 'index'])->name('trainer.index');
        Route::post('/trainer/payments/{payment}/confirm', [App\Http\Controllers\Frontend\TrainerController::class, 'confirmPayment'])->name('trainer.confirm-payment');
    });
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

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Schedule Routes
    Route::resource('schedules', App\Http\Controllers\Admin\ScheduleController::class);
    
    // Booking Routes
    Route::resource('bookings', App\Http\Controllers\Admin\BookingController::class);

    // Payment routes
    Route::resource('payments', App\Http\Controllers\Admin\PaymentController::class);

    // Trainer routes
    Route::resource('trainers', App\Http\Controllers\Admin\TrainerController::class);

    // Category routes
    Route::resource('categories', CategoryController::class);
});
