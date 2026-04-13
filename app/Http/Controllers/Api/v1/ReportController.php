<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $isSuperadmin = $user->hasRole('Superadmin');

            $laporans = Report::when($isSuperadmin, function ($query) use ($user) {
                // If Superadmin, show reports from the same branch
                return $query->whereHas('user.employee', function ($q) use ($user) {
                    $q->where('branch_id', $user->employee->branch_id);
                });
            })->when(!$isSuperadmin, function ($query) use ($user) {
                // If not Superadmin, only show reports created by the logged-in user
                return $query->where('user_id', $user->id);
            })->latest()->get()->map(function ($laporan) {
                return [
                    'id' => $laporan->id,
                    'title' => $laporan->title,
                    'description' => $laporan->description,
                    'image' => $laporan->image ? asset('storage/' . $laporan->image) : null,
                    'created_at' => $laporan->created_at,
                    'updated_at' => $laporan->updated_at,
                    'user_id' => $laporan->user_id,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'List laporan',
                'data' => $laporans
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $laporan = Report::with([
                'user:id,name,email',
                'user.employee:id,user_id,branch_id,address',
                'user.employee.branch:id,name'
            ])->find($id);

            if (!$laporan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak ditemukan',
                    'data' => null
                ], 404);
            }

            $data = [
                'id' => $laporan->id,
                'title' => $laporan->title,
                'description' => $laporan->description,
                'image' => $laporan->image ? asset('storage/' . $laporan->image) : null,
                'created_at' => $laporan->created_at,
                'updated_at' => $laporan->updated_at,
                'user' => [
                    'id' => $laporan->user->id ?? null,
                    'name' => $laporan->user->name ?? null,
                    'email' => $laporan->user->email ?? null,
                    'employee' => [
                        'branch' => $laporan->user->employee->branch->name ?? null,
                        'address' => $laporan->user->employee->address ?? null
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Detail laporan',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120', // max 5 MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('laporans', 'public');
            }

            $laporan = Report::create([
                'title' => $request->title,
                'description' => $request->description,
                'image' => $imagePath,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dibuat',
                'data' => $laporan
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
