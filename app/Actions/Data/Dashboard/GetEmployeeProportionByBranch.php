<?php

namespace App\Actions\Data\Dashboard;

use App\Models\Employee;
use App\Models\Branch;

class GetEmployeeProportionByBranch
{
    public function execute($branchId = null)
    {
        // Get employee count by department (filtered by branch if given)
        $query = Employee::with(['department', 'branch'])
            ->whereHas('department'); // Only get employees with department

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $departmentData = $query->get()
            ->groupBy('department.name')
            ->map(function ($employees) {
                return $employees->count();
            })
            ->filter(function ($count) {
                return $count > 0; // Only include departments with employees
            });

        // Get all departments for reference
        $allDepartments = \App\Models\Department::all();
        $defaultDepartments = $allDepartments->pluck('name')->toArray();

        // If branch is selected, only show departments that have employees in that branch
        // Otherwise, show all departments with their counts
        if ($branchId) {
            // Only show departments that exist in the selected branch
            $categories = $departmentData->keys()->toArray();
            $data = $departmentData->values()->toArray();
        } else {
            // Show all departments, with 0 for departments without employees
            $categories = $defaultDepartments;
            $data = [];
            foreach ($defaultDepartments as $department) {
                $data[] = $departmentData->get($department, 0);
            }
        }

        return [
            'categories' => $categories,
            'data' => $data
        ];
    }
}
