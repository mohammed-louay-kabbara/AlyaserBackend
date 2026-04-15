<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\cart_item;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.1',
            'items.*.purchase_type' => 'required',
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
            $productIds = collect($request->items)->pluck('product_id')->unique();
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
            foreach ($request->items as $item) {
                $product = $products[$item['product_id']];
                $secureUnitPrice = ($item['purchase_type'] == 'طرد') 
                                 ? $product->wholesale_price 
                                 : $product->retail_price;
                $subTotal = $secureUnitPrice * $item['quantity'];
                $totalAmount += $subTotal;
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'],
                    'purchase_type' => $item['purchase_type'],
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $secureUnitPrice, // السعر الآمن المجلوب من السيرفر
                    'sub_total'     => $subTotal
                ]);
            }
            $order->update(['total_amount' => $totalAmount]);
            cart_item::where('user_id',Auth::id())->delete();
            DB::commit();
            return response()->json([
                'message' => 'تم إنشاء الفاتورة بنجاح',
                'order'   => $order->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء إصدار الفاتورة: ' . $e->getMessage()
            ], 500);
        }
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
        // 1. التحقق من صحة البيانات المرسلة أولاً
        $request->validate([
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|numeric|min:0.1',
            'items.*.purchase_type' => 'required|string',
        ]);

        // 2. جلب الطلب والتأكد من أنه يخص المستخدم الحالي
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
                $unitPrice = $product->price; 
                
                $subTotal = $unitPrice * $item['quantity'];
                $totalAmount += $subTotal;

                // تجهيز المصفوفة للإدخال الجماعي
                $newOrderItems[] = [
                    'order_id'      => $order->id,
                    'product_id'    => $item['product_id'],
                    'purchase_type' => $item['purchase_type'],
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $unitPrice,
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

}

