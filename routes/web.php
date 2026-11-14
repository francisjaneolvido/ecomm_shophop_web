<?php

use App\Http\Controllers\Auth\BuyerEmailVerificationController;
use App\Http\Controllers\Auth\BuyerRegistrationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SellerRegistrationController;
use App\Http\Controllers\CatalogRevisionController;
use App\Http\Controllers\HelpCenterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HybridFinanceController;
use App\Http\Controllers\PublicSearchController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ShopHop Web Routes
|--------------------------------------------------------------------------
| Ang mga branch routes ay nasa routes/branch.php na.
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/catalog/revision', CatalogRevisionController::class)
    ->middleware('throttle:60,1')->name('catalog.revision');

Route::get('/search/suggestions', [PublicSearchController::class, 'suggestions'])
    ->middleware('throttle:120,1')->name('search.suggestions');
Route::get('/help', [HelpCenterController::class, 'index'])->name('help.index');

Route::get('/search', [PublicSearchController::class, 'index'])
    ->name('search.index');
Route::get('/shop/{seller}', [ShopController::class, 'show'])->whereNumber('seller')->name('shop.show');

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
    return redirect()->route('register');
})->name('create-account');

/*
|--------------------------------------------------------------------------
| Future Shop Routes
|--------------------------------------------------------------------------
| Wala pang CategoryController/ProductController sa App\Http\Controllers.
| I-import ulit kapag nagawa na.
*/

Route::redirect('/categories', '/search', 301);

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
    'show',
])->name('buyer.verify-email.show');

Route::post('/buyer/verify-email', [
    BuyerEmailVerificationController::class,
    'verify',
])->middleware('throttle:20,1')->name('buyer.verify-email.verify');

Route::post('/buyer/verify-email/resend', [
    BuyerEmailVerificationController::class,
    'resend',
])
    ->middleware('throttle:6,1')
    ->name('buyer.verify-email.resend');

// Shared financial workspace; controller enforces current role, partner and facility ownership.
Route::middleware('auth:web,rider')->prefix('financial-workspace')->name('hybrid.')->group(function () {
    Route::get('/discrepancies', [\App\Http\Controllers\CodDiscrepancyController::class, 'index'])->name('discrepancies.index');
    Route::post('/discrepancies/{case}/notes', [\App\Http\Controllers\CodDiscrepancyController::class, 'note'])->whereNumber('case')->name('discrepancies.note');
    Route::post('/discrepancies/{case}/proposals', [\App\Http\Controllers\CodDiscrepancyController::class, 'propose'])->whereNumber('case')->name('discrepancies.propose');
    Route::post('/discrepancies/{case}/resolutions/{resolution}/review', [\App\Http\Controllers\CodDiscrepancyController::class, 'review'])->whereNumber('case')->whereNumber('resolution')->name('discrepancies.review');
    Route::post('/discrepancies/{case}/resolutions/{resolution}/return', [\App\Http\Controllers\CodDiscrepancyController::class, 'recordReturn'])->whereNumber('case')->whereNumber('resolution')->name('discrepancies.return');
    Route::post('/platform-rules', [HybridFinanceController::class, 'platformRule'])->name('platform-rule');
    Route::get('/', [HybridFinanceController::class, 'index'])->name('index');
    Route::post('/batches', [HybridFinanceController::class, 'submit'])->name('submit');
    Route::post('/batches/{batch}/verify', [HybridFinanceController::class, 'verify'])->whereNumber('batch')->name('verify');
    Route::post('/policies', [HybridFinanceController::class, 'policy'])->name('policy');
    Route::post('/payroll', [HybridFinanceController::class, 'payroll'])->name('payroll');
    Route::post('/earnings/{earning}/approve', [HybridFinanceController::class, 'approve'])->whereNumber('earning')->name('approve');
    Route::post('/earnings/{earning}/paid', [HybridFinanceController::class, 'paid'])->whereNumber('earning')->name('paid');
});
