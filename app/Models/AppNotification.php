<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'pesan',
        'status',
        'is_success',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
