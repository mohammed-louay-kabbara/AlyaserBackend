<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;
// نعطي اسماً مستعاراً لإشعار فايربيز لتمييزه عن موديل قاعدة البيانات
use Kreait\Firebase\Messaging\Notification as FirebaseNotification; 
use App\Models\UserNotification; // الموديل الخاص بنا

class FcmService
{
    protected $messaging;

    public function __construct()
    {
        // تهيئة فايربيز مرة واحدة عند استدعاء الخدمة
        $factory = (new Factory)->withServiceAccount(storage_path('app/alyaser-cfee3-firebase-adminsdk-fbsvc-2a83ea8a88.json'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendAndSaveNotification($userId, $token, $title, $body, $destination)
    {
        try {
            // 1. إرسال الإشعار لفايربيز
            $notification = FirebaseNotification::create($title, $body);
            
            // نرسل الوجهة في الـ Data لكي يفهمها فلاتر عند الضغط
            $data = [
                'destination' => $destination,
            ];

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);
            $this->messaging->send($message);

            // 2. حفظ الإشعار في قاعدة البيانات
            UserNotification::create([
                'user_id'     => $userId,
                'title'       => $title,
                'body'        => $body,
                'destination' => $destination,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('FCM Error: ' . $e->getMessage());
            return false;
        }
    }
}