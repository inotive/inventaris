<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'basic_salary',
        'performance_allowance',
        'meal_allowance',
        'bpjs_health_allowance',
        'bpjs_employment_allowance',
        'operational_allowance',
        'overtime_allowance',
        'housing_allowance',
        'holiday_allowance',
        'other_allowance',
        'loan_deduction',
        'catering_deduction',
        'bpjs_health_deduction',
        'bpjs_employment_deduction',
        'pph21_deduction',
        'cash_advance_deduction',
        'operational_deduction',
        'other_deduction',
    ];

    // ✅ Tambahkan relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function piutangs()
    {
        return $this->hasMany(FinancialPiutang::class);
    }

    public function bonuses()
    {
        return $this->hasMany(FinancialBonus::class);
    }
}
