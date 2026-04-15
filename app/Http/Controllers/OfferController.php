<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class OfferController extends Controller
{
    public function index()
    {
        // جلب العروض مع منتجاتها، بشرط أن يكون تاريخ الانتهاء أكبر من الوقت الحالي
        $offers = Offer::with('product')
            ->where('expires_at', '>', now()) 
            ->orderBy('expires_at', 'asc') // اختياري: عرض الأقرب للانتهاء أولاً
            ->get();

        // التحقق من وجود بيانات قبل الإرسال
        if ($offers->isEmpty()) {
            return response()->json([
                'message' => 'لا توجد عروض نشطة حالياً',
                'data' => []
            ], 200);
        }

        return response()->json($offers, 200);
    }

    public function offer_admin()
    {
        $offers = Offer::with('product')->get();
        $products = Product::all(); // إرسال المنتجات للمودال
        return view('offers', compact('offers', 'products'));  
    }

    // 2. إضافة عرض جديد (Create)
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'expires_at'  => 'required|date|after:now',
            'product_id'  => 'required|exists:products,id',
            'image'       => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // معالجة رفع الصورة
        $path = $request->file('image')->store('offers', 'public');

        $offer = Offer::create([
            'description' => $request->description,
            'expires_at'  => $request->expires_at,
            'product_id'  => $request->product_id,
            'image'       => $path
        ]);

        return back();
    }

    // 3. عرض عرض واحد بالتفصيل (Read Single)
    public function show($id)
    {
        $offer = Offer::with('product')->find($id);
        if (!$offer) return response()->json(['message' => 'العرض غير موجود'], 404);
        
        return response()->json($offer, 200);
    }

    // 4. تعديل عرض (Update)
    public function update(Request $request, $id)
    {
        $offer = Offer::find($id);
        if (!$offer) return response()->json(['message' => 'العرض غير موجود'], 404);

        $request->validate([
            'description' => 'sometimes|string',
            'expires_at'  => 'sometimes|date|after:now',
            'product_id'  => 'sometimes|exists:products,id',
            'image'       => 'sometimes|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة
            Storage::disk('public')->delete($offer->image);
            // رفع الصورة الجديدة
            $offer->image = $request->file('image')->store('offers', 'public');
        }

        $offer->update($request->only(['description', 'expires_at', 'product_id']));

        return back();
    }

    // 5. حذف عرض (Delete)
    public function destroy($id)
    {
        $offer = Offer::find($id);
        if (!$offer) return response()->json(['message' => 'العرض غير موجود'], 404);
        // حذف ملف الصورة من التخزين
        Storage::disk('public')->delete($offer->image);
        $offer->delete();
        return back();
    }
}