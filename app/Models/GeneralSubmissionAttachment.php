<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSubmissionAttachment extends Model
{
    protected $fillable = [
        'general_submission_id',
        'file_path',
        'file_name',
        'file_url',
        'file_type',
        'file_size',
    ];

    public function generalSubmission()
    {
        return $this->belongsTo(GeneralSubmission::class);
    }
}
