<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Employee;
use App\Models\Department;

class GetEmployeeProportionData
{
    public function execute()
    {
        // Get employee count by department
        $departmentData = Employee::with('department')
            ->get()
            ->groupBy('department.name')
            ->map(function ($employees) {
                return $employees->count();
            });

        $departments = Department::all();
        $defaultDepartments = $departments->pluck('name')->toArray();

        // Fill in missing departments with 0
        $data = [];
        foreach ($defaultDepartments as $department) {
            $data[] = $departmentData->get($department, 0);
        }

        return [
            'categories' => $defaultDepartments,
            'data' => $data
        ];
    }
}
