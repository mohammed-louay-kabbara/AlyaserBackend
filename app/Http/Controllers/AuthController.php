<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\FcmService; // استيراد الخدمة الجديدة
use Illuminate\Http\Request;
use Exception;

class AuthController extends Controller
{
    public function fcm_token(Request $request)
    {
        User::where('id',Auth::id())->update(['fcm_token' => $request->fcm_token]);
        return response()->json(['تم الحفظ بنجاح'], 200);
    }

public function login_admin(Request $request)
{
    $credentials = $request->validate([
        'phone'    => 'required|string',
        'password' => 'required',
    ]);

    // نحدد Guard الـ web هنا بشكل صريح
    if (Auth::guard('web')->attempt($credentials)) {
        
        $user = Auth::guard('web')->user();

        if ($user->role != 1) { 
            Auth::guard('web')->logout();
            return back()->withErrors(['error' => 'غير مصرح لك بالدخول']);
        }

        $request->session()->regenerate();
        return redirect()->intended('/dashboard_admin');
    }

    return back()->withErrors(['phone' => 'بيانات الدخول غير صحيحة']);
}
    public function me()
    {
        return response()->json(auth()->user(), 200);
    }
    public function admin()
    {
        User::where('id',Auth::id())->update([ 'role' => 1]);
        return response()->json(['لقد أصبحت ادمن'], 200);
    }

    // 2. تسجيل دخول المستخدم العادي باستخدام الهاتف
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('phone', 'password');
        
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['status' => false, 'error' => 'رقم الهاتف أو كلمة المرور غير صحيحة'], 401);
        }

        $user = auth()->user();
        if ($user->activated == 0) {
            return response()->json(['status' => false, 'error' => 'لم يتم تفعيل حسابك بعد'], 403);
        }

        return $this->respondWithToken($token);
    }

    // 3. التسجيل بالحقول الجديدة (المنطقة واسم المحل)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|unique:users,phone',
            'zone'      => 'required|string',
            'address'   => 'required|string',
            'password'  => 'required|string|min:8', 
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }      

        try {
            $user = User::create([
                'name'      => $request->name,
                'phone'     => $request->phone,
                'zone'      => $request->zone,
                'shop_name' => $request->shop_name,
                'address'   => $request->address,
                'password'  => Hash::make($request->password),
                'role'      => 2, // مستخدم عادي افتراضياً
            ]);
            
            $token = auth()->login($user);
            return response()->json([
                'status'  => true,
                'message' => 'تم تسجيل المستخدم بنجاح',
                'user'    => $user,
                'access_token' => $token,
            ], 201);

        } catch (Exception $e) {
            return response()->json(['status' => false, 'error' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()], 500);
        }
    }

    // 4. تحديث الملف الشخصي بالحقول الجديدة
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'      => 'sometimes|required|string|max:255',
            'phone'     => 'sometimes|required|string|unique:users,phone,' . $user->id,
            'zone'      => 'sometimes|required|string',
            'shop_name' => 'sometimes|required|string',
            'address'   => 'sometimes|required|string',
        ]);

        if ($request->filled('new_password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json(['message' => 'كلمة المرور القديمة غير صحيحة'], 400);
            }
            $user->password = Hash::make($request->new_password);
        }

        if ($request->filled('name'))      $user->name = $request->name;
        if ($request->filled('phone'))     $user->phone = $request->phone;
        if ($request->filled('zone'))      $user->zone = $request->zone;
        if ($request->filled('shop_name')) $user->shop_name = $request->shop_name;
        if ($request->has('address'))     $user->address = $request->address;

        $user->save();
        return response()->json(['message' => 'تم التحديث بنجاح', 'user' => $user], 200);
    }

    public function activated($id, FcmService $fcmService) // حقن الخدمة هنا
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $user->update(['activated' => 1]);

        // إرسال الإشعار باستخدام الخدمة
        if ($user->fcm_token) {
            $fcmService->sendAndSaveNotification(
                $user->id,
                $user->fcm_token, 
                'تم تفعيل حسابك! 🎉', 
                'أهلاً بك في تطبيق الياسر، تم قبول طلب انضمامك بنجاح.',
                'home'
            );
        }

        return response()->json(['message' => 'تم تنشيط الحساب بنجاح'], 200);
    }
    public function logout()
    {
        auth()->logout();
        return response()->json(['status' => true, 'message' => 'تم تسجيل الخروج']);
    }

    public function logout_admin()
    {
        auth()->logout();
        return response()->json(['status' => true, 'message' => 'تم تسجيل الخروج']);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}