<?php

use App\Http\Controllers\dashboard\AuthController;
use App\Http\Controllers\dashboard\CustomerController;
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
