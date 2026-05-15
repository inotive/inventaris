<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            if (!Schema::hasColumn('answers', 'inspection_id')) {
                $table->unsignedBigInteger('inspection_id')->nullable()->after('employee_id');
                $table->foreign('inspection_id')->references('id')->on('inspections')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            if (Schema::hasColumn('answers', 'inspection_id')) {
                $table->dropForeign(['inspection_id']);
                $table->dropColumn('inspection_id');
            }
        });
    }
};
