<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\SearchHistory;
use App\Models\exchange_rate;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Auth;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Models\category;

class ProductController extends Controller
{
    public function addproduct(){
        Product::create([
            'name' => 'سكر',
            'retail_price' => 10000,
            'wholesale_price' => 100000,
            'quantity' => 10,
        ]);
        return response()->json('تم الحفظ بنجاح', 200);
    }
    public function syncWithAmeen(Request $request)
    {
        try {
            // تمديد وقت التنفيذ لتجنب الـ Timeout مع البيانات الضخمة
            set_time_limit(0);
            $ameenProducts = DB::connection('ameen')
                ->table('mt000')
                ->select('GUID', 'Name', 'Retail', 'Whole', 'Qty')
                ->where('bHide', 0)
                ->get();

            $count = 0;
            foreach ($ameenProducts as $product) {
                Product::updateOrCreate(
                    ['ameen_guid' => $product->GUID],
                    [
                        'name'            => $product->Name,
                        'retail_price'    => $product->Retail ?? 0,
                        'wholesale_price' => $product->Whole ?? 0,
                        'quantity'        => $product->Qty ?? 0,
                    ]
                );
                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "تمت المزامنة بنجاح! تم استيراد وتحديث {$count} منتج."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المزامنة: ' . $e->getMessage()
            ], 500);
        }
    }
    public function index()
    {
// 1. جلب سعر صرف الدولار
    // افترضنا أن اسم العملة في الجدول هو 'USD'. يمكنك تغييره حسب ما هو مسجل لديك
    $dollarRate = exchange_rate::where('is_default', 1)->value('rate');
    // تأمين الكود ضد خطأ "القسمة على صفر" في حال كان الجدول فارغاً
    if (!$dollarRate || $dollarRate <= 0) {
        $dollarRate = 1; 
    }
    // 2. جلب المنتجات المفلترة
    $products = Product::where('retail_price', '!=', 0)
                       ->where('wholesale_price', '!=', 0)
                       ->paginate(20);

    // 3. التعديل على مجموعة البيانات (Collection) الموجودة داخل الـ Paginator
    $products->getCollection()->transform(function ($product) use ($dollarRate) {
        
        // المعادلة هنا هي (السعر المحلي تقسيم سعر صرف الدولار)
        // استخدمنا round لتقريب الرقم إلى خانتين عشريتين
        $product->retail_price_usd = round($product->retail_price / $dollarRate, 2);
        $product->wholesale_price_usd = round($product->wholesale_price / $dollarRate, 2);
        
        // إذا كنت تريد استبدال السعر الأصلي تماماً (ولا تريد حقولاً جديدة)، يمكنك تفعيل السطرين التاليين بدلاً من السابقين:
        // $product->retail_price = round($product->retail_price / $dollarRate, 2);
        // $product->wholesale_price = round($product->wholesale_price / $dollarRate, 2);

        return $product;
    });

    // 4. إرسال الرد
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
        $categories=category::get();
        return view('Products', compact('Products','categories'));
    }
    public function search_admin(Request $request){
    $categories=category::get();
    $search = $request->input('search');
    $status = $request->input('stock_status');
    $category = $request->input('category_id');

    $Products = Product::query()
        // فلترة بالاسم إذا وجد بحث
        ->when($search, function ($query, $search) {
            return $query->where('name', 'LIKE', "%{$search}%");
        })
        // فلترة بالحالة (موجود / منتهٍ)
        ->when($status, function ($query, $status) {
            if ($status == 'available') {
                return $query->where('quantity', '>', 0);
            } elseif ($status == 'out_of_stock') {
                return $query->where('quantity', '<=', 0);
            }
        })->when($category, function ($query, $category) {
            return $query->where('category_id', $category);
        })
        ->paginate(20)
        ->withQueryString(); // مهم جداً للحفاظ على الفلترة عند التنقل بين الصفحات
    return view('products', compact('Products','categories'));
    }
    public function deleteImage($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            $imagePath = public_path($product->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
            $product->image = null;
            $product->save();

            // العودة مع رسالة نجاح تظهر في لوحة التحكم
            return back()->with('success', 'تم حذف الصورة بنجاح');
        }

        return back()->with('error', 'الصورة غير موجودة');
    }

    public function uploadImage(Request $request, $id)
{
    if (!$request->hasFile('image')) {
        return response()->json(['error' => 'No image file provided'], 422);
    }

    $file = $request->file('image');
    
    // Validate the file
    $validator = Validator::make($request->all(), [
        'image' => 'image|mimes:jpeg,png,jpg,webp|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    if (!$file->isValid()) {
        return response()->json(['error' => 'Invalid image file'], 422);
    }

    $product = Product::findOrFail($id);
    
    // توليد اسم فريد للصورة لمنع التكرار
    $filename = time() . '_' . $file->getClientOriginalName();
    
    // حفظ الصورة في مجلد public/uploads/products
    $file->move(public_path('uploads/products'), $filename);

    // تحديث مسار الصورة في قاعدة البيانات
    $imagePath = 'uploads/products/' . $filename;
    $product->image = $imagePath;
    $product->save();

    // إعادة المسار كـ JSON ليقوم الجافاسكربت بعرضها فوراً
    return response()->json(['image' => $imagePath], 200);
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
public function exportExcel() 
{
    return Excel::download(new ProductsExport, 'products_list.xlsx');
}

public function exportPdf()
{
    $products = Product::with('category')->get();

    $html = view('reports.products_pdf', compact('products'))->render();

    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'cairo',
        'directionality' => 'rtl'
    ]);

    $mpdf->WriteHTML($html);

    return response($mpdf->Output('products.pdf', 'S'))
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="products.pdf"');
}

public function getAdminProducts(Request $request)
{
    $search = $request->input('search');
    $status = $request->input('stock_status');
    $category = $request->input('category_id');

    $query = Product::query();

    if ($search) {
        $query->where('name', 'LIKE', "%{$search}%");
    }

    if ($status) {
        if ($status == 'available') {
            $query->where('quantity', '>', 0);
        } elseif ($status == 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        }
    }

    if ($category) {
        $query->where('category_id', $category);
    }

    $products = $query->latest()->paginate(20);
    $categories = category::get();

    return response()->json([
        'products' => $products,
        'categories' => $categories
    ]);
}

public function searchAdmin(Request $request)
{
    $search = $request->input('search');
    $status = $request->input('stock_status');
    $category = $request->input('category_id');

    $query = Product::query();

    if ($search) {
        $query->where('name', 'LIKE', "%{$search}%");
    }

    if ($status) {
        if ($status == 'available') {
            $query->where('quantity', '>', 0);
        } elseif ($status == 'out_of_stock') {
            $query->where('quantity', '<=', 0);
        }
    }

    if ($category) {
        $query->where('category_id', $category);
    }

    $products = $query->latest()->paginate(20);

    return response()->json($products);
}
}
