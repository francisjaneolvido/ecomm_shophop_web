<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Logistics\RegistrationController as LogisticsRegistrationController;
use App\Http\Controllers\Logistics\DashboardController as LogisticsDashboardController;
use App\Http\Controllers\Logistics\RiderController;
use App\Http\Controllers\Logistics\DeliveryController;
use App\Http\Controllers\Logistics\ReportController;


/*
|--------------------------------------------------------------------------
| ShopHop Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/register', [RegisterController::class, 'create'])
    ->name('register');

Route::post('/register', [RegisterController::class, 'store'])
    ->name('register.store');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

/*
|--------------------------------------------------------------------------
| Future Shop Routes
|--------------------------------------------------------------------------
*/

// Route::get('/categories', [CategoryController::class, 'index'])
//     ->name('categories.index');

// Route::get('/categories/{category}', [CategoryController::class, 'show'])
//     ->name('categories.show');

// Route::get('/products/{product}', [ProductController::class, 'show'])
//     ->name('products.show');

// Route::get('/deals', [HomeController::class, 'deals'])
//     ->name('deals');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/admin/registration', function () {
    return view('admin.registration');
})->name('admin.registrations');

Route::get('/admin/users', function () {
    return view('admin.user-accounts');
})->name('admin.users');

Route::get('/admin/seller-compliance', function () {
    return view('admin.seller-compliance');
})->name('admin.compliance');

Route::get('/admin/complaints-disputes', function () {
    return view('admin.complaints-disputes');
})->name('admin.disputes');

Route::get('/admin/commission', function () {
    return view('admin.commission');
})->name('admin.commission');

Route::get('/admin/reports', function () {
    return view('admin.reports');
})->name('admin.reports');

Route::get('/admin/chat', function () {
    return view('admin.chat-messaging');
})->name('admin.chat');

Route::get('/admin/settings', function () {
    return view('admin.platform-settings');
})->name('admin.settings');

Route::get('/admin/accounts', function () {
    return view('admin.account-management');
})->name('admin.accounts');

Route::get('/admin/logout', function () {
    return redirect('/');
})->name('admin.logout');


/*
|--------------------------------------------------------------------------
| Logistics Routes
|--------------------------------------------------------------------------
*/
// Public — anyone can apply to become a Logistics Partner.
Route::prefix('logistics-partner')->name('logistics.')->group(function () {
    Route::get('/apply', [LogisticsRegistrationController::class, 'create'])->name('register');
    Route::post('/apply', [LogisticsRegistrationController::class, 'store'])->name('register.store');
    Route::get('/terms', [LogisticsRegistrationController::class, 'terms'])->name('terms');
});

// Partner console. Auth muna disabled habang wala pang account/login system — IBALIK PAG READY NA.
Route::prefix('logistics-partner')
    ->name('logistics.')
    // ->middleware(['auth']) // TODO: i-uncomment 'to pag may account system na
    ->group(function () {
        Route::get('/dashboard', [LogisticsDashboardController::class, 'index'])->name('dashboard');

        Route::get('/riders', [RiderController::class, 'index'])->name('riders.index');
        Route::post('/riders/{rider}/approve', [RiderController::class, 'approve'])->name('riders.approve');
        Route::post('/riders/{rider}/disapprove', [RiderController::class, 'disapprove'])->name('riders.disapprove');
        Route::post('/riders/{rider}/suspend', [RiderController::class, 'suspend'])->name('riders.suspend');
        Route::post('/riders/{rider}/activate', [RiderController::class, 'activate'])->name('riders.activate');
        Route::post('/riders/{rider}/warn', [RiderController::class, 'warn'])->name('riders.warn');

        Route::get('/deliveries', [DeliveryController::class, 'board'])->name('deliveries.board');
        Route::post('/deliveries/{delivery}/assign', [DeliveryController::class, 'assign'])->name('deliveries.assign');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });



/*
|--------------------------------------------------------------------------
| Buyer Routes
|--------------------------------------------------------------------------
*/
Route::get('/buyer/profile', function () {
    return view('buyer.buyer-profile-settings');
    });

Route::patch('/buyer/settings/profile', [ProfileController::class, 'update'])->name('buyer.settings.profile.update');
Route::patch('/buyer/settings/address', [ProfileController::class, 'updateAddress'])->name('buyer.settings.address.update');
Route::patch('/buyer/settings/allergens', [ProfileController::class, 'updateAllergens'])->name('buyer.settings.allergens.update');
Route::patch('/buyer/settings/password', [ProfileController::class, 'updatePassword'])->name('buyer.settings.password.update');
Route::patch('/buyer/settings/notifications', [ProfileController::class, 'updateNotifications'])->name('buyer.settings.notifications.update');

