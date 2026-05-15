<?php

namespace App\Models;

use Database\Seeders\AnswerSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',
        'category_id',
        'question',
        'required',
        'guidance',
        'answer_type',
    ];

    public function category()
    {
        return $this->belongsTo(QuestionCategory::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
