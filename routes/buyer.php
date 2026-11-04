<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Buyer\DashboardController as BuyerDashboardController;
use App\Http\Controllers\Buyer\ShowProductDetails_Controller as BuyerProductController;
use App\Http\Controllers\Buyer\CategoryController as BuyerCategoryController;
use App\Http\Controllers\Buyer\CartController as BuyerCartController;
use App\Http\Controllers\Buyer\CheckoutController as BuyerCheckoutController;
use App\Http\Controllers\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Buyer\BuyerProfileController;
use App\Http\Controllers\Buyer\FavoriteController as BuyerFavoriteController;
use App\Http\Controllers\ShopChatController;


/*
|--------------------------------------------------------------------------
| Buyer Catalog / Product Browsing
|--------------------------------------------------------------------------
|
| These routes intentionally do NOT use approved.role:buyer.
|
| Reason:
| - Catalog/category/product pages are browsing pages.
| - Admin/Seller/Logistics sessions may open a product/category while testing.
| - Applying the Buyer role middleware here caused an unnecessary 403.
|
| Buyer-private operations remain protected further below.
|
*/

Route::prefix('buyer')
    ->name('buyer.')
    ->group(function () {

        Route::get('/categories', [
            BuyerCategoryController::class,
            'index',
        ])->name('category.index');

        Route::get('/category/{category}', [
            BuyerCategoryController::class,
            'show',
        ])->name('category.show');

        Route::get('/product/{product}', [
            BuyerProductController::class,
            'show',
        ])->name('product.show');
    });


/*
|--------------------------------------------------------------------------
| Buyer-Only Routes
|--------------------------------------------------------------------------
|
| These pages/actions are tied to the authenticated Buyer's own data and
| therefore remain protected.
|
| Requirements:
| - logged in
| - buyer account
| - email verified
| - administrator approved
|
*/

Route::prefix('buyer')
    ->name('buyer.')
    ->middleware([
        'auth',
        'approved.role:buyer',
    ])
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Buyer Dashboard
        |--------------------------------------------------------------------------
        */

        // Buyer lands on the same marketplace as guests, with authenticated
        // wishlist/cart/account actions. Other private Buyer pages stay intact.
        Route::get('/dashboard', [
            \App\Http\Controllers\HomeController::class,
            'index',
        ])->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | My Likes / Favorites
        |--------------------------------------------------------------------------
        */

        Route::get('/likes', [
            BuyerFavoriteController::class,
            'index',
        ])->name('likes');

        Route::post('/likes/{product}', [
            BuyerFavoriteController::class,
            'store',
        ])->whereNumber('product')->name('likes.store');

        Route::delete('/likes/{product}', [
            BuyerFavoriteController::class,
            'destroy',
        ])->whereNumber('product')->name('likes.destroy');


        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */

        Route::get('/cart', [
            BuyerCartController::class,
            'index',
        ])->name('cart');

        Route::post('/cart/add', [
            BuyerCartController::class,
            'add',
        ])->name('cart.add');

        Route::post('/cart/buy-now', [
            BuyerCartController::class,
            'buyNow',
        ])->name('cart.buyNow');

        Route::patch('/cart/{lineKey}', [
            BuyerCartController::class,
            'update',
        ])->name('cart.update');

        Route::delete('/cart/{lineKey}', [
            BuyerCartController::class,
            'remove',
        ])->name('cart.remove');

        Route::post('/cart/remove-many', [
            BuyerCartController::class,
            'removeMany',
        ])->name('cart.removeMany');

        Route::get('/cart/count', [
            BuyerCartController::class,
            'count',
        ])->name('cart.count');


        /*
        |--------------------------------------------------------------------------
        | Checkout
        |--------------------------------------------------------------------------
        */

        Route::get('/cart/checkout', [
            BuyerCheckoutController::class,
            'index',
        ])->name('cart.checkout');

        Route::post('/checkout/place-order', [
            BuyerCheckoutController::class,
            'placeOrder',
        ])->name('checkout.place');


        /*
        |--------------------------------------------------------------------------
        | My Orders
        |--------------------------------------------------------------------------
        */

        Route::get('/orders', [
            BuyerOrderController::class,
            'index',
        ])->name('orders');

        Route::post('/orders/{order}/confirm-receipt', [
            BuyerOrderController::class,
            'confirmReceipt',
        ])->name('orders.confirm-receipt');


        /*
        |--------------------------------------------------------------------------
        | Messages
        |--------------------------------------------------------------------------
        */

        Route::get('/messages', function () {
            return view('buyer.messages.index');
        })->name('messages');


        /*
        |--------------------------------------------------------------------------
        | Buyer Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', function () {
            return view('buyer.buyer-profile-settings');
        })->name('profile');

        Route::patch('/settings/address', [BuyerProfileController::class, 'updateAddress'])
            ->name('settings.address.update');

        Route::patch('/settings/profile', [
            BuyerProfileController::class,
            'update',
        ])->name('settings.profile.update');
    });


/*
|--------------------------------------------------------------------------
| Buyer/Seller Live Chat
|--------------------------------------------------------------------------
|
| Intentionally OUTSIDE the buyer-only group above: both Buyers and Sellers
| use this chat, so it only requires a logged-in user, not approved.role:buyer.
| Conversations are protected by hasParticipant() in ShopChatController.
|
*/

Route::middleware('auth')
    ->prefix('chat')
    ->name('chat.')
    ->group(function () {

        Route::get('/messages-inbox', [ShopChatController::class, 'page'])->name('page');

        Route::post('/open', [ShopChatController::class, 'open'])->name('open');

        Route::get('/conversations', [ShopChatController::class, 'conversations'])->name('conversations');

        Route::get('/{chat}/messages', [ShopChatController::class, 'messages'])
            ->whereNumber('chat')->name('messages');

        Route::post('/{chat}/send', [ShopChatController::class, 'send'])
            ->whereNumber('chat')->middleware('throttle:30,1')->name('send');
    });