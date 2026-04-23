<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\cart_item;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Offer;

class CartItemController extends Controller
{
public function index()
{
    // 1. جلب كل عناصر السلة مع تحميل علاقات المنتج والعرض معاً
    $cartItems = cart_item::where('user_id', Auth::id())
        ->with(['product', 'offer'])
        ->get();

    // 2. تصفية المنتجات العادية (التي تحتوي على product_id)
    $products = $cartItems->whereNotNull('product_id')->values();

    // 3. تصفية العروض (التي تحتوي على offer_id)
    $offers = $cartItems->whereNotNull('offer_id')->values();

    // 4. إرسال الرد بشكل منفصل
    return response()->json([
        'products' => $products,
        'offers'   => $offers
    ], 200);
}

    public function store(Request $request)
{
    // التحقق من وجود أحد المعرفين (إما منتج أو عرض)
    $request->validate([
        'product_id'    => 'required_without:offer_id|exists:products,id',
        'offer_id'      => 'required_without:product_id|exists:offers,id',
        'purchase_type' => 'required', // في حال كان عرضاً، يمكن تجاهل هذا أو استخدامه كـ "عرض"
    ]);

    $userId = Auth::id();
    $price = 0;
    $productId = $request->product_id;
    $offerId = $request->offer_id;

    if ($productId) {
        // إذا كان منتجاً عادياً
        $product = Product::findOrFail($productId);
        $price = ($request->purchase_type == 'طرد') 
                 ? $product->wholesale_price 
                 : $product->retail_price;
    } else {
        // إذا كان عرضاً (Offer)
        $offer = Offer::findOrFail($offerId);
        $price = $offer->price; // سعر العرض ثابت
    }

    // التحديث أو الإنشاء
    $cartItem = cart_item::updateOrCreate(
        [
            'user_id'       => $userId,
            'product_id'    => $productId, // سيكون null إذا كان عرضاً
            'offer_id'      => $offerId,   // سيكون null إذا كان منتجاً
            'purchase_type' => $request->purchase_type
        ],
        [
            'price_at_addition' => $price
        ]
    );

    return response()->json(['message' => 'تمت الإضافة للسلة', 'item' => $cartItem], 200);
}

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
