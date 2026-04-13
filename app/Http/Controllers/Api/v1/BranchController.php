<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 10); // default 10

        $branchs = Branch::when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('contact', 'like', "%{$search}%")
                ->orWhere('region', 'like', "%{$search}%");
        })
            ->paginate($limit);

        return ResponseFormatter::successWithPagination(
            BranchResource::collection($branchs->items()),
            'branchs',
            'List of Branchs',
            $branchs->total(),
            $branchs->count(),
            $branchs->perPage(),
            $branchs->currentPage(),
            $branchs->lastPage()
        );
    }

    public function allBranch(Request $request)
    {
        $branches = Branch::all();

        return ResponseFormatter::success(
            BranchResource::collection($branches),
            'List of all Branches',
            'branches'
        );
    }

    public function show($id)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return ResponseFormatter::error('Branch not found', 404);
        }

        return ResponseFormatter::success(
            new BranchResource($branch),
            'Branch detail',
            'branch'
        );
    }
}
