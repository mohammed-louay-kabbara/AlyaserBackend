<?php







use Illuminate\Support\Facades\Route;



use App\Http\Controllers\AuthController;



use App\Http\Controllers\ProductController;



use App\Http\Controllers\CategoryController;



use App\Http\Controllers\OfferController;



use App\Http\Controllers\CartItemController;



use App\Http\Controllers\OrderController;



use App\Http\Controllers\ExchangeRateController;



use App\Http\Controllers\NotificationController;







use App\Http\Controllers\InvoiceController;



use App\Http\Controllers\AdminController;



use App\Http\Controllers\warehousecontroller;















Route::group([







    'middleware' => 'api',



    'prefix' => 'auth'







], function ($router) {







});







Route::post('logout', [AuthController::class,'logout']);



Route::get('addproduct', [ProductController::class,'addproduct']);



Route::post('fcm_token', [AuthController::class,'fcm_token']);



Route::post('sendPushNotification', [NotificationController::class,'sendPushNotification']);



Route::get('read_all', [NotificationController::class,'read_all']);



Route::get('my_Notification',[NotificationController::class,'my_Notification']);



Route::get('category_search/{id}',[ProductController::class,'category_search']);



Route::get('getSearchScreenData',[ProductController::class,'getSearchScreenData']);



Route::put('/orders/{orderId}/update', [OrderController::class, 'updateOrder']);



Route::post('refresh', [AuthController::class,'refresh']);



Route::get('me', [AuthController::class,'me']);



Route::post('login', [AuthController::class,'login']);



Route::post('register', [AuthController::class,'register']);



Route::post('admin_role', [AuthController::class,'admin']);



Route::resource('Product',ProductController::class);



Route::get('Product-search',[ProductController::class,'search']);



Route::resource('Category',CategoryController::class);



Route::resource('offers', OfferController::class);



Route::resource('CartItem',CartItemController::class);



Route::post('delete_CartItem/{id}',[CartItemController::class,'destroy']);



Route::post('CartItem_clear',[CartItemController::class,'clear']);



Route::resource('ExchangeRate', ExchangeRateController::class);



Route::get('get_exchange_rate', [ExchangeRateController::class,'get_exchange_rate']);



Route::post('add_order', [OrderController::class,'store']);



Route::get('get_order', [OrderController::class,'index']);



Route::get('order_details/{id}', [OrderController::class,'Order_details']);



Route::post('/profile/update', [AuthController::class, 'updateProfile']);



Route::get('/activated/{id}',[AuthController::class,'activated']);



Route::post('/deliver',[OrderController::class,'status']);







// Admin Dashboard API Routes



Route::middleware(['auth:api'])->group(function () {



    // Dashboard Statistics



    Route::get('/admin/dashboard', [AdminController::class, 'dashboardStats']);



    



    // Users Management



    Route::get('/admin/users', [AdminController::class, 'getUsers']);



    Route::post('/admin/users/bulk-toggle-status', [AdminController::class, 'bulkToggleStatus']);



    Route::post('/admin/users/{id}/reset-password', [AdminController::class, 'resetPassword']);



    



    // Products Management



    Route::get('/admin/products', [ProductController::class, 'getAdminProducts']);



    Route::get('/admin/products/search', [ProductController::class, 'searchAdmin']);



    Route::post('/admin/products/sync-ameen', [ProductController::class, 'syncWithAmeen']);



    Route::post('/admin/products/{id}/upload-image', [ProductController::class, 'uploadImage']);



    Route::delete('/admin/products/{id}/delete-image', [ProductController::class, 'deleteImage']);



    Route::get('/admin/products/export-excel', [ProductController::class, 'exportExcel']);



    Route::get('/admin/products/export-pdf', [ProductController::class, 'exportPdf']);



    



    // Orders Management



    Route::get('/admin/orders', [OrderController::class, 'getAdminOrders']);



    Route::post('/admin/orders/{id}/send-to-warehouse', [OrderController::class, 'sendToWarehouse']);



    Route::post('/admin/orders/bulk-send', [OrderController::class, 'bulkSendToWarehouse']);



    Route::get('/admin/orders/print', [OrderController::class, 'printOrders']);



    Route::put('/admin/orders/{id}', [OrderController::class, 'update']);



    Route::delete('/admin/orders/{id}', [OrderController::class, 'destroy']);



    Route::get('/admin/orders/user/{id}', [OrderController::class, 'orders_user']);



    Route::get('/admin/orders/user/{id}/json', [OrderController::class, 'getUserOrdersJson']);



    Route::get('/admin/orders/warehouse/{id}/json', [OrderController::class, 'getWarehouseOrdersJson']);



    



    // Categories Management



    Route::get('/admin/categories', [CategoryController::class, 'getAdminCategories']);



    



    // Offers Management



    Route::get('/admin/offers', [OfferController::class, 'getAdminOffers']);



    



    // Warehouses Management



    Route::get('/admin/warehouses', [warehousecontroller::class, 'getAdminWarehouses']);



    Route::post('/admin/warehouses', [warehousecontroller::class, 'store']);



    Route::put('/admin/warehouses/{id}', [warehousecontroller::class, 'update']);



    Route::delete('/admin/warehouses/{id}', [warehousecontroller::class, 'destroy']);



    



    // Notifications



    Route::get('/admin/users-list', [NotificationController::class, 'getUsersList']);



    Route::post('/admin/notifications/send', [NotificationController::class, 'sendNotification']);



    Route::get('/admin/notifications/user/{id}/json', [NotificationController::class, 'getUserNotificationsJson']);



});



