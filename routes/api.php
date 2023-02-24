<?php

use App\Http\Controllers\API\Admin\CategoryController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ShopOwner\ProductController;
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
        Route::get('/user/products/search', [GetProductController::class, 'search']);
        Route::post('/user/be-shop', [UserController::class, 'beShopOwner']);
        Route::get('/user/products', [GetProductController::class, 'index']);
        Route::get('/user/products/{id}', [GetProductController::class, 'getById']);
    });

    /**
     * Group for shop owner.
     *
     */
    Route::group(['middleware' => ['role:shop']], function () {
        Route::post('/products', [ProductController::class, 'create']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'getById']);
        Route::get('/products/search/{name}', [ProductController::class, 'search']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
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
