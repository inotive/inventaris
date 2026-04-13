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
        Schema::create('good_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no')->unique();
            $table->foreignId('from_branch')->constrained('branches');
            $table->foreignId('to_branch')->constrained('branches');
            $table->foreignId('sent_by')->nullable()->constrained('employees');
            $table->foreignId('received_by')->nullable()->constrained('employees');
            $table->text('purpose')->nullable();
            $table->string('status')->default('Shipped');
            $table->datetime('transferred_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('good_transfers');
    }
};
