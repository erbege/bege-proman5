<?php

namespace App\Http\Controllers;

use App\Models\FcmToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FcmTokenController extends Controller
{
    /**
     * Register a new FCM token for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|max:512',
            'device_name' => 'nullable|string|max:255',
            'platform' => 'nullable|string|in:web,android,ios',
        ]);

        $user = Auth::user();

        // Upsert: update if token exists, create if not
        FcmToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $user->id,
                'device_name' => $request->device_name ?? 'Web Browser',
                'platform' => $request->platform ?? 'web',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'FCM token registered successfully',
        ]);
    }

    /**
     * Remove an FCM token (logout/unsubscribe).
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $deleted = FcmToken::where('token', $request->token)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted > 0 ? 'FCM token removed successfully' : 'Token not found',
        ]);
    }
}
