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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->nullable()->constrained('material_requests')->nullOnDelete();
            $table->string('request_no')->unique();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('employees')->restrictOnDelete();
            $table->datetime('requested_at');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->restrictOnDelete();
            $table->datetime('approved_at')->nullable();
            $table->string('requirement')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('Pending')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
