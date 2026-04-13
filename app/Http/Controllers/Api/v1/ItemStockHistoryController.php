<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ItemStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ItemStockHistoryController extends Controller
{
    /**
     * GET /api/v1/item-stocks/history
     * Query params:
     * - q: search by item name/code
     * - type: in|out (default: all)
     * - date_from: filter from date (Y-m-d)
     * - date_to: filter to date (Y-m-d)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return ResponseFormatter::error('Unauthenticated', 401);

        $type = strtolower((string) $request->query('type', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $search = $request->query('q');

        $query = ItemStock::query()
            ->with(['item:id,name,code'])
            ->select(['id', 'item_id', 'type', 'amount', 'initial_stock', 'last_stock', 'source_type', 'source_id', 'created_at'])
            ->orderByDesc('id');

        // Filter by type (in/out)
        if (in_array($type, ['in', 'out'])) {
            $query->where('type', $type);
        }

        // Filter by date range
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Search by item name or code
        if ($search) {
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $rows = $query->get();

        $mapSourceLabel = function (string $sourceType): string {
            $base = class_basename($sourceType);
            return match ($base) {
                'PurchaseOrder' => 'Pembelian Barang',
                'GoodTransfer' => 'Transfer Barang',
                'GoodIssue' => 'Pemakaian Barang',
                default => $base,
            };
        };

        $items = $rows->map(function ($r) use ($mapSourceLabel) {
            return [
                'id' => $r->id,
                'item_name' => $r->item->name ?? '-',
                'item_code' => $r->item->code ?? null,
                'type' => $r->type == 'In' ? 'Masuk' : 'Keluar',
                'amount' => (int) $r->amount,
                'last_stock' => (int) $r->last_stock,
                'source_label' => $mapSourceLabel((string) $r->source_type),
                'date' => $r->created_at->format('Y-m-d'),
            ];
        });

        return ResponseFormatter::success($items->toArray(), 'Daftar Stok Masuk dan Keluar');
    }
}
