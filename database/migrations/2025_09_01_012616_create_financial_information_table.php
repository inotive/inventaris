<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_information', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); 
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('performance_allowance', 15, 2)->default(0);
            $table->decimal('meal_allowance', 15, 2)->default(0);
            $table->decimal('bpjs_health_allowance', 15, 2)->default(0);
            $table->decimal('bpjs_employment_allowance', 15, 2)->default(0);
            $table->decimal('operational_allowance', 15, 2)->default(0);
            $table->decimal('overtime_allowance', 15, 2)->default(0);
            $table->decimal('housing_allowance', 15, 2)->default(0);
            $table->decimal('holiday_allowance', 15, 2)->default(0);
            $table->decimal('other_allowance', 15, 2)->default(0);
            $table->decimal('total_income', 15, 2)->default(0);

            $table->decimal('loan_deduction', 15, 2)->default(0);
            $table->decimal('catering_deduction', 15, 2)->default(0);
            $table->decimal('bpjs_health_deduction', 15, 2)->default(0);
            $table->decimal('bpjs_employment_deduction', 15, 2)->default(0);
            $table->decimal('pph21_deduction', 15, 2)->default(0);
            $table->decimal('cash_advance_deduction', 15, 2)->default(0);
            $table->decimal('operational_deduction', 15, 2)->default(0);
            $table->decimal('other_deduction', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_information');
    }
};
