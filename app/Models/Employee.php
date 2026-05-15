<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'staff_id',
        'name',
        'contact',
        'address',
        'provinsi',
        'kota',
        'kecamatan',
        'kelurahan',
        'status',
        'age',
        'gender',
        'birthplace',
        'religion',
        'nik',
        'ktp',
        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'certificate',
        'contract',
        'verification',
        'working_start_date',
        'branch_id',
        'department_id',
        'shift_id',
        'position_id',
        'resign_date',
        'birthdate',
        'salary',
        'leave_quota_per_year',
        'loan_quota',
        'file_name',
        'path',
    ];

    protected $casts = [
        'verification' => 'boolean',
        'created_at' => 'string',
        'updated_at' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // public function permission(){
    //     return $this->belongsTo(\Spatie\Permission\Models\Permission::class, 'permission_id');
    // }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'position_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function attendanceShiftWorks()
    {
        return $this->hasMany(AttendanceShiftWork::class, 'employee_id');
    }

    // public function salary()
    // {
    //     return $this->hasOne(Salary::class, 'employee_id');
    // }

    public function receivables()
    {
        return $this->hasMany(Receivable::class, 'request_by');
    }

    public function approvedReceivables()
    {
        return $this->hasMany(Receivable::class, 'approved_by');
    }

    public function leaveBalance()
    {
        return $this->hasMany(EmployeeLeaveBalance::class, 'employee_id')->where('year', now()->year);
    }

    public function annualLeaveBalance()
    {
        return $this->hasOne(EmployeeLeaveBalance::class, 'employee_id')->where('leave_type_id', 1)->where('year', now()->year);
    }

    public function receivableBalance()
    {
        return $this->hasOne(EmployeeReceivableBalance::class, 'employee_id')->where('period_year', now()->year);
    }

    public function receivablePayment()
    {
        return $this->hasMany(EmployeeReceivablePayment::class, 'employee_id');
    }
    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    public function salarySlips()
    {
        return $this->hasMany(SalarySlip::class, 'employee_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'employee_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(EmployeeLeaveRequest::class, 'employee_id');
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'submitted_by', 'id');
    }

    public function overtimes()
    {
        return $this->hasMany(EmployeeOvertime::class, 'employee_id');
    }

    public function reimbursements()
    {
        return $this->hasMany(Reimbursement::class, 'employee_id');
    }

    public function dayOffs()
    {
        return $this->hasMany(EmployeeDayOff::class, 'employee_id');
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class, 'employee_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'employee_id');
    }

    public function performances()
    {
        return $this->hasMany(EmployeePerformance::class, 'employee_id');
    }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'cheklist_employees', 'employee_id', 'checklist_id');
    }
    /**
     * Get the full URL of the employee's photo.
     *
     * This is an accessor for the 'photo_url' attribute. If the employee has a photo path set,
     * it returns the publicly accessible URL to the photo stored in the 'public' disk.
     * If no photo path is set, it returns null.
     *
     * @return string|null The URL to the employee's photo or null if not available.
     *
     * @psalm-suppress UndefinedMethod
     */
    // public function getPhotoUrlAttribute()
    // {
    //     if ($this->path) {
    //         // Determine domain based on deployment target (not APP_ENV)
    //         // APP_ENV controls Laravel/Vite behavior (local/production)
    //         // APP_DEPLOYMENT controls which server domain to use
    //         $deployment = config('app.deployment', config('app.env'));

    //         if ($deployment === 'local') {
    //             // Local development - use APP_URL from .env
    //             $baseUrl = config('app.url');
    //             return "{$baseUrl}/storage/" . $this->path;
    //         } elseif ($deployment === 'development' || $deployment === 'dev') {
    //             // Development server
    //             $domain = 'dev.henskristal.web.id';
    //             return "https://{$domain}/storage/" . $this->path;
    //         } else {
    //             // Production server
    //             $domain = 'app.henskristal.web.id';
    //             return "https://{$domain}/storage/" . $this->path;
    //         }
    //     }
    //     return null;
    // }

    public function getPhotoUrlAttribute()
    {
        if (!$this->path) return null;

        $path = $this->path;

        // Strip leading ./ or /
        $path = ltrim($path, './ ');
        $path = ltrim($path, '/');

        // Already a full URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Strip leading 'storage/' if present — we'll re-add it consistently
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        // Normalize old format: 'employees/...' -> 'uploads/employees/...'
        if (!str_starts_with($path, 'uploads/')) {
            $path = 'uploads/' . $path;
        }

        // Always return with 'storage/' prefix
        // .htaccess rewrites /storage/* -> public/storage/* on server
        return 'storage/' . $path; // e.g. 'storage/uploads/employees/filename.jpg'
    }

    public function datasets()
    {
        return $this->hasMany(EmployeeDataset::class, 'employee_id');
    }
}
