<?php

use App\Http\Controllers\dashboard\AuthController;
use App\Http\Controllers\dashboard\BrandController;
use App\Http\Controllers\dashboard\CategoryController;
use App\Http\Controllers\dashboard\CustomerController;
use App\Http\Controllers\dashboard\ProductController;
use App\Http\Controllers\dashboard\StaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

###### auth admin #####
Route::prefix('admin')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout');
    Route::post('update-password', 'updatePassword');
});

Route::prefix('customers')->middleware(['auth:sanctum'])->group(function () {
    Route::post('add-customer', [CustomerController::class, 'createCustomer']);
    Route::post('update-customer/{id}', [CustomerController::class, 'updateCustomer']);
    Route::get('customers', [CustomerController::class, 'listing']);
    Route::get('view-customer/{id}', [CustomerController::class, 'viewCustomer']);
    Route::get('delete-customer/{id}', [CustomerController::class, 'deleteCustomer']);
});

Route::prefix('brands')->middleware(['auth:sanctum'])->group(function () {
    Route::post('add-brand', [BrandController::class, 'createBrand']);
    Route::post('update-brand/{id}', [BrandController::class, 'updateBrand']);
    Route::get('brands', [BrandController::class, 'listing']);
    Route::get('view-brand/{id}', [BrandController::class, 'viewBrand']);
    Route::get('delete-brand/{id}', [BrandController::class, 'deleteBrand']);
});

Route::prefix('categories')->middleware(['auth:sanctum'])->group(function () {
    Route::post('add-category', [CategoryController::class, 'createCategory']);
    Route::post('update-category/{id}', [CategoryController::class, 'updateCategory']);
    Route::get('categories', [CategoryController::class, 'listing']);
    Route::get('view-category/{id}', [CategoryController::class, 'viewCategory']);
    Route::get('delete-category/{id}', [CategoryController::class, 'deleteCategory']);
});

Route::prefix('staff')->middleware(['auth:sanctum'])->group(function () {
    Route::post('add-staff', [StaffController::class, 'createStaff']);
    Route::post('update-staff/{id}', [StaffController::class, 'updateStaff']);
    // Route::get('staff', [StaffController::class, 'listing']);
    Route::get('view-staff/{id}', [StaffController::class, 'viewStaff']);
    Route::get('delete-staff/{id}', [StaffController::class, 'deleteStaff']);
});

Route::prefix('products')->middleware(['auth:sanctum'])->group(function () {
    Route::post('add-product', [ProductController::class, 'createProduct']);
    Route::post('update-product/{id}', [ProductController::class, 'updateSProduct']);
    Route::get('products', [ProductController::class, 'listing']);
    Route::get('view-product/{id}', [ProductController::class, 'viewProduct']);
    Route::get('delete-product/{id}', [ProductController::class, 'deleteProduct']);
});