<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'late_duration_minutes')) {
                $table->decimal('late_duration_minutes', 8, 2)->nullable()->after('status');
            }
            if (!Schema::hasColumn('attendances', 'overtime_duration_minutes')) {
                $table->decimal('overtime_duration_minutes', 8, 2)->nullable()->after('late_duration_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'overtime_duration_minutes')) {
                $table->dropColumn('overtime_duration_minutes');
            }
            if (Schema::hasColumn('attendances', 'late_duration_minutes')) {
                $table->dropColumn('late_duration_minutes');
            }
        });
    }
};
