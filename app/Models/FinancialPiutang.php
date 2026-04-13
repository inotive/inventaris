<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialPiutang extends Model
{
    protected $fillable = ['financial_information_id', 'debtor_name', 'amount', 'due_date'];

    public function financialInformation()
    {
        return $this->belongsTo(FinancialInformation::class);
    }
}
