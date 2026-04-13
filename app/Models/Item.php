<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'unit_id',
        'category_id',
        'name',
        'code',
        'description',
        'min_stock',
        'image_path',
    ];

    protected $casts = [
        'min_stock' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function stocks()
    {
        return $this->hasMany(ItemStock::class, 'item_id')->orderByDesc('id');
    }
    public function stock()
    {
        return $this->hasOne(ItemStock::class, 'item_id')->latest();
    }

    public function transfers()
    {
        return $this->hasMany(GoodTransferItem::class, 'item_id');
    }

    public function movements()
    {
        return $this->hasMany(ItemMovement::class, 'item_id');
    }

    public function goodIssueItems()
    {
        return $this->hasMany(GoodIssueItem::class, 'item_id');
    }

    public function goodReceiptItems()
    {
        return $this->hasMany(GoodReceiptItem::class, 'item_id');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'item_id');
    }

    public function purchaseRequestItems()
    {
        return $this->hasMany(PurchaseRequestItem::class, 'item_id');
    }

    public function materialRequestItems()
    {
        return $this->hasMany(MaterialRequestItem::class, 'item_id');
    }

    // Public URL for image
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) return null;
        $path = ltrim($this->image_path, '/');
        // If already a URL (e.g., http or https), return as is
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }
        return asset('storage/' . $path);
    }
}
