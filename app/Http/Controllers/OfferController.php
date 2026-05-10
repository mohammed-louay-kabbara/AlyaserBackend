<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class OfferController extends Controller
{
    public function index()
    {
        // جلب العروض مع منتجاتها، بشرط أن يكون تاريخ الانتهاء أكبر من الوقت الحالي
        $offers = Offer::where('expires_at', '>', now()) 
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
        $offers = Offer::get();
        $products = Product::all(); // إرسال المنتجات للمودال
        return view('offers', compact('offers', 'products'));
    }

    public function getAdminOffers(Request $request)
    {
        $query = Offer::with('products');
        
        $search = $request->input('search');
        if ($search) {
            $query->where('description', 'LIKE', "%{$search}%");
        }
        
        $offers = $query->get();
        $products = Product::all();
        return response()->json([
            'offers' => $offers,
            'products' => $products
        ]);
    }
public function store(Request $request)
{
    // 1. التحقق من البيانات
    $request->validate([
        'description' => 'required|string',
        'expires_at'  => 'required',
        'price'       => 'required|numeric|min:0', // سعر العرض الكلي
        'image'       => 'required|image|mimes:jpeg,png,jpg|max:2048',
        'products'    => 'required|string'
    ]);

    try {
        DB::beginTransaction();

        // 2. معالجة وحفظ الصورة
        $path = $request->file('image')->store('offers', 'public');

        // 3. إنشاء سجل العرض الأساسي
        $offer = Offer::create([
            'description' => $request->description,
            'expires_at'  => $request->expires_at,
            'price'       => $request->price,
            'image'       => $path,
        ]);

        // 4. فك ترميز المنتجات من JSON
        $products = json_decode($request->products, true);

        // 5. ربط المنتجات بالعرض في الجدول الوسيط (Pivot Table)
        if (is_array($products)) {
            foreach ($products as $item) {
                $offer->products()->attach($item['product_id'], [
                    'quantity' => $item['quantity']
                ]);
            }
        }

        DB::commit();
        return response()->json(['message' => 'تم إنشاء العرض بنجاح'], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => $e->getMessage()], 500);
    }
}

    // 3. عرض عرض واحد بالتفصيل (Read Single)
    public function show($id)
    {
        $offer = Offer::find($id);
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
            'expires_at'  => 'sometimes|date',
            'price'       => 'sometimes|numeric|min:0',
            'image'       => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'products'    => 'sometimes|string'
        ]);

        try {
            DB::beginTransaction();

            // Update image if provided
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة
                Storage::disk('public')->delete($offer->image);
                // رفع الصورة الجديدة
                $offer->image = $request->file('image')->store('offers', 'public');
            }

            // Update basic fields
            if ($request->filled('description')) {
                $offer->description = $request->description;
            }
            if ($request->filled('expires_at')) {
                $offer->expires_at = $request->expires_at;
            }
            if ($request->filled('price')) {
                $offer->price = $request->price;
            }
            $offer->save();

            // Update products if provided
            if ($request->filled('products')) {
                $products = json_decode($request->products, true);
                // Remove all existing product relationships
                $offer->products()->detach();
                // Attach new products
                foreach ($products as $item) {
                    $offer->products()->attach($item['product_id'], [
                        'quantity' => $item['quantity']
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'تم تحديث العرض بنجاح'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // 5. حذف عرض (Delete)
    public function destroy($id)
    {
        $offer = Offer::find($id);
        if (!$offer) return response()->json(['message' => 'العرض غير موجود'], 404);
        
        try {
            // حذف ملف الصورة من التخزين
            Storage::disk('public')->delete($offer->image);
            
            // حذف العرض
            $offer->delete();
            
            return response()->json(['message' => 'تم حذف العرض بنجاح'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'فشل في حذف العرض: ' . $e->getMessage()], 500);
        }
    }
}