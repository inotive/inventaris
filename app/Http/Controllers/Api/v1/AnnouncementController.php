<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $limit = (int) ($request->input('limit', 10));

        $announcements = Announcement::query()
            ->with(['user:id,name'])
            ->when($q, function ($s) use ($q) {
                $s->where('title', 'like', "%{$q}%");
            })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function (Announcement $a) {
                return [
                    'id' => $a->id,
                    'title' => $a->title,
                    'img_url' => $a->img_url ? asset($a->img_url) : null,
                    'content' => $a->content,
                    'created_by' => optional($a->user)->name,
                    'created_at' => optional($a->created_at)?->locale('id')->isoFormat('D MMMM YYYY'),
                    'updated_at' => optional($a->updated_at)?->locale('id')->isoFormat('D MMMM YYYY'),
                ];
            });

        return ResponseFormatter::success($announcements, 'List of announcements');
    }

    public function show($id)
    {
        $announcement = Announcement::with('user:id,name')->find($id);

        if (!$announcement) {
            return ResponseFormatter::error('Announcement not found', 404);
        }

        return ResponseFormatter::success([
            'id' => $announcement->id,
            'title' => $announcement->title,
            'img_url' => $announcement->img_url ? asset($announcement->img_url) : null,
            'content' => $announcement->content,
            'created_by' => optional($announcement->user)->name,
            'created_at' => optional($announcement->created_at)?->locale('id')->isoFormat('D MMMM YYYY'),
            'updated_at' => optional($announcement->updated_at)?->locale('id')->isoFormat('D MMMM YYYY'),
        ], 'Announcement detail');
    }
}
