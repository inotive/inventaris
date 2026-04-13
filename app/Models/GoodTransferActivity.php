<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodTransferActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'good_transfer_id',
        'title',
        'description',
        'created_by',
    ];

    public function goodTransfer()
    {
        return $this->belongsTo(GoodTransfer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
