<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\WorkPrinciple;
use Illuminate\Http\Request;

class WorkPrincipleController extends Controller
{
    /**
     * GET /api/v1/work-principles/prinsip
     */
    public function prinsip(Request $request)
    {
        return $this->listByCategory('prinsip', $request);
    }

    /**
     * GET /api/v1/work-principles/etos-kerja
     */
    public function etosKerja(Request $request)
    {
        return $this->listByCategory('etos kerja', $request);
    }

    private function listByCategory(string $category, Request $request)
    {
        $q = $request->input('q');
        $limit = (int) $request->input('limit', 20);

        $rows = WorkPrinciple::query()
            ->where('category', $category)
            ->when($q, function ($s) use ($q) {
                $s->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($limit)
            ->withQueryString();

        return ResponseFormatter::success(
            $rows,
            'Work principles fetched'
        );
    }

    /**
     * GET /api/v1/work-principles (grouped by category)
     * Query params:
     * - q: optional search by title/description
     * - categories[]: optional array of category names to include
     */
    public function index(Request $request)
    {
        $q = (string) $request->input('q', '');
        $cats = (array) $request->input('categories', []);

        $rows = WorkPrinciple::query()
            ->when($q !== '', function ($s) use ($q) {
                $s->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when(!empty($cats), function ($s) use ($cats) {
                $s->whereIn('category', $cats);
            })
            ->orderBy('category')
            ->orderBy('created_at', 'desc')
            ->get(['id','category','title','description','img_url']);

        $grouped = $rows->groupBy('category')->map(function ($items, $cat) {
            return [
                'category' => $cat,
                'items' => $items->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'title' => $r->title,
                        'description' => $r->description,
                        'img_url' => $r->img_url,
                    ];
                })->values(),
            ];
        })->values();

        return ResponseFormatter::success($grouped, 'Work principles grouped by category');
    }
}
