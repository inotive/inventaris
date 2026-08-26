<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function view(Request $request)
    {
        $path = $request->query('path');
        
        if (!$path) {
            abort(400, 'Path is required');
        }

        // Security check: prevent directory traversal attacks
        if (str_contains($path, '..') || str_contains($path, '\\')) {
            abort(403, 'Forbidden');
        }

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        $file = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000');
    }
}
