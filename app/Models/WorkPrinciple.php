<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkPrinciple extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'title',
        'description',
        'created_by',
        'img_url',
    ];
}
