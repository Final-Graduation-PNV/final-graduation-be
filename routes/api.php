<?php

use App\Http\Controllers\API\Admin\CategoryController;
use App\Http\Controllers\API\Admin\HandleShopOwnerController;
use App\Http\Controllers\API\AllRole\AllRoleController;
use App\Http\Controllers\API\Authentication\AuthController;
use App\Http\Controllers\API\ShopOwner\ProductController;
use App\Http\Controllers\API\ShopOwner\ShopOwnerController;
use App\Http\Controllers\API\User\CartController;
use App\Http\Controllers\API\User\GetProductController;
use App\Http\Controllers\API\User\PaymentController;
use App\Http\Controllers\API\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * Group sign-up and sign-in user.
 *
 */
Route::controller(AuthController::class)->group(function () {
    Route::post('/register','register');
    Route::post('/users/{id}/verify','verifyEmail');
    Route::post('/users/resend-otp','resendOTP');
    Route::post('/users/{id}','cancel');
    Route::post('/login','login');
});

/**
 * Group all account can handle.
 *
 */
Route::controller(AllRoleController::class)->group(function () {
    Route::get('/get-shop', 'getAllShopOwner');
    Route::get('/categories','getCategories');
    Route::get('/payment','vnpayPayment');
    Route::get('/return','vnpayReturn')->name('return');
});

/**
 * Private authors routes.
 *
 */
Route::group(['middleware' => ['auth:sanctum']], function () {
    /**
     * Group for user.
     *
     */
    Route::group(['middleware' => ['role:user']], function () {
        Route::controller(GetProductController::class)->group(function () {
            Route::get('/user/products/search/key','searchKey');                // Search products by product name, product description, category name and user city
            Route::get('/user/products/search/city-cate','searchCityCate');     // Search products by category name and user city
            Route::get('/user/products','index');                               // Get all products
            Route::get('/user/products/{id}','getById');                        // Get detail products
        });

        Route::controller(UserController::class)->group(function () {
            Route::get('/user/profile','profile');
            Route::patch('/user/profile','editProfile');
            Route::post('/user/be-shop','beShopOwner');   // Register as a shop owner
        });

        /**
         * Group crud cart.
         *
         */
        Route::controller(CartController::class)->group(function () {
            Route::post('/user/products/{id}/carts','add');
            Route::get('/user/carts','getCartByUserId');
            Route::patch('/user/carts/{id}','updateQuantity');
            Route::delete('/user/carts/{id}','deleteCart');
            Route::delete('/user/carts','deleteMany');
            Route::delete('/user/clear-carts','clear');
        });

        /**
         *  Group payment.
         *
         */
        Route::controller(PaymentController::class)->group(function () {
            Route::patch('/user/detail-payment','showAmount');
            Route::patch('/user/payment','payment');
        });
    });

    /**
     * Group for shop owner.
     *
     */
    Route::group(['middleware' => ['role:shop']], function () {
        /**
         *  Group search and crud product.
         *
         */
        Route::controller(ProductController::class)->group(function () {
            Route::get('/shop/products/search','search');
            Route::post('/shop/products','create');
            Route::put('/shop/products/{id}','update');
            Route::get('/shop/products','index');
            Route::get('/shop/products/{id}','getById');
            Route::delete('/shop/products/{id}','destroy');
        });

        /**
         *  Group payment.
         *
         */
        Route::controller(ShopOwnerController::class)->group(function () {
            Route::patch('/shop/check','checkoutAccount');
            Route::get('/shop/vnpay/create','checkoutPayment');
        });
    });

    /**
     * Group for admin.
     *
     */
    Route::group(['middleware' => ['role:admin']], function () {
        /**
         * CRUD category.
         *
         */
        Route::controller(CategoryController::class)->group(function () {
            Route::get('/admin/categories/{id}','getById');
            Route::post('/admin/categories','create');
            Route::patch('/admin/categories/{id}','update');
            Route::delete('/admin/categories/{id}','destroy');
        });

        /**
         * Notification account renewal.
         *
         */
        Route::get('/admin/shops', [HandleShopOwnerController::class, 'notificationShopOwnerAccount']);
    });

    /**
     * Post for sign out.
     *
     */
    Route::post('/logout', [AuthController::class, 'logout']);
});
