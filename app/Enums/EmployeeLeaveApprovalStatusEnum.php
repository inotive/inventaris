<?php

namespace App\Enums;

/**
 * Enum EmployeeLeaveRequestStatusEnum
 *
 * Represents the types of employee leave request status available in the system.
 */
enum EmployeeLeaveApprovalStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

