<?php

use App\Http\Controllers\dashboard\AuthController;
use Illuminate\Http\Request;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

###### auth admin #####
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout');
    Route::post('add-customer', 'createCustomer');
    Route::post('update-customer/{id}', 'updateCustomer');
    Route::get('customers', 'listing');
    Route::get('view-customer/{id}', 'viewCustomer');
});
