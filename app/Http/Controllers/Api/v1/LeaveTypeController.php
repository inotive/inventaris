<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\LeaveTypeResource;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    /**
     * Get list of leave types.
     */
    public function index(Request $request)
    {
        $search   = $request->get('search');
        $limit    = $request->get('limit', 10);
        $offset   = $request->get('offset', 0);
        $category = $request->get('category');

        $leaveTypes = LeaveType::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->orderBy('name')
            ->skip($offset)
            ->take($limit)
            ->get();

        // hitung total data (tanpa limit/offset)
        $total = LeaveType::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->count();

        return ResponseFormatter::successWithPaginationV2(
            LeaveTypeResource::collection($leaveTypes),
            'List of Leave Types',
            $total,
            $leaveTypes->count(),
            (int) $limit,
            (int) floor($offset / $limit) + 1,
            (int) ceil($total / $limit)
        );
    }

    /**
     * Get leave type detail.
     */
    public function show($id)
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return ResponseFormatter::error('Leave Type not found', 404);
        }

        return ResponseFormatter::success(
            new LeaveTypeResource($leaveType),
            'Leave Type detail retrieved successfully'
        );
    }
}
