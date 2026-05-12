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
    $Product_count = Product::where('quantity', '>=',1)->count();
    $Product_quantity = Product::where('quantity','<=', 10)->where('quantity', '>',0)->count();
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

    $users = $query->paginate(10); // أو paginate(20)
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

public function updateUserRole(Request $request, $id)
{
    $request->validate([
        'role' => 'required|integer|in:1,2,3'
    ]);

    $user = User::find($id);
    if (!$user) {
        return response()->json(['error' => 'المستخدم غير موجود'], 404);
    }

    $user->role = $request->role;
    $user->save();

    return response()->json(['message' => 'تم تحديث دور المستخدم بنجاح'], 200);
}




public function dashboardStats()
{
    $users_count = User::count();
    $activated = User::where('activated', 0)->count();
    $category = category::count();
    $Product_count = Product::where('quantity', '>=',1)->count();
    $Product_quantity = Product::where('quantity','<=', 8)->count();
    $Order_pending = Order::where('status', 'pending')->count();
    $Order_processing = Order::where('status', 'processing')->count();
    $Offers_count = Offer::where('expires_at', '>', now())->count();
    $Offers = Offer::where('expires_at', '>', now())->get();
    $exchange_rates = exchange_rate::orderBy('created_at', 'desc')->take(3)->get();
    $top_products = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
        ->groupBy('product_id')
        ->orderBy('total_sold', 'desc')
        ->take(5)
        ->with('product')
        ->get();

    return response()->json([
        'users_count' => $users_count,
        'activated' => $activated,
        'category' => $category,
        'Product_count' => $Product_count,
        'Product_quantity' => $Product_quantity,
        'Order_pending' => $Order_pending,
        'Order_processing' => $Order_processing,
        'Offers_count' => $Offers_count,
        'Offers' => $Offers,
        'top_products' => $top_products,
        'exchange_rates' => $exchange_rates
    ]);
}

public function getUsers(Request $request)
{
    $query = User::with('role')->where('role_id',2);

    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    if ($request->has('activated') && $request->activated !== 'all') {
        $query->where('activated', $request->activated);
    }

    if ($request->has('status') && $request->status === 'pending') {
        $query->where('activated', 0);
    }

    $users = $query->latest()->get();

    return response()->json([
        'status' => true,
        'data' => $users
    ]);
}

public function resetPassword(Request $request, $id)
{
    $request->validate([
        'password' => 'required|min:6'
    ]);

    User::where('id', $id)->update([
        'password' => Hash::make($request->password),
        'force_password_change' => true
    ]);

    return response()->json([
        'success' => true,
        'message' => 'تم تحديث كلمة المرور بنجاح'
    ]);
}

}
