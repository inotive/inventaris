<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReportCommentController extends Controller
{
    /**
     * Get all comments for a specific report.
     * 
     * @param int $reportId
     * @return JsonResponse
     */
    public function index(int $reportId): JsonResponse
    {
        try {
            Log::info('📋 [REPORT-COMMENT] Fetching comments for report', [
                'report_id' => $reportId,
                'user_id' => Auth::id(),
            ]);

            $report = Report::findOrFail($reportId);

            $comments = ReportComment::with(['user:id,name,profile_photo_path'])
                ->where('report_id', $reportId)
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('✅ [REPORT-COMMENT] Comments fetched successfully', [
                'report_id' => $reportId,
                'count' => $comments->count(),
            ]);

            return ResponseFormatter::success([
                'report' => $report,
                'comments' => $comments,
                'total_comments' => ReportComment::where('report_id', $reportId)->count(),
            ], 'Komentar berhasil diambil');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('⚠️ [REPORT-COMMENT] Report not found', [
                'report_id' => $reportId,
            ]);
            return ResponseFormatter::error('Laporan tidak ditemukan', 404);
        } catch (\Exception $e) {
            Log::error('🔥 [REPORT-COMMENT] Error fetching comments', [
                'report_id' => $reportId,
                'error' => $e->getMessage(),
            ]);
            return ResponseFormatter::error('Gagal mengambil komentar: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a new comment for a report.
     * 
     * @param Request $request
     * @param int $reportId
     * @return JsonResponse
     */
    public function store(Request $request, int $reportId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'content' => 'required|string|max:1000',
            ], [
                'content.required' => 'Komentar tidak boleh kosong',
                'content.max' => 'Komentar maksimal 1000 karakter',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors()->first(), 422);
            }

            $report = Report::findOrFail($reportId);

            Log::info('➕ [REPORT-COMMENT] Creating new comment', [
                'report_id' => $reportId,
                'user_id' => Auth::id(),
            ]);

            $comment = ReportComment::create([
                'report_id' => $reportId,
                'user_id' => Auth::id(),
                'content' => $request->input('content'),
            ]);

            $comment->load('user:id,name,profile_photo_path');

            Log::info('✅ [REPORT-COMMENT] Comment created successfully', [
                'comment_id' => $comment->id,
                'report_id' => $reportId,
                'user_id' => Auth::id(),
            ]);

            return ResponseFormatter::success($comment, 'Komentar berhasil ditambahkan');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('⚠️ [REPORT-COMMENT] Report not found for comment creation', [
                'report_id' => $reportId,
            ]);
            return ResponseFormatter::error('Laporan tidak ditemukan', 404);
        } catch (\Exception $e) {
            Log::error('🔥 [REPORT-COMMENT] Error creating comment', [
                'report_id' => $reportId,
                'error' => $e->getMessage(),
            ]);
            return ResponseFormatter::error('Gagal menambahkan komentar: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update a specific comment.
     * 
     * @param Request $request
     * @param int $reportId
     * @param int $commentId
     * @return JsonResponse
     */
    public function update(Request $request, int $reportId, int $commentId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'content' => 'required|string|max:1000',
            ], [
                'content.required' => 'Komentar tidak boleh kosong',
                'content.max' => 'Komentar maksimal 1000 karakter',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error($validator->errors()->first(), 422);
            }

            $comment = ReportComment::where('report_id', $reportId)
                ->where('id', $commentId)
                ->firstOrFail();

            // Check authorization
            if (!$comment->canBeEditedBy(Auth::user())) {
                Log::warning('🚫 [REPORT-COMMENT] Unauthorized edit attempt', [
                    'comment_id' => $commentId,
                    'comment_owner' => $comment->user_id,
                    'requester' => Auth::id(),
                ]);
                return ResponseFormatter::error('Anda tidak memiliki izin untuk mengedit komentar ini', 403);
            }

            Log::info('🔄 [REPORT-COMMENT] Updating comment', [
                'comment_id' => $commentId,
                'report_id' => $reportId,
                'user_id' => Auth::id(),
            ]);

            $comment->update([
                'content' => $request->input('content'),
            ]);

            $comment->load('user:id,name,profile_photo_path');

            Log::info('✅ [REPORT-COMMENT] Comment updated successfully', [
                'comment_id' => $comment->id,
            ]);

            return ResponseFormatter::success($comment, 'Komentar berhasil diperbarui');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('⚠️ [REPORT-COMMENT] Comment not found for update', [
                'comment_id' => $commentId,
                'report_id' => $reportId,
            ]);
            return ResponseFormatter::error('Komentar tidak ditemukan', 404);
        } catch (\Exception $e) {
            Log::error('🔥 [REPORT-COMMENT] Error updating comment', [
                'comment_id' => $commentId,
                'error' => $e->getMessage(),
            ]);
            return ResponseFormatter::error('Gagal memperbarui komentar: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Delete a specific comment.
     * 
     * @param int $reportId
     * @param int $commentId
     * @return JsonResponse
     */
    public function destroy(int $reportId, int $commentId): JsonResponse
    {
        try {
            $comment = ReportComment::where('report_id', $reportId)
                ->where('id', $commentId)
                ->firstOrFail();

            // Check authorization
            if (!$comment->canBeDeletedBy(Auth::user())) {
                Log::warning('🚫 [REPORT-COMMENT] Unauthorized delete attempt', [
                    'comment_id' => $commentId,
                    'comment_owner' => $comment->user_id,
                    'requester' => Auth::id(),
                ]);
                return ResponseFormatter::error('Anda tidak memiliki izin untuk menghapus komentar ini', 403);
            }

            Log::info('🗑️ [REPORT-COMMENT] Deleting comment', [
                'comment_id' => $commentId,
                'report_id' => $reportId,
                'user_id' => Auth::id(),
            ]);

            // Soft delete the comment (replies will remain but can be filtered)
            $comment->delete();

            Log::info('✅ [REPORT-COMMENT] Comment deleted successfully', [
                'comment_id' => $commentId,
            ]);

            return ResponseFormatter::success(null, 'Komentar berhasil dihapus');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('⚠️ [REPORT-COMMENT] Comment not found for deletion', [
                'comment_id' => $commentId,
                'report_id' => $reportId,
            ]);
            return ResponseFormatter::error('Komentar tidak ditemukan', 404);
        } catch (\Exception $e) {
            Log::error('🔥 [REPORT-COMMENT] Error deleting comment', [
                'comment_id' => $commentId,
                'error' => $e->getMessage(),
            ]);
            return ResponseFormatter::error('Gagal menghapus komentar: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a single comment by ID.
     * 
     * @param int $reportId
     * @param int $commentId
     * @return JsonResponse
     */
    public function show(int $reportId, int $commentId): JsonResponse
    {
        try {
            $comment = ReportComment::with(['user:id,name,profile_photo_path'])
                ->where('report_id', $reportId)
                ->where('id', $commentId)
                ->firstOrFail();

            return ResponseFormatter::success($comment, 'Komentar berhasil diambil');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ResponseFormatter::error('Komentar tidak ditemukan', 404);
        } catch (\Exception $e) {
            Log::error('🔥 [REPORT-COMMENT] Error fetching comment', [
                'comment_id' => $commentId,
                'error' => $e->getMessage(),
            ]);
            return ResponseFormatter::error('Gagal mengambil komentar: ' . $e->getMessage(), 500);
        }
    }
}
