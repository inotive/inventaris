<?php

namespace App\Enums;

/**
 * Enum SubmissionStatusEnum
 *
 * Represents the status of submissions available in the system.
 */

enum SubmissionStatusEnum:int
{
    case Pending = 0;
    case Approved = 1;
    case Rejected = 2;
    case Cancelled = 3;

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'Menunggu',
            self::Approved  => 'Disetujui',
            self::Rejected  => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
