<?php

use App\Http\Controllers\API\Admin\CategoryController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ShopOwner\ProductController;
use App\Http\Controllers\API\User\CartController;
use App\Http\Controllers\API\User\GetProductController;
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
 * Sign-up and sign-in user.
 *
 */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

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
        Route::get('/user/products/search', [GetProductController::class, 'search']);   // Search products by product name, product description and user city
        Route::post('/user/be-shop', [UserController::class, 'beShopOwner']);           // Register as a shop owner
        Route::get('/user/products', [GetProductController::class, 'index']);           // Get all products
        Route::get('/user/products/{id}', [GetProductController::class, 'getById']);    // Get detail products
        /**
         * CRUD cart.
         *
         */
        Route::post('/user/product/carts', [CartController::class, 'add']);
        Route::get('/user/carts', [CartController::class, 'getCartByUserId']);
        Route::patch('/user/carts/{id}', [CartController::class, 'updateQuantity']);
        Route::delete('/user/carts/{id}', [CartController::class, 'deleteCart']);
        Route::delete('/user/carts', [CartController::class, 'deleteMany']);
        Route::delete('/user/clear-carts', [CartController::class, 'clear']);
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
        Route::get('/shop/products/search/{name}', [ProductController::class, 'search']);
        Route::delete('/shop/products/{id}', [ProductController::class, 'destroy']);
    });

    /**
     * Group for admin.
     *
     */
    Route::group(['middleware' => ['role:admin']], function () {
        Route::get('/admin/categories', [CategoryController::class, 'index']);
        Route::get('/admin/categories/{id}', [CategoryController::class, 'getById']);
        Route::post('/admin/categories', [CategoryController::class, 'create']);
        Route::patch('/admin/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy']);
    });

    /**
     * Post for sign out.
     *
     */
    Route::post('/logout', [AuthController::class, 'logout']);
});
