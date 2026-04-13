<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\SearchHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function addproduct(){
        Product::create([
            'name' => 'سكر',
            'retail_price' => 10000,
            'wholesale_price' => 100000,
            'quantity' => 10,
        
        ]);
        return response()->json('تم الحفظ بنجاح', 200);
    }
    public function index()
    {
        $products = Product::paginate(20);
            
        return response()->json($products, 200);
    }
    public function getSearchScreenData()
    {
        $recentSearches = [];

        // 1. جلب عمليات البحث الأخيرة للمستخدم الحالي (أحدث 5 عمليات)
        if (Auth::check()) {
            $recentSearches = SearchHistory::where('user_id', Auth::id())
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->pluck('term'); // pluck تجلب مصفوفة من النصوص مباشرة
        }

        // 2. جلب الأكثر بحثاً بشكل عام (أكثر 10 كلمات تكراراً في النظام)
        $trendingSearches = SearchHistory::select('term', DB::raw('count(*) as total'))
            ->groupBy('term')
            ->orderBy('total', 'desc')
            ->take(10)
            ->pluck('term');

        return response()->json([
            'status' => true,
            'data' => [
                'recent_searches' => $recentSearches,
                'trending_searches' => $trendingSearches,
            ]
        ], 200);
    }
    public function product_admin()
    {
        // جلب المنتجات مرتبة من الأحدث إلى الأقدم مع تقسيمها لصفحات
        $Products = Product::latest()->paginate(20); 
        
        return view('Products', compact('Products'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:1'
        ], [
            'name.required' => 'يرجى إدخال محرف واحد على الأقل للبحث',
        ]);

        // --- إضافة السجل هنا ---
        if (Auth::check()) {
            // إذا كان مسجلاً، نحفظها باسمه، وإذا بحث عنها مجدداً نحدث وقتها فقط
            SearchHistory::updateOrCreate(
                ['user_id' => Auth::id(), 'term' => $request->name],
                ['updated_at' => now()]
            );
        } else {
            // إذا كان زائراً، نحفظ الكلمة فقط من أجل إحصائيات "الأكثر بحثاً"
            SearchHistory::create(['term' => $request->name]);
        }
        // -----------------------

        $products = Product::where('name', 'LIKE', '%' . $request->name . '%')->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'لم يتم العثور على منتجات مطابقة لبحثك',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم جلب النتائج بنجاح',
            'data' => $products
        ], 200);
    }
    public function category_search($id)
    {
        // 2. البحث في قاعدة البيانات مع جلب النتائج كصفحات (20 منتج في الصفحة)
        $products = Product::where('category_id', $id)
            ->get();
        // 3. التحقق مما إذا كانت النتيجة فارغة
        if ($products->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'لا يوجد منتجات تنتمي لهذا الصنف',
                'data' => []
            ], 404); // 404 Not Found
        }
        // 4. إرجاع النتائج بنجاح
        return response()->json([
            'status' => true,
            'message' => 'تم جلب النتائج بنجاح',
            'data' => $products
        ], 200);
    }
    public function deleteRecentSearch(Request $request)
{
    $request->validate([
        'term' => 'required|string'
    ]);
    SearchHistory::where('user_id', Auth::id())
                 ->where('term', $request->term)
                 ->delete();
    return response()->json([
        'status' => true,
        'message' => 'تم الحذف بنجاح'
    ], 200);
}
}
