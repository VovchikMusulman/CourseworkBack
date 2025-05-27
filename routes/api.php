<?php
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::middleware('auth:sanctum')->get('/check-auth', function (Request $request) {
    return response()->json(['authenticated' => true]);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/categories', [CategoryController::class, 'store']);
//Route::put('/categories/{category}', [CategoryController::class, 'update']);
//Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

Route::post('/products', [ProductController::class, 'store']);
//Route::put('/products/{product}', [ProductController::class, 'update']);
//Route::delete('/products/{product}', [ProductController::class, 'destroy']);

//Route::get('/all-orders', [OrderController::class, 'allOrders']);
//Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
//Route::get('/products/category/{category}', [ProductController::class, 'productsByCategory']);
Route::get('/products/search/{query}', [ProductController::class, 'search']);
//Route::get('/reviews/product/{product}', [ReviewController::class, 'index']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Cart routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{cartItem}', [CartController::class, 'remove']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);

    // Admin routes
    Route::middleware('admin')->group(function () {
    });
});
