<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $sqlFile = database_path('sql/checklists.sql');
        
        if (!file_exists($sqlFile)) {
            $this->command->error("SQL file not found: {$sqlFile}");
            return;
        }

        $sql = file_get_contents($sqlFile);
        
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Extract only INSERT statements
        preg_match_all('/INSERT INTO `checklists`.*?;/is', $sql, $matches);
        
        if (empty($matches[0])) {
            $this->command->warn('No INSERT statements found in SQL file');
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        foreach ($matches[0] as $statement) {
            try {
                DB::unprepared($statement);
            } catch (\Exception $e) {
                $this->command->warn("Skipped statement: " . $e->getMessage());
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('Checklists seeded successfully!');
    }

    public function runOld(): void
    {
        $vehicles = Vehicle::pluck('id')->toArray();

        // Helper to resolve or create category/department by name
        $resolveCategory = function (?string $name) {
            if (!$name) return null;
            $name = trim($name);
            return ChecklistCategory::firstOrCreate(['name' => $name]);
        };
        $resolveDepartment = function (?string $name) {
            if (!$name) return null;
            $name = trim($name);
            return Department::firstOrCreate(['name' => $name], ['status' => true]);
        };

        // Base samples (existing)
        $samples = [
            ['name' => 'Checklist Inspeksi Truck', 'sop_code' => 'SOP-OPR-001', 'status' => 'Active', 'description' => 'Langkah pembukaan operasional harian', 'type' => 'single', 'category' => 'KENDARAAN', 'department' => 'DRIVER'],
            ['name' => 'Checklist K3 Truck', 'sop_code' => 'SOP-K3-010', 'status' => 'Active', 'description' => 'Pemeriksaan keselamatan area gudang', 'type' => 'multiple', 'category' => 'KENDARAAN', 'department' => 'DRIVER'],
            ['name' => 'Checklist Distribusi', 'sop_code' => 'SOP-OPR-002', 'status' => 'Draft', 'description' => 'Langkah penutupan operasional harian', 'type' => 'single', 'category' => 'PERALATAN PABRIK', 'department' => 'OFFICE'],
        ];

        // Additional items from request (name, category, department)
        $more = [
            ['CHECKLIST HARIAN (MEKANIK) CATATAN DRIVER', 'KENDARAAN', 'DRIVER'],
            ['PRA OPERASI SPV BPP', 'KOMPUTER', 'USER'],
            ['CHECKLIST HARIAN SPV BPP', 'KOMPUTER', 'ADMIN'],
            ['CHECKLIST HARIAN (MEKANIK) CATATAN SPV', 'KENDARAAN', 'OFFICE'],
            ['PRA OPERASI SPV SMD', 'PERALATAN PABRIK', 'OFFICE'],
            ['CHECKLIST HARIAN SPV SMD', 'CHECKLIST HARIAN SPV SMD', 'OFFICE'],
            ['PRA OPERASI MEKANIK BPP', 'CHECKLIST PRA OPERASI MEKANIK BPP', 'USER'],
            ['PRA OPERASI MEKANIK SMD', 'PERLENGKAPAN BENGKEL', 'USER'],
            ['CHECKLIST HARIAN (MEKANIK) CATATAN MEKANIK', 'KENDARAAN', 'GUDANG'],
            ['PERSONAL HYGIENE SAMARINDA', 'PERALATAN PABRIK', 'USER'],
            ['CHECKLIST PERSONAL HYGENE BALIKPAPAN', 'PERLENGKAPAN KANTOR', 'USER'],
            ['PERSONEL HYGIENE CHECKLIST', 'PERSONEL HYGIENE CHECKLIST', 'ADMIN'],
            ['KONTROLING CCP HENSKRISTAL SMD', 'PERALATAN PABRIK', 'ADMIN'],
            ['GENERAL CLEANING SAMARINDA', 'PERALATAN PABRIK', 'DRIVER'],
            ['Checklist CS Office SMD', 'PERLENGKAPAN KANTOR', 'STAFF'],
            ['Checklist CS Laundry SMD', 'PERLENGKAPAN KANTOR', 'OFFICE'],
            ['Checklist CS Area Dalam SMD', 'PERLENGKAPAN KANTOR', 'ADMIN'],
            ['Checklist CS Area Luar SMD', 'PERLENGKAPAN KANTOR', 'GUDANG'],
            ['VERIFIKASI THERMOMETER SMD', 'PERLENGKAPAN BENGKEL', 'OFFICE'],
        ];

        foreach ($more as [$name, $catName, $deptName]) {
            $samples[] = [
                'name' => $name,
                'sop_code' => 'SOP-AUTO-' . substr(md5($name), 0, 6),
                'status' => 'Active',
                'description' => $name,
                'type' => 'single',
                'category' => $catName,
                'department' => $deptName,
            ];
        }

        foreach ($samples as $sample) {
            $cat = isset($sample['category']) ? $resolveCategory($sample['category']) : null;
            $dep = isset($sample['department']) ? $resolveDepartment($sample['department']) : null;

            Checklist::firstOrCreate(
                ['name' => $sample['name']],
                [
                    'sop_code' => $sample['sop_code'] ?? ('SOP-AUTO-' . substr(md5($sample['name']), 0, 6)),
                    'status' => $sample['status'] ?? 'Active',
                    'description' => $sample['description'] ?? $sample['name'],
                    'type' => $sample['type'] ?? 'single',
                    'category_id' => $cat?->id,
                    'department_id' => $dep?->id,
                ]
            );
        }
    }
}
