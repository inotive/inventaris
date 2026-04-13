<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add quotas to roles
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'leave_quota_per_year')) {
                $table->integer('leave_quota_per_year')->default(12)->after('name');
            }
            if (!Schema::hasColumn('roles', 'loan_quota')) {
                $table->bigInteger('loan_quota')->default(0)->after('leave_quota_per_year');
            }
        });

        // Add quotas to employees
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'leave_quota_per_year')) {
                $table->integer('leave_quota_per_year')->default(0)->after('salary');
            }
            if (!Schema::hasColumn('employees', 'loan_quota')) {
                $table->bigInteger('loan_quota')->default(0)->after('leave_quota_per_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'loan_quota')) $table->dropColumn('loan_quota');
            if (Schema::hasColumn('roles', 'leave_quota_per_year')) $table->dropColumn('leave_quota_per_year');
        });
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'loan_quota')) $table->dropColumn('loan_quota');
            if (Schema::hasColumn('employees', 'leave_quota_per_year')) $table->dropColumn('leave_quota_per_year');
        });
    }
};
