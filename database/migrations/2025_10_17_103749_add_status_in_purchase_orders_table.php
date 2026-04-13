<?php

use App\Models\PurchaseOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->string('status_invoice')->default(PurchaseOrder::STATUS_INVOICE_BELUM_LUNAS)->after('note');
            $table->string('status_delivered')->default(PurchaseOrder::STATUS_DELIVERED_PENDING)->after('status_invoice');
            $table->integer('paid_amount')->default(0)->after('status_delivered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('status')->default('invoice')->after('note');
            $table->dropColumn('status_invoice');
            $table->dropColumn('status_delivered');
            $table->dropColumn('paid_amount');
        });
    }
};
