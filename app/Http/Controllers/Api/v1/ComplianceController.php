<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Compliance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplianceController extends Controller
{
    /**
     * GET /api/v1/compliances
     */
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $section = $request->string('section')->toString();

        $rows = Compliance::query()
            ->with(['user:id,name'])
            ->when($q, function ($s) use ($q) {
                $s->where('title', 'like', "%{$q}%");
            })
            ->when($section, function ($s) use ($section) {
                $s->where('section', $section);
            })
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page') ?: 15)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    /**
     * GET /api/v1/compliances/{compliance}
     */
    public function show(Compliance $compliance)
    {
        $compliance->load(['user:id,name']);
        return response()->json([
            'success' => true,
            'data' => $compliance,
        ]);
    }

    /**
     * POST /api/v1/compliances
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'section' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'img_file' => ['nullable', 'image', 'max:4096'],
            'img_url' => ['nullable', 'url'],
            'created_by' => ['nullable', 'integer'],
        ]);

        if (empty($data['created_by']) && $request->user()) {
            $data['created_by'] = $request->user()->id;
        }

        if ($request->hasFile('img_file')) {
            $path = $request->file('img_file')->store('compliances', 'public');
            $data['img_url'] = Storage::url($path);
        }

        $row = Compliance::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Compliance created',
            'data' => $row,
        ], 201);
    }

    /**
     * PUT/PATCH /api/v1/compliances/{compliance}
     */
    public function update(Request $request, Compliance $compliance)
    {
        $data = $request->validate([
            'section' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'img_file' => ['nullable', 'image', 'max:4096'],
            'img_url' => ['nullable', 'url'],
            'created_by' => ['nullable', 'integer'],
        ]);

        if (empty($data['created_by']) && $request->user()) {
            $data['created_by'] = $request->user()->id;
        }

        if ($request->hasFile('img_file')) {
            $path = $request->file('img_file')->store('compliances', 'public');
            $data['img_url'] = Storage::url($path);
        }

        $compliance->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Compliance updated',
            'data' => $compliance->fresh('user:id,name'),
        ]);
    }

    /**
     * DELETE /api/v1/compliances/{compliance}
     */
    public function destroy(Compliance $compliance)
    {
        $compliance->delete();
        return response()->json([
            'success' => true,
            'message' => 'Compliance deleted',
        ]);
    }
}
