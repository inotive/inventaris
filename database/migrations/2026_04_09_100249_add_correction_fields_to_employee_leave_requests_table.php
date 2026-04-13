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
        Schema::table('employee_leave_requests', function (Blueprint $table) {
            $table->date('corrected_start_date')->nullable()->after('end_date');
            $table->date('corrected_end_date')->nullable()->after('corrected_start_date');
            $table->unsignedBigInteger('corrected_by')->nullable()->after('corrected_end_date');
            $table->timestamp('corrected_at')->nullable()->after('corrected_by');
            $table->text('correction_reason')->nullable()->after('corrected_at');

            $table->foreign('corrected_by')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_leave_requests', function (Blueprint $table) {
            $table->dropForeign(['corrected_by']);
            $table->dropColumn([
                'corrected_start_date',
                'corrected_end_date',
                'corrected_by',
                'corrected_at',
                'correction_reason',
            ]);
        });
    }
};
