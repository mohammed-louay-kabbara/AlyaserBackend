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

public function update(Request $request, $id)
{
    $order = Order::findOrFail($id);
    $order->notes = $request->notes;
    // إذا تغيرت الطلبية نعتبرها غير متزامنة مع الأمين ليتم إرسالها من جديد
    $order->is_synced = false; 
    $submittedItemIds = []; // مصفوفة لتخزين معرفات العناصر التي جاءت من الفورم
    $newTotalAmount = 0;    // لحساب الإجمالي الجديد للطلب

    // 2. معالجة المنتجات (Items)
    if ($request->has('items')) {
        foreach ($request->items as $itemData) {
            
            // حساب المجموع الفرعي لهذا العنصر
            $subTotal = $itemData['quantity'] * $itemData['unit_price'];
            $newTotalAmount += $subTotal;

            if (isset($itemData['item_id']) && $itemData['item_id'] !== 'new') {
                // أ - تحديث منتج موجود مسبقاً
                $orderItem = OrderItem::find($itemData['item_id']);
                if ($orderItem) {
                    $orderItem->update([
                        'product_id'    => $itemData['product_id'],
                        'purchase_type' => $itemData['purchase_type'],
                        'quantity'      => $itemData['quantity'],
                        'unit_price'    => $itemData['unit_price'],
                        'sub_total'     => $subTotal,
                    ]);
                    $submittedItemIds[] = $orderItem->id;
                }
            } else {
                // ب - إضافة منتج جديد تمت إضافته من زر "إضافة منتج جديد"
                $newItem = $order->items()->create([
                    'product_id'    => $itemData['product_id'],
                    'purchase_type' => $itemData['purchase_type'],
                    'quantity'      => $itemData['quantity'],
                    'unit_price'    => $itemData['unit_price'],
                    'sub_total'     => $subTotal,
                ]);
                $submittedItemIds[] = $newItem->id;
            }
        }
    }

    // 3. حذف المنتجات التي كانت في الطلب وتم مسحها من الفورم
    // نحذف أي عنصر تابع للطلب ولا يوجد الـ ID الخاص به ضمن المصفوفة التي أرسلناها
    $order->items()->whereNotIn('id', $submittedItemIds)->delete();

    // 4. تحديث الإجمالي الكلي للطلبية وحفظها
    $order->total_amount = $newTotalAmount;
    $order->save();

    return back()->with('success', 'تم تحديث الطلبية ومحتوياتها بنجاح.');
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
        $order = Order::where('id', $id)->with('items.product')->with('items')->get();
        return response()->json($order, 200);
    }
    public function updateOrder(Request $request, $orderId)
    {
        $request->validate([
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.1',
            'items.*.purchase_type' => 'required|string',
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

        // 3. التحقق من حالة الطلب الشرط الأساسي (pending)
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، لا يمكن تعديل الطلب لأنه قيد المعالجة أو تم الانتهاء منه.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // مسح العناصر القديمة للطلب للبدء من جديد
            $order->items()->delete();

            $totalAmount = 0;
            $newOrderItems = [];

            // تحسين الأداء: جلب أسعار المنتجات المطلوبة دفعة واحدة لمنع استعلامات N+1
            $productIds = collect($request->items)->pluck('product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            // تجهيز العناصر الجديدة وحساب المجموع
            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);
                
                if (!$product) {
                    throw new \Exception('أحد المنتجات غير متوفر.');
                }

                // يمكنك تخصيص السعر هنا إذا كان يعتمد على purchase_type (مفرق/جملة)
                $secureUnitPrice = ($item['purchase_type'] == 'طرد') 
                                 ? $product->wholesale_price 
                                 : $product->retail_price;
                $subTotal = $secureUnitPrice * $item['quantity'];
                $totalAmount += $subTotal;

                // تجهيز المصفوفة للإدخال الجماعي
                $newOrderItems[] = [
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'],
                    'purchase_type' => $item['purchase_type'],
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $secureUnitPrice,
                    'sub_total'     => $subTotal,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
            // إدخال العناصر الجديدة دفعة واحدة (Bulk Insert) أسرع بكثير للطلبات الكبيرة
            OrderItem::insert($newOrderItems);

            // 4. تحديث بيانات الطلب الأساسية
            $order->update([
                'total_amount' => $totalAmount,
                'notes'        => $request->notes ?? $order->notes,
                
                // نقطة حاسمة: إعادة هذه القيمة إلى false تضمن أن التعديل الجديد 
                // سيتم التقاطه وتصديره لاحقاً عند المزامنة مع برنامج الأمين 
                // ولن يتم تجاهله كطلب مسبق المزامنة.
                'is_synced'    => false 
            ]);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'تم تعديل الطلب بنجاح.',
                'data'    => $order->load('items') // إرجاع الطلب مع التعديلات الجديدة
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تعديل الطلب.',
                'error'   => config('app.debug') ? $e->getMessage() : null // إظهار الخطأ الدقيق فقط في بيئة التطوير
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
    public function destroy(Request $request, $id,FcmService $fcmService)
{
      $order=Order::findOrFail($id);
    if ($request->deletion_reason) {
        if ($order->user->fcm_token) {
            $fcmService->sendAndSaveNotification(
            $order->user->id,
            $order->user->fcm_token,
            'تم حذف طلبك',
            $request->deletion_reason,
            'order'
        );
        }
        }
        // 2. حذف الطلب نفسه
        $order->delete();
        return back()->with('success', 'تم حذف الطلب وجميع محتوياته بنجاح.');
    }

public function getAdminOrders(Request $request)
{
    $query = Order::with('user')->orderBy('created_at', 'desc');

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
