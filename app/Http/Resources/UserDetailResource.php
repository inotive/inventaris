<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Attendance;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $employee = $this->employee;

        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'email' => $this->email,

            'employee_id'   => optional($employee)->id,
            'employee_name' => optional($employee)->name,
            'status'        => optional($employee)->status,
            'umur'          => $employee && $employee->birthdate
                ? now()->diffInYears($employee->birthdate)
                : null,
            'contact'       => optional($employee)->contact,
            'address'       => optional($employee)->address,

            'shift_kerja' => $employee && $employee->shift ? [
                'id'         => $employee->shift->id,
                'name'       => $employee->shift->name,
                'start_time' => $employee->shift->start_time,
                'end_time'   => $employee->shift->end_time,
            ] : null,

            'bonus_bulan_ini'   => $employee->bonus_bulan_ini ?? 0,
            'piutang_bulan_ini' => $employee->piutang_bulan_ini ?? 0,

            'total_hadir_bulan_ini' => $this->employee
                ? Attendance::where('employee_id', $this->employee->id)
                ->whereBetween('tanggal', [
                    now()->startOfMonth()->toDateString(),
                    now()->endOfMonth()->toDateString(),
                ])
                ->whereIn('status', ['ON TIME', 'TERLAMBAT'])
                ->count()
                : 0,
        ];
    }
}
