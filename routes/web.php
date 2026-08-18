<?php
// Path in your project: routes/web.php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ShopHop Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

// Example routes for future pages — uncomment/build controllers as you go
// Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
// Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
// Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
// Route::get('/deals', [HomeController::class, 'deals'])->name('deals');