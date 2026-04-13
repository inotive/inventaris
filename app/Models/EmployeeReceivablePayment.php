<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeReceivablePayment extends Model
{
    protected $fillable = [
        'receivable_id',
        'employee_id',
        'paid_date',
        'amount',
        'method',
        'reference_no',
        'note',
    ];

    public function receivable()
    {
        return $this->belongsTo(Receivable::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
