<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Checklist;
use Illuminate\Support\Facades\DB;

class ChecklistSignerSeeder extends Seeder
{
    public function run(): void
    {
        $checklist = Checklist::first();
        if (! $checklist) return;

        $now = now();
        $rows = [
            ['checklist_id' => $checklist->id, 'role' => 'Supervisor', 'required' => true,  'order_index' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['checklist_id' => $checklist->id, 'role' => 'Manager',    'required' => false, 'order_index' => 2, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($rows as $r) {
            DB::table('checklist_signers')->updateOrInsert(
                ['checklist_id' => $r['checklist_id'], 'role' => $r['role']],
                $r
            );
        }
    }
}
