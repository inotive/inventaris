<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDayOff extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'name',
    ];
}
