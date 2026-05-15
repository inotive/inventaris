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
        Schema::table('submissions', function (Blueprint $table) {
            // Change the type column to be a string with a default value
            $table->string('type')->default('others')->change();
            
            // Add an index for better performance on type filtering
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            // Remove the index
            $table->dropIndex(['type']);
            
            // Revert the type column to its original state if needed
            // Note: This is a simplified version - adjust according to your original schema
            $table->string('type')->nullable()->change();
        });
    }
};
