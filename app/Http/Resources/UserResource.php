<?php

namespace App\Http\Resources;

use Faker\Calculator\Ean;
use App\Models\Attendance;
use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $employee = $this->employee; // may be null if not associated

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'profile_photo_url' => (function () {
                // Check if user has uploaded a photo
                if ($this->profile_photo_path) {
                    // Return URL to uploaded photo
                    return url('storage/' . $this->profile_photo_path);
                }

                // Check if profile_photo_url is already a full URL (e.g., from Google login)
                if ($this->profile_photo_url && filter_var($this->profile_photo_url, FILTER_VALIDATE_URL)) {
                    return $this->profile_photo_url;
                }

                // Return default avatar with first letter of name
                $initial = strtoupper(substr($this->name ?? 'U', 0, 1));
                return "https://ui-avatars.com/api/?name={$initial}&color=7F9CF5&background=EBF4FF";
            })(),

            'google_id' => $this->google_id,
            'contact' => $this->employee->contact ?? null,
            'status' => $this->employee->status ?? null,
            'age' => $this->employee->age ?? null,
            'address' => $this->employee->address ?? null,
            'role' => $this->getRoleNames()->first(),
            'last_seen' => $this->last_seen,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'employee_id' => optional($employee)->id,
            'employee_name' => optional($employee)->name,
            'employee_position' => $employee && $employee->role ? $employee->role->name : $this->primary_role,

            // IDs for form auto-selection
            'branch_id' => optional($employee)->branch_id,
            'department_id' => optional($employee)->department_id,
            'shift_id' => optional($employee)->shift_id,

            'employee_department' => $employee && $employee->department ? [
                'id' => $employee->department->id,
                'name' => $employee->department->name,
            ] : null,
            'employee_branch' => $employee && $employee->branch ? [
                'id' => $employee->branch->id,
                'name' => $employee->branch->name,
            ] : null,

            'shift_kerja' => $employee && $employee->shift ? [
                'id' => $employee->shift->id,
                'name' => $employee->shift->name,
                'start_time' => $employee->shift->start_time,
                'end_time' => $employee->shift->end_time,
            ] : null,

            'total_kehadiran' => $employee
                ? Attendance::where('employee_id', $employee->id)
                    ->whereBetween('tanggal', [
                        now()->startOfMonth()->toDateString(),
                        now()->endOfMonth()->toDateString(),
                    ])
                    ->whereIn('status', ['ON TIME', 'TERLAMBAT'])
                    ->count()
                : 0,

            'cuti_terpakai' => optional(optional($employee)->annualLeaveBalance)->used_quota ?? 0,
            'sisa_cuti' => optional(optional($employee)->annualLeaveBalance)->remaining_quota ?? 0,

            'gaji' => optional($employee)->salary,
            'bonus_bulan_ini' => $employee && $employee->bonuses
                ? $employee->bonuses()->whereMonth('created_at', now()->month)->sum('amount')
                : 0,

            'piutang_bulan_ini' => (function () use ($employee) {
                if (!$employee)
                    return 0;
                $balance = $employee->receivableBalance()
                    ->first();
                return (int) ($balance->used_amount ?? 0);
            })(),

            'piutang_limit' => (function () use ($employee) {
                if (!$employee)
                    return 0;
                $balance = $employee->receivableBalance()
                    ->first();
                return (int) ($balance->remaining_amount ?? 0);
            })(),

            'salary_slip' => $this->when($employee && $employee->salarySlips->count() > 0, function () use ($employee) {
                $baseUrl = rtrim(config('app.url'), '/');
                return [
                    'id' => $employee->salarySlips->first()->id,
                    'bulan' => $employee->salarySlips->first()->bulan,
                    'file_url' => $baseUrl . $employee->salarySlips->first()->file_url,
                ];
            }, null),
        ];
    }
}
