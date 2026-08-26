<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_disposals', function (Blueprint $table) {
            $table->id();
            $table->string('disposal_no')->unique();
            $table->foreignId('item_id')->constrained('items');
            $table->integer('quantity');
            $table->enum('reason', ['rusak', 'hilang'])->default('rusak');
            $table->text('description')->nullable();
            $table->string('source_type')->nullable(); // polymorphic: GoodReceipt or GoodTransfer
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('employees');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->date('disposal_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_disposals');
    }
};
