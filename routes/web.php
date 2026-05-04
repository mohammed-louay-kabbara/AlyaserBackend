<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\warehousecontroller;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;

// React App Route (SPA)
Route::get('/app/{any?}', function () {
    return file_get_contents(public_path('react-app/index.html'));
})->where('any', '.*');

// مسارات متاحة للجميع (بدون تسجيل دخول)
Route::get('/', function () {
    return view('welcome');
})->name('login');

Route::post('login_admin', [AuthController::class, 'login_admin'])->name('login_admin');
Route::post('/forgot-password', [AdminController::class, 'forgot_password'])->name('forgot-password');
Route::post('/logout_web', [AuthController::class, 'logout_web'])->name('logout_web');

// --- المسارات المحمية ---
// أي مسار داخل هذه المجموعة سيتطلب تسجيل الدخول
Route::middleware(['auth:web'])->group(function () {
    // Warehouse User Routes (role=3) - must come before dynamic route
    Route::get('/warehouse/dashboard', [warehousecontroller::class, 'dashboard'])->name('warehouse.dashboard');
    Route::post('/warehouse/orders/{id}/ready', [warehousecontroller::class, 'markAsReady'])->name('warehouse.markAsReady');
    Route::get('/warehouse/print/{id}', [warehousecontroller::class, 'printOrder'])->name('warehouse.print');
    
    Route::get('/dashboard_admin', [AdminController::class, 'index'])->name('dashboard_admin');
    // رابط لتجربة تصدير الفاتورة رقم 1 كمثال
    Route::get('users', [AdminController::class, 'get_users'])->name('users');
    Route::post('/admin/users/bulk-toggle-status', [AdminController::class, 'bulkToggleStatus']);
    Route::post('add_order', [OrderController::class,'store'])->name('add_order');
    Route::resource('Category', CategoryController::class);
    Route::resource('warehouse', warehousecontroller::class);
    Route::get('warehouse/{id}', [warehousecontroller::class, 'show_orders'])->name('warehouse.orders');
    
    Route::get('Notifications', [NotificationController::class, 'Notification'])->name('Notifications');
    Route::get('notifications/{id}', [NotificationController::class, 'userNotifications'])->name('user.notifications');
    Route::post('warehouse/notifications', [NotificationController::class, 'sendWarehouseNotification'])->name('warehouse.notifications');
    Route::post('sendNotification', [NotificationController::class, 'sendNotification'])->name('Notifications.send');
    Route::get('categories', [CategoryController::class, 'show_admin'])->name('categories');
    Route::get('products', [ProductController::class, 'product_admin'])->name('Products');
    Route::post('/products/sync-ameen', [ProductController::class, 'syncWithAmeen'])->name('products.sync_ameen');
    Route::get('Product-search', [ProductController::class, 'search_admin'])->name('Product-search');
    Route::get('orders_user/{id}', [OrderController::class, 'orders_user'])->name('orders_user');
    Route::post('/admin/products/{id}/upload-image', [ProductController::class, 'uploadImage']);
    Route::delete('/admin/products/{id}/delete-image', [ProductController::class, 'deleteImage']);
    Route::get('offers', [OfferController::class, 'offer_admin'])->name('offers');
    Route::post('Offer.store', [OfferController::class, 'store'])->name('Offer.store');
    Route::delete('Offer_destroy/{id}', [OfferController::class, 'destroy'])->name('Offer.destroy');
    Route::put('Offer_update/{id}', [OfferController::class, 'update'])->name('Offer.update');
    Route::get('/activated/{id}', [AuthController::class, 'activated']);
    Route::get('/get_order', [OrderController::class, 'get_order'])->name('get_order');
    Route::get('/exportExcel', [ProductController::class, 'exportExcel'])->name('products.export.excel');
    Route::get('/exportPdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf');
    Route::post('/sendToWarehouse/{id}', [OrderController::class, 'sendToWarehouse'])->name('Order.sendToWarehouse');
    Route::delete('/Order_destroy/{id}', [OrderController::class, 'destroy'])->name('Order.destroy');
    Route::resource('orders', OrderController::class)->only(['destroy', 'update']);
    // Route::delete('/Order_update/{id}', [OrderController::class, 'update'])->name('Order.update');
    Route::post('/orders/bulk-send', [OrderController::class, 'bulkSendToWarehouse'])->name('Order.bulkSend');
    Route::get('/orders/print', [OrderController::class, 'printOrders'])->name('Order.print');
    Route::put('/order_update/{id}', [OrderController::class, 'update'])->name('Order.update');
    // مسار تسجيل الخروج يجب أن يكون محمياً أيضاً
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// مسارات تجريبية (اختياري وضعها خارج أو داخل الحماية)
Route::get('/test-invoice', [InvoiceController::class, 'exportTxt']);
Route::get('/export-invoice/{id}', [OrderController::class, 'exportOrderToAmeenTxt']);



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
