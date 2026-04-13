<?php

namespace App\Actions\Data\Item;

use App\Models\Item;
use App\Models\ItemStock;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class UpdateStockItem
{
    /**
     * Buat transaksi stok baru untuk sebuah item.
     * Menerima: type = in|out|transfer_in|transfer_out (case-insensitive)
     *           amount ATAU quantity
     */
    public function execute(array $data, Item $item): ItemStock
    {
        $type = strtolower($data['type'] ?? 'in');
        $qty  = (float) ($data['amount'] ?? $data['quantity'] ?? 0);

        if (! in_array($type, ['in','out','transfer_in','transfer_out'], true)) {
            throw new InvalidArgumentException('Tipe stok tidak valid');
        }
        if ($qty <= 0) {
            throw new InvalidArgumentException('Jumlah/amount harus > 0');
        }

        // Ambil stok terakhir (pakai id desc biar konsisten)
        $last = $item->stocks()->orderByDesc('id')->first();
        $initial = $last?->last_stock ?? 0.0;

        $lastStock = match ($type) {
            'in','transfer_in'     => $initial + $qty,
            'out','transfer_out'   => $initial - $qty,
        };

        $payload = [
            'item_id'       => $item->id,
            'type'          => $type,
            'source_type'   => $data['source_type'] ?? null,   // <-- perbaiki (tadinya $data['source'])
            'source_id'     => $data['source_id'] ?? null,
            'initial_stock' => $initial,
            'amount'        => $qty,
            'last_stock'    => $lastStock,
            'note'          => $data['note'] ?? null,
            'tanggal'       => $data['tanggal'] ?? now()->toDateString(),
            // backdate created_at agar list urut sesuai tanggal input (sesuai implementasi store() kamu)
            'created_at'    => $data['tanggal'] ?? now(),
            'updated_at'    => now(),
        ];

        return ItemStock::create($payload);
    }
}
