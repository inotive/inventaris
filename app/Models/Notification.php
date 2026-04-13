<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'model_type',
        'model_id',
        'category',
        'additional_data',
        'pesan',
        'status',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'status' => 'integer',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related model (polymorphic).
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        return $this->update(['status' => 1]);
    }
}
