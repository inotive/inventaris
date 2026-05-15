<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Vehicle;
use App\Models\Employee;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {

        $branches = Branch::all();
        $vehicleTypes = VehicleType::all();
        $employees = Employee::all();

        if ($branches->isEmpty() || $vehicleTypes->isEmpty() || $employees->isEmpty()) {
            $this->command->warn('Required data missing. Please seed branches, vehicle types, and employees first.');
            return;
        }

        // $vehicles = [
        //     [
        //         'license_code' => 'B 1234 ABC',
        //         'chassis_code' => 'CHS001',
        //         'machine_code' => 'ENG001',
        //         'route' => 'Jakarta - Bandung',
        //         'track' => 'Track A1',
        //         'status' => true,
        //     ],
        //     [
        //         'license_code' => 'B 5678 DEF',
        //         'chassis_code' => 'CHS002',
        //         'machine_code' => 'ENG002',
        //         'route' => 'Surabaya - Malang',
        //         'track' => 'Track B2',
        //         'status' => true,
        //     ],
        //     [
        //         'license_code' => 'B 9012 GHI',
        //         'chassis_code' => 'CHS003',
        //         'machine_code' => 'ENG003',
        //         'route' => 'Medan - Pekanbaru',
        //         'track' => 'Track C3',
        //         'status' => true,
        //     ],
        //     [
        //         'license_code' => 'B 3456 JKL',
        //         'chassis_code' => 'CHS004',
        //         'machine_code' => 'ENG004',
        //         'route' => 'Semarang - Solo',
        //         'track' => 'Track D4',
        //         'status' => false,
        //     ],
        //     [
        //         'license_code' => 'B 7890 MNO',
        //         'chassis_code' => 'CHS005',
        //         'machine_code' => 'ENG005',
        //         'route' => 'Yogyakarta - Purwokerto',
        //         'track' => 'Track E5',
        //         'status' => true,
        //     ],
        // ];

        // foreach ($vehicles as $vehicleData) {
        //     Vehicle::create([
        //         'vehicle_type_id' => $vehicleTypes->random()->id,
        //         'branch_id' => $branches->random()->id,
        //         'route' => $vehicleData['route'],
        //         'employee_id' => $employees->random()->id,
        //         'track' => $vehicleData['track'],
        //         'license_code' => $vehicleData['license_code'],
        //         'chassis_code' => $vehicleData['chassis_code'],
        //         'machine_code' => $vehicleData['machine_code'],
        //         'status' => $vehicleData['status'],
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }


        // Import from d_kendaraan.csv
        $csv = base_path('d_kendaraan.csv');
        if (!is_file($csv)) {
            return;
        }

        if (($handle = fopen($csv, 'r')) === false) {
            return;
        }

        $headers = fgetcsv($handle, 0, ';');
        if (!$headers) {
            fclose($handle);
            return;
        }
        // normalize headers
        $headers = array_map(function ($h) {
            return trim(str_replace(['"', '"'], '', $h), "\xEF\xBB\xBF\" ");
        }, $headers);

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) !== count($headers)) continue;
            $data = array_combine($headers, $row);

            $license = trim($data['nomor_polisi'] ?? '');
            if ($license === '') continue;

            // skip if exists
            if (Vehicle::where('license_code', $license)->exists()) continue;

            $branchId = (int) ($data['id_cabang'] ?? null) ?: null;
            // resolve or create vehicle type from 'jenis'
            $jenis = trim($data['jenis'] ?? '');
            if ($jenis === '') {
                $jenis = 'LAINNYA';
            }
            $vehicleType = VehicleType::firstOrCreate(['name' => $jenis, 'category' => '-']);

            Vehicle::create([
                'vehicle_type_id' => $vehicleType->id,
                'branch_id' => $branchId,
                'employee_id' => null, // driver assignment optional
                'license_code' => $license,
                'status' => strtolower(trim($data['status'] ?? 'AKTIF')) === 'aktif',
            ]);
        }

        fclose($handle);

        $this->command->info('Vehicle seeder completed successfully.');
    }
}
