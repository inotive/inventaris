<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop view if exists
        DB::statement('DROP VIEW IF EXISTS v_goods_transactions_pending_count');

        // Create view untuk transaksi barang (hanya tabel yang punya status)
        DB::statement("
            CREATE VIEW v_goods_transactions_pending_count AS
            SELECT
                'material_requests' as transaction_type,
                'Permintaan Barang' as type_label,
                COUNT(*) as pending_count
            FROM material_requests
            WHERE status = 'pending'

            UNION ALL

            SELECT
                'purchase_requests' as transaction_type,
                'Pengajuan Pembelian' as type_label,
                COUNT(*) as pending_count
            FROM purchase_requests
            WHERE status = 'on_progress'

            UNION ALL

            SELECT
                'good_transfers' as transaction_type,
                'Perpindahan Barang' as type_label,
                COUNT(*) as pending_count
            FROM good_transfers
            WHERE status = 'on_progress'

            UNION ALL

            SELECT
                'good_issues' as transaction_type,
                'Pemakaian Barang' as type_label,
                COUNT(*) as pending_count
            FROM good_issues
            WHERE status = 'on_progress'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_goods_transactions_pending_count');
    }
};
