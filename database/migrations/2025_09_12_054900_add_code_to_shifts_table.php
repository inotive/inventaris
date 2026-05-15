<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
        });

        // Backfill existing rows with sequential codes S1, S2, ... based on id order
        $rows = DB::table('shifts')->orderBy('id')->get(['id']);
        $n = 1;
        foreach ($rows as $row) {
            DB::table('shifts')->where('id', $row->id)->update(['code' => 'S' . $n]);
            $n++;
        }
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
