<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadMultipleRequest;
use App\Http\Requests\UploadRequest;
use App\Models\Answer;
use App\Models\AnswerHasAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function single(UploadRequest $request)
    {
        $upload = $request->validated();
        $uploadedFile = $upload['file'];

        $folder = "uploads/answers";
        $filename = uniqid() . '.' . $uploadedFile->getClientOriginalExtension();

        $path = $uploadedFile->storeAs(
            $folder,
            $filename,
            'public'
        );

        return ResponseFormatter::success([
            'file'      => $path,
            'extension' => $uploadedFile->getClientOriginalExtension(),
            'type'      => $uploadedFile->getMimeType(),
            'path'      => $folder,
        ], 'File uploaded successfully');
    }

    public function multiple(UploadMultipleRequest $request)
    {
        $upload = $request->validated();
        $uploadedFiles = $upload['files'];
        $attachments = [];


        foreach ($uploadedFiles as $uploadedFile) {
            $folder = "uploads/answers/";

            $path = $uploadedFile->storeAs(
                $folder,
                uniqid() . '.' . $uploadedFile->getClientOriginalExtension(),
                'public'
            );

            $attachments = [
                'file'      => $uploadedFile->getClientOriginalName(),
                'path'      => $path,
                'extension' => $uploadedFile->getClientOriginalExtension(),
                'type'      => $uploadedFile->getMimeType(),
            ];
        }

        return ResponseFormatter::success($attachments, 'Files uploaded successfully');
    }


    public function destroy(string $type, int $id)
    {
        switch ($type) {
            case 'answer':
                $attachment = AnswerHasAttachment::find($id);
                if (!$attachment) {
                    return ResponseFormatter::error('Attachment not found', 404);
                }

                // hapus file fisik
                if (Storage::disk('public')->exists($attachment->path)) {
                    Storage::disk('public')->delete($attachment->path);
                }

                $attachment->delete();
                break;


            default:
                return ResponseFormatter::error('Invalid type', 422);
        }

        return ResponseFormatter::success(null, 'File deleted successfully');
    }
}
