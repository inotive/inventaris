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
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            // $table->foreignId('permission_id')->nullable()->constrained('permissions');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->date('working_start_date')->nullable();
            $table->bigInteger('salary')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'branch_id',
                'department_id',
                'position_id',
                'shift_id',
                'working_star_date',
                'salary'
            ]);
        });
    }
};
