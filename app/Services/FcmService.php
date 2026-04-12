<?php

namespace App\Services;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;
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
            $notification = FirebaseNotification::create($title, $body);
            $data = [
                'destination' => $destination,
            ];
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);
            $this->messaging->send($message);
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