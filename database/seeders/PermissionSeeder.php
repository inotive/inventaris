<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $modules = [
            'Dashboard' => [
                'key' => 'dashboard',
                'actions' => ['view'],
            ],
            'Hasil Inspeksi' => [
                'key' => 'inspections',
                'actions' => ['view'],
            ],
            'Laporan' => [
                'key' => 'reports',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Pengajuan - Semua Pengajuan' => [
                'key' => 'submission_all',
                'actions' => ['view'],
            ],
            'Pengajuan - Sakit' => [
                'key' => 'submission_sick',
                'actions' => ['view', 'approve'],
            ],
            'Pengajuan - Cuti' => [
                'key' => 'submission_leave',
                'actions' => ['view', 'approve'],
            ],
            'Pengajuan - Khusus' => [
                'key' => 'submission_others',
                'actions' => ['view', 'approve'],
            ],
            'Pengajuan - Lembur' => [
                'key' => 'submission_overtime',
                'actions' => ['view', 'approve'],
            ],
            'Pengajuan - Piutang' => [
                'key' => 'submission_debt',
                'actions' => ['view', 'approve'],
            ],
            'Pengajuan - Umum' => [
                'key' => 'submission_general',
                'actions' => ['view', 'approve'],
            ],
            'Pengajuan - Reimbursement' => [
                'key' => 'submission_reimbursement',
                'actions' => ['view', 'approve'],
            ],
            'Pengajuan - Karyawan Harian' => [
                'key' => 'submission_daily',
                'actions' => ['view', 'create', 'edit', 'delete', 'approve'],
            ],
            'Absensi Realtime' => [
                'key' => 'realtime_presences',
                'actions' => ['create'],
            ],
            'Daftar Karyawan' => [
                'key' => 'employees',
                'actions' => ['view', 'create', 'edit', 'delete', 'viewDetail'],
            ],
            'Shift' => [
                'key' => 'work_shifts',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Jadwal' => [
                'key' => 'calendar',
                'actions' => ['view', 'edit'],
            ],
            'Absensi' => [
                'key' => 'presences',
                'actions' => ['view', 'edit'],
            ],
            'Rekap Karyawan' => [
                'key' => 'attendance_recap',
                'actions' => ['view'],
            ],
            'Rekap Slip Gaji' => [
                'key' => 'attendance_recap',
                'actions' => ['view', 'create_salary_slip', 'delete_salary_slip'],
            ],
            'Data Kendaraan' => [
                'key' => 'vehicles',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Riwayat Service' => [
                'key' => 'vehicle_services',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Riwayat Inspeksi Kendaraan' => [
                'key' => 'vehicle_inspections',
                'actions' => ['view'],
            ],
            'Rekap Inspeksi Kendaraan' => [
                'key' => 'vehicle_inspection_recap',
                'actions' => ['view'],
            ],
            'Rekap Performance Karyawan' => [
                'key' => 'employee_performances',
                'actions' => ['view', 'edit', 'calculate_kpi'],
            ],
            'Permintaan Barang' => [
                'key' => 'material_requests',
                'actions' => ['view', 'create', 'edit', 'delete', 'approve'],
            ],
            'Pengajuan Barang' => [
                'key' => 'purchase_requests',
                'actions' => ['view', 'create', 'edit', 'delete', 'approve'],
            ],
            'Pembelian Barang' => [
                'key' => 'purchase_orders',
                'actions' => ['view', 'create', 'edit', 'delete', 'approve'],
            ],
            'Perpindahan Barang' => [
                'key' => 'good_transfers',
                'actions' => ['view', 'create', 'edit', 'delete', 'approve'],
            ],
            'Penerimaan Barang' => [
                'key' => 'good_receipts',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Pemakaian Barang' => [
                'key' => 'good_issues',
                'actions' => ['view', 'create', 'edit', 'delete', 'approve'],
            ],
            'Daftar Barang' => [
                'key' => 'items',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Stock Masuk' => [
                'key' => 'stock_in',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Stock Keluar' => [
                'key' => 'stock_out',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Rekap Stok' => [
                'key' => 'stock_recap',
                'actions' => ['view'],
            ],
            'Daftar Cabang' => [
                'key' => 'branches',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Daftar Departemen' => [
                'key' => 'departments',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Daftar Pengguna' => [
                'key' => 'users',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Jabatan & Akses' => [
                'key' => 'roles',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Daftar Checklist' => [
                'key' => 'checklists',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Kategori Checklist' => [
                'key' => 'checklist_categories',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Kategori Shift Kerja' => [
                'key' => 'shifts',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Wilayah Absensi' => [
                'key' => 'absence_areas',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Kategori Izin' => [
                'key' => 'leave_types',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Hak Cuti Karyawan' => [
                'key' => 'leave_balances',
                'actions' => ['manage'],
            ],
            'Kategori Barang' => [
                'key' => 'item_categories',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Satuan Barang' => [
                'key' => 'units',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Kategori Kendaraan' => [
                'key' => 'vehicle_categories',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Prinsip & Etos Kerja' => [
                'key' => 'work_principles',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Pengumuman' => [
                'key' => 'announcements',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
            'Tata Tertib' => [
                'key' => 'compliance',
                'actions' => ['view', 'create', 'edit', 'delete'],
            ],
        ];

        $labels = [
            'view'    => 'Lihat Data',
            'create'  => 'Tambah Data',
            'edit'    => 'Edit Data',
            'delete'  => 'Hapus Data',
            'approve' => 'Approve',
            'viewDetail' => 'Lihat Detail',
            'create_salary_slip' => 'Tambah Slip Gaji',
            'delete_salary_slip' => 'Hapus Slip Gaji',
            'calculate_kpi' => 'Hitung KPI',
            'manage' => 'Kelola',
        ];

        foreach ($modules as $groupName => $module) {
            foreach ($module['actions'] as $action) {
                Permission::firstOrCreate(
                    [
                        'name' => "{$module['key']}.{$action}",
                        'guard_name' => 'web',
                    ],
                    [
                        'group_name'   => $groupName,
                        'display_name' => "{$labels[$action]}",
                    ]
                );
            }
        }
    }
}
