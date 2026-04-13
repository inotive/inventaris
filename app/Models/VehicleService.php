<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleService extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'date',
        'category_name',
        'sub_category_name',
        'distance',
        'cost',
        'note',
    ];

    protected $casts = [
        'category_name' => 'array',
        'sub_category_name' => 'array',
        'date' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function vehicleFilePath()
    {
        return $this->hasMany(VehicleFilePath::class, 'service_id', 'id');
    }
}
