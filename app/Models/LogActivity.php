<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;

    protected $table = 'log_activies';
    protected $fillable = [
        'users_id',
        'model_type',
        'model_id',
        'activity',
        'description',
        'data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
