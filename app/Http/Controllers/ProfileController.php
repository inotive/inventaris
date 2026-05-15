<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function showDetail()
    {
        $user = Auth::user()->load([
            'employee.department:id,name',
            'employee.branch:id,name',
            'employee.shift:id,name,start_time,end_time',
        ]);

        return Inertia::render('Profile/Show', [
            // Props expected by Profile/Show.vue
            'confirmsTwoFactorAuthentication' => false,
            'sessions' => [],
            // Additional read-only employee detail for Detail.vue
            'employee' => optional($user->employee) ? [
                'name' => $user->employee->name,
                'contact' => $user->employee->contact,
                'address' => $user->employee->address,
                'department' => optional($user->employee->department)->name,
                'branch' => optional($user->employee->branch)->name,
                'shift' => optional($user->employee->shift) ? [
                    'name' => $user->employee->shift->name,
                    'start_time' => $user->employee->shift->start_time,
                    'end_time' => $user->employee->shift->end_time,
                ] : null,
            ] : null,
        ]);
    }



    public function edit(Request $request)
    {
        return Inertia::render('Profile/Edit', [
            'user' => $request->user(),
        ]);
    }

    public function apiShow(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }
}
