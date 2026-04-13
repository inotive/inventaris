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
        Schema::table('vehicle_services', function (Blueprint $table) {
            $table->string('category_name')->nullable()->after('vehicle_id');
            $table->string('sub_category_name')->nullable()->after('category_name');
            $table->integer('cost')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_services', function (Blueprint $table) {
            $table->dropColumn(['category_name', 'sub_category_name']);
            $table->integer('cost')->nullable(false)->change();
        });
    }
};
