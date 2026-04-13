<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeePhotoSeeder extends Seeder
{
    public function run(): void
    {
        $sourceRoot = base_path('foto_karyawan');
        if (!is_dir($sourceRoot)) {
            $this->command?->warn("Folder 'foto_karyawan' tidak ditemukan di project root.");
            return;
        }

        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'gif', 'heic', 'heif'];
        $disk = Storage::disk('public');
        $destDir = 'uploads/employees';
        if (!$disk->exists($destDir)) {
            $disk->makeDirectory($destDir);
        }

        $total = 0;
        $updated = 0;
        $skipped = 0;
        $notFound = 0;
        $errors = 0;

        // Level-1 folders (kategori seperti DRIVER, OFFICE, dsb)
        $level1 = array_values(array_filter(scandir($sourceRoot), function ($f) use ($sourceRoot) {
            return $f !== '.' && $f !== '..' && is_dir($sourceRoot . DIRECTORY_SEPARATOR . $f);
        }));

        foreach ($level1 as $group) {
            $this->command?->info("Memproses kategori: {$group}");
            $groupPath = $sourceRoot . DIRECTORY_SEPARATOR . $group;
            // Level-2 folders (nama karyawan)
            $empFolders = array_values(array_filter(scandir($groupPath), function ($f) use ($groupPath) {
                return $f !== '.' && $f !== '..' && is_dir($groupPath . DIRECTORY_SEPARATOR . $f);
            }));

            $this->command?->line("  Ditemukan " . count($empFolders) . " folder karyawan");

            foreach ($empFolders as $empFolder) {
                $total++;
                $empPath = $groupPath . DIRECTORY_SEPARATOR . $empFolder;

                // Ambil file gambar pertama
                $files = array_values(array_filter(scandir($empPath), function ($f) use ($empPath) {
                    return $f !== '.' && $f !== '..' && is_file($empPath . DIRECTORY_SEPARATOR . $f);
                }));
                if (empty($files)) {
                    $this->command?->line("  → {$empFolder}: Tidak ada file");
                    $skipped++;
                    continue;
                }

                // filter by allowed extensions
                $files = array_values(array_filter($files, function ($f) use ($allowed) {
                    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                    return in_array($ext, $allowed, true);
                }));
                if (empty($files)) {
                    $this->command?->line("  → {$empFolder}: Tidak ada gambar valid");
                    $skipped++;
                    continue;
                }

                sort($files, SORT_NATURAL | SORT_FLAG_CASE);
                $srcFile = $empPath . DIRECTORY_SEPARATOR . $files[0];
                $origName = $files[0];

                // Cari employee berdasarkan nama folder (case-insensitive)
                $folderName = trim((string) $empFolder);
                if ($folderName === '') {
                    $this->command?->line("  → {$empFolder}: Nama folder kosong");
                    $skipped++;
                    continue;
                }
                $normalized = preg_replace('/\s+/', ' ', strtolower($folderName));

                $employee = Employee::query()
                    ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
                    ->first();

                if (!$employee) {
                    // coba cocokkan dengan slug
                    $folderSlug = Str::slug($folderName);
                    $employee = Employee::all()->first(function ($e) use ($folderSlug) {
                        return Str::slug((string) $e->name) === $folderSlug;
                    });
                }

                $isNewEmployee = false;
                if (!$employee) {
                    // Auto-create user and employee if not found
                    $this->command?->warn("  → {$empFolder}: Karyawan tidak ditemukan, membuat data baru...");

                    try {
                        // Create user with minimal data
                        $username = Str::slug($folderName, '_');
                        $suffix = 1;
                        while (User::where('username', $username)->exists()) {
                            $username = Str::slug($folderName, '_') . $suffix;
                            $suffix++;
                        }

                        $user = User::create([
                            'name' => $folderName,
                            'username' => $username,
                            'email' => $username . '@henkristal.local',
                            'password' => Hash::make('123123'),
                        ]);

                        // Assign default role
                        if (method_exists($user, 'syncRoles')) {
                            $user->syncRoles(['User']);
                        }

                        // Create employee
                        $employee = Employee::create([
                            'user_id' => $user->id,
                            'name' => $folderName,
                            'branch_id' => 1, // default branch
                            'department_id' => null,
                            'position_id' => null,
                            'shift_id' => 1, // default shift
                            'working_start_date' => now()->toDateString(),
                        ]);

                        $isNewEmployee = true;
                        $this->command?->info("  → {$empFolder}: User & Employee berhasil dibuat (username: {$username})");
                    } catch (\Throwable $e) {
                        $this->command?->error("  → {$empFolder}: Gagal membuat user/employee - {$e->getMessage()}");
                        $notFound++;
                        continue;
                    }
                }

                // // Generate shift schedule, leave balances, and receivable balance for ALL employees
                // // (both new and existing ones)
                // try {
                //     $this->generateShiftSchedule($employee);
                //     $this->createLeaveBalances($employee);
                //     $this->createReceivableBalance($employee);
                // } catch (\Throwable $e) {
                //     $this->command?->warn("  → {$empFolder}: Gagal generate schedule/balances - {$e->getMessage()}");
                // }

                try {
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    $safeName = time() . '_' . Str::slug($employee->name, '_') . '.' . $ext;
                    $destPath = $destDir . '/' . $safeName;

                    // Salin file ke storage public
                    $stream = fopen($srcFile, 'r');
                    $disk->put($destPath, $stream);
                    if (is_resource($stream)) fclose($stream);

                    // Update kolom di employees
                    $employee->file_name = $safeName;
                    $employee->path = $destPath;
                    $employee->save();

                    $this->command?->info("  ✓ {$empFolder}: Berhasil diupdate → {$safeName}");
                    $updated++;
                } catch (\Throwable $e) {
                    $this->command?->error("  ✗ {$empFolder}: Gagal - {$e->getMessage()}");
                    $errors++;
                }
            }
        }

        $this->command?->info("EmployeePhotoSeeder selesai. Total folder: {$total}, updated: {$updated}, skipped: {$skipped}, notFound: {$notFound}, errors: {$errors}");
    }
}
