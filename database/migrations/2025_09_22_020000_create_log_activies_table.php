<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_activies', function (Blueprint $table) {
            // No numeric id per request
            $table->unsignedBigInteger('users_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->text('description');
            $table->timestamps();

            $table->index(['users_id']);
            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_activies');
    }
};
