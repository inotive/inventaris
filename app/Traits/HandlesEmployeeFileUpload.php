<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

trait HandlesEmployeeFileUpload
{
    /**
     * Upload employee photo
     *
     * @param Request $request
     * @param string $fieldName
     * @param string|null $oldFileName
     * @param string|null $oldPath
     * @return array ['file_name' => string|null, 'path' => string|null]
     */
    protected function uploadEmployeePhoto(Request $request, string $fieldName = 'photo', ?string $oldFileName = null, ?string $oldPath = null): array
    {
        if (!$request->hasFile($fieldName)) {
            return [
                'file_name' => $oldFileName,
                'path' => $oldPath,
            ];
        }

        // Delete old file if exists
        if ($oldFileName && Storage::disk('public')->exists('uploads/employees/' . $oldFileName)) {
            Storage::disk('public')->delete('uploads/employees/' . $oldFileName);
        }

        $file = $request->file($fieldName);
        $fileName = "employee_" . Carbon::now()->timestamp . "." . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs('uploads/employees', $fileName, 'public');

        return [
            'file_name' => $fileName,
            'path' => $storedPath,
        ];
    }

    /**
     * Upload employee photo with alternative filename format
     *
     * @param Request $request
     * @param string $fieldName
     * @param string|null $oldFileName
     * @param string|null $oldPath
     * @return array ['file_name' => string|null, 'path' => string|null]
     */
    protected function uploadEmployeePhotoWithTimestamp(Request $request, string $fieldName = 'photo', ?string $oldFileName = null, ?string $oldPath = null): array
    {
        if (!$request->hasFile($fieldName)) {
            return [
                'file_name' => $oldFileName,
                'path' => $oldPath,
            ];
        }

        // Delete old file if exists
        if ($oldFileName && Storage::disk('public')->exists('uploads/employees/' . $oldFileName)) {
            Storage::disk('public')->delete('uploads/employees/' . $oldFileName);
        }

        $file = $request->file($fieldName);
        $ext = $file->getClientOriginalExtension();
        $unixTime = time();
        $fileName = $unixTime . '_employee_photo.' . $ext;
        $storedPath = $file->storeAs('uploads/employees', $fileName, 'public');

        return [
            'file_name' => $fileName,
            'path' => $storedPath,
        ];
    }

    /**
     * Save employee document (can be file upload or URL)
     *
     * @param string $title
     * @param \Illuminate\Http\UploadedFile|null $file
     * @param string|null $url
     * @param int $employeeId
     * @param bool $updateIfExists
     * @return \App\Models\EmployeeDocument|null
     */
    protected function saveEmployeeDocument(
        string $title,
        ?\Illuminate\Http\UploadedFile $file = null,
        ?string $url = null,
        int $employeeId,
        bool $updateIfExists = false
    ): ?\App\Models\EmployeeDocument {
        $path = null;

        if ($file) {
            $ext = $file->getClientOriginalExtension();
            $unixTime = time();
            $name = $unixTime . '_employee_document_' . $title . '.' . $ext;
            $path = $file->storeAs('uploads/employee_documents', $name, 'public');
        } elseif ($url) {
            $path = $url; // Store URL directly for external links
        }

        if (!$path) {
            return null;
        }

        if ($updateIfExists) {
            $existing = \App\Models\EmployeeDocument::where('employee_id', $employeeId)
                ->where('title', $title)
                ->first();

            if ($existing) {
                // Delete old file if it exists in storage (not URL)
                if ($existing->file_path && !filter_var($existing->file_path, FILTER_VALIDATE_URL)) {
                    if (Storage::disk('public')->exists($existing->file_path)) {
                        Storage::disk('public')->delete($existing->file_path);
                    }
                }

                $existing->update([
                    'file_path' => $path,
                    'uploaded_by' => auth()->id(),
                ]);

                return $existing;
            }
        }

        return \App\Models\EmployeeDocument::create([
            'employee_id' => $employeeId,
            'title' => $title,
            'file_path' => $path,
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Upload employee signature
     *
     * @param Request $request
     * @param int $employeeId
     * @param string $fieldName
     * @param bool $updateIfExists
     * @return \App\Models\EmployeeDocument|null
     */
    protected function uploadEmployeeSignature(
        Request $request,
        int $employeeId,
        string $fieldName = 'signature',
        bool $updateIfExists = true
    ): ?\App\Models\EmployeeDocument {
        if (!$request->hasFile($fieldName)) {
            return null;
        }

        $sig = $request->file($fieldName);
        $ext = $sig->getClientOriginalExtension();
        $unixTime = time();
        $sigName = 'signature_' . $unixTime . '.' . $ext;
        $sigPath = $sig->storeAs('uploads/employee_documents', $sigName, 'public');

        if ($updateIfExists) {
            $existing = \App\Models\EmployeeDocument::where('employee_id', $employeeId)
                ->whereIn('title', ['Tanda Tangan', 'Signature', 'TTD'])
                ->first();

            if ($existing) {
                // Delete old file
                if ($existing->file_path && !filter_var($existing->file_path, FILTER_VALIDATE_URL)) {
                    if (Storage::disk('public')->exists($existing->file_path)) {
                        Storage::disk('public')->delete($existing->file_path);
                    }
                }

                $existing->update([
                    'file_path' => $sigPath,
                    'title' => 'Tanda Tangan',
                    'uploaded_by' => auth()->id(),
                ]);

                return $existing;
            }
        }

        return \App\Models\EmployeeDocument::create([
            'employee_id' => $employeeId,
            'title' => 'Tanda Tangan',
            'file_path' => $sigPath,
            'uploaded_by' => auth()->id(),
        ]);
    }

    /**
     * Batch save multiple employee documents
     *
     * @param Request $request
     * @param int $employeeId
     * @param array $documents Array of ['title' => string, 'file_field' => string, 'url_field' => string]
     * @param bool $updateIfExists
     * @return array Array of created/updated documents
     */
    protected function saveEmployeeDocumentsBatch(
        Request $request,
        int $employeeId,
        array $documents,
        bool $updateIfExists = false
    ): array {
        $results = [];

        foreach ($documents as $doc) {
            $title = $doc['title'];
            $fileField = $doc['file_field'] ?? null;
            $urlField = $doc['url_field'] ?? null;

            $file = $fileField ? $request->file($fileField) : null;
            $url = $urlField ? $request->input($urlField) : null;

            $saved = $this->saveEmployeeDocument(
                $title,
                $file,
                $url,
                $employeeId,
                $updateIfExists
            );

            if ($saved) {
                $results[] = $saved;
            }
        }

        return $results;
    }

    /**
     * Delete employee file from storage
     *
     * @param string $filePath
     * @param string $disk
     * @return bool
     */
    protected function deleteEmployeeFile(string $filePath, string $disk = 'public'): bool
    {
        // Skip if it's a URL
        if (filter_var($filePath, FILTER_VALIDATE_URL)) {
            return false;
        }

        if (Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }

        return false;
    }

    /**
     * Get full URL for employee file
     *
     * @param string|null $filePath
     * @return string|null
     */
    protected function getEmployeeFileUrl(?string $filePath): ?string
    {
        if (!$filePath) {
            return null;
        }

        // If it's already a URL, return as is
        if (filter_var($filePath, FILTER_VALIDATE_URL)) {
            return $filePath;
        }

        return asset('storage/' . ltrim($filePath, '/'));
    }
}

