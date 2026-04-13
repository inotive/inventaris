<?php

namespace App\Actions\Data\Absence;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HandlePhotoUpload
{
    // Constants for storage
    private const STORAGE_DISK = 'public';
    private const PHOTO_STORAGE_PATH = 'attendances';

    /**
     * Handle photo upload from file or base64
     */
    public function execute(Request $request, $employee): ?string
    {
        if ($request->hasFile('photo')) {
            return $this->handleFileUpload($request, $employee);
        }

        if ($request->filled('photo_base64')) {
            return $this->handleBase64Upload($request, $employee);
        }

        return null;
    }

    /**
     * Handle file upload
     */
    private function handleFileUpload(Request $request, $employee): ?string
    {
        Log::info('Processing file upload for attendance photo', [
            'employee_id' => $employee->id,
            'file_size' => $request->file('photo')->getSize(),
            'file_type' => $request->file('photo')->getMimeType()
        ]);

        $photoPath = $request->file('photo')->store(self::PHOTO_STORAGE_PATH, self::STORAGE_DISK);

        Log::info('File upload successful', ['photo_path' => $photoPath]);

        return $photoPath;
    }

    /**
     * Handle base64 photo upload
     */
    private function handleBase64Upload(Request $request, $employee): ?string
    {
        Log::info('Processing base64 photo upload for attendance', [
            'employee_id' => $employee->id,
            'data_length' => strlen($request->input('photo_base64'))
        ]);

        $base64Data = $request->input('photo_base64');

        if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            Log::warning('Invalid base64 image format', [
                'employee_id' => $employee->id
            ]);
            return null;
        }

        $extension = strtolower($matches[1]);
        $imageData = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1));
        $fileName = self::PHOTO_STORAGE_PATH . '/' . uniqid('att_') . '.' . $extension;

        Storage::disk(self::STORAGE_DISK)->put($fileName, $imageData);

        Log::info('Base64 photo upload successful', [
            'photo_path' => $fileName,
            'file_extension' => $extension
        ]);

        return $fileName;
    }
}
