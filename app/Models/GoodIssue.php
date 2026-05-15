<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'kode_usage',
        'department_id',
        'request_by',
        'date',
        'requirement',
        'status',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (GoodIssue $goodIssue) {
            $goodIssue->kode_usage = static::generateKodeUsage($goodIssue->request_id);
        });
    }

    public static function generateKodeUsage()
    {
        $year = date('Y');
        $prefix = 'USG-' . $year . '-';

        // Cari kode terakhir untuk tahun ini
        $lastKode = self::where('kode_usage', 'like', $prefix . '%')
            ->orderByDesc('kode_usage')
            ->value('kode_usage');

        if ($lastKode) {
            // Ambil increment terakhir
            $lastIncrement = (int) substr($lastKode, strrpos($lastKode, '-') + 1);
            $nextIncrement = $lastIncrement + 1;
        } else {
            $nextIncrement = 1;
        }

        $kode = $prefix . str_pad($nextIncrement, 5, '0', STR_PAD_LEFT);

        // Pastikan kode_usage tidak duplikat (jika ada race condition)
        while (self::where('kode_usage', $kode)->exists()) {
            $nextIncrement++;
            $kode = $prefix . str_pad($nextIncrement, 5, '0', STR_PAD_LEFT);
        }

        return $kode;
    }

    public function request()
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function items()
    {
        return $this->hasMany(GoodIssueItem::class);
    }

    public function item()
    {
        return $this->belongsToMany(Item::class, GoodIssueItem::class);
    }

    public function requestBy()
    {
        return $this->belongsTo(Employee::class, 'request_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function stocks()
    {
        return $this->morphMany(ItemStock::class, 'source');
    }

    public function activities()
    {
        return $this->hasMany(GoodIssueActivity::class)->orderBy('created_at', 'desc');
    }
}
