<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;

use App\Http\Controllers\Auth\BuyerRegistrationController;
use App\Http\Controllers\Auth\SellerRegistrationController;
use App\Http\Controllers\Auth\LoginController;


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

Route::get('/register', function () {
    return redirect()->route('home')->with('open_modal', 'account-type');
})->name('register');

Route::post('/register', [BuyerRegistrationController::class, 'store'])
    ->name('register.store');

Route::post('/seller/register', [SellerRegistrationController::class, 'store'])
    ->name('seller.register.store');

Route::get('/login', function () {
    return redirect()->route('home')->with('open_modal', 'login');
})->name('login');

Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store');

Route::post('/logout', [LoginController::class, 'destroy'])
    ->name('logout');

Route::get('/create-account', function () {
    return view('auth.create-account');
})->name('create-account');


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


Route::get('/buyer/store/{slug?}', function ($slug = null) {
    return view('buyer.store.show');
})->name('buyer.store.show');