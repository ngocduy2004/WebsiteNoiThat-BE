<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AttributeController;

use App\Http\Controllers\BannerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProducSaleController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\Product_StoreController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\JWTAuthenticate;
use App\Http\Middleware\RoleMiddleware;





// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::get('/dashboard/admin', function () {
    return ['message' => 'Chào admin!'];
})->middleware(['jwt.auth', 'role:admin']);
Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);


// BẢNG PRODUCT


// Route::middleware([
//     JWTAuthenticate::class,
//     RoleMiddleware::class . ':admin'
// ])->group(function () {

//     Route::get('/dashboard/admin', function () {
//         return response()->json(['message' => 'Chào admin']);
//     });

//     Route::apiResource('products', ProductController::class);
//     Route::apiResource('categories', CategoryController::class);
//     Route::apiResource('orders', OrderController::class);
//     Route::apiResource('users', UserController::class);
// });
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);
Route::get('/products-new', [ProductController::class, 'product_new']);
Route::delete('/product-images/{id}', [ProductController::class, 'deleteImage']);


// BẢNG CATEGORY
Route::get('/categories/{slug}/products', [CategoryController::class, 'products']);
Route::get('categories/tree', [CategoryController::class, 'tree']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::put('/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);



// BẢNG POST
Route::get('/post', [PostController::class, 'index']);
Route::post('/post', [PostController::class, 'store']);
Route::get('/post/{id}', [PostController::class, 'show']);
Route::put('/post/{id}', [PostController::class, 'update']);
Route::delete('/post/{id}', [PostController::class, 'destroy']);



// BẢNG CONTACT
Route::get('/contact', [ContactController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store']);
Route::get('/contact/{id}', [ContactController::class, 'show']);
Route::put('/contact/{id}', [ContactController::class, 'update']);
Route::delete('/contact/{id}', [ContactController::class, 'destroy']);


// BẢNG MENU
Route::get('/menu', [MenuController::class, 'index']);
Route::post('/menu', [MenuController::class, 'store']);
Route::get('/menu/{id}', [MenuController::class, 'show']);
Route::put('/menu/{id}', [MenuController::class, 'update']);
Route::delete('/menu/{id}', [MenuController::class, 'destroy']);


// BẢNG BANNER
Route::get('/banner-active', [BannerController::class, 'active']);
Route::get('/banner', [BannerController::class, 'index']);
Route::post('/banner', [BannerController::class, 'store']);
Route::get('/banner/{id}', [BannerController::class, 'show']);
Route::put('/banner/{id}', [BannerController::class, 'update']);
Route::delete('/banner/{id}', [BannerController::class, 'destroy']);





Route::get('/product-image', [ProductImageController::class, 'index']);


// BẢNG PRODUCT_SALE
Route::get('/product-sale', [ProducSaleController::class, 'index']); // GET /api/product-sale
Route::post('/product-sale', [ProducSaleController::class, 'store']);
Route::get('/product-sale/{id}', [ProducSaleController::class, 'show']);
Route::put('/product-sale/{id}', [ProducSaleController::class, 'update']);
Route::delete('/product-sale/{id}', [ProducSaleController::class, 'destroy']);
// Lấy sản phẩm đang khuyến mãi
Route::get('products-sale', [ProductController::class, 'getSaleProducts']);


// BẢNG TOPIC
Route::get('/topic', [TopicController::class, 'index']);
Route::post('/topic', [TopicController::class, 'store']);
Route::get('/topic/{id}', [TopicController::class, 'show']);
Route::put('/topic/{id}', [TopicController::class, 'update']);
Route::delete('/topic/{id}', [TopicController::class, 'destroy']);




// BẢNG SETTING
Route::get('/setting', [SettingController::class, 'index']);
Route::post('/setting', [SettingController::class, 'store']);
Route::get('/setting/{id}', [SettingController::class, 'show']);
Route::put('/setting/{id}', [SettingController::class, 'update']);
Route::delete('/setting/{id}', [SettingController::class, 'destroy']);

// BẢNG ORDER
Route::get('/order', [OrderController::class, 'index']);
Route::post('/order', [OrderController::class, 'store']);
Route::get('/order/{id}', [OrderController::class, 'show']);
Route::put('/order/{id}', [OrderController::class, 'update']);
Route::delete('/order/{id}', [OrderController::class, 'destroy']);
Route::put('/order/cancel/{id}', [OrderController::class, 'cancel']);

// BẢNG USER
Route::get('/users', [UserController::class, 'index']);      // Lấy danh sách
Route::post('/users', [UserController::class, 'store']);     // Tạo mới
Route::get('/users/{id}', [UserController::class, 'show']);  // Xem chi tiết
Route::put('/users/{id}', [UserController::class, 'update']); // Cập nhật (Khớp với frontend của bạn)
Route::delete('/users/{id}', [UserController::class, 'destroy']); // Xóa


Route::get('/promotion', [PromotionController::class, 'index']);
Route::post('/promotion', [PromotionController::class, 'store']);



// BẢNG PRODUCT_STORE
Route::get('/product_store', [Product_StoreController::class, 'index']);
Route::post('/product_store', [Product_StoreController::class, 'store']);
Route::get('/product_store/{id}', [Product_StoreController::class, 'show']);
Route::put('product_store/{id}', [Product_StoreController::class, 'update']);
Route::delete('/product_store/{id}', [Product_StoreController::class, 'destroy']);

// BẢNG ATTRIBUTE
Route::middleware('jwt.auth')->group(function () {
    Route::get('get-cart', [CartController::class, 'getCart']);
    Route::post('add-to-cart', [CartController::class, 'addToCart']);
    Route::post('update-cart', [CartController::class, 'updateCart']);
    Route::delete('remove-item/{product_id}', [CartController::class, 'removeItem']);
});

// Đảm bảo route này nằm TRONG group jwt.auth
Route::middleware('jwt.auth')->group(function () {
    Route::get('get-cart', [CartController::class, 'getCart']);
    Route::post('add-to-cart', [CartController::class, 'addToCart']);
    Route::post('update-cart', [CartController::class, 'updateCart']);
    Route::delete('remove-item/{product_id}', [CartController::class, 'removeItem']);
    Route::delete('clear-cart', [CartController::class, 'clearCart']);

    // Di chuyển vào đây để Laravel hiểu auth()->user() là ai
    Route::post('merge-cart', [CartController::class, 'mergeCart']);
});

// Route cho Dashboard Stats
Route::get('dashboard/stats', [DashboardController::class, 'stats']);


Route::get('vnpay-return', [App\Http\Controllers\OrderController::class, 'vnpayReturn']);




//cart
// // routes/api.php
// Route::post('/cart/add', [CartController::class, 'addToCart'])
//     ->middleware('auth:sanctum');