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
        Schema::table('items', function (Blueprint $table) {
            // min_stock: minimal stock threshold; using decimal for precision
            if (!Schema::hasColumn('items', 'min_stock')) {
                $table->decimal('min_stock', 15, 2)->default(0)->after('code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'min_stock')) {
                $table->dropColumn('min_stock');
            }
        });
    }
};
