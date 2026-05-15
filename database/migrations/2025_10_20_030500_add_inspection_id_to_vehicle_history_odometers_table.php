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
        Schema::table('vehicle_history_odometers', function (Blueprint $table) {
            // Tambahkan kolom inspection_id nullable
            if (!Schema::hasColumn('vehicle_history_odometers', 'inspection_id')) {
                $table->foreignId('inspection_id')
                    ->nullable()
                    ->after('vehicle_id')
                    ->constrained('inspections')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_history_odometers', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_history_odometers', 'inspection_id')) {
                // Hapus constraint dan kolom
                $table->dropForeign(['inspection_id']);
                $table->dropColumn('inspection_id');
            }
        });
    }
};
