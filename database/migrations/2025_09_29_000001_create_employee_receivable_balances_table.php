<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_receivable_balances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedSmallInteger('period_year')->nullable();
            $table->unsignedTinyInteger('period_month')->nullable();
            $table->unsignedBigInteger('limit_amount');
            $table->unsignedBigInteger('used_amount')->default(0);
            $table->unsignedBigInteger('remaining_amount')->default(0);
            $table->enum('policy', ['monthly','yearly','none'])->default('monthly');
            $table->timestamps();

            $table->unique(['employee_id','period_year','period_month'], 'uniq_employee_period');
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_receivable_balances');
    }
};
