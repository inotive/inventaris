<?php

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
        Schema::table('item_stocks', function (Blueprint $table) {
            $table->string('type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_stocks', function (Blueprint $table) {
            // Note: Reverting to enum might cause data loss if there are 'adjust_in', 'transfer_in', etc.
            $table->enum('type', ['In', 'Out'])->change();
        });
    }
};
