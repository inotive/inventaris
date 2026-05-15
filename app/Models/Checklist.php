<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sop_code',
        'category_id',
        // 'branch_id',
        'department_id',
        'vehicle_id',
        'status',
        'description',
        'type',
        'durasi',
        'count',
        'reminder_time',
        'reminder_frequency',
        'reminder_days',
        'reminder_enabled',
    ];

    protected $casts = [
        'reminder_days' => 'array',
        'reminder_enabled' => 'boolean',
        'durasi' => 'integer',
        'count' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(ChecklistCategory::class, 'category_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'location_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'cheklist_employees', 'checklist_id', 'employee_id');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'checklist_departemens', 'checklist_id', 'department_id');
    }

    /**
     * Get the type attribute with Indonesian label
     */
    public function getTypeAttribute($value)
    {
        $typeMap = [
            'single' => 'Perorang',
            'multiple' => 'Banyak Orang',
            'personal' => 'Perorang',
            'group' => 'Berkelompok',
        ];

        return $typeMap[strtolower($value)] ?? $value;
    }
}
