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
        Schema::table('good_transfer_items', function (Blueprint $table) {
            $table->integer('quantity_received')->after('quantity_transferred')->default(0);
            $table->text('note_received')->after('quantity_received')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_transfer_items', function (Blueprint $table) {
            $table->dropColumn('quantity_received');
            $table->dropColumn('note_received');
        });
    }
};
