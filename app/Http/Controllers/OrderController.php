<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\cart_item;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\FcmService;
use App\Models\Offer;
use Mpdf\Mpdf;

class OrderController extends Controller
{
    public function orders_user($user_id)
    {
        $Orders=Order::with('items')->where('user_id',$user_id)->get();
        return view('orders_user',compact('Orders'));
    }

    public function getUserOrdersJson($user_id, Request $request)
    {
        $orders = Order::with(['items.product', 'user'])->where('user_id', $user_id)->latest()->paginate(20);
        return response()->json($orders, 200);
    }

    public function getWarehouseOrdersJson($warehouse_id)
    {
        $orders = Order::with(['items.product', 'user'])->where('warehouse_id', $warehouse_id)->latest()->get();
        return response()->json($orders, 200);
    }

// public function update(Request $request, $id)
// {
//     $order = Order::findOrFail($id);
//     $order->notes = $request->notes;
//     // إذا تغيرت الطلبية نعتبرها غير متزامنة مع الأمين ليتم إرسالها من جديد
//     $order->is_synced = false; 
//     $submittedItemIds = []; // مصفوفة لتخزين معرفات العناصر التي جاءت من الفورم
//     $newTotalAmount = 0;    // لحساب الإجمالي الجديد للطلب

//     // 2. معالجة المنتجات (Items)
//     if ($request->has('items')) {
//         foreach ($request->items as $itemData) {
            
//             // حساب المجموع الفرعي لهذا العنصر
//             $subTotal = $itemData['quantity'] * $itemData['unit_price'];
//             $newTotalAmount += $subTotal;

//             if (isset($itemData['item_id']) && $itemData['item_id'] !== 'new') {
//                 // أ - تحديث منتج موجود مسبقاً
//                 $orderItem = OrderItem::find($itemData['item_id']);
//                 if ($orderItem) {
//                     $orderItem->update([
//                         'product_id'    => $itemData['product_id'],
//                         'purchase_type' => $itemData['purchase_type'],
//                         'quantity'      => $itemData['quantity'],
//                         'unit_price'    => $itemData['unit_price'],
//                         'sub_total'     => $subTotal,
//                     ]);
//                     $submittedItemIds[] = $orderItem->id;
//                 }
//             } else {
//                 // ب - إضافة منتج جديد تمت إضافته من زر "إضافة منتج جديد"
//                 $newItem = $order->items()->create([
//                     'product_id'    => $itemData['product_id'],
//                     'purchase_type' => $itemData['purchase_type'],
//                     'quantity'      => $itemData['quantity'],
//                     'unit_price'    => $itemData['unit_price'],
//                     'sub_total'     => $subTotal,
//                 ]);
//                 $submittedItemIds[] = $newItem->id;
//             }
//         }
//     }

//     // 3. حذف المنتجات التي كانت في الطلب وتم مسحها من الفورم
//     // نحذف أي عنصر تابع للطلب ولا يوجد الـ ID الخاص به ضمن المصفوفة التي أرسلناها
//     $order->items()->whereNotIn('id', $submittedItemIds)->delete();

//     // 4. تحديث الإجمالي الكلي للطلبية وحفظها
//     $order->total_amount = $newTotalAmount;
//     $order->save();

//     return back()->with('success', 'تم تحديث الطلبية ومحتوياتها بنجاح.');
// }

public function update(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // التحقق من البيانات (Validation)
    $request->validate([
        'items' => 'required|array',
        'items.*.quantity' => 'required|numeric|min:0.1',
        'items.*.unit_price' => 'required|numeric',
    ]);

    // إذا تغيرت الطلبية نعتبرها غير متزامنة مع الأمين
    $order->is_synced = false; 
    $order->notes = $request->notes;

    $submittedItemIds = []; 
    $newTotalAmount = 0; 

    if ($request->has('items')) {
        foreach ($request->items as $itemData) {
            
            // حساب المجموع الفرعي
            $subTotal = $itemData['quantity'] * $itemData['unit_price'];
            $newTotalAmount += $subTotal;

            // تجهيز البيانات للحفظ (دعم المنتج أو العرض)
            $itemPayload = [
                'product_id'    => $itemData['product_id'] ?? null,
                'offer_id'      => $itemData['offer_id'] ?? null,
                'purchase_type' => $itemData['purchase_type'],
                'quantity'      => $itemData['quantity'],
                'unit_price'    => $itemData['unit_price'],
                'sub_total'     => $subTotal,
            ];

            if (isset($itemData['item_id']) && $itemData['item_id'] !== 'new') {
                // 1. تحديث عنصر موجود
                $orderItem = OrderItem::find($itemData['item_id']);
                if ($orderItem) {
                    $orderItem->update($itemPayload);
                    $submittedItemIds[] = $orderItem->id;
                }
            } else {
                // 2. إضافة عنصر جديد (سواء كان منتج أو عرض)
                $newItem = $order->items()->create($itemPayload);
                $submittedItemIds[] = $newItem->id;
            }
        }
    }

    // 3. حذف العناصر التي تم إزالتها من واجهة المستخدم
    $order->items()->whereNotIn('id', $submittedItemIds)->delete();

    // 4. تحديث الإجمالي والحفظ
    $order->total_amount = $newTotalAmount;
    $order->save();

    // في الـ API نفضل إرجاع JSON بدلاً من back()
    return response()->json([
        'message' => 'تم تحديث الطلبية ومحتوياتها بنجاح',
        'order' => $order->load('items')
    ], 200);
}

public function get_order(Request $request)
{
    $products = Product::select('id', 'name')->get();
    // جلب الطلبات مع بيانات الزبون لتجنب مشكلة (N+1 Queries)
    $query = Order::with('user')->orderBy('created_at', 'desc');
    // 1. البحث باستخدام اسم الزبون
    if ($request->filled('search')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        });
    }
    // 2. الفرز باستخدام التاريخ
    if ($request->filled('date')) {
        $query->whereDate('created_at', $request->date);
    }
    // 3. الفرز باستخدام حالة الطلب
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    $warehouses = User::where('role', 3)->get();
    $orders = $query->paginate(20); 
    return view('orders', compact('orders', 'warehouses','products'));
}
public function sendToWarehouse($id)
{
    $order = Order::findOrFail($id);
    
    // تغيير الحالة لضمان مزامنتها كطلب جديد إلى المستودع أو النظام المحاسبي
    $order->update([
        'is_synced' => false, // لإعادة التقاطها بواسطة جدولة المزامنة
        'status' => 'pending' // أو أي حالة تعتمدها للمستودع
    ]);

    return back()->with('success', 'تم توجيه الطلب للمستودع وسيتم مزامنته قريباً.');
}
public function store(Request $request)
{
    $request->validate([
        'items' => 'required|array|min:1',
        'items.*.quantity'      => 'required|numeric|min:0.1',
        'items.*.purchase_type' => 'required',
        // التحقق من وجود أحدهما لكل عنصر في المصفوفة
        'items.*.product_id'    => 'nullable|exists:products,id',
        'items.*.offer_id'      => 'nullable|exists:offers,id',
    ]);
    try {
        DB::beginTransaction();
        
        $totalAmount = 0;
        $order = Order::create([
            'user_id'      => Auth::id(), 
            'total_amount' => 0, 
            'status'       => 'pending',
            'notes'        => $request->notes,
        ]);

        foreach ($request->items as $item) {
            $unitPrice = 0;

            if (!empty($item['product_id'])) {
                // منطق المنتج
                $product = Product::find($item['product_id']);
                $unitPrice = ($item['purchase_type'] == 'طرد') 
                             ? $product->wholesale_price 
                             : $product->retail_price;
            } elseif (!empty($item['offer_id'])) {
                // منطق العرض
                $offer = Offer::find($item['offer_id']);
                $unitPrice = $offer->price;
            }
            $subTotal = $unitPrice * $item['quantity'];
            $totalAmount += $subTotal;
            OrderItem::create([
                'order_id'      => $order->id,
                'product_id'    => $item['product_id'] ?? null,
                'offer_id'      => $item['offer_id'] ?? null,
                'purchase_type' => $item['purchase_type'],
                'quantity'      => $item['quantity'],
                'unit_price'    => $unitPrice,
                'sub_total'     => $subTotal
            ]);
        }

        $order->update(['total_amount' => $totalAmount]);
        
        // مسح السلة للمستخدم
        cart_item::where('user_id', Auth::id())->delete();

        DB::commit();
        
        return response()->json([
            'message' => 'تم إنشاء الطلب بنجاح',
            'order'   => $order->load('items.product', 'items.offer')
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'خطأ: ' . $e->getMessage()], 500);
    }
}

    public function status(Request $request)
    {
        if($request->status == 'completed'){
            Order::where('id', $request->order_id)->update(['status' => 'completed']);
        }
        else{
            Order::where('id', $request->order_id)->update(['status' => 'error','problem'=> $request->problem]);
        }
       return response()->json(['message' => 'تم تحديث حالة الطلب بنجاح'], 200);
    }

    public function index()
    {
        $orders = Order::where('user_id', Auth::id())->withcount('items')->orderBy('created_at', 'desc')->get();
        return response()->json($orders, 200);
    }
    public function Order_details($id){
        $order = Order::where('id', $id)->with(['items.product','items.offer'])->with('items')->get();
        return response()->json($order, 200);
    }
public function updateOrder(Request $request, $orderId)
{
    $request->validate([
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',

        'items.*.purchase_type' => 'required|in:product,offer',
        'items.*.quantity'      => 'required|numeric|min:0.1',

        'items.*.product_id' => 'nullable|exists:products,id',
        'items.*.offer_id'   => 'nullable|exists:offers,id',
    ]);

    // تحقق يدوي حسب نوع السطر
    foreach ($request->items as $index => $item) {
        if (($item['purchase_type'] ?? null) === 'product' && empty($item['product_id'])) {
            return response()->json([
                'success' => false,
                'message' => "المنتج رقم " . ($index + 1) . " يجب أن يحتوي على product_id."
            ], 422);
        }

        if (($item['purchase_type'] ?? null) === 'offer' && empty($item['offer_id'])) {
            return response()->json([
                'success' => false,
                'message' => "العرض رقم " . ($index + 1) . " يجب أن يحتوي على offer_id."
            ], 422);
        }
    }

    $order = Order::where('id', $orderId)
        ->where('user_id', auth()->id())
        ->first();

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'الطلب غير موجود أو لا تملك صلاحية الوصول إليه.'
        ], 404);
    }

    if ($order->status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'عذراً، لا يمكن تعديل الطلب لأنه قيد المعالجة أو تم الانتهاء منه.'
        ], 403);
    }

    try {
        DB::beginTransaction();

        // حذف العناصر القديمة
        $order->items()->delete();

        $totalAmount = 0;
        $newOrderItems = [];

        $productIds = collect($request->items)
            ->where('purchase_type', 'product')
            ->pluck('product_id')
            ->filter()
            ->unique();

        $offerIds = collect($request->items)
            ->where('purchase_type', 'offer')
            ->pluck('offer_id')
            ->filter()
            ->unique();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $offers = Offer::whereIn('id', $offerIds)->get()->keyBy('id');

        foreach ($request->items as $item) {
            $quantity = (float) $item['quantity'];
            $purchaseType = $item['purchase_type'];

            $productId = null;
            $offerId = null;
            $secureUnitPrice = 0;

            if ($purchaseType === 'product') {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw new \Exception('أحد المنتجات غير متوفر.');
                }

                $productId = $product->id;

                $secureUnitPrice = ($item['purchase_type'] === 'طرد')
                    ? (float) $product->wholesale_price
                    : (float) $product->retail_price;
            }

            if ($purchaseType === 'offer') {
                $offer = $offers->get($item['offer_id']);

                if (!$offer) {
                    throw new \Exception('أحد العروض غير متوفر.');
                }

                $offerId = $offer->id;

                // الأفضل أن يكون للعرض سعر مستقل
                $secureUnitPrice = (float) ($offer->offer_price ?? 0);

                if ($secureUnitPrice <= 0) {
                    throw new \Exception("العرض رقم {$offer->id} لا يحتوي على سعر صالح.");
                }
            }

            $subTotal = $secureUnitPrice * $quantity;
            $totalAmount += $subTotal;

            $newOrderItems[] = [
                'order_id'      => $order->id,
                'product_id'    => $productId,
                'offer_id'      => $offerId,
                'purchase_type' => $purchaseType,
                'quantity'      => $quantity,
                'unit_price'    => $secureUnitPrice,
                'sub_total'     => $subTotal,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        OrderItem::insert($newOrderItems);

        $order->update([
            'total_amount' => $totalAmount,
            'notes'        => $request->notes ?? $order->notes,
            'is_synced'    => false,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'تم تعديل الطلب بنجاح.',
            'data'    => $order->load(['items.product', 'items.offer'])
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Error updating order: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء تعديل الطلب.',
            'error'   => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

    public function bulkSendToWarehouse(Request $request)
{
    $request->validate([
        'order_ids' => 'required|string', // ستأتي كـ string مفصولة بفواصل
        'warehouse_id' => 'required|exists:users,id'
    ]);

    // تحويل النص إلى مصفوفة أرقام
    $ids = explode(',', $request->order_ids);

    // تحديث الطلبات المحددة
    // ملاحظة: يُفضل إضافة عمود 'warehouse_id' لجدول orders لتخزين المستودع المختار
    Order::whereIn('id', $ids)->update([
        'is_synced' => false, // لإعادة إرسالها للأمين إذا لزم الأمر
        'status' => 'confirmed',
        'warehouse_id' => $request->warehouse_id // تأكد من إضافة هذا العمود لقاعدة البيانات وفي موديل Order
    ]);

    return back()->with('success', 'تم إرسال الطلبات المحددة إلى المستودع بنجاح.');
}

// 3. دالة طباعة الطلبات المحددة
public function printOrders(Request $request)
{
    if (!$request->has('ids')) {
        return response()->json(['error' => 'لم يتم تحديد أي طلبات للطباعة.'], 400);
    }

    $ids = explode(',', $request->ids);
    
    // جلب الطلبات مع تفاصيلها (الزبون، والعناصر المطلوبة)
    $orders = Order::with(['user', 'items'])->whereIn('id', $ids)->get();

    // Generate PDF using mPDF
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
    ]);

    $html = view('print_orders', compact('orders'))->render();
    $mpdf->WriteHTML($html);

    // Return PDF as downloadable response
    return response($mpdf->Output('', 'S'))
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="orders.pdf"');
}
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        
        // Handle deletion reason if provided (for API calls)
        $deletionReason = request('deletion_reason');
        
        if ($deletionReason && $order->user->fcm_token) {
            $fcmService = app(FcmService::class);
            $fcmService->sendAndSaveNotification(
                $order->user->id,
                $order->user->fcm_token,
                'تم حذف طلبك',
                $deletionReason,
                'order'
            );
        }
        
        // Delete the order
        $order->delete();
        
        // Return JSON response for API calls
        if (request()->expectsJson()) {
            return response()->json(['message' => 'تم حذف الطلب وجميع محتوياته بنجاح.'], 200);
        }
        
        // Return redirect for web calls
        return back()->with('success', 'تم حذف الطلب وجميع محتوياته بنجاح.');
    }

public function getAdminOrders(Request $request)
{
    $query = Order::with(['user', 'items.product'])->orderBy('created_at', 'desc');

    if ($request->filled('search')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('date')) {
        $query->whereDate('created_at', $request->date);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $warehouses = User::where('role', 3)->get();
    $products = Product::select('id', 'name')->get();
    $orders = $query->paginate(20);

    return response()->json([
        'orders' => $orders,
        'warehouses' => $warehouses,
        'products' => $products
    ]);
}

}
