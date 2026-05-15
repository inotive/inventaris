<?php

namespace App\Enums;

/**
 * Enum SubmissionTypeEnum
 *
 * Represents the types of submissions available in the system.
 */
enum SubmissionTypeEnum: int
{
    case all = 0;
    case SICK = 1;
    case ANNUAL_LEAVE = 2;
    case OTHERS = 3;
    case OVERTIME = 4;
    case DEBT = 5;
    case REIMBURSEMENT = 6;
    case GENERAL = 7;
    case EMPLOYEE = 8;

    public function label(): array
    {
        return match ($this) {
            self::all => [
                'name' => 'submissions',
                'label' => 'Semua Pengajuan',
                'permission' => 'submission_all.view',
            ],
            self::SICK => [
                'name' => 'sick',
                'label' => 'Sakit',
                'permission' => 'submission_sick.view',
            ],
            self::ANNUAL_LEAVE => [
                'name' => 'annual-leave',
                'label' => 'Cuti Tahunan',
                'permission' => 'submission_leave.view',
            ],
            self::OTHERS => [
                'name' => 'others',
                'label' => 'Izin Lainnya',
                'permission' => 'submission_others.view',
            ],
            self::OVERTIME => [
                'name' => 'overtime',
                'label' => 'Lembur',
                'permission' => 'submission_overtime.view',
            ],
            self::DEBT => [
                'name' => 'debt',
                'label' => 'Piutang',
                'permission' => 'submission_debt.view',
            ],
            self::REIMBURSEMENT => [
                'name' => 'reimbursement',
                'label' => 'Reimbursement',
                'permission' => 'submission_reimbursement.view',
            ],
            self::EMPLOYEE => [
                'name' => 'employee',
                'label' => 'Karyawan',
                'permission' => 'submission_daily.view',
            ],
            self::GENERAL => [
                'name' => 'general',
                'label' => 'Pengajuan Umum',
                'permission' => 'submission_general.view',
            ],
        };
    }
}
