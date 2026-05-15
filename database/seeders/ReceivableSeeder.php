<?php

namespace Database\Seeders;

use App\Models\Receivable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReceivableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Receivable::create([
            'amount' => 2000000,
            'date' => now()->subDays(10),
            'tenor' => 6,
            'note' => 'Pinjaman untuk kebutuhan keluarga',
            'file_path' => null,
            'status' => 'pending',
            'request_by' => 1,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        Receivable::create([
            'amount' => 1500000,
            'date' => now()->subDays(5),
            'tenor' => 3,
            'note' => 'Pinjaman untuk biaya kesehatan',
            'file_path' => null,
            'status' => 'approved',
            'request_by' => 2,
            'approved_by' => 1,
            'approved_at' => now()->subDays(3),
        ]);

        Receivable::create([
            'amount' => 1000000,
            'date' => now()->subDays(2),
            'tenor' => 2,
            'note' => 'Pinjaman untuk keperluan mendesak',
            'file_path' => null,
            'status' => 'rejected',
            'request_by' => 3,
            'approved_by' => 1,
            'approved_at' => now()->subDay(),
        ]);
    }
}
