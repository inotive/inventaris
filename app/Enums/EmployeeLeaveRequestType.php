<?php

namespace App\Enums;

/**
 * Enum EmployeeLeaveRequestType
 *
 * Represents the types of submissions available in the system.
 */
enum EmployeeLeaveRequestType: int
{
    case SICK = 1;
    case ANNUAL_LEAVE = 2;
    case OTHERS = 3;
    case OVERTIME = 4;
    case DEBT = 5;

    public function label(): string
    {
        return match ($this) {
            self::SICK => 'Sakit',
            self::ANNUAL_LEAVE => 'Cuti Tahunan',
            self::OTHERS => 'Khusus',
            self::OVERTIME => 'Lembur',
            self::DEBT => 'Piutang',
        };
    }

}

