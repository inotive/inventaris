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
        Schema::table('good_receipt_items', function (Blueprint $table) {
            $table->integer('quantity_transferred')->after('quantity_received')->default(0);
            $table->text('note_transferred')->after('quantity_transferred')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_receipt_items', function (Blueprint $table) {
            $table->dropColumn('quantity_transferred');
            $table->dropColumn('note_transferred');
        });
    }
};
