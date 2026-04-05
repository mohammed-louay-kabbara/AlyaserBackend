<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Exception; // لالتقاط أخطاء قاعدة البيانات

class AuthController extends Controller
{
    /**
     * تسجيل الدخول
     */
    public function login(Request $request)
    {
        // 1. التحقق من المدخلات أولاً لضمان عدم إرسال قيم فارغة للسيرفر
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        
        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => false,
                'error' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
            ], 401);
        }
        $user=User::where('email',$request->email)->first();
        if ($user->activated==0) {
                        return response()->json([
                'status' => false,
                'error' => 'لم يتم تفعيل حسابك بعد'
            ], 403);
        }
        return $this->respondWithToken($token);
    }
    public function activated($id)
    {
        User::where('id',$id)->update([
            'activated' => 1
        ]);
        return response()->json(['تم تنشيط الحساب بنجاح'], 200,);
    }

    public function register(Request $request)
    {
        // 2. تم إصلاح الخطأ المطبعي (string) في حقل الهاتف وإضافة التحقق للعنوان
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'required|string|max:20',
            'address'   => 'required|string',
            'password'  => 'required|string|min:8', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }      

        try {
            // 3. محاولة إدخال البيانات في SQL Server داخل Try-Catch لتجنب توقف التطبيق عند أخطاء الاتصال
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'    => $request->phone,
                'address'  => $request->address,
                'password' => Hash::make($request->password),
            ]);
            
            // 4. طريقة أنظف لتوليد التوكن مباشرة للمستخدم الجديد
            $token = auth()->login($user);

            return response()->json([
                'status'  => true,
                'message' => 'تم تسجيل المستخدم بنجاح',
                'user'    => $user,
                'access_token' => $token, // توحيد اسم المتغير ليتطابق مع دالة respondWithToken
            ], 201); // 201 تعني Created

        } catch (Exception $e) {
            // التقاط أي مشكلة تتعلق بالـ SQL Server وإرجاعها بشكل نظيف للتطبيق
            return response()->json([
                'status' => false,
                'error'  => 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب بيانات المستخدم الحالي
     */
    public function me()
    {
        return response()->json([
            'user' => auth()->user()
        ]);
    }

    public function updateProfile(Request $request)
    {
        // جلب المستخدم الحالي من التوكن
        $user = Auth::user();

        // 1. قواعد التحقق (Validation)
        $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'address'      => 'nullable|string',
            // استثناء المستخدم الحالي من شرط عدم التكرار
            'email'        => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone'        => 'sometimes|required|string|unique:users,phone,' . $user->id,
            // التحقق من كلمات المرور (اختياري، يطلب فقط إذا تم إرسال كلمة مرور جديدة)
            'old_password' => 'required_with:new_password',
            'new_password' => 'nullable|string|min:6',
        ]);

        // 2. تحديث كلمة المرور (إذا طلب المستخدم ذلك)
        if ($request->filled('new_password')) {
            // التحقق من مطابقة كلمة المرور القديمة
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json([
                    'message' => 'كلمة المرور القديمة غير صحيحة'
                ], 400); // 400 Bad Request
            }
            // تشفير وتحديث كلمة المرور الجديدة
            $user->password = Hash::make($request->new_password);
        }

        // 3. تحديث البيانات الأساسية (نستخدم filled للتأكد من إرسال الحقل)
        if ($request->filled('name'))    $user->name = $request->name;
        if ($request->filled('email'))   $user->email = $request->email;
        if ($request->filled('phone'))   $user->phone = $request->phone;
        if ($request->has('address'))    $user->address = $request->address; // has تسمح بتمرير قيمة فارغة (null)

        // 4. حفظ التغييرات في قاعدة البيانات
        $user->save();

        return response()->json([
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'user'    => $user
        ], 200);
    }

    /**
     * تسجيل الخروج وإبطال التوكن
     */
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    /**
     * تجديد التوكن
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * هيكلة الرد الخاص بالتوكن
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user() // إرجاع بيانات المستخدم مع التوكن مفيد جداً في تطبيقات الموبايل
        ]);
    }
}