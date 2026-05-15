<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePerformance extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'category',
        'score',
        'notes',
        'reported_by',
    ];

    protected $casts = [
        'score' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Get the score attribute, handling both numeric and array values
     * For format { score: value } (Kehadiran, Checklist), return as array
     * For array values (Productivity, Attitude, Initiative), return as array
     */
    public function getScoreAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        // If it's already decoded (from cast), return as is
        if (is_array($value)) {
            return $value;
        }

        // If it's a JSON string, decode it
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        // If it's numeric (legacy), convert to { score: value } format
        if (is_numeric($value)) {
            return ['score' => $value];
        }

        return $value;
    }

    /**
     * Set the score attribute, handling both numeric and array values
     */
    public function setScoreAttribute($value)
    {
        // If it's numeric, store as JSON with { score: value } format
        if (is_numeric($value)) {
            $this->attributes['score'] = json_encode(['score' => $value]);
            return;
        }

        // If it's already an array, encode it
        if (is_array($value)) {
            $this->attributes['score'] = json_encode($value);
            return;
        }

        // Otherwise, store as is (will be handled by cast)
        $this->attributes['score'] = $value;
    }

}
