<?php

namespace App\Actions\Data\GoodIssue;

use App\Models\GoodIssue;
use App\Models\ItemStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApproveGoodIssue
{
    public function execute(GoodIssue $goodIssue, $approvals = [])
    {
        DB::transaction(function () use ($goodIssue, $approvals) {
            // Update status to approved
            $goodIssue->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            // Update items with approved quantities and notes
            foreach ($approvals as $approval) {
                if (!isset($approval['id'])) continue;

                $item = $goodIssue->items()->where('id', $approval['id'])->first();
                if (!$item) continue;

                $quantityApproved = $approval['quantity_approved'] ?? $item->quantity_issued;

                // Validate stock availability
                $lastLogItemStock = ItemStock::where('item_id', $item->item_id)
                    ->latest('id')
                    ->first();

                $currentStock = $lastLogItemStock?->last_stock ?? 0;

                if ($quantityApproved > $currentStock) {
                    throw new \Exception("Jumlah yang disetujui ({$quantityApproved}) melebihi stok yang tersedia ({$currentStock}) untuk item {$item->item?->name}.");
                }

                $item->update([
                    'quantity_approved' => $quantityApproved,
                    'note_received' => $approval['note_received'] ?? null,
                ]);
            }

            // Refresh items to get updated data
            $goodIssue->refresh();
            $goodIssue->load('items');

            // Update stock for each approved item
            foreach ($goodIssue->items as $item) {
                $quantityApproved = $item->quantity_approved ?? $item->quantity_issued;

                $lastLogItemStock = ItemStock::where('item_id', $item->item_id)
                    ->latest('id')
                    ->first();

                $initialStock = $lastLogItemStock?->last_stock ?? 0;
                $lastStock = $initialStock - $quantityApproved;

                ItemStock::create([
                    'item_id' => $item->item_id,
                    'type' => 'Out',
                    'source_type' => GoodIssue::class,
                    'source_id' => $goodIssue->id,
                    'initial_stock' => $initialStock,
                    'amount' => $quantityApproved,
                    'last_stock' => $lastStock,
                ]);
            }
        });
    }
}

