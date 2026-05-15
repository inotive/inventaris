<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrasi data penilaian dari skala 0-10 ke skala 1-5
     * untuk kategori manual: Keterampilan, Kerjasama, Disiplin
     */
    public function up(): void
    {
        // Ambil semua data penilaian manual
        $performances = DB::table('employee_performances')
            ->whereIn('category', ['Keterampilan', 'Kerjasama', 'Disiplin'])
            ->get();

        foreach ($performances as $performance) {
            $score = json_decode($performance->score, true);
            
            // Skip jika bukan array atau kosong
            if (!is_array($score) || empty($score)) {
                continue;
            }

            $needsUpdate = false;
            $newScore = [];

            foreach ($score as $indicator => $value) {
                if (is_numeric($value)) {
                    // Jika nilai > 5, berarti masih menggunakan skala lama (0-10)
                    if ($value > 5) {
                        // Konversi dari skala 0-10 ke 1-5
                        // Formula: ((value / 10) * 4) + 1
                        // Contoh: 10 -> 5, 8 -> 4.2, 6 -> 3.4, 0 -> 1
                        $newValue = max(1, min(5, round((($value / 10) * 4) + 1, 1)));
                        $newScore[$indicator] = $newValue;
                        $needsUpdate = true;
                    } else {
                        // Nilai sudah dalam range 1-5, atau 0 (konversi ke 1)
                        $newScore[$indicator] = $value == 0 ? 1 : $value;
                        if ($value == 0) {
                            $needsUpdate = true;
                        }
                    }
                } else {
                    // Bukan numeric, tetap simpan
                    $newScore[$indicator] = $value;
                }
            }

            // Update jika ada perubahan
            if ($needsUpdate) {
                DB::table('employee_performances')
                    ->where('id', $performance->id)
                    ->update([
                        'score' => json_encode($newScore),
                        'notes' => $performance->notes . "\n[Migrasi otomatis: Data dikonversi dari skala 0-10 ke 1-5 pada " . now()->format('Y-m-d H:i:s') . "]"
                    ]);
            }
        }

        // Log hasil migrasi
        \Log::info("Migrasi skala penilaian selesai. Total data diproses: " . $performances->count());
    }

    /**
     * Reverse the migrations.
     * 
     * Kembalikan data dari skala 1-5 ke 0-10
     */
    public function down(): void
    {
        $performances = DB::table('employee_performances')
            ->whereIn('category', ['Keterampilan', 'Kerjasama', 'Disiplin'])
            ->where('notes', 'like', '%[Migrasi otomatis:%')
            ->get();

        foreach ($performances as $performance) {
            $score = json_decode($performance->score, true);
            
            if (!is_array($score) || empty($score)) {
                continue;
            }

            $newScore = [];
            foreach ($score as $indicator => $value) {
                if (is_numeric($value) && $value >= 1 && $value <= 5) {
                    // Konversi balik dari 1-5 ke 0-10
                    // Formula: ((value - 1) / 4) * 10
                    $newValue = round((($value - 1) / 4) * 10, 1);
                    $newScore[$indicator] = $newValue;
                } else {
                    $newScore[$indicator] = $value;
                }
            }

            // Hapus catatan migrasi dari notes
            $notes = preg_replace('/\n?\[Migrasi otomatis:.*?\]/', '', $performance->notes);

            DB::table('employee_performances')
                ->where('id', $performance->id)
                ->update([
                    'score' => json_encode($newScore),
                    'notes' => $notes
                ]);
        }

        \Log::info("Rollback migrasi skala penilaian selesai. Total data diproses: " . $performances->count());
    }
};
