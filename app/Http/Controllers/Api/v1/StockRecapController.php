<?php

namespace App\Http\Controllers\Api\v1;

use App\Actions\Data\Item\UpdateStockItem;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\ItemStock\UpdateItemStockRequest;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockRecapController extends Controller
{
    /**
     * Request body (JSON):
     * - q: string (optional) search by item name
     * - category: string (optional) search by category/jenis name
     * - month: string (required) format YYYY-MM
     * - limit: int (optional) default 20
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $month = (string) $request->input('month');
        $branchId = $request->input('branch_id');

        if (empty($month) || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            return ResponseFormatter::error('Parameter month wajib diisi dengan format YYYY-MM', 422);
        }

        $startDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $endDate = \Carbon\Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        // Get item IDs for branch filter if specified
        $itemIds = null;
        if ($branchId) {
            $itemIds = DB::table('items')
                ->where('branch_id', $branchId)
                ->pluck('id')
                ->toArray();

            // If no items found for this branch, return early
            if (empty($itemIds)) {
                return ResponseFormatter::success([], 'Stock recap');
            }
        }

        // Stok awal: sebelum tanggal awal
        $stokAwalQuery = DB::table('item_stocks')
            ->select('item_id', DB::raw("SUM(CASE WHEN type IN ('in','transfer_in') THEN amount ELSE 0 END) - SUM(CASE WHEN type IN ('out','transfer_out') THEN amount ELSE 0 END) as stok_awal"))
            ->whereDate('created_at', '<', $startDate);

        if ($itemIds !== null) {
            $stokAwalQuery->whereIn('item_id', $itemIds);
        }

        $stokAwal = $stokAwalQuery->groupBy('item_id')
            ->pluck('stok_awal', 'item_id');

        // Masuk selama bulan
        $masukQuery = DB::table('item_stocks')
            ->select('item_id', DB::raw('SUM(amount) as total'))
            ->whereIn('type', ['in', 'transfer_in'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($itemIds !== null) {
            $masukQuery->whereIn('item_id', $itemIds);
        }

        $masuk = $masukQuery->groupBy('item_id')
            ->pluck('total', 'item_id');

        // Keluar selama bulan
        $keluarQuery = DB::table('item_stocks')
            ->select('item_id', DB::raw('SUM(amount) as total'))
            ->whereIn('type', ['out', 'transfer_out'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($itemIds !== null) {
            $keluarQuery->whereIn('item_id', $itemIds);
        }

        $keluar = $keluarQuery->groupBy('item_id')
            ->pluck('total', 'item_id');

        $query = Item::query()
            ->with(['category:id,name', 'branch:id,name'])
            ->select(['id', 'name', 'category_id', 'branch_id']);

        if ($q !== '') {
            $query->where('name', 'like', "%{$q}%");
        }

        // Filter by branch if specified
        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            // Apply automatic branch filtering for non-Superadmin/non-Branch-2 users
            $user = auth()->user();
            $isSuperadmin = $user->hasRole('Superadmin');
            $isBranch2 = ($user->employee && $user->employee->branch_id == 2);

            if (!$isSuperadmin && !$isBranch2 && $user->employee) {
                $query->where('branch_id', $user->employee->branch_id);
            }
        }

        $items = $query->orderBy('name')->get();

        $rows = $items->map(function (Item $item) use ($stokAwal, $masuk, $keluar) {
            $awal = (int) ($stokAwal[$item->id] ?? 0);
            $qtyIn = (int) ($masuk[$item->id] ?? 0);
            $qtyOut = (int) ($keluar[$item->id] ?? 0);
            $akhir = $awal + $qtyIn - $qtyOut;
            return [
                'id' => $item->id,
                'nama_barang' => $item->name,
                'cabang' => $item->branch->name ?? '-',
                'jenis' => $item->category->name ?? '-',
                'awal' => $awal,
                'masuk' => $qtyIn,
                'keluar' => $qtyOut,
                'akhir' => $akhir,
            ];
        });

        return ResponseFormatter::success($rows, 'Stock recap');
    }

    public function update(UpdateItemStockRequest $request)
    {
        $data = $request->validated();
        $item = Item::findOrFail($data['item_id']);

        app(UpdateStockItem::class)->execute($data, $item);

        return ResponseFormatter::success(null, 'Stok barang berhasil diperbarui');
    }
}
