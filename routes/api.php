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

use App\Http\Controllers\RoleController;

use App\Http\Controllers\StaffController;















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
Route::post('read_Notification/{id}', [NotificationController::class,'read_Notification']);
Route::get('my_Notification',[NotificationController::class,'my_Notification']);
Route::get('category_search/{id}',[ProductController::class,'category_search']);
Route::get('getSearchScreenData',[ProductController::class,'getSearchScreenData']);
Route::put('/orders/{orderId}/update', [OrderController::class, 'updateOrder']);
Route::post('refresh', [AuthController::class,'refresh']);
Route::get('me', [AuthController::class,'me']);
Route::post('login', [AuthController::class,'login']);
Route::post('register', [AuthController::class,'register']);
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



    Route::get('/admin/dashboard', [AdminController::class, 'dashboardStats'])->middleware('permission:view_dashboard');



    



    // Users Management



    Route::get('/admin/users', [AdminController::class, 'getUsers'])->middleware('permission:view_users');



    Route::post('/admin/users/bulk-toggle-status', [AdminController::class, 'bulkToggleStatus'])->middleware('permission:manage_users');



    Route::post('/admin/users/{id}/reset-password', [AdminController::class, 'resetPassword'])->middleware('permission:manage_users');

    Route::put('/admin/users/{id}/role', [AdminController::class, 'updateUserRole'])->middleware('permission:manage_users');



    // Staff Management



    Route::get('/admin/staff', [StaffController::class, 'getAdminStaff'])->middleware('permission:view_staff');



    Route::post('/admin/staff', [StaffController::class, 'createStaff'])->middleware('permission:create_staff');



    Route::put('/admin/staff/{id}', [StaffController::class, 'updateStaff'])->middleware('permission:manage_staff');



    Route::delete('/admin/staff/{id}', [StaffController::class, 'deleteStaff'])->middleware('permission:delete_staff');



    Route::get('/admin/staff/{id}', [StaffController::class, 'getStaffDetail'])->middleware('permission:view_staff');



    



    // Products Management



    Route::get('/admin/products', [ProductController::class, 'getAdminProducts'])->middleware('permission:view_products');



    Route::get('/admin/products/search', [ProductController::class, 'searchAdmin'])->middleware('permission:view_products');



    Route::post('/admin/products/sync-ameen', [ProductController::class, 'syncWithAmeen'])->middleware('permission:create_products');



    Route::post('/admin/products/{id}/upload-image', [ProductController::class, 'uploadImage'])->middleware('permission:edit_products');

    Route::post('/admin/products/{id}/delete-image', [ProductController::class, 'deleteImage'])->middleware('permission:edit_products');
    
    Route::post('/admin/products/delete-all', [ProductController::class, 'delete_all'])->middleware('permission:delete_products');

    Route::get('/admin/orders/export-ameen/{id}', [OrderController::class, 'exportOrderToAmeenTxt'])->middleware('permission:view_orders');

    Route::post('/admin/orders/export-ameen-multiple', [OrderController::class, 'exportMultipleOrdersToAmeenTxt'])->middleware('permission:view_orders');

    Route::post('/warehouse/orders/{id}/ready', [warehousecontroller::class, 'markAsReady'])->name('warehouse.markAsReady');


    Route::get('/admin/products/export-excel', [ProductController::class, 'exportExcel'])->middleware('permission:export_products');



    Route::get('/admin/products/export-pdf', [ProductController::class, 'exportPdf'])->middleware('permission:export_products');



    



    // Orders Management



    Route::get('/admin/orders', [OrderController::class, 'getAdminOrders'])->middleware('permission:view_orders');
    Route::get('/admin/orders/by-number/{orderNumber}', [OrderController::class, 'getAdminOrderByNumber'])->middleware('permission:view_orders');



    Route::post('/admin/orders/{id}/send-to-warehouse', [OrderController::class, 'sendToWarehouse'])->middleware('permission:manage_orders');



    Route::post('/admin/orders/bulk-send', [OrderController::class, 'bulkSendToWarehouse'])->middleware('permission:manage_orders');



    Route::get('/admin/orders/print', [OrderController::class, 'printOrders'])->middleware('permission:print_orders');



    Route::put('/admin/orders/{id}', [OrderController::class, 'update'])->middleware('permission:manage_orders');



    Route::delete('/admin/orders/{id}', [OrderController::class, 'destroy'])->middleware('permission:delete_orders');



    Route::get('/admin/orders/user/{id}', [OrderController::class, 'orders_user'])->middleware('permission:view_orders');



    Route::get('/admin/orders/user/{id}/json', [OrderController::class, 'getUserOrdersJson'])->middleware('permission:view_orders');



    Route::get('/admin/orders/warehouse/{id}/json', [OrderController::class, 'getWarehouseOrdersJson'])->middleware('permission:view_orders');

    // Warehouse User Routes (role=3)
    Route::get('/warehouse/orders', [OrderController::class, 'getWarehouseUserOrders']);
    Route::put('/orders/{id}/status', [OrderController::class, 'updateOrderStatus']);



    



    // Categories Management

    Route::get('/admin/categories', [CategoryController::class, 'getAdminCategories'])->middleware('permission:view_categories');
    Route::post('/admin/categories', [CategoryController::class, 'store'])->middleware('permission:create_categories');
    Route::post('/admin/categories/{id}', [CategoryController::class, 'update'])->middleware('permission:edit_categories');
    Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy'])->middleware('permission:delete_categories');
    Route::post('/admin/categories/{id}/products', [CategoryController::class, 'assignProducts'])->middleware('permission:edit_categories');
    Route::delete('/admin/categories/{id}/products/{productId}', [CategoryController::class, 'removeProduct'])->middleware('permission:edit_categories');

    // Offers Management

    Route::get('/admin/offers', [OfferController::class, 'getAdminOffers'])->middleware('permission:view_offers');
    Route::post('/admin/offers_update/{id}', [OfferController::class, 'update'])->middleware('permission:edit_offers');

    // Exchange Rates Management

    Route::get('/admin/exchange-rates', [ExchangeRateController::class, 'index'])->middleware('permission:view_rates');

    Route::post('/admin/exchange-rates', [ExchangeRateController::class, 'store'])->middleware('permission:edit_rates');

    Route::put('/admin/exchange-rates/{id}', [ExchangeRateController::class, 'update'])->middleware('permission:edit_rates');

    Route::delete('/admin/exchange-rates/{id}', [ExchangeRateController::class, 'destroy'])->middleware('permission:edit_rates');

    // Warehouses Management

    Route::get('/admin/warehouses', [warehousecontroller::class, 'getAdminWarehouses'])->middleware('permission:view_warehouses');

    Route::post('/admin/warehouses', [warehousecontroller::class, 'store'])->middleware('permission:create_warehouses');

    Route::put('/admin/warehouses/{id}', [warehousecontroller::class, 'update'])->middleware('permission:edit_warehouses');

    Route::delete('/admin/warehouses/{id}', [warehousecontroller::class, 'destroy'])->middleware('permission:delete_warehouses');

    // Notifications

    Route::get('/admin/users-list', [NotificationController::class, 'getUsersList'])->middleware('permission:view_users');

    Route::post('/admin/notifications/send', [NotificationController::class, 'sendNotification'])->middleware('permission:send_notifications');

    Route::get('/admin/notifications/user/{id}/json', [NotificationController::class, 'getUserNotificationsJson'])->middleware('permission:view_notifications');




    // Roles & Permissions Management

    Route::get('/admin/roles', [RoleController::class, 'index'])->middleware('permission:manage_roles');

    Route::get('/admin/roles/{id}', [RoleController::class, 'show'])->middleware('permission:manage_roles');

    Route::put('/admin/roles/{id}', [RoleController::class, 'update'])->middleware('permission:manage_roles');

    Route::post('/admin/roles', [RoleController::class, 'store'])->middleware('permission:manage_roles');

    Route::delete('/admin/roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:manage_roles');

    Route::get('/admin/permissions', [RoleController::class, 'getPermissions'])->middleware('permission:manage_roles');

    Route::post('/admin/roles/seed', [RoleController::class, 'seed'])->middleware('permission:manage_roles');



});

Route::post('/admin/roles', [RoleController::class, 'store'])->middleware('permission:manage_roles');


