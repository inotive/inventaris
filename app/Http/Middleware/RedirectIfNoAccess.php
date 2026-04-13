<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNoAccess
{
    /**
     * Handle an incoming request.
     *
     * Redirect user to first accessible page if they don't have dashboard access
     * If no accessible page found, logout and redirect to login
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // If user is not authenticated, let auth middleware handle it
        if (!$user) {
            return $next($request);
        }

        // If user has dashboard access, proceed normally
        if ($user->can('dashboard.view')) {
            return $next($request);
        }

        // User doesn't have dashboard access, redirect to first accessible page
        $redirectRoute = $this->getFirstAccessibleRoute($user);

        if ($redirectRoute) {
            return redirect()->route($redirectRoute)->with('info', 'Anda tidak memiliki akses ke dashboard. Dialihkan ke halaman yang tersedia.');
        }

        // If no accessible route found, logout user and redirect to login with error
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', 'Akun Anda tidak memiliki akses ke sistem ini. Silakan hubungi administrator untuk mendapatkan hak akses yang sesuai.');
    }

    /**
     * Get first accessible route for user based on their permissions
     */
    private function getFirstAccessibleRoute($user): ?string
    {
        // Define routes in priority order
        $routePermissions = [
            // Dashboard (highest priority)
            'dashboard' => 'dashboard.view',

            // Inspections
            'inspections.index' => 'inspections.view',

            // Reports
            'reports.index' => 'reports.view',

            // Submissions
            'submissions.index' => 'submission_all.view',

            // Attendance
            'presences.index' => 'presences.view',

            // Employees
            'employees.index' => 'employees.view',

            // Vehicles
            'vehicles.index' => 'vehicles.view',

            // Items
            'items.index' => 'items.view',

            // Checklists
            'checklists.index' => 'checklists.view',

            // Profile (fallback - everyone should have access)
            'profile.show' => null, // No permission required
        ];

        foreach ($routePermissions as $route => $permission) {
            // If no permission required or user has permission
            if ($permission === null || $user->can($permission)) {
                // Check if route exists
                if (\Route::has($route)) {
                    return $route;
                }
            }
        }

        return null;
    }
}
