<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});
Route::post('login_admin', [AuthController::class,'login_admin'])->name('login_admin');
Route::get('/test-invoice', [InvoiceController::class, 'exportTxt']);
Route::get('/dashboard_admin', [AdminController::class, 'index'])->name('dashboard_admin');
Route::get('users',[AdminController::class,'get_users'])->name('users');

Route::get('/export-real-invoice', function () {
    // الترتيب حسب إعدادات الأمين: الرمز ثم Tab ثم الكمية ثم Tab ثم رقم الوحدة ثم Tab ثم السعر
    
    // مادة 1: تمر سري اكسترا
    $item1 = "93322039\t1.00\t2\t103000.00"; 
    
    // مادة 2: تمر خلاص
    $item2 = "0113016\t1.00\t2\t84000.00";
    
    // مادة 3: كابتشينو (توب)
    $item3 = "11112\t1.00\t1\t13000.00";

    // دمج الأسطر بفاصل سطر جديد متوافق مع ويندوز
    $content = $item1 . "\r\n" . $item2 . "\r\n" . $item3;

    $fileName = 'ameen_real_invoice.txt';

    return response()->streamDownload(function () use ($content) {
        // نستخدم UTF-8 مع BOM لضمان قراءة اللغة العربية بشكل صحيح في بعض نسخ الأمين القديمة
        echo "\xEF\xBB\xBF"; 
        echo $content;
    }, $fileName, [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
});

Route::get('/activated/{id}',[AuthController::class,'activated']);
