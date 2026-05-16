<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use App\Services\FcmService;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;

use App\Models\UserNotification;

class NotificationController extends Controller
{
    public function Notification()
    {
        $users=User::get();
        return view('Notifications',compact('users'));
    }

    public function getUsersList(Request $request)
    {
        $users = User::select('id', 'name', 'phone', 'zone')->latest()->get();
        return response()->json($users);
    }
    

    public function sendNotification(Request $request, FcmService $fcmService)
{
    // التحقق من صحة البيانات المدخلة
    $request->validate([
        'title' => 'required|string|max:255',
        'body' => 'required|string',
        'user_ids' => 'required|array',
        'user_ids.*' => 'exists:users,id',
        'destination' => 'nullable|string',
    ]);

    // جلب المستخدمين المحددين فقط من قاعدة البيانات (مع التوكين الخاص بكل مستخدم)
    $selectedUsers = User::whereIn('id', $request->user_ids)->get();

    $successCount = 0;

    foreach ($selectedUsers as $user) {
        // افترض أنك تخزن توكين الجهاز في حقل اسمه fcm_token داخل جدول المستخدمين
        if ($user->fcm_token) {
            $result = $fcmService->sendAndSaveNotification(
                $user->id,
                $user->fcm_token,
                $request->title,
                $request->body,
                $request->destination ?? ''
            );

            if ($result) {
                $successCount++;
            }
        }
    }

    return response()->json(['message' => "تم إرسال الإشعار بنجاح لـ {$successCount} مستخدمين."], 200);
}
    public function userNotifications($id)
    {
        $user = User::find($id);
        $notifications = $user->notifications()->get();
        return view('user_notifications', compact('notifications'));
    }

    public function getUserNotificationsJson($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'المستخدم غير موجود'], 404);
        }
        $notifications = $user->notifications()->latest()->get();
        return response()->json($notifications, 200);
    }

    public function sendPushNotification(Request $request)
    {
    $token="dBrx1XnKTZ-Qej8J6Zs7r6:APA91bGenYb6yqZzWqWQtqvSWBQVl1CB57tOqo3K4UODaKFbHZ32zessVEdr3sfQQqkN3POSnbZgnYCPDskKRYoTHAzF9G4MS2M6DWnzqvtyGDZ-QxOSMIc";
        // 1. تحديد المستخدم الذي سنرسل له (مثال: جلبنا المستخدم رقم 1)
        $user = User::find(1); 
        // 2. التحقق من أن المستخدم لديه توكن مسجل
        if (!$user || !$user->fcm_token) {
            return response()->json(['message' => 'المستخدم لا يملك FCM Token'], 404);
        }
        // 3. تهيئة الاتصال بفايربيز
        $factory = (new Factory)->withServiceAccount(storage_path('app/alyaser-cfee3-firebase-adminsdk-fbsvc-2a83ea8a88.json'));
        $messaging = $factory->createMessaging();

        // 4. بناء محتوى الإشعار
        $notification = Notification::create('طلب جديد!', 'تم تحديث حالة طلبك بنجاح.');

        // 5. تجهيز الرسالة
        $message = CloudMessage::withTarget('token', $token )
            ->withNotification($notification)
            ->withData([
                'order_id' => '12345',
                'type' => 'order_update' // بيانات إضافية تفيد تطبيق الموبايل عند الضغط على الإشعار
            ]);
        try {
            // 6. إرسال الإشعار
            $messaging->send($message);
          
        } catch (\Exception $e) {
            return response()->json(['error' => 'فشل الإرسال: ' . $e->getMessage()], 500);
        }
    }
    public function my_Notification(){
        return UserNotification::where('user_id', auth()->id())
                           ->orderBy('created_at', 'desc')
                           ->get();
    }
        public function read_all(){
         UserNotification::where('user_id', auth()->id())
                           ->update(['is_read'=> 1]);
          return response()->json(['message' => 'لقد عملية القراءة بنجاح']);
        }
        public function read_Notification($id){
         UserNotification::where('user_id', auth()->id())->where('id',$id)
                           ->update(['is_read'=> 1]);
          return response()->json(['message' => 'لقد عملية القراءة بنجاح']);
        }
}