<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use Spatie\Permission\Models\Role as SpatieRole;

class HKMSBranchDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $xlsxPath = base_path('HKMS.xlsx');
        $csvPath  = base_path('HKMS.csv');

        if (!file_exists($xlsxPath) && !file_exists($csvPath)) {
            $this->command?->warn("HKMS.xlsx or HKMS.csv not found at project root.");
            return;
        }

        $rows = [];

        // Try PhpSpreadsheet for .xlsx first
        if (file_exists($xlsxPath) && class_exists('PhpOffice\\PhpSpreadsheet\\IOFactory')) {
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($xlsxPath);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($xlsxPath);
                $sheet = $spreadsheet->getActiveSheet();
                // Use toArray to reliably read all rows and columns
                // Parameters: $nullValue, $calculateFormulas, $formatData, $returnCellRefAsArray
                $rows = $sheet->toArray(null, true, true, false);
            } catch (\Throwable $e) {
                $this->command?->warn('Failed reading HKMS.xlsx: ' . $e->getMessage());
            }
        }

        // Fallback to CSV if needed
        if (empty($rows) && file_exists($csvPath)) {
            if (($handle = fopen($csvPath, 'r')) !== false) {
                while (($data = fgetcsv($handle)) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        }

        if (empty($rows)) {
            $this->command?->warn('No rows loaded from HKMS file.');
            return;
        }

        // Detect header row (scan first 10 rows for likely header)
        $headerRowIndex = 0;
        $headers = [];
        for ($i = 0; $i < min(10, count($rows)); $i++) {
            $probe = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $rows[$i] ?? []);
            if (empty(array_filter($probe, function ($v) { return $v !== ''; }))) continue;
            $hasUsername = in_array('username', $probe, true) || in_array('user', $probe, true) || in_array('akun', $probe, true) || in_array('uname', $probe, true);
            $hasBranch   = in_array('cabang', $probe, true) || in_array('branch', $probe, true);
            $hasDept     = in_array('departemen', $probe, true) || in_array('department', $probe, true) || in_array('dept', $probe, true);
            if ($hasUsername && $hasBranch && $hasDept) {
                $headers = $probe;
                $headerRowIndex = $i;
                break;
            }
        }
        if (empty($headers)) {
            // fallback to first row
            $headers = array_map(function ($h) { return strtolower(trim((string) $h)); }, $rows[0] ?? []);
            $headerRowIndex = 0;
        }

        // helper to find column index by aliases
        $colIndex = function (string $key) use ($headers): ?int {
            $aliases = [
                'username' => ['username', 'user', 'akun', 'uname'],
                'name' => ['nama', 'name'],
                'branch' => ['cabang', 'branch'],
                'department' => ['departemen', 'department', 'dept'],
                'level' => ['level'],
            ];
            $list = $aliases[$key] ?? [$key];
            foreach ($list as $label) {
                $idx = array_search($label, $headers, true);
                if ($idx !== false) return (int) $idx;
            }
            return null;
        };

        $idxName       = $colIndex('name');
        $idxBranchName = $colIndex('branch');
        $idxDeptName   = $colIndex('department');
        $idxLevel      = $colIndex('level');

        if ($idxName === null || $idxBranchName === null || $idxDeptName === null) {
            $this->command?->warn('Required columns not found. Need Name, Cabang, Departemen. Detected headers: '.json_encode($headers));
            return;
        }

        $updated = 0; $skipped = 0; $notFound = 0; $noMap = 0;
        $skippedList = [];
        $notFoundList = [];

        // Build lookup for branches and departments by name (case-insensitive)
        $branchMap = Branch::query()->select('id', 'name')->get()->reduce(function ($carry, $item) {
            $carry[strtolower(trim($item->name))] = $item->id;
            return $carry;
        }, []);
        $deptMap = Department::query()->select('id', 'name')->get()->reduce(function ($carry, $item) {
            $carry[strtolower(trim($item->name))] = $item->id;
            return $carry;
        }, []);

        // Start after the detected header row
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $r = $rows[$i];
            if (!is_array($r)) { 
                $skipped++; 
                $skippedList[] = "Row " . ($i + 1) . " (invalid array)";
                continue; 
            }

            $name = isset($r[$idxName]) ? trim((string) $r[$idxName]) : '';
            $branchName = isset($r[$idxBranchName]) ? trim((string) $r[$idxBranchName]) : '';
            $deptName = isset($r[$idxDeptName]) ? trim((string) $r[$idxDeptName]) : '';
            $levelName = ($idxLevel !== null && isset($r[$idxLevel])) ? trim((string) $r[$idxLevel]) : '';

            if ($name === '') { 
                $skipped++; 
                $skippedList[] = "Row " . ($i + 1) . " (empty name)";
                continue; 
            }

            // Find employee by name (case-insensitive)
            $employee = Employee::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
            if (!$employee) { 
                $notFound++; 
                $notFoundList[] = $name . " (employee not found)";
                continue; 
            }

            // Get user from employee
            $user = $employee->user;
            if (!$user) { 
                $notFound++; 
                $notFoundList[] = $name . " (user not found for employee)";
                continue; 
            }

            // Try exact match first (case-insensitive)
            $branchId = $branchMap[strtolower($branchName)] ?? null;
            $deptId   = $deptMap[strtolower($deptName)] ?? null;

            // Fallback: try case-insensitive LIKE match
            if (!$branchId && $branchName !== '') {
                $maybe = Branch::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($branchName) . '%'])->value('id');
                if ($maybe) { 
                    $branchId = $maybe; 
                    $branchMap[strtolower($branchName)] = $maybe;
                    $this->command?->line("  Matched branch: '{$branchName}' → ID {$branchId}");
                } else {
                    $this->command?->warn("  Branch not found: '{$branchName}' for {$name}");
                }
            }
            if (!$deptId && $deptName !== '') {
                $maybe = Department::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($deptName) . '%'])->value('id');
                if ($maybe) { 
                    $deptId = $maybe; 
                    $deptMap[strtolower($deptName)] = $maybe;
                    $this->command?->line("  Matched department: '{$deptName}' → ID {$deptId}");
                } else {
                    $this->command?->warn("  Department not found: '{$deptName}' for {$name}");
                }
            }

            if ($branchId || $deptId) {
                $update = [];
                if ($branchId) $update['branch_id'] = $branchId;
                if ($deptId) $update['department_id'] = $deptId;
                if (!empty($update)) {
                    Employee::where('id', $employee->id)->update($update);
                    $updated++;
                } else {
                    $noMap++;
                }
            } else {
                $noMap++;
            }

            // Sync role from Level column if provided (only if role exists)
            if ($levelName !== '') {
                $roleName = ucwords(strtolower($levelName));
                $role = SpatieRole::where('name', $roleName)->first();
                if ($role) {
                    // Replace user's roles with the level role
                    try { $user->syncRoles([$roleName]); } catch (\Throwable $e) {}
                } else {
                    // Optionally log missing role
                    $this->command?->warn("Role '{$roleName}' not found; skipping sync for {$name}.");
                }
            }
        }

        $this->command?->info("HKMS update done. Updated: {$updated}, NotFound: {$notFound}, Skipped: {$skipped}, NoMap: {$noMap}");
        
        // Show details of NotFound items
        if (!empty($notFoundList)) {
            $this->command?->warn("\n=== NOT FOUND ({$notFound} items) ===");
            foreach ($notFoundList as $item) {
                $this->command?->line("  ❌ {$item}");
            }
        }
        
        // Show details of Skipped items
        if (!empty($skippedList)) {
            $this->command?->warn("\n=== SKIPPED ({$skipped} items) ===");
            foreach ($skippedList as $item) {
                $this->command?->line("  ⏭️  {$item}");
            }
        }
    }
}
