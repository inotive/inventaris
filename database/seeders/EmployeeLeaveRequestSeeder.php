<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeLeaveRequest;
use App\Models\EmployeeLeaveApproval;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Enums\EmployeeLeaveRequestStatusEnum;
use App\Enums\EmployeeLeaveApprovalStatusEnum;

class EmployeeLeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $leaveTypes = LeaveType::all();

        foreach ($employees as $employee) {

            $requestsCount = rand(2, 5);

            for ($i = 0; $i < $requestsCount; $i++) {

                $leaveType = $leaveTypes->random();

                $startDate = fake()->dateTimeBetween('-2 months', '+1 month');
                $totalDays = rand(1, 5);
                $endDate = (clone $startDate)->modify("+{$totalDays} days");

                // Pilih status leave request secara random
                $status = EmployeeLeaveRequestStatusEnum::values()[array_rand(EmployeeLeaveRequestStatusEnum::values())];

                // Buat leave request
                $leaveRequest = EmployeeLeaveRequest::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'total_days' => $totalDays,
                    'reason' => fake()->sentence(),
                    'status' => $status,
                    //                    'attachment' => [
                    //                        [
                    //                            'file_name' => fake()->word() . '.pdf',
                    //                            'file_url' => fake()->url(),
                    //                            'file_path' => '/uploads/' . fake()->word() . '.pdf',
                    //                            'file_type' => 'application/pdf',
                    //                            'file_size' => rand(100, 5000),
                    //                        ],
                    //                    ],
                    'approved_by' => null,
                    'approved_at' => null,
                ]);

                // Tentukan jumlah level approver (misal 2 level)
                $levels = 2;

                $approvers = Employee::where('id', '!=', $employee->id)->inRandomOrder()->take($levels)->get();

                $approvalApprovedAt = fake()->dateTimeBetween($startDate, '+1 month');

                foreach ($approvers as $level => $approver) {

                    // Tentukan status approval berdasarkan leave request status
                    $approvalStatus = EmployeeLeaveApprovalStatusEnum::PENDING;

                    if ($status === EmployeeLeaveRequestStatusEnum::APPROVED) {
                        $approvalStatus = EmployeeLeaveApprovalStatusEnum::APPROVED;
                    } elseif ($status === EmployeeLeaveRequestStatusEnum::REJECTED) {
                        // Level terakhir reject, sebelumnya approve
                        $approvalStatus = ($level == $levels - 1) ? EmployeeLeaveApprovalStatusEnum::REJECTED : EmployeeLeaveApprovalStatusEnum::APPROVED;
                    }

                    $approval = EmployeeLeaveApproval::create([
                        'leave_request_id' => $leaveRequest->id,
                        'approved_id' => $approver->id,
                        'level' => $level + 1,
                        'status' => $approvalStatus,
                        'note' => fake()->sentence(),
                        'approved_at' => in_array($approvalStatus, [EmployeeLeaveApprovalStatusEnum::APPROVED, EmployeeLeaveApprovalStatusEnum::REJECTED]) ? $approvalApprovedAt : null,
                    ]);

                    // Jika approval terakhir approved/rejected, update leave request
                    if ($level == $levels - 1 && $status !== EmployeeLeaveRequestStatusEnum::PENDING) {
                        $leaveRequest->update([
                            'approved_by' => $approver->id,
                            'approved_at' => $approvalApprovedAt,
                        ]);
                    }
                }
            }
        }
    }
}
