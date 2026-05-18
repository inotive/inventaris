<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('good_transfers', function (Blueprint $table) {
            $table->text('attachment_path')->nullable()->change();
            $table->text('attachment_name')->nullable()->change();
        });

        Schema::table('good_receipts', function (Blueprint $table) {
            $table->text('attachment_path')->nullable()->change();
            $table->text('attachment_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('good_transfers', function (Blueprint $table) {
            $table->string('attachment_path', 255)->nullable()->change();
            $table->string('attachment_name', 255)->nullable()->change();
        });

        Schema::table('good_receipts', function (Blueprint $table) {
            $table->string('attachment_path', 255)->nullable()->change();
            $table->string('attachment_name', 255)->nullable()->change();
        });
    }
};
