<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'branch_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'last_stock',
        'current_stock',
        'notes',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    // Backward-compatible alias if some parts still call warehouse()
    public function warehouse(): BelongsTo
    {
        return $this->branch();
    }
}
