<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalarySlip extends Model
{
    protected $fillable = [
        'employee_id',
        'bulan',
        'file_url',
        'upload_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
