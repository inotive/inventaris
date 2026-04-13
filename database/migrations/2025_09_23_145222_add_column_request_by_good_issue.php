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
        Schema::table('good_issues', function (Blueprint $table) {
            $table->string('kode_usage')->after('department_id')->unique()->nullable();
            $table->foreignId('request_by')->after('kode_usage')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('approved_by')->after('request_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->datetime('approved_at')->after('approved_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('good_issues', function (Blueprint $table) {
            $table->dropColumn('kode_usage');
            $table->dropForeign(['request_by']);
            $table->dropColumn('request_by');
            $table->dropForeign(['approved_by']);
            $table->dropColumn('approved_by');
            $table->dropColumn('approved_at');
        });
    }
};
