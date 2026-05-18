<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemDisposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'disposal_no',
        'item_id',
        'quantity',
        'reason',
        'description',
        'source_type',
        'source_id',
        'reported_by',
        'attachment_path',
        'attachment_name',
        'disposal_date',
    ];

    protected $casts = [
        'disposal_date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function reporter()
    {
        return $this->belongsTo(Employee::class, 'reported_by');
    }

    public function source()
    {
        return $this->morphTo();
    }

    public static function generateDisposalNo(): string
    {
        $ym = now()->format('Ym');
        $prefix = 'DSP-' . $ym . '-';

        $last = static::query()
            ->where('disposal_no', 'like', $prefix . '%')
            ->orderBy('disposal_no', 'desc')
            ->value('disposal_no');

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last);
            $lastSeq = (int) ($parts[2] ?? 0);
            $seq = $lastSeq + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
