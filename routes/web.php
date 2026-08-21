<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

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