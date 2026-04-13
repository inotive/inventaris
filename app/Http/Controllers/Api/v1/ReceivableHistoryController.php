<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceivableHistoryController extends Controller
{
    public function history(Request $request)
    {
        $employee = Auth::user()?->employee ?? null;
        if (!$employee) {
            return ResponseFormatter::error('Employee not found for authenticated user', 400);
        }

        // Riwayat piutang diambil dari tabel receivables (kolom request_by)
        $query = Receivable::with(['requester', 'approver'])
            ->where('request_by', $employee->id);
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        $rows = $query->orderByDesc('date')->get();

        // Normalize items
        $items = $rows->map(function ($r) {
            return [
                'id' => $r->id,
                'submitted_at' => (string) $r->created_at,
                'date' => (string) $r->date,
                'amount' => (float) $r->amount,
                'tenor' => (int) ($r->tenor ?? 0),
                'status' => $r->status,
                'note' => $r->note,
                'has_attachment' => !empty($r->file_path),
            ];
        });

        return ResponseFormatter::success([
            'summary' => [
            ],
            'items' => $items,
        ], 'Receivable history retrieved');
    }
}
