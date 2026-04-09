<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\User;

class NotificationController extends Controller
{
    public function sendPushNotification(Request $request)
    {
        // 1. تحديد المستخدم الذي سنرسل له (مثال: جلبنا المستخدم رقم 1)
        $user = User::find(1); 

        // 2. التحقق من أن المستخدم لديه توكن مسجل
        if (!$user || !$user->fcm_token) {
            return response()->json(['message' => 'المستخدم لا يملك FCM Token'], 404);
        }

        // 3. تهيئة الاتصال بفايربيز
        $factory = (new Factory)->withServiceAccount(storage_path('app/firebase_credentials.json'));
        $messaging = $factory->createMessaging();

        // 4. بناء محتوى الإشعار
        $notification = Notification::create('طلب جديد!', 'تم تحديث حالة طلبك بنجاح.');

        // 5. تجهيز الرسالة
        $message = CloudMessage::withTarget('token', $user->fcm_token)
            ->withNotification($notification)
            ->withData([
                'order_id' => '12345',
                'type' => 'order_update' // بيانات إضافية تفيد تطبيق الموبايل عند الضغط على الإشعار
            ]);

        try {
            // 6. إرسال الإشعار
            $messaging->send($message);
            return response()->json(['message' => 'تم إرسال الإشعار بنجاح']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'فشل الإرسال: ' . $e->getMessage()], 500);
        }
    }
}