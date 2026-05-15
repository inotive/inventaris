<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('report_comments', function (Blueprint $table) {
            // Drop old foreign key
            $table->dropForeign(['report_id']);

            // Add new foreign key to reports table
            $table->foreign('report_id')
                ->references('id')
                ->on('reports')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_comments', function (Blueprint $table) {
            // Drop foreign key to reports
            $table->dropForeign(['report_id']);

            // Restore foreign key to daily_reports
            $table->foreign('report_id')
                ->references('id')
                ->on('daily_reports')
                ->onDelete('cascade');
        });
    }
};
