<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use App\Enums\LeaveTypeCategoryEnum;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Cuti Tahunan',
                'category' => LeaveTypeCategoryEnum::ANNUAL_LEAVE,
                'leave_quota_per_year' => 12,
                'description' => 'Cuti berbayar tahunan untuk karyawan',
            ],
            [
                'name' => 'Cuti Sakit',
                'category' => LeaveTypeCategoryEnum::SICK_LEAVE,
                'leave_quota_per_year' => 10,
                'description' => 'Cuti ketika karyawan sakit',
            ],
            // Special Leave — detailed reasons
            [
                'name' => 'Menikah',
                'category' => LeaveTypeCategoryEnum::SPECIAL_LEAVE,
                // typical max 3 days
                'leave_quota_per_year' => 3,
                'description' => 'Cuti khusus untuk karyawan yang menikah (maks 3 hari)',
            ],
            [
                'name' => 'Menikahkan Anak',
                'category' => LeaveTypeCategoryEnum::SPECIAL_LEAVE,
                'leave_quota_per_year' => 2,
                'description' => 'Cuti khusus saat menikahkan anak (maks 2 hari)',
            ],
            [
                'name' => 'Anggota Keluarga Serumah Meninggal',
                'category' => LeaveTypeCategoryEnum::SPECIAL_LEAVE,
                'leave_quota_per_year' => 1,
                'description' => 'Cuti khusus ketika anggota keluarga serumah meninggal (maks 1 hari)',
            ],
            [
                'name' => 'Suami/Istri/Orangtua/Anak/Menantu Meninggal',
                'category' => LeaveTypeCategoryEnum::SPECIAL_LEAVE,
                'leave_quota_per_year' => 2,
                'description' => 'Cuti khusus ketika suami/istri, orangtua, anak, atau menantu meninggal (maks 2 hari)',
            ],
            [
                'name' => 'Istri Melahirkan atau Keguguran',
                'category' => LeaveTypeCategoryEnum::SPECIAL_LEAVE,
                'leave_quota_per_year' => 2,
                'description' => 'Cuti khusus ketika istri melahirkan atau mengalami keguguran (maks 2 hari)',
            ],
            [
                'name' => 'Kewajiban Negara / Kedinasan',
                'category' => LeaveTypeCategoryEnum::SPECIAL_LEAVE,
                'leave_quota_per_year' => 1,
                'description' => 'Cuti khusus untuk memenuhi kewajiban negara/kedinasan',
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::updateOrCreate(
                ['name' => $leaveType['name']],
                $leaveType
            );
        }
    }
}
