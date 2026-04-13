<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleHistoryOdometer extends Model
{
    use HasFactory;

    protected $table = 'vehicle_history_odometers';

    protected $fillable = [
        'vehicle_id',
        'inspection_id',
        'tanggal',
        'last_km',
        'current_km',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'last_km' => 'integer',
        'current_km' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
