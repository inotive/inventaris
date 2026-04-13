-- ============================================================================
-- PENDING SUBMISSIONS VIEW
-- ============================================================================
-- Purpose: Menampilkan jumlah pengajuan pending untuk badge di sidebar
-- Updated: 2026-02-24 - Disederhanakan dan disamakan dengan migration aktif
-- Usage: Digunakan oleh HandleInertiaRequests.php untuk badge count
-- ============================================================================

CREATE OR REPLACE VIEW v_pending_submissions_count AS
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

    -- Type 7: Umum
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
) as main;

-- ============================================================================
-- CARA PAKAI
-- ============================================================================
-- SELECT * FROM v_pending_submissions_count ORDER BY submission_type, branch_id;
-- SELECT SUM(pending_count) as total FROM v_pending_submissions_count;
-- SELECT submission_type, branch_id, pending_count
-- FROM v_pending_submissions_count
-- WHERE submission_type = 1;

-- ============================================================================
-- MAPPING TABEL KE TIPE SUBMISSION
-- ============================================================================
-- Type 1 (Sakit)           -> employee_leave_requests (leave_types.category = 'sick_leave')
-- Type 2 (Cuti Tahunan)    -> employee_leave_requests (leave_types.category = 'annual_leave')
-- Type 3 (Izin Lainnya)    -> employee_leave_requests (leave_types.category = 'special_leave')
-- Type 4 (Lembur)          -> employee_overtimes
-- Type 5 (Piutang)         -> receivables
-- Type 6 (Reimbursement)   -> reimbursements
-- Type 7 (Umum)            -> general_submissions
-- Type 8 (Karyawan Harian) -> daily_reports
