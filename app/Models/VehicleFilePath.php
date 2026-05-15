<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleFilePath extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'document_id',
        'service_id',
        'file_path',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
