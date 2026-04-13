<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AppNotification;

class NotificationController extends Controller
{
    /**
     * GET /api/v1/notifications/history
     * Returns notifications for the authenticated user
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return ResponseFormatter::error('Unauthenticated', 401);
        }

        $limit = (int) $request->input('limit', 15);
        $q = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $q->where('status', $request->input('status'));
        }
        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $q->where(function ($w) use ($search) {
                $w->where('pesan', 'like', $search);
            });
        }

        $rows = $q->paginate($limit)->withQueryString();

        return ResponseFormatter::success($rows, 'Notification history fetched');
    }

    /**
     * POST /api/v1/notifications
     * Create a new notification
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => ['required','integer','exists:users,id'],
            'model_type' => ['nullable','string'],
            'model_id' => ['nullable','integer'],
            'pesan' => ['required','string'],
            'status' => ['nullable','integer'],
            'is_success' => ['nullable','boolean'],
        ]);

        $notif = AppNotification::create([
            'user_id' => $request->integer('user_id'),
            'model_type' => $request->input('model_type'),
            'model_id' => $request->input('model_id'),
            'pesan' => $request->input('pesan'),
            'status' => (int) $request->input('status', 0),
            'is_success' => (bool) $request->boolean('is_success', false),
        ]);

        return ResponseFormatter::success($notif, 'Notification created');
    }

    /**
     * POST /api/v1/notifications/read
     * Read a notification
     */
    public function read($notificationId)
    {
        $notification = AppNotification::findOrFail($notificationId);
        $notification->update(['status' => 1]);

        return ResponseFormatter::success($notification, 'Notification read');
    }
}
