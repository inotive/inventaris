<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_principles', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['prinsip', 'etos kerja']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('img_url')->nullable();
            $table->timestamps();

            $table->index(['category', 'created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_principles');
    }
};
