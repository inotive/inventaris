<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Inspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_id',
        'inspection_number',
        'submit_date',
        'submitted_by',
        'created_by',
        'status',
        'approved_by',
        'approved_date',
        'remarks',
        'location_id',
        'model_type',
        'model_id',
    ];

    protected $casts = [
        'submit_date' => 'datetime',
        'approved_date' => 'datetime',
    ];

    // Relationships
    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function location()
    {
        return $this->belongsTo(Branch::class, 'location_id');
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * Scope untuk menyembunyikan inspeksi duplikat/spam.
     *
     * Jika ada inspeksi dari creator yang sama, checklist_id yang sama,
     * dan status yang sama dengan waktu berdekatan (<= threshold menit),
     * hanya tampilkan yang pertama (id terkecil).
     * Duplikat sisanya di-hide.
     *
     * @param Builder $query
     * @param int $thresholdMinutes Batas menit untuk deteksi duplikat (default 3)
     * @return Builder
     */
    public function scopeExcludeDuplicates(Builder $query, int $thresholdMinutes = 3): Builder
    {
        // Subquery: cari semua ID inspeksi yang merupakan duplikat (bukan yang pertama)
        // Kriteria duplikat:
        // - actor (COALESCE(created_by, submitted_by)) sama
        // - checklist_id sama
        // - status sama
        // - waktu submit/create berdekatan (<= threshold)
        // - id lebih kecil (artinya row saat ini adalah duplikat dari row sebelumnya)
        return $query->whereNotExists(function ($sub) use ($thresholdMinutes) {
            $sub->select(DB::raw(1))
                ->from('inspections as earlier')
                ->whereRaw('COALESCE(earlier.created_by, earlier.submitted_by) = COALESCE(inspections.created_by, inspections.submitted_by)')
                ->whereColumn('earlier.checklist_id', 'inspections.checklist_id')
                ->whereColumn('earlier.status', 'inspections.status')
                ->whereColumn('earlier.id', '<', 'inspections.id')
                ->whereRaw(
                    'ABS(TIMESTAMPDIFF(MINUTE, COALESCE(earlier.submit_date, earlier.created_at), COALESCE(inspections.submit_date, inspections.created_at))) <= ?',
                    [$thresholdMinutes]
                );
        });
    }
}
