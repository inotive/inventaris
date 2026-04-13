<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('item_movements', function (Blueprint $table) {
            if (Schema::hasColumn('item_movements', 'warehouse_id') && !Schema::hasColumn('item_movements', 'branch_id')) {
                $table->renameColumn('warehouse_id', 'branch_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_movements', function (Blueprint $table) {
            if (Schema::hasColumn('item_movements', 'branch_id') && !Schema::hasColumn('item_movements', 'warehouse_id')) {
                $table->renameColumn('branch_id', 'warehouse_id');
            }
        });
    }
};
