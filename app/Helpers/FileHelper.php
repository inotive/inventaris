<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Save a list of base64 files to storage.
     *
     * @param array  $files   Array of base64 strings
     * @param string $path    Folder path relative to storage/app/public
     * @return array          Array of file metadata
     */
    public static function saveBase64Files(array $files, string $path = 'uploads'): array
    {
        $result = [];

        foreach ($files as $fileBase64) {
            // Format: data:<mime>;base64,<data>
            if (preg_match('/^data:(.*?);base64,(.*)$/', $fileBase64, $matches)) {
                $mime = $matches[1];
                $data = base64_decode($matches[2]);

                // Guess extension from mime
                $extension = explode('/', $mime)[1] ?? 'bin';

                // Generate unique filename
                $fileName = Str::uuid()->toString() . '.' . $extension;

                // Save to storage
                $filePath = $path . '/' . $fileName;
                Storage::disk('public')->put($filePath, $data);

                // Build metadata
                $result[] = [
                    'file_name' => $fileName,
                    'file_url'  => Storage::url($filePath),
                    'file_path' => $filePath,
                    'file_type' => $mime,
                    'file_size' => strlen($data),
                ];
            }
        }

        return $result;
    }
}
