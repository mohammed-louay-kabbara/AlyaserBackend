<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;




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




Route::get('/test-api', function () {
    return response()->json(['تم تعديل الصورة بنجاح'], 200);
});
