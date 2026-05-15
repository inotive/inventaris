<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop view if exists
        DB::statement('DROP VIEW IF EXISTS v_pending_submissions_count');

        // Re-create view with branch_id
        DB::statement("
            CREATE VIEW v_pending_submissions_count AS
            SELECT 
                'submissions' as source_table,
                main.*
            FROM (
                -- Type 1: Sakit (sick_leave)
                SELECT 
                    1 as submission_type, 
                    'Sakit' as type_label,
                    e.branch_id,
                    COUNT(*) as pending_count
                FROM employee_leave_requests elr
                JOIN employees e ON elr.employee_id = e.id
                JOIN leave_types lt ON elr.leave_type_id = lt.id
                WHERE elr.status = 'pending' AND lt.category = 'sick_leave'
                GROUP BY e.branch_id

                UNION ALL

                -- Type 2: Cuti Tahunan (annual_leave)
                SELECT 
                    2 as submission_type, 
                    'Cuti Tahunan' as type_label,
                    e.branch_id,
                    COUNT(*) as pending_count
                FROM employee_leave_requests elr
                JOIN employees e ON elr.employee_id = e.id
                JOIN leave_types lt ON elr.leave_type_id = lt.id
                WHERE elr.status = 'pending' AND lt.category = 'annual_leave'
                GROUP BY e.branch_id

                UNION ALL

                -- Type 3: Izin Lainnya (special_leave)
                SELECT 
                    3 as submission_type, 
                    'Izin Lainnya' as type_label,
                    e.branch_id,
                    COUNT(*) as pending_count
                FROM employee_leave_requests elr
                JOIN employees e ON elr.employee_id = e.id
                JOIN leave_types lt ON elr.leave_type_id = lt.id
                WHERE elr.status = 'pending' AND lt.category = 'special_leave'
                GROUP BY e.branch_id

                UNION ALL

                -- Type 4: Lembur
                SELECT 
                    4 as submission_type, 
                    'Lembur' as type_label,
                    e.branch_id,
                    COUNT(*) as pending_count
                FROM employee_overtimes eo
                JOIN employees e ON eo.employee_id = e.id
                WHERE eo.status = 'pending'
                GROUP BY e.branch_id

                UNION ALL

                -- Type 5: Piutang
                SELECT 
                    5 as submission_type, 
                    'Piutang' as type_label,
                    e.branch_id,
                    COUNT(*) as pending_count
                FROM receivables r
                JOIN employees e ON r.request_by = e.id
                -- Handle both legacy and current pending status values
                WHERE r.status IN ('pending', 'on_progress')
                GROUP BY e.branch_id

                UNION ALL

                -- Type 6: Reimbursement
                SELECT 
                    6 as submission_type, 
                    'Reimbursement' as type_label,
                    e.branch_id,
                    COUNT(*) as pending_count
                FROM reimbursements r
                JOIN employees e ON r.employee_id = e.id
                WHERE r.status = 'pending'
                GROUP BY e.branch_id

                UNION ALL

                -- Type 7: Umum (Use branch_id directly from table or fallback to employee's branch)
                SELECT 
                    7 as submission_type, 
                    'Umum' as type_label,
                    COALESCE(gs.branch_id, e.branch_id) as branch_id,
                    COUNT(*) as pending_count
                FROM general_submissions gs
                LEFT JOIN employees e ON gs.employee_id = e.id
                WHERE gs.status = 'pending'
                GROUP BY COALESCE(gs.branch_id, e.branch_id)

                UNION ALL

                -- Type 8: Karyawan Harian
                SELECT 
                    8 as submission_type, 
                    'Karyawan Harian' as type_label,
                    e.branch_id,
                    COUNT(*) as pending_count
                FROM daily_reports dr
                JOIN employees e ON dr.employee_id = e.id
                WHERE dr.status = 'pending'
                GROUP BY e.branch_id
            ) as main
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original view without branch_id grouping (simplified version for rollback)
        DB::statement('DROP VIEW IF EXISTS v_pending_submissions_count');
        
        // Note: Ideally we would restore the exact previous definition, but for 'down' 
        // in dev environment, dropping is often sufficient or we could recreate the old one.
        // For safety I will just drop it.
    }
};
