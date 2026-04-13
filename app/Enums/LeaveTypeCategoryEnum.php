<?php

namespace App\Enums;


/**
 * Enum LeaveTypeCategoryEnum
 *
 * Represents the types of leave type category available in the system.
 */
enum LeaveTypeCategoryEnum: string
{
    case ANNUAL_LEAVE = "annual_leave";
    case SICK_LEAVE = "sick_leave";
    case SPECIAL_LEAVE = "special_leave";

    public function label(): string
    {
        return match ($this) {
            self::SICK_LEAVE => 'Sakit',
            self::ANNUAL_LEAVE => 'Cuti Tahunan',
            self::SPECIAL_LEAVE => 'Cuti Khusus',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}


