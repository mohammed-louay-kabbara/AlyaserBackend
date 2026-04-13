<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\category;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Models\Offer;
use App\Models\OrderItem;
use App\Models\exchange_rate;

class AdminController extends Controller
{
public function index()
{
    $users_count = User::count();
    $activated = User::where('activated', 0)->count(); // ربما تقصد غير المنشطين هنا؟
    $category = category::count();
    $Product_count = Product::count();
    $Product_quantity = Product::where('quantity', 0)->count();
    $Order_pending = Order::where('status', 'pending')->count();
    $Order_processing = Order::where('status', 'processing')->count();
    $Offers_count = Offer::where('expires_at', '>', now())->count();
    $Offers = Offer::where('expires_at', '>', now())->get();
    // جلب آخر 3 أسعار صرف مرتبة من الأحدث إلى الأقدم
    $exchange_rates = exchange_rate::orderBy('created_at', 'desc')->take(3)->get();
    // جلب أكثر 5 منتجات طلباً بناءً على مجموع الكميات المباعة
    $top_products = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
        ->groupBy('product_id')
        ->orderBy('total_sold', 'desc')
        ->take(5)
        ->with('product') // جلب بيانات المنتج المرتبط (الاسم، الصورة، إلخ)
        ->get();
    return view('dashboard', compact(
        'users_count', 
        'activated', 
        'category', 
        'Product_count', 
        'Product_quantity', 
        'Order_pending', 
        'Offers_count', 
        'Order_processing', 
        'Offers',
        'top_products',
        'exchange_rates' // تأكد من إضافة المتغير هنا
    ));
}
    public function forgot_password(Request $request)
    {
        User::where('id',$request->user_id)->update([
            'password'  => Hash::make($request->password)
        ]);
        return back();
    }
    // public function get_users()
    // {
    //     $users=User::orderBy('created_at', 'desc')->get();
    //     return view('users',compact('users'));
    // }
    // public function get_users(Request $request)
    // {
    //         $query = User::query();
    //         // إذا تم إرسال كلمة للبحث، قم بالفلترة
    //         if ($request->has('search') && $request->search != '') {
    //             $query->where('name', 'like', '%' . $request->search . '%');
    //         }
    //         $users = $query->get(); // أو paginate(20) وهو الأفضل
            
    //         return view('users', compact('users'));
    // }
    public function get_users(Request $request)
{
    $query = User::query();

    // 1. فلترة البحث بالاسم (إذا وجد)
    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    // 2. فلترة حالة التفعيل
    // نستخدم has('activated') للتأكد من أن القيمة مرسلة، ونتجاهل 'all'
    if ($request->has('activated') && $request->activated !== 'all') {
        $query->where('activated', $request->activated);
    }

    $users = $query->get(); // أو paginate(20)

    return view('users', compact('users'));
}
public function bulkToggleStatus(Request $request)
{
    // التأكد من وصول البيانات بشكل صحيح
    $request->validate([
        'ids' => 'required|array',
        'activated' => 'required|boolean',
    ]);

    // تحديث حالة جميع المستخدمين المحددين دفعة واحدة
    User::whereIn('id', $request->ids)->update([
        'activated' => $request->activated
    ]);

    return response()->json([
        'success' => true, 
        'message' => 'تم تحديث حالة المستخدمين بنجاح'
    ]);
}
    
}
