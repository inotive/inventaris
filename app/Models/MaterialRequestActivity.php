<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialRequestActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_request_id',
        'title',
        'description',
        'created_by',
    ];

    public function materialRequest()
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
