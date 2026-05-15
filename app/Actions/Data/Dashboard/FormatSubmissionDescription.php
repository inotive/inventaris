<?php

namespace App\Actions\Data\Dashboard;

class FormatSubmissionDescription
{
    public function execute($submission)
    {
        $employeeName = $submission->employee->name ?? 'Karyawan';
        $type = $submission->type ?? 'submission';

        switch ($type) {
            case 'sick':
                return "Karyawan '{$employeeName}' izin sakit";
            case 'overtime':
                return "Karyawan '{$employeeName}' lembur";
            case 'leave':
                return "Karyawan '{$employeeName}' izin cuti";
            case 'debt':
                return "Karyawan '{$employeeName}' mengajukan pinjaman";
            case 'other':
                return "Karyawan '{$employeeName}' mengajukan permohonan lain";
            default:
                return "Karyawan '{$employeeName}' mengajukan {$type}";
        }
    }
}
