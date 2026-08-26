<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop unique/index on code using try-catch blocks to prevent errors if they don't exist
        try {
            Schema::table('items', function (Blueprint $table) {
                $table->dropUnique('items_code_unique');
            });
        } catch (\Throwable $e) {}

        try {
            Schema::table('items', function (Blueprint $table) {
                $table->dropIndex('items_code_index');
            });
        } catch (\Throwable $e) {}

        Schema::table('items', function (Blueprint $table) {
            $table->unique(['branch_id', 'code']);
        });
    }

    public function down(): void
    {
        try {
            Schema::table('items', function (Blueprint $table) {
                $table->dropUnique(['branch_id', 'code']);
            });
        } catch (\Throwable $e) {}

        try {
            Schema::table('items', function (Blueprint $table) {
                $table->unique('code');
            });
        } catch (\Throwable $e) {}
    }
};
