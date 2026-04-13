<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'weekly_pattern')) {
                // JSON of weekdays applicability, e.g. {"mon":true,"tue":true,...}
                $table->json('weekly_pattern')->nullable()->after('late_tolerance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            if (Schema::hasColumn('shifts', 'weekly_pattern')) {
                $table->dropColumn('weekly_pattern');
            }
        });
    }
};
