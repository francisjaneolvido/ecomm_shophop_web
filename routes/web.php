<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicSearchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;

use App\Http\Controllers\Auth\BuyerRegistrationController;
use App\Http\Controllers\Auth\SellerRegistrationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\BuyerEmailVerificationController;
use App\Http\Controllers\PaymentReceiptController;


/*
|--------------------------------------------------------------------------
| ShopHop Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/search', [PublicSearchController::class, 'index'])
    ->name('search.index');


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Bookmarked former registration URL now leads to the dedicated Logistics site.
Route::get('/logistics-partner/apply', function () {
    return redirect()->route('logistics.register');
})->name('logistics.legacy-apply');

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


// Retain public Store URLs as availability destinations; the legacy parameter has no defined public Seller lookup.
Route::get('/buyer/store/{slug?}', function ($slug = null) {
    return view('buyer.store.show');
})->name('buyer.store.show');


/*
|--------------------------------------------------------------------------
| Buyer Email Verification
|--------------------------------------------------------------------------
*/

Route::get('/buyer/verify-email', [
    BuyerEmailVerificationController::class,
    'show'
])->name('buyer.verify-email.show');


Route::post('/buyer/verify-email', [
    BuyerEmailVerificationController::class,
    'verify'
])->name('buyer.verify-email.verify');


Route::post('/buyer/verify-email/resend', [
    BuyerEmailVerificationController::class,
    'resend'
])
    ->middleware('throttle:6,1')
    ->name('buyer.verify-email.resend');

// Private receipts require a web identity and a second record-level Buyer/Admin check.
Route::get('/payment-receipts/{payment}', [PaymentReceiptController::class, 'show'])
    ->middleware('auth')->name('payments.receipt');
