<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'foto_masuk')) {
                $table->string('foto_masuk')->nullable()->after('jam_masuk');
            }
            if (!Schema::hasColumn('attendances', 'foto_keluar')) {
                $table->string('foto_keluar')->nullable()->after('jam_keluar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'foto_masuk')) {
                $table->dropColumn('foto_masuk');
            }
            if (Schema::hasColumn('attendances', 'foto_keluar')) {
                $table->dropColumn('foto_keluar');
            }
        });
    }
};
