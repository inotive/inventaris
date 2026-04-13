<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\SalarySlip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SalarySlipSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil maksimal 10 karyawan untuk contoh
        $employees = Employee::query()->select('id')->limit(10)->get();
        if ($employees->isEmpty()) {
            // Tidak ada karyawan, lewati agar seeder tidak error di project kosong
            return;
        }

        // Generate untuk 3 bulan terakhir (termasuk bulan ini)
        $months = collect(range(0, 2))->map(function ($i) {
            return now()->subMonths($i)->format('Y-m'); // contoh: 2026-09
        });

        foreach ($employees as $emp) {
            foreach ($months as $bulan) {
                SalarySlip::firstOrCreate(
                    [
                        'employee_id' => $emp->id,
                        'bulan' => $bulan,
                    ],
                    [
                        'file_url' => 'salary-slips/' . $emp->id . '-' . Str::slug($bulan) . '-' . Str::random(6) . '.pdf',
                        'upload_by' => 1, // admin/user id pengunggah; sesuaikan jika perlu
                    ]
                );
            }
        }
    }
}
