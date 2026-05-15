<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialBonus extends Model
{
    protected $fillable = ['financial_information_id', 'employee_name', 'amount'];

    public function financialInformation()
    {
        return $this->belongsTo(FinancialInformation::class);
    }
}
