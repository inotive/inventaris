<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodIssueActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'good_issue_id',
        'title',
        'description',
        'created_by',
    ];

    public function goodIssue()
    {
        return $this->belongsTo(GoodIssue::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

