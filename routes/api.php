<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\CartItemController;
use App\Http\Controllers\ExchangeRateController;



Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('logout', [AuthController::class,'logout']);
    Route::post('refresh', [AuthController::class,'refresh']);
    Route::post('me', [AuthController::class,'me']);
});
 Route::post('login', [AuthController::class,'login']);
Route::post('register', [AuthController::class,'register']);

Route::resource('Product',ProductController::class);
Route::get('Product-search',[ProductController::class,'search']);
Route::resource('Category',CategoryController::class);
Route::resource('offers', OfferController::class);
Route::resource('CartItem',CartItemController::class);
Route::resource('ExchangeRate', ExchangeRateController::class);

