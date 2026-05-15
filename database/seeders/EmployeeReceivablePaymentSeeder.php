<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Receivable;
use App\Models\EmployeeReceivablePayment;

class EmployeeReceivablePaymentSeeder extends Seeder
{
    public function run(): void
    {
        $receivables = Receivable::query()->get();
        foreach ($receivables as $r) {
            $employeeId = $r->employee_id ?? $r->request_by ?? null;
            if (!$employeeId) continue;

            EmployeeReceivablePayment::firstOrCreate([
                'receivable_id' => $r->id,
                'employee_id' => $employeeId,
                'paid_date' => now()->toDateString(),
                'amount' => (int) min(100000, ($r->amount ?? 100000)),
                'method' => 'payroll_cut',
                'reference_no' => 'SIM-' . now()->format('YmdHis'),
                'note' => 'Seeder pembayaran simulasi',
            ]);
        }
    }
}
