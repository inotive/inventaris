<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\Data\Submission\General\CreateGeneralSubmission;
use App\Actions\Data\Submission\General\UpdateGeneralSubmission;
use App\Actions\Data\Submission\General\UpdateStatusGeneralSubmission;
use App\Actions\Data\Submission\General\GetGeneralSubmissionPaginate;
use App\Http\Resources\SubmissionGeneralResource as SubmissionGeneralResources;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubmissionGeneralResource;
use App\Http\Requests\Submission\SubmissionGeneralRequest;
use App\Http\Requests\Submission\UpdateStatusGeneralRequest;
use App\Http\Requests\Submission\UpdateSubmissionGeneralRequest;
use App\Models\GeneralSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionGeneralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, GetGeneralSubmissionPaginate $action)
    {
        $submission = $action->execute(
            perPage: $request->input('per_page', 10),
            page: $request->input('page', 1),
            sortBy: 'created_at',
            sortDirection: 'desc',
        );
        return SubmissionGeneralResource::collection($submission)
            ->additional([
                'code' => 200,
                'success' => true,
                'message' => 'Data submission general berhasil diambil'
            ]);
    }
    /**
     * display search and paginate resource.
     */
    public function search(Request $request, GetGeneralSubmissionPaginate $action)
    {
        $submission = $action->execute(
            $request->input('search'),
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('name_filter'),
            $request->input('branch_filter'),
            $request->input('status_filter'),
            $request->input('sort_by', 'created_at'),
            $request->input('sort_direction', 'desc'),
            $request->input('per_page', 10),
            $request->input('page', 1),
        );
        return ResponseFormatter::success(
            SubmissionGeneralResource::collection($submission)->response()->getData(true),
            'Data submission general berhasil diambil'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $generalSubmission = GeneralSubmission::with(['employee.branch', 'attachments', 'approver'])
            ->find($id);

        if (!$generalSubmission) {
            return ResponseFormatter::error(null, 'Data submission general tidak ditemukan', 404);
        }

        // if(Auth::user()->role !== 'Superadmin' && $generalSubmission->employee_id != Auth::user()->employee->id){
        //     return ResponseFormatter::error(null, 'Anda tidak memiliki akses untuk melihat data submission general ini', 403);
        // }

        return ResponseFormatter::success(
            new SubmissionGeneralResource($generalSubmission),
            'Data submission general berhasil diambil'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubmissionGeneralRequest $request)
    {
        app(CreateGeneralSubmission::class)->execute(Auth::user(), $request->validated());

        return ResponseFormatter::success(null, 'Data submission general berhasil dibuat');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubmissionGeneralRequest $request, string $id)
    {
        $generalSubmission = GeneralSubmission::findOrFail($id);
        app(UpdateGeneralSubmission::class)->execute(Auth::user(), $generalSubmission, $request->validated());

        return ResponseFormatter::success(null, 'Data submission general berhasil diupdate');
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStatus(UpdateStatusGeneralRequest $request, string $id)
    {
        $generalSubmission = GeneralSubmission::findOrFail($id);

        if ($generalSubmission->status == 'approved') {
            return ResponseFormatter::error('Data submission general sudah diapprove', 422);
        }

        if ($generalSubmission->status == 'rejected') {
            return ResponseFormatter::error('Data submission general sudah direject', 422);
        }

        if ($request->status == 'cancelled' && $generalSubmission->employee_id != Auth::user()->employee->id) {
            return ResponseFormatter::error('Anda tidak memiliki akses untuk membatalkan data submission general ini', 422);
        }

        app(UpdateStatusGeneralSubmission::class)->execute(Auth::user(), $generalSubmission, $request->validated());

        return ResponseFormatter::success(null, 'Data submission general berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $generalSubmission = GeneralSubmission::findOrFail($id);

        if ($generalSubmission->status == 'approved') {
            return ResponseFormatter::error('Data submission general sudah diapprove', 422);
        }

        if ($generalSubmission->status == 'rejected') {
            return ResponseFormatter::error('Data submission general sudah direject', 422);
        }

        if ($generalSubmission->employee_id != Auth::user()->employee->id) {
            return ResponseFormatter::error('Anda tidak memiliki akses untuk menghapus data submission general ini', 422);
        }

        $generalSubmission->delete();

        return ResponseFormatter::success(null, 'Data submission general berhasil dihapus');
    }
}
