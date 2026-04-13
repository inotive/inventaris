# Pending Submissions View Documentation

## Overview

View `v_pending_submissions_count` dipakai untuk menampilkan jumlah pending submission di sidebar, dengan dukungan filter per cabang.

Referensi pemakaian:
- `app/Http/Middleware/HandleInertiaRequests.php`

## Active View

### `v_pending_submissions_count`

Tujuan:
- Menggabungkan jumlah pending dari semua sumber submission utama.
- Menyediakan kolom `branch_id` agar badge bisa difilter sesuai cabang user.

Kolom:
- `source_table`
- `submission_type`
- `type_label`
- `branch_id`
- `pending_count`

Contoh query:

```sql
SELECT *
FROM v_pending_submissions_count
ORDER BY submission_type, branch_id;
```

Total pending semua tipe/cabang:

```sql
SELECT SUM(pending_count) AS total_pending
FROM v_pending_submissions_count;
```

## Source Mapping

- Type 1 (Sakit): `employee_leave_requests` + `leave_types.category = 'sick_leave'`
- Type 2 (Cuti Tahunan): `employee_leave_requests` + `leave_types.category = 'annual_leave'`
- Type 3 (Izin Lainnya): `employee_leave_requests` + `leave_types.category = 'special_leave'`
- Type 4 (Lembur): `employee_overtimes`
- Type 5 (Piutang): `receivables` (`status IN ('pending', 'on_progress')`)
- Type 6 (Reimbursement): `reimbursements`
- Type 7 (Umum): `general_submissions`
- Type 8 (Karyawan Harian): `daily_reports`

## Migration

Migration aktif:
- `database/migrations/2026_02_03_140000_add_branch_id_to_pending_submissions_view.php`

Jalankan migration:

```bash
php artisan migrate
```

Rollback migration terakhir:

```bash
php artisan migrate:rollback
```

## Manual SQL

Jika ingin membuat view secara manual:

```bash
mysql -u username -p database_name < database/sql/pending_submissions_views.sql
```

## Troubleshooting

Cek status migration:

```bash
php artisan migrate:status
```

Refresh cache app:

```bash
php artisan cache:clear
```

Verifikasi isi view:

```sql
SELECT * FROM v_pending_submissions_count;
```
