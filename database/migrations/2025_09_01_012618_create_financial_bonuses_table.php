<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_bonuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('financial_information_id');
            $table->string('title');
            $table->date('bonus_date');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('financial_information_id')
                ->references('id')
                ->on('financial_information')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('financial_bonuses', function (Blueprint $table) {
            $table->dropForeign(['financial_information_id']);
        });
        Schema::dropIfExists('financial_bonuses');
    }
};
