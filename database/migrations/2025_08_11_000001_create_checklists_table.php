<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sop_code')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('checklist_categories')->nullOnDelete();
            // $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            // $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['Draft', 'Active', 'Inactive'])->default('Draft');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklists');
    }
};
