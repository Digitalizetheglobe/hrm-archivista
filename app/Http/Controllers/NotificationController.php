<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark all unread notifications as read for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearAll()
    {
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
            return response()->json([
                'success' => true,
                'message' => 'All notifications cleared successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not authenticated.'
        ], 401);
    }

    /**
     * Mark a specific notification as read.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        if ($user) {
            $notification = $user->notifications()->find($id);
            if ($notification) {
                $notification->markAsRead();
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read.'
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to mark notification as read.'
        ], 400);
    }
}
