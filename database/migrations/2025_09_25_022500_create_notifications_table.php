<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('title')->nullable();
            $table->string('category')->nullable();
            $table->text('pesan');
            $table->unsignedTinyInteger('status')->default(0); // 0 = baru, 1 = terbaca, 2 = selesai
            $table->boolean('is_success')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['model_type', 'model_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
