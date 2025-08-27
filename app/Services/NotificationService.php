<?php

namespace App\Services;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private static ?string $baseUrl = null;
    private static ?string $token = null;

    private static function boot(): void
    {
        if (self::$baseUrl === null) {
            self::$baseUrl = rtrim(env('NOTIFICATION_URL', 'http://localhost:3000/notification'), '/');
            self::$token = (string) env('NOTIFICATION_TOKEN', '');
        }
    }

    public static function sendToUser(int $userId, string $title, string $message, string $type): ?array
    {
        self::boot();

        try {
            $res = Http::timeout(5)
                ->withToken(self::$token)
                ->post(self::$baseUrl . "/user/{$userId}", [
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                ]);

            // $res=Http::withToken(self::$token)
            //     ->post(self::$baseUrl, [
            //         'title' => $title,
            //         'message' => $message,
            //         'type'=> $type,
            //     ]);

            if ($res->successful()) {

                Notification::create([
                    'user_id' => $userId,
                    'title' => $title,
                    'message' => $message,
                    'status' => 'unread',
                    'type' => $type,
                    'created_at' => Carbon::now(),
                ]);
                return $res->json();
            }

            Log::warning('Notify failed', ['status' => $res->status(), 'body' => $res->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Notify exception', ['e' => $e->getMessage()]);
            return null;
        }
    }
}
