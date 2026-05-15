<?php

namespace App\Models;

use Database\Seeders\VehicleSeeder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_type_id',
        'branch_id',
        'route',
        'employee_id',
        'track',
        'license_code',
        'chassis_code',
        'machine_code',
        'status',
    ];

    /**
     * Attributes yang ditambahkan secara dinamis (tidak ada di database)
     */
    protected $appends = [];

    /**
     * Attributes yang bisa di-cast
     */
    protected $casts = [
        'daily_km' => 'array',
    ];

    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function documents()
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function services()
    {
        return $this->hasMany(VehicleService::class);
    }

    public function odometers()
    {
        return $this->hasMany(VehicleHistoryOdometer::class, 'vehicle_id');
    }

    public function latestOdometer()
    {
        // get the latest by tanggal, then id as tiebreaker
        return $this->hasOne(VehicleHistoryOdometer::class, 'vehicle_id')->latestOfMany('tanggal');
    }

    public function latestService()
    {
        return $this->hasOne(VehicleService::class, 'vehicle_id')->latestOfMany('date');
    }

    public function inspections()
    {
        return $this->morphMany(Inspection::class, 'model');
    }

    public function checklists()
    {
        return $this->hasMany(Checklist::class);
    }

    public function files()
    {
        return $this->hasMany(VehicleFilePath::class);
    }
}
