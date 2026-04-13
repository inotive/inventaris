<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationWidgetController extends Controller
{
    /**
     * Return latest notifications for the authenticated user (header widget).
     * Response shape is intentionally lightweight and compatible with the mobile app.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $limit = (int) $request->input('limit', 10);

        $rows = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        $items = $rows->map(function ($r) {
            $createdAt = optional($r->created_at);
            return [
                'id' => $r->id,
                'message' => $r->pesan,
                'status' => (int) ($r->status ?? 0),
                'model_type' => $r->model_type ?? null,
                'model_id' => $r->model_id ?? null,
                'time_ago' => $createdAt ? now()->parse($createdAt)->diffForHumans() : null,
                'created_at' => $createdAt,
            ];
        });

        return response()->json(['data' => $items]);
    }
}
