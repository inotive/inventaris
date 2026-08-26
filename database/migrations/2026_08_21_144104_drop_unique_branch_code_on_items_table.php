<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nama index unique yang dibuat oleh migrasi
     * 2026_06_25_140548_alter_items_table_change_unique_code.php
     */
    private const UNIQUE_INDEX = 'items_branch_id_code_unique';

    /**
     * Lepas constraint unique(branch_id, code) agar kode barang yang sudah
     * diarsipkan (soft delete) tetap bisa dipakai ulang untuk barang baru.
     * Keunikan kode barang aktif divalidasi di level aplikasi (ItemController).
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Index biasa dibuat lebih dulu sebagai pengganti agar foreign key
            // pada kolom branch_id tidak kehilangan index yang mendukungnya.
            $table->index(['branch_id', 'code'], 'items_branch_id_code_index');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropUnique(self::UNIQUE_INDEX);
        });
    }

    public function down(): void
    {
        // down() hanya aman dijalankan jika tidak ada duplikat (branch_id, code),
        // termasuk antara barang aktif dan barang terarsip.
        Schema::table('items', function (Blueprint $table) {
            $table->unique(['branch_id', 'code'], self::UNIQUE_INDEX);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('items_branch_id_code_index');
        });
    }
};
