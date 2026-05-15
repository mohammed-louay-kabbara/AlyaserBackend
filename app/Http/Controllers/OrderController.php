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
use Illuminate\Support\Facades\Validator; // تأكد من وجود هذا السطر في أعلى الملف
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

    public function getWarehouseUserOrders(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role_id != 3) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }
        
        $orders = Order::with(['items.product', 'user'])->where('warehouse_id', $user->id)->latest()->get();
        return response()->json($orders, 200);
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required'
        ]);
        
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'الطلب غير موجود'], 404);
        }
        
        $order->status = $request->status;
        $order->save();
        
        return response()->json(['message' => 'تم تحديث حالة الطلب بنجاح'], 200);
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
    $warehouses = User::where('role_id', 3)->get();
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
// public function exportOrderToAmeenTxt($id)
// {
//     $order = Order::with('items.product')->findOrFail($id);
//     $content = ""; 

//     foreach ($order->items as $item) {
//         // نأخذ الـ GUID فقط بدون I=
//         $guid = strtolower($item->product->ameen_guid); 
//         $qty = number_format($item->quantity, 2, '.', ''); 
//         $price = number_format($item->price, 2, '.', ''); 
//         $unitNumber = "1"; // رقم الوحدة

//         // سنصدر 4 أعمدة فقط يفصل بينها Tab
//         $content .= "{$guid}\t{$qty}\t{$unitNumber}\t{$price}\r\n";
//     }

//     $fileName = "Ameen_Import_" . $order->id . ".txt";

//     return response($content)
//         ->withHeaders([
//             'Content-Type' => 'text/plain; charset=utf-8',
//             'Content-Disposition' => "attachment; filename={$fileName}",
//         ]);
// }

// public function exportOrderToAmeenTxt($id)
// {
//     $order = Order::with('items.product')->findOrFail($id);
//     $lines = [];

//     foreach ($order->items as $item) {
//         $product = $item->product;

//         // ── حراسة البيانات ──────────────────────────────────────
//         if (blank($product->ameen_guid)) {
//             \Log::warning("Product ID {$product->id} has no ameen_guid — skipped");
//             continue;
//         }

//         // الأولوية: كود Ameen الرقمي، ثم GUID كاحتياط
//         $code = $product->ameen_code ?? strtoupper($product->ameen_guid);

//         // السعر: من سطر الطلب أولاً، ثم retail_price كاحتياط، ثم 1 لتجنب الرفض
//         $price = $item->price
//               ?? $product->retail_price
//               ?? 0;

//         if ($price <= 0) {
//             \Log::warning("Product ID {$product->id} has zero price — Ameen will reject it");
//         }

//         $qty      = number_format($item->quantity, 2, '.', '');
//         $priceStr = number_format($price,          2, '.', '');

//         $lines[] = "{$code}\t{$qty}\t{$priceStr}";
//     }

//     if (empty($lines)) {
//         return response()->json([
//             'error' => 'لا توجد منتجات صالحة للتصدير. تأكد من وجود ameen_guid وسعر لكل منتج.'
//         ], 422);
//     }

//     $content  = implode("\r\n", $lines) . "\r\n";
//     $fileName = "Ameen_Import_Order_{$order->id}.txt";

//     return response($content)
//         ->withHeaders([
//             'Content-Type'        => 'text/plain; charset=utf-8',
//             'Content-Disposition' => "attachment; filename={$fileName}",
//         ]);
// }
public function exportOrderToAmeenTxt($id)
{
    $order = Order::with([
        'items.product',
        'items.offer.products',
    ])->findOrFail($id);

    $lines = [];

    foreach ($order->items as $item) {

        // ══════════════════════════════════════════
        // الحالة 1: عرض
        // ══════════════════════════════════════════
        $isOffer = $item->purchase_type === 'عرض'
                || !is_null($item->offer_id)
                || is_null($item->product_id);

        if ($isOffer && $item->offer) {

            foreach ($item->offer->products as $offerProduct) {

                // استخراج الكود بأمان
                $code = $offerProduct->ameen_code
                     ?? ($offerProduct->ameen_guid ? strtoupper($offerProduct->ameen_guid) : null);

                if (blank($code)) {
                    \Log::warning("Offer product ID {$offerProduct->id} has no ameen_code or ameen_guid — skipped");
                    continue;
                }

                $pivotType  = $offerProduct->pivot->purchase_type;
                $pivotQty   = $offerProduct->pivot->quantity * $item->quantity;
                $unitNumber = $pivotType === 'طرد' ? 2 : 1;

                $price = $pivotType === 'طرد'
                    ? $offerProduct->wholesale_price
                    : $offerProduct->retail_price;

                if ($price <= 0) {
                    \Log::warning("Offer product ID {$offerProduct->id} has zero price — Ameen will reject it");
                }

                $qty      = number_format($pivotQty, 2, '.', '');
                $priceStr = number_format($price,    2, '.', '');

                $lines[] = "{$code}\t{$qty}\t{$unitNumber}\t{$priceStr}";
            }

            continue;
        }

        // ══════════════════════════════════════════
        // الحالة 2: منتج مباشر (قطعة أو طرد)
        // ══════════════════════════════════════════
        $product = $item->product;

        if (is_null($product)) {
            \Log::warning("OrderItem ID {$item->id} has no product and no offer — skipped");
            continue;
        }

        // استخراج الكود بأمان
        $code = $product->ameen_code
             ?? ($product->ameen_guid ? strtoupper($product->ameen_guid) : null);

        if (blank($code)) {
            \Log::warning("Product ID {$product->id} has no ameen_code or ameen_guid — skipped");
            continue;
        }

        $unitNumber = $item->purchase_type === 'طرد' ? 2 : 1;

        $price = $item->unit_price > 0
            ? $item->unit_price
            : ($unitNumber === 2
                ? $product->wholesale_price
                : $product->retail_price);

        if ($price <= 0) {
            \Log::warning("Product ID {$product->id} has zero price — Ameen will reject it");
        }

        $qty      = number_format($item->quantity, 2, '.', '');
        $priceStr = number_format($price,          2, '.', '');

        $lines[] = "{$code}\t{$qty}\t{$unitNumber}\t{$priceStr}";
    }

    if (empty($lines)) {
        return response()->json([
            'error' => 'لا توجد منتجات صالحة للتصدير. تأكد من وجود ameen_guid أو ameen_code وسعر لكل منتج.'
        ], 422);
    }

    $content  = implode("\r\n", $lines) . "\r\n";
    $fileName = "Ameen_Import_Order_{$order->id}.txt";

    return response($content)
        ->withHeaders([
            'Content-Type'        => 'text/plain; charset=utf-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ]);
}

public function exportMultipleOrdersToAmeenTxt(Request $request)
{
    $orderIds = $request->input('order_ids', []);

    if (empty($orderIds)) {
        return response()->json([
            'error' => 'لم يتم تحديد أي طلبات للتصدير'
        ], 422);
    }

    $orders = Order::with('items.product')->whereIn('id', $orderIds)->get();
    $lines = [];

    

    foreach ($orders as $order) {
        foreach ($order->items as $item) {
    \Log::info("Item ID: {$item->id} | purchase_type: '{$item->purchase_type}' | product_id: {$item->product_id} | offer_id: {$item->offer_id}");
        foreach ($order->items as $item) {
            $product = $item->product;

            // ── حراسة البيانات ──────────────────────────────────────
            if (blank($product->ameen_guid)) {
                \Log::warning("Product ID {$product->id} has no ameen_guid — skipped");
                continue;
            }

            // الأولوية: كود Ameen الرقمي، ثم GUID كاحتياط
            $code = $product->ameen_code ?? strtoupper($product->ameen_guid);

            // السعر: من سطر الطلب أولاً، ثم retail_price كاحتياط، ثم 1 لتجنب الرفض
            $price = $item->price
                  ?? $product->retail_price
                  ?? 0;

            if ($price <= 0) {
                \Log::warning("Product ID {$product->id} has zero price — Ameen will reject it");
            }

            $qty      = number_format($item->quantity, 2, '.', '');
            $priceStr = number_format($price,          2, '.', '');

            $lines[] = "{$code}\t{$qty}\t{$priceStr}";
        }
    }

    if (empty($lines)) {
        return response()->json([
            'error' => 'لا توجد منتجات صالحة للتصدير. تأكد من وجود ameen_guid وسعر لكل منتج.'
        ], 422);
    }

    $content  = implode("\r\n", $lines) . "\r\n";
    $fileName = "Ameen_Import_Multiple_Orders_" . time() . ".txt";

    return response($content)
        ->withHeaders([
            'Content-Type'        => 'text/plain; charset=utf-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ]);
}}
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.quantity'      => 'required|numeric|min:0.1',
            'items.*.purchase_type' => 'required',
            'items.*.product_id'    => 'nullable|exists:products,id',
            'items.*.offer_id'      => 'nullable|exists:offers,id',
    ]);
        // 2. الآن نستخدم المتغير $validator الذي عرفناه للتو (وليس $request->validator)
        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطأ في البيانات المدخلة',
                'errors'  => $validator->errors()
            ], 422);
        }

    try {
        DB::beginTransaction();

        // 1. تجهيز أجزاء المعرف الخاص بالطلب
        $now = now();
        $year = $now->format('Y');  // 2026
        $month = $now->format('m'); // 04
        $userId = Auth::id();

        // 2. حساب رقم الطلب التسلسلي لهذا العميل خلال هذا الشهر
        $orderCount = Order::where('user_id', $userId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        // 3. تنسيق المعرف AY-Year-Month-CustomerId-OrderNo
        // sprintf تستخدم لإضافة أصفار على اليسار (Padding)
        // %05d تعني رقم من 5 خانات، و %03d تعني رقم من 3 خانات
        $customOrderId = sprintf('AY-%s-%s-%05d-%03d', 
            $year, 
            $month, 
            $userId, 
            $orderCount
        );

        $totalAmount = 0;

        // 4. إنشاء الطلب مع المعرف الجديد
        $order = Order::create([
            'order_number'  => $customOrderId, // المعرف الجديد
            'user_id'       => $userId, 
            'total_amount'  => 0, 
            'status'        => 'pending',
            'notes'         => $request->notes,
            'delivery_type' => $request->delivery_type
        ]);

        foreach ($request->items as $item) {
            $unitPrice = 0;

            if (!empty($item['product_id'])) {
                $product = Product::find($item['product_id']);
                $unitPrice = ($item['purchase_type'] == 'طرد') 
                             ? $product->wholesale_price 
                             : $product->retail_price;
            } elseif (!empty($item['offer_id'])) {
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
        
        // مسح السلة
        cart_item::where('user_id', Auth::id())->delete();

        DB::commit();
        
        return response()->json([
            'message' => 'تم إنشاء الطلب بنجاح',
            'order_number' => $customOrderId,
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
        $order = Order::where('id', $id)->with(['user','items.product','items.offer'])->with('items')->get();
        return response()->json($order, 200);
    }
public function updateOrder(Request $request, $orderId)
{
    $request->validate([
        'notes'                    => 'nullable|string',
        'items'                    => 'required|array|min:1',
        'items.*.quantity'         => 'required|numeric|min:0.1',
        'items.*.purchase_type'    => 'required|in:قطعة,طرد,عرض',
        'items.*.product_id'       => 'nullable|exists:products,id',
        'items.*.offer_id'        => 'nullable|exists:offers,id',
    ]);

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

        $order->items()->delete();

        $totalAmount = 0;
        $newOrderItems = [];

        $productIds = collect($request->items)
            ->whereIn('purchase_type', ['قطعة', 'طرد'])
            ->pluck('product_id')
            ->filter()
            ->unique();

        $offerIds = collect($request->items)
            ->where('purchase_type', 'عرض')
            ->pluck('offer_id')
            ->filter()
            ->unique();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $offers   = Offer::whereIn('id', $offerIds)->get()->keyBy('id');

        foreach ($request->items as $item) {
            $quantity = (float) $item['quantity'];
            $purchaseType = $item['purchase_type'];

            $productId = null;
            $offerId = null;
            $secureUnitPrice = 0;

            if ($purchaseType === 'عرض') {
                if (empty($item['offer_id'])) {
                    throw new \Exception('يجب إرسال offer_id للعرض.');
                }

                $offer = $offers->get($item['offer_id']);

                if (!$offer) {
                    throw new \Exception('العرض غير موجود.');
                }

                $offerId = $offer->id;
                $secureUnitPrice = (float) ($offer->price ?? 0);


                if ($secureUnitPrice <= 0) {
                    throw new \Exception("العرض رقم {$offer->id} لا يحتوي على سعر صالح.");
                }
            } else {
                if (empty($item['product_id'])) {
                    throw new \Exception('يجب إرسال product_id للمنتج.');
                }

                $product = $products->get($item['product_id']);

                if (!$product) {
                    throw new \Exception('المنتج غير موجود.');
                }

                $productId = $product->id;

                if ($purchaseType === 'طرد') {
                    $secureUnitPrice = (float) $product->wholesale_price;
                } else {
                    $secureUnitPrice = (float) $product->retail_price;
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
            'delivery_type' => $request->delivery_type,
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
        'default_font' => 'dejavusans',
        'tempDir' => storage_path('app/mpdf_temp'),
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
    $perPage = $request->input('per_page', 10);

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
    if ($request->filled('area')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('zone', $request->area);
        });
    }

    // Force delivery_type = 'delivery' for warehouse_manager
    $user = auth()->user();
    if ($user && $user->role && $user->role->name_en === 'warehouse_manager') {
        $query->where('delivery_type', 'delivery');
    } else {
        if ($request->filled('delivery_type')) {
            $query->where('delivery_type', $request->delivery_type);
        }
    }

    $warehouses = User::where('role_id', 3)->get();
    $orders = $query->paginate($perPage);

    return response()->json([
        'orders' => $orders,
        'warehouses' => $warehouses
    ]);
}

public function getAdminOrderByNumber($orderNumber)
{
    $order = Order::with(['user', 'items.product', 'items.offer'])
        ->where('order_number', $orderNumber)
        ->first();

    if (!$order) {
        return response()->json(['message' => 'الطلب غير موجود'], 404);
    }

    return response()->json(['order' => $order], 200);
}

}
