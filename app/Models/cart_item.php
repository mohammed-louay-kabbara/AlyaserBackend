<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cart_item extends Model
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
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
