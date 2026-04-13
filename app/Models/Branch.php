<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'region',
        'contact',
        'email',
        'address',
        'description',
    ];

    public function department()
    {
        return $this->hasMany(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'branch_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'branch_id');
    }
}
