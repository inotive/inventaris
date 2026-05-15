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
        Schema::table('vehicle_file_paths', function (Blueprint $table) {
            // Check and drop existing foreign keys if they exist
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'vehicle_file_paths'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            foreach ($foreignKeys as $fk) {
                if (str_contains($fk->CONSTRAINT_NAME, 'document_id')) {
                    $table->dropForeign(['document_id']);
                }
                if (str_contains($fk->CONSTRAINT_NAME, 'service_id')) {
                    $table->dropForeign(['service_id']);
                }
            }

            // Add new foreign key constraints
            $table->foreign('document_id')->references('id')->on('vehicle_documents')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('vehicle_services')->cascadeOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_file_paths', function (Blueprint $table) {
            // Drop the new foreign keys
            $table->dropForeign(['document_id']);
            $table->dropForeign(['service_id']);

            // Restore original foreign keys to vehicles table
            $table->foreign('document_id')->references('id')->on('vehicles')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('vehicles')->cascadeOnDelete();
        });
    }
};
