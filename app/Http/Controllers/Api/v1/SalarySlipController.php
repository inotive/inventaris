<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\SalaryResource;
use App\Models\SalarySlip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SalarySlipController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return ResponseFormatter::error('Unauthenticated', 401);
        $employeeId = optional($user->employee)->id;
        if (!$employeeId) return ResponseFormatter::error('No employee profile', 422);

        $month = $request->string('month')->toString(); // YYYY-MM

        $query = SalarySlip::query()->where('employee_id', $employeeId)->orderByDesc('bulan');
        if ($month) $query->where('bulan', $month);

        return ResponseFormatter::success(SalaryResource::collection($query->get()));
    }

    public function download(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) return ResponseFormatter::error('Unauthenticated', 401);
        $employeeId = optional($user->employee)->id;
        if (!$employeeId) return ResponseFormatter::error('No employee profile', 422);

        $slip = SalarySlip::where('employee_id', $employeeId)->find($id);
        if (!$slip) return ResponseFormatter::error('Salary slip not found', 404);

        $file = (string) $slip->file_url;
        if (!$file) return ResponseFormatter::error('File not available', 404);

        // If remote URL, return the URL so mobile app can open/download
        if (Str::startsWith($file, ['http://', 'https://'])) {
            return ResponseFormatter::success(['url' => $file]);
        }

        // Try to resolve local storage/public paths
        // Case 1: stored as /storage/relative-path (public URL for disk('public'))
        $publicPrefix = '/storage/';
        if (Str::startsWith($file, $publicPrefix)) {
            $relative = Str::after($file, $publicPrefix); // maps to disk('public')
            if (Storage::disk('public')->exists($relative)) {
                $path = Storage::disk('public')->path($relative);
                return response()->download($path);
            }
        }

        // Case 2: raw path within public path
        $publicPath = public_path($file);
        if (is_file($publicPath)) {
            return response()->download($publicPath);
        }

        // Case 3: try default disk direct path
        if (Storage::exists($file)) {
            $path = Storage::path($file);
            return response()->download($path);
        }

        return ResponseFormatter::error('File not found', 404);
    }
}
