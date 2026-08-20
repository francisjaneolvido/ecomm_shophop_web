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