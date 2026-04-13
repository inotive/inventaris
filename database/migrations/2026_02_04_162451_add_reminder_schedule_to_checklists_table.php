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
        Schema::table('checklists', function (Blueprint $table) {
            $table->time('reminder_time')->default('15:20:00')->after('count')->comment('Jam pengiriman reminder (WITA)');
            $table->enum('reminder_frequency', ['daily', 'weekly', 'monthly'])->default('daily')->after('reminder_time')->comment('Frekuensi pengiriman reminder');
            $table->json('reminder_days')->nullable()->after('reminder_frequency')->comment('Hari-hari pengiriman (untuk weekly: [1,2,3,4,5] = Senin-Jumat)');
            $table->boolean('reminder_enabled')->default(true)->after('reminder_days')->comment('Aktifkan/nonaktifkan reminder otomatis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->dropColumn(['reminder_time', 'reminder_frequency', 'reminder_days', 'reminder_enabled']);
        });
    }
};
