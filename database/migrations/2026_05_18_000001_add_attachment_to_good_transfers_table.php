<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('good_transfers', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('purpose');
            $table->string('attachment_name')->nullable()->after('attachment_path');
        });

        Schema::table('good_receipts', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('note');
            $table->string('attachment_name')->nullable()->after('attachment_path');
        });
    }

    public function down(): void
    {
        Schema::table('good_transfers', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name']);
        });
        Schema::table('good_receipts', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name']);
        });
    }
};
