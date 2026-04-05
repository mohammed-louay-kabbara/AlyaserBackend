<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\cart_item;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;

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
        $order = Order::where('id', $id)->with('items')->get();
        return response()->json($order, 200);
    }
}