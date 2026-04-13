<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_receivable_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('receivable_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('paid_date');
            $table->unsignedBigInteger('amount');
            $table->string('method')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('receivable_id')->references('id')->on('receivables')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->index(['employee_id','paid_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_receivable_payments');
    }
};
