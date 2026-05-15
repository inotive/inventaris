<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'employee_id',
        'inspection_id',
        'answer',
        'note',
        'attachment',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attachments()
    {
        return $this->hasMany(AnswerHasAttachment::class, 'answer_id');
    }
}
