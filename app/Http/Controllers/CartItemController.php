<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\cart_item;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartItemController extends Controller
{
   // عرض محتويات السلة للمستخدم الحالي
    public function index()
    {
        $cartItems = cart_item::where('user_id', Auth::id())
            ->with('product')
            ->get();
        return response()->json($cartItems, 200);
    }

    public function store(Request $request)
    {
   
        $request->validate([
            'product_id'    => 'required|exists:products,id',
            'purchase_type' => 'required',
        ]);


        $product = Product::findOrFail($request->product_id);

        $price = ($request->purchase_type == 'طرد') 
                 ? $product->wholesale_price 
                 : $product->retail_price;

        // التحقق إذا كان المنتج موجود مسبقاً في السلة لتحديث الكمية فقط
        $cartItem = cart_item::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'purchase_type' => $request->purchase_type
            ],
            [
                'price_at_addition' => $price // تثبيت السعر اللحظي
            ]
        );

        return response()->json(['message' => 'تمت الإضافة للسلة', 'item' => $cartItem], 200);
    }

    // حذف عنصر من السلة
    public function destroy($id)
    {
        $item = cart_item::where('user_id', Auth::id())->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'تم حذف العنصر'], 200);
    }

    // تفريغ السلة بالكامل
    public function clear()
    {
        cart_item::where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'تم تفريغ السلة بنجاح'], 200);
    }
}
