<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheklistEmployee extends Model
{
    protected $fillable = [
        'checklist_id',
        'employee_id',
        'status',
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
