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
        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->decimal('quantity_requested', 18, 4)->change();
            $table->decimal('quantity_approved', 18, 4)->nullable()->change();
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('quantity_ordered', 18, 4)->change();
            $table->decimal('quantity_received', 18, 4)->default(0)->change();
        });

        Schema::table('good_receipt_items', function (Blueprint $table) {
            $table->decimal('quantity_received', 18, 4)->change();
            $table->decimal('quantity_transferred', 18, 4)->default(0)->change();
        });

        Schema::table('good_transfer_items', function (Blueprint $table) {
            $table->decimal('quantity_transferred', 18, 4)->change();
            $table->decimal('quantity_received', 18, 4)->default(0)->change();
        });

        Schema::table('good_issue_items', function (Blueprint $table) {
            $table->decimal('quantity_issued', 18, 4)->change();
            $table->decimal('quantity_approved', 18, 4)->nullable()->change();
        });

        Schema::table('material_request_items', function (Blueprint $table) {
            $table->decimal('quantity_requested', 18, 4)->change();
            $table->decimal('quantity_approved', 18, 4)->nullable()->change();
        });

        Schema::table('item_disposals', function (Blueprint $table) {
            $table->decimal('quantity', 18, 4)->change();
        });

        Schema::table('item_stocks', function (Blueprint $table) {
            $table->decimal('initial_stock', 18, 4)->nullable()->change();
            $table->decimal('amount', 18, 4)->nullable()->change();
            $table->decimal('last_stock', 18, 4)->change();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->decimal('min_stock', 18, 4)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->integer('min_stock')->default(0)->change();
        });

        Schema::table('item_stocks', function (Blueprint $table) {
            $table->integer('initial_stock')->nullable()->change();
            $table->integer('amount')->nullable()->change();
            $table->integer('last_stock')->change();
        });

        Schema::table('item_disposals', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('material_request_items', function (Blueprint $table) {
            $table->integer('quantity_requested')->change();
            $table->integer('quantity_approved')->nullable()->change();
        });

        Schema::table('good_issue_items', function (Blueprint $table) {
            $table->integer('quantity_issued')->change();
            $table->integer('quantity_approved')->nullable()->change();
        });

        Schema::table('good_transfer_items', function (Blueprint $table) {
            $table->integer('quantity_transferred')->change();
            $table->integer('quantity_received')->default(0)->change();
        });

        Schema::table('good_receipt_items', function (Blueprint $table) {
            $table->integer('quantity_received')->change();
            $table->integer('quantity_transferred')->default(0)->change();
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->integer('quantity_ordered')->change();
            $table->integer('quantity_received')->default(0)->change();
        });

        Schema::table('purchase_request_items', function (Blueprint $table) {
            $table->integer('quantity_requested')->change();
            $table->integer('quantity_approved')->nullable()->change();
        });
    }
};
