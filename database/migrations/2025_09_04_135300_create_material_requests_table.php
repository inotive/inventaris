<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no')->unique();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->foreignId('requested_by')->constrained('employees')->restrictOnDelete();
            $table->datetime('requested_at');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->datetime('approved_at')->nullable();
            $table->string('requirement')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('Pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};
