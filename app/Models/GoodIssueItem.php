<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodIssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'good_issue_id',
        'item_id',
        'quantity_issued',
        'quantity_approved',
        'note',
        'note_received',
    ];

    public function goodIssue()
    {
        return $this->belongsTo(GoodIssue::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
