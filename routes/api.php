<?php

use App\Http\Controllers\API\Admin\CategoryController;
use App\Http\Controllers\API\Admin\HandleShopOwnerController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ShopOwner\ProductController;
use App\Http\Controllers\API\ShopOwner\ShopOwnerController;
use App\Http\Controllers\API\User\CartController;
use App\Http\Controllers\API\User\GetProductController;
use App\Http\Controllers\API\User\PaymentController;
use App\Http\Controllers\API\User\UserController;
use App\Http\Controllers\API\VerificationController;
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
 * Sign-up and sign-in user.
 *
 */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/email/resend-otp', [AuthController::class, 'reregister']);
Route::post('/email/verify-otp/{id}', [VerificationController::class, 'verifyOTP']);
Route::post('/email/logout-otp/{id}', [VerificationController::class, 'destroy']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/categories', [AuthController::class, 'category']);
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
        Route::get('/user/products/search/key', [GetProductController::class, 'searchKey']);   // Search products by product name, product description, category name and user city
        Route::get('/user/products/search/city-cate', [GetProductController::class, 'searchCityCate']);   // Search products by category name and user city
        Route::get('/user/products', [GetProductController::class, 'index']);           // Get all products
        Route::get('/user/products/{id}', [GetProductController::class, 'getById']);    // Get detail products
        Route::post('/user/be-shop', [UserController::class, 'beShopOwner']);           // Register as a shop owner
        /**
         * CRUD cart.
         *
         */
        Route::post('/user/carts/{id}', [CartController::class, 'add']);
        Route::get('/user/carts', [CartController::class, 'getCartByUserId']);
        Route::patch('/user/carts/{id}', [CartController::class, 'updateQuantity']);
        Route::delete('/user/carts/{id}', [CartController::class, 'deleteCart']);
        Route::delete('/user/carts', [CartController::class, 'deleteMany']);
        Route::delete('/user/clear-carts', [CartController::class, 'clear']);
        /**
         *  Payment.
         *
         */
        Route::patch('/user/detail-payment', [PaymentController::class, 'showAmount']);
        Route::patch('/user/payment', [PaymentController::class, 'payment']);
    });

    /**
     * Group for shop owner.
     *
     */
    Route::group(['middleware' => ['role:shop']], function () {
        Route::post('/shop/products', [ProductController::class, 'create']);
        Route::put('/shop/products/{id}', [ProductController::class, 'update']);
        Route::get('/shop/products', [ProductController::class, 'index']);
        Route::get('/shop/products/{id}', [ProductController::class, 'getById']);
        Route::get('/shop/search', [ProductController::class, 'search']);
        Route::delete('/shop/products/{id}', [ProductController::class, 'destroy']);
        Route::get('/shop/check', [ShopOwnerController::class, 'checkoutAccount']);
        Route::get('/shop/vnpay/create', [ShopOwnerController::class, 'checkoutPayMent']);
        Route::get('/shop/vnpay/payment', [ShopOwnerController::class, 'vnpayPayment']);
        Route::get('/shop/vnpay/return', [ShopOwnerController::class, 'vnpayReturn'])->name('return');
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
        Route::get('/admin/categories', [CategoryController::class, 'index']);
        Route::get('/admin/categories/{id}', [CategoryController::class, 'getById']);
        Route::post('/admin/categories', [CategoryController::class, 'create']);
        Route::patch('/admin/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy']);
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
