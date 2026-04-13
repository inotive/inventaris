<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'description')) {
                $table->text('description')->nullable()->after('code');
            }
            if (!Schema::hasColumn('items', 'min_stock')) {
                $table->integer('min_stock')->default(0)->after('description');
            }
            if (!Schema::hasColumn('items', 'image_path')) {
                $table->string('image_path')->nullable()->after('min_stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'image_path')) {
                $table->dropColumn('image_path');
            }
            if (Schema::hasColumn('items', 'min_stock')) {
                $table->dropColumn('min_stock');
            }
            if (Schema::hasColumn('items', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
