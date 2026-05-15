<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items');
            // use unsignedBigInteger to avoid hard FK if warehouses table not present in this module
            $table->unsignedBigInteger('warehouse_id');
            // Movement direction/type
            $table->string('type'); // in, out, adjust_in, adjust_out, transfer_in, transfer_out, return_in
            $table->decimal('quantity', 18, 4);
            // Reference info
            $table->string('reference_type')->nullable(); // purchase_order, sales_return, material_request, transfer_stock, adjustment, manual, etc
            $table->unsignedBigInteger('reference_id')->nullable();
            // Stock snapshot
            $table->decimal('last_stock', 18, 4)->default(0);
            $table->decimal('current_stock', 18, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'warehouse_id']);
            $table->index(['item_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_movements');
    }
};
