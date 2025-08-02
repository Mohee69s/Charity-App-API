<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FcmToken;
use App\Jobs\SendFirebaseNotification;

class NotificationController extends Controller
{
    public function storeToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = auth()->user();

        $user->fcmTokens()->updateOrCreate(
            ['token' => $request->token],
            []
        );

        return response()->json(['message' => 'FCM token saved']);
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
        ]);

        $user = auth()->user();
        $token = $user->fcmTokens()->latest()->first()?->token;

        if (!$token) {
            return response()->json(['error' => 'No FCM token found for user'], 404);
        }

        SendFirebaseNotification::dispatch(
            $token,
            $request->title,
            $request->body
        );

        return response()->json(['message' => 'Notification queued']);
    }
}
