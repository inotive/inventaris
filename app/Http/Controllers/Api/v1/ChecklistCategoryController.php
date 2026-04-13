<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChecklistCategoryResource;
use App\Models\ChecklistCategory;
use Illuminate\Http\Request;

class ChecklistCategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->employee) {
            return ResponseFormatter::error('Employee not found', 404);
        }

        $departmentName = $user->employee->department->name ?? null;
        $search = $request->get('search');
        $limit = $request->get('limit', 10);

        $categories = ChecklistCategory::with(['checklists' => function ($query) use ($departmentName) {
            $query->whereHas('departments', function ($q) use ($departmentName) {
                $q->where('name', $departmentName);
            });
        }])
            ->whereHas('checklists', function ($query) use ($departmentName) {
                $query->whereHas('departments', function ($q) use ($departmentName) {
                    $q->where('name', $departmentName);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->paginate($limit);

        return ResponseFormatter::successWithPagination(
            ChecklistCategoryResource::collection($categories->items()),
            'categories',
            'List of Checklist Categories',
            $categories->total(),
            $categories->count(),
            $categories->perPage(),
            $categories->currentPage(),
            $categories->lastPage()
        );
    }

    public function show($id)
    {
        $category = ChecklistCategory::withCount('checklists')->find($id);

        if (!$category) {
            return ResponseFormatter::error('Checklist category not found', 404);
        }

        return ResponseFormatter::success(
            new ChecklistCategoryResource($category),
            'Checklist category detail',
        );
    }
}
