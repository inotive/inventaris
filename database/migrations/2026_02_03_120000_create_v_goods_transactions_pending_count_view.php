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
        DB::statement("
            CREATE OR REPLACE VIEW v_goods_transactions_pending_count AS
            SELECT 
                'material_requests' as transaction_type, 
                COUNT(*) as pending_count 
            FROM material_requests 
            WHERE status = 'on_progress'
            
            UNION ALL
            
            SELECT 
                'purchase_requests' as transaction_type, 
                COUNT(*) as pending_count 
            FROM purchase_requests 
            WHERE status = 'on_progress'
            
            UNION ALL
            
            SELECT 
                'purchase_orders' as transaction_type, 
                COUNT(*) as pending_count 
            FROM purchase_orders 
            WHERE status_delivered = 'pending'
            
            UNION ALL
            
            SELECT 
                'good_issues' as transaction_type, 
                COUNT(*) as pending_count 
            FROM good_issues 
            WHERE status = 'on_progress'
            
            UNION ALL
            
            SELECT 
                'good_transfers' as transaction_type, 
                COUNT(*) as pending_count 
            FROM good_transfers 
            WHERE status = 'pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_goods_transactions_pending_count");
    }
};
