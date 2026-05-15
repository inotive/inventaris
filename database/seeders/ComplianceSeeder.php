<?php

namespace Database\Seeders;

use App\Models\Compliance;
use Illuminate\Database\Seeder;

class ComplianceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $data = [
            // Hak & Kewajiban
            ['section' => 'hak', 'title' => 'Ringkas', 'img_url' => null, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'hak', 'title' => 'Rapi', 'img_url' => null, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'hak', 'title' => 'Resik', 'img_url' => null, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now],

            // Larangan
            ['section' => 'larangan', 'title' => 'Membawa Senjata Tajam', 'img_url' => null, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'larangan', 'title' => 'Berbuat Onar', 'img_url' => null, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['section' => 'larangan', 'title' => 'Berbuat Kriminal', 'img_url' => null, 'created_by' => 1, 'created_at' => $now, 'updated_at' => $now],
        ];

        // Insert ignoring duplicates by title + section (basic protection)
        foreach ($data as $row) {
            Compliance::firstOrCreate(
                ['title' => $row['title'], 'section' => $row['section']],
                $row
            );
        }
    }
}
