<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AnswerHasAttachment extends Model
{

    use HasFactory;

    protected $fillable = [
        'answer_id',
        'file',
    ];

    public function answer()
    {
        return $this->belongsTo(Answer::class, 'answer_id');
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }
}
