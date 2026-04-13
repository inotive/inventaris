<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'name',
        'status',
        'description',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_departemens', 'department_id', 'checklist_id');
    }
}
