<template>

    <Head title="Rekaptulasi Absensi" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div class="flex gap-2 items-center">
                <div class="relative">
                    <input v-model="local.q" type="text" placeholder="Cari..."
                        class="py-2.5 pr-8 pl-3 w-64 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20" />
                    <span class="absolute right-2 top-1/2 text-gray-400 -translate-y-1/2">🔍</span>
                </div>

            </div>
        </div>

        <!-- Filters Section (Moved to Top) -->
        <div class="px-4 py-3 bg-white rounded-lg border border-gray-200">
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <div class="flex flex-wrap gap-2 items-center flex-1">
                    <div class="w-auto min-w-[160px] max-w-[200px]">
                        <select v-model="local.branch_id" :disabled="!isSuperadmin && user?.employee?.branch_id !== 2"
                            class="w-full px-3 h-9 text-sm rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:cursor-not-allowed"
                            @change="onBranchChange">
                            <option value="">Semua Cabang</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                {{ branch.name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-auto min-w-[160px] max-w-[200px]">
                        <select v-model="local.department_id"
                            class="w-full px-3 h-9 text-sm rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option value="">Semua Departemen</option>
                            <option v-for="department in departments" :key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-auto min-w-[100px] max-w-[120px]">
                        <select v-model="local.year"
                            class="w-full px-3 h-9 text-sm rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option v-for="y in yearOptions" :key="y" :value="y">
                                {{ y }}
                            </option>
                        </select>
                    </div>
                    <div class="w-auto min-w-[100px] max-w-[120px]">
                        <select v-model="local.month"
                            class="w-full px-3 h-9 text-sm rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option v-for="m in monthOptions" :key="m.value" :value="m.value">
                                {{ m.label }}
                            </option>
                        </select>
                    </div>
                    <div class="w-auto min-w-[140px] max-w-[160px]">
                        <select v-model="local.status"
                            class="w-full px-3 h-9 text-sm rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option value="">Semua Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="w-auto min-w-[140px]">
                    <button type="button" @click="exportToExcel"
                        class="w-full px-4 h-9 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-300 hover:bg-emerald-100 transition-colors">
                        Export Excel
                    </button>
                </div>
            </div>
        </div>

        <div class="flex overflow-hidden flex-col bg-white rounded-lg border border-gray-200">
            <div class="px-2 sm:px-4 md:px-6 pt-4 border-b">
                <div class="flex gap-4 items-center mb-3 text-sm">
                    <button v-for="t in tabs" :key="t.value" :class="[
                        'px-3 py-2 rounded-t',
                        local.tab === t.value
                            ? 'text-blue-600 border-b-2 border-blue-600'
                            : 'text-gray-600 hover:text-gray-800',
                    ]" @click="switchTab(t.value)">
                        {{ t.label }}
                    </button>
                </div>
            </div>

            <div v-if="local.tab === 'monthly-status'">
                <MonthlyStatusTab :rows="attendances" :year="local.year" :month="local.month"
                    :key="`monthly-${local.tab}`" />
            </div>
            <div v-else-if="local.tab === 'late'">
                <LateTab :rows="attendances" :key="`late-${local.tab}`" />
            </div>
            <div v-else-if="local.tab === 'leave'">
                <LeaveTab :rows="leaveRows" :key="`leave-${local.tab}`" />
            </div>
            <div v-else-if="local.tab === 'overtime'">
                <OvertimeTab :rows="attendances" :key="`overtime-${local.tab}`" />
            </div>
            <div v-else-if="local.tab === 'salary'">
                <div class="p-4 border-b">
                    <button v-if="can('attendance_recap.create_salary_slip')" @click="openSalaryModal"
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        + Tambah Slip Gaji
                    </button>
                </div>
                <SalaryTab :rows="salaryRows" :year="local.year" :key="`salary-${local.tab}`" />
            </div>

            <!-- Salary Slip Modal -->
            <Modal :show="showSalaryModal" title="Tambah Slip Gaji" confirmText="Simpan" maxWidth="md"
                @close="closeSalaryModal" @confirm="saveSalarySlip">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Pilih Karyawan
                        </label>
                        <select v-model="salaryForm.employee_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Karyawan</option>
                            <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                                {{ employee.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Bulan
                        </label>
                        <select v-model="salaryForm.bulan"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Bulan</option>
                            <option v-for="month in monthYearOptions" :key="month.value" :value="month.value">
                                {{ month.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Upload File PDF
                        </label>
                        <input ref="fileInput" type="file" accept=".pdf" @change="handleFileChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p class="text-xs text-gray-500 mt-1">
                            Hanya file PDF yang diperbolehkan
                        </p>
                    </div>
                </div>
            </Modal>
            <!-- <div v-else>
                <div class="grid grid-cols-7 gap-3 pb-4">
                    <div class="flex gap-2 items-center p-3 rounded-lg border">
                        <span
                            class="inline-flex justify-center items-center w-6 h-6 font-semibold text-emerald-600 bg-emerald-100 rounded-full"
                            >{{ summary.on_time }}</span
                        >
                        <span class="text-sm text-gray-700">Sesuai Jadwal</span>
                    </div>
                    <div class="flex gap-2 items-center p-3 rounded-lg border">
                        <span
                            class="inline-flex justify-center items-center w-6 h-6 font-semibold text-amber-600 bg-amber-100 rounded-full"
                            >{{ summary.late }}</span
                        >
                        <span class="text-sm text-gray-700">Terlambat</span>
                    </div>
                    <div class="flex gap-2 items-center p-3 rounded-lg border">
                        <span
                            class="inline-flex justify-center items-center w-6 h-6 font-semibold text-sky-600 bg-sky-100 rounded-full"
                            >{{ summary.early_leave }}</span
                        >
                        <span class="text-sm text-gray-700">Pulang Cepat</span>
                    </div>
                    <div class="flex gap-2 items-center p-3 rounded-lg border">
                        <span
                            class="inline-flex justify-center items-center w-6 h-6 font-semibold text-indigo-600 bg-indigo-100 rounded-full"
                            >{{ summary.overtime }}</span
                        >
                        <span class="text-sm text-gray-700">Lembur</span>
                    </div>
                    <div class="flex gap-2 items-center p-3 rounded-lg border">
                        <span
                            class="inline-flex justify-center items-center w-6 h-6 font-semibold text-rose-600 bg-rose-100 rounded-full"
                            >{{ summary.missing }}</span
                        >
                        <span class="text-sm text-gray-700"
                            >Tanpa Rekam Jam</span
                        >
                    </div>
                    <div class="flex gap-2 items-center p-3 rounded-lg border">
                        <span
                            class="inline-flex justify-center items-center w-6 h-6 font-semibold text-teal-600 bg-teal-100 rounded-full"
                            >{{ summary.leave }}</span
                        >
                        <span class="text-sm text-gray-700">Cuti</span>
                    </div>
                    <div class="flex gap-2 items-center p-3 rounded-lg border">
                        <span
                            class="inline-flex justify-center items-center w-6 h-6 font-semibold text-fuchsia-600 bg-fuchsia-100 rounded-full"
                            >{{ summary.sick }}</span
                        >
                        <span class="text-sm text-gray-700">Sakit</span>
                    </div>
                </div>
                <div class="overflow-auto" data-simplebar>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr>
                                <th
                                    class="p-3 bg-gray-100 border-gray-200 border-y"
                                >
                                    <div
                                        class="font-medium text-center text-gray-600"
                                    >
                                        No
                                    </div>
                                </th>
                                <th
                                    class="p-3 bg-gray-100 border border-gray-200"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Nama & Jabatan Pekerja
                                    </div>
                                </th>
                                <th
                                    class="p-3 bg-gray-100 border border-gray-200"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Waktu Masuk
                                    </div>
                                </th>
                                <th
                                    class="p-3 bg-gray-100 border border-gray-200"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Waktu Keluar
                                    </div>
                                </th>
                                <th
                                    class="p-3 bg-gray-100 border-gray-200 border-y"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Total Jam Kerja
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-if="
                                    attendances.data && attendances.data.length
                                "
                                v-for="(row, idx) in attendances.data"
                                :key="row.id"
                                class="border-b border-gray-200"
                            >
                                <td class="p-3 text-center">
                                    {{
                                        (attendances.current_page - 1) *
                                            attendances.per_page +
                                        idx +
                                        1
                                    }}
                                </td>
                                <td class="p-3">
                                    <div class="flex gap-3 items-center">
                                        <AvatarInitials
                                            :name="row.employee?.name || '-'"
                                            :gender="row.employee?.gender || ''"
                                            :size="32"
                                        />
                                        <div class="flex flex-col">
                                            <span
                                                class="font-medium text-gray-800"
                                                >{{
                                                    row.employee?.name || "-"
                                                }}</span
                                            >
                                            <span
                                                class="text-xs text-gray-500"
                                                >{{
                                                    row.department || "-"
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="flex gap-3 items-center">
                                        <div class="flex -space-x-2">
                                            <img
                                                v-for="i in 2"
                                                :key="i"
                                                src="/images/avatars/avatar-1.png"
                                                class="w-6 h-6 rounded-full border"
                                            />
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="font-medium text-gray-800"
                                                >{{
                                                    row.jam_masuk || "-"
                                                }}</span
                                            >
                                            <span
                                                class="text-xs text-gray-500"
                                                >{{
                                                    formatDate(row.date)
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="flex gap-3 items-center">
                                        <div class="flex -space-x-2">
                                            <img
                                                v-for="i in 2"
                                                :key="i"
                                                src="/images/avatars/avatar-2.png"
                                                class="w-6 h-6 rounded-full border"
                                            />
                                        </div>
                                        <div class="flex flex-col">
                                            <span
                                                class="font-medium text-gray-800"
                                                >{{
                                                    row.jam_keluar || "-"
                                                }}</span
                                            >
                                            <span
                                                class="text-xs text-gray-500"
                                                >{{
                                                    formatDate(row.date)
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    {{
                                        computeDuration(
                                            row.jam_masuk,
                                            row.jam_keluar
                                        )
                                    }}
                                </td>
                            </tr>
                            <tr v-else>
                                <td
                                    colspan="5"
                                    class="py-6 text-center text-gray-500"
                                >
                                    Tidak ada data
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination
                    v-if="attendances.data && attendances.data.length"
                    :pagination="attendances"
                    class="border-t"
                />
            </div> -->
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/Pagination.vue";
import Modal from "@/Components/common/Modal.vue";
import AvatarInitials from "@/Components/common/AvatarInitials.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted } from "vue";
import axios from "axios";
import * as XLSX from 'xlsx';
import MonthlyStatusTab from "./components/MonthlyStatusTab.vue";
import LateTab from "./components/LateTab.vue";
import LeaveTab from "./components/LeaveTab.vue";
import OvertimeTab from "./components/OvertimeTab.vue";
import SalaryTab from "./components/SalaryTab.vue";
import { useAuth } from "@/Composables/useAuth";

const { can, is } = useAuth();

const breadcrumbs = [{ label: "Absensi" }, { label: "Rekaptulasi Absensi" }];

const isSuperadmin = computed(() => is("Superadmin"));
defineOptions({ layout: AppLayout });

const props = defineProps({
    tab: String,
    summary: Object,
    attendances: Object,
    leaveRows: Object,
    lateRows: Object,
    overtimeRows: Object,
    salaryRows: Object,
    filters: Object,
    branches: Array,
    departments: Array,
    employees: Array,
});

const tabs = [
    // { value: "daily", label: "Presensi Harian" },
    { value: "monthly-status", label: "Status Absensi Bulanan" },
    { value: "late", label: "Keterlambatan" },
    { value: "overtime", label: "Lembur" },
    { value: "leave", label: "Cuti" },
    { value: "salary", label: "Gaji dan Tunjangan" },
];

const now = new Date();
const { user } = useAuth();

// Initialize branch_id: if not Superadmin, use user's branch_id
const getInitialBranchId = () => {
    if (props.filters?.branch_id) {
        return props.filters.branch_id;
    }
    if (!isSuperadmin.value && user.value?.employee?.branch_id && user.value.employee.branch_id !== 2) {
        return user.value.employee.branch_id;
    }
    return "";
};

const local = ref({
    tab: props.tab || "monthly-status",
    q: props.filters?.q || "",
    branch_id: getInitialBranchId(),
    department_id: props.filters?.department_id || "",
    employee_id: props.filters?.employee_id || "",
    date_from: props.filters?.date_from || "",
    date_to: props.filters?.date_to || "",
    year: props.filters?.year || now.getFullYear(),
    month: props.filters?.month || now.getMonth() + 1,
    status: props.filters?.status || "",
});

// Reactive departments based on selected branch
const departments = ref(props.departments || []);

const monthOptions = [
    { value: 1, label: "Jan" },
    { value: 2, label: "Feb" },
    { value: 3, label: "Mar" },
    { value: 4, label: "Apr" },
    { value: 5, label: "Mei" },
    { value: 6, label: "Jun" },
    { value: 7, label: "Jul" },
    { value: 8, label: "Agu" },
    { value: 9, label: "Sep" },
    { value: 10, label: "Okt" },
    { value: 11, label: "Nov" },
    { value: 12, label: "Des" },
];
const yearOptions = Array.from({ length: 6 }).map(
    (_, i) => now.getFullYear() - 3 + i
);

// Salary modal state
const showSalaryModal = ref(false);
const fileInput = ref(null);
const salaryForm = ref({
    employee_id: '',
    bulan: '',
    file: null
});

// Generate month-year options for salary slip
const monthYearOptions = computed(() => {
    const options = [];
    const currentYear = local.value.year;

    for (let month = 1; month <= 12; month++) {
        const monthKey = `${currentYear}-${String(month).padStart(2, '0')}`;
        const monthName = monthOptions.find(m => m.value === month)?.label || `Bulan ${month}`;
        options.push({
            value: monthKey,
            label: `${monthName} ${currentYear}`
        });
    }

    return options;
});


// Watch branch_id to prevent changes if user is not Superadmin
watch(
    () => local.value.branch_id,
    (newVal) => {
        if (!isSuperadmin.value && user.value?.employee?.branch_id && user.value.employee.branch_id !== 2) {
            // Force branch_id to user's branch_id if not Superadmin
            if (newVal !== user.value.employee.branch_id) {
                local.value.branch_id = user.value.employee.branch_id;
            }
        }
    }
);

let t = null;
watch(
    () => ({ ...local.value }),
    () => {
        clearTimeout(t);
        t = setTimeout(() => fetchList(), 300);
    },
    { deep: true }
);

// Fetch data saat komponen pertama kali dimuat
onMounted(() => {
    // Hanya fetch jika belum ada data atau data kosong
    if (
        !props.attendances ||
        !props.attendances.data ||
        props.attendances.data.length === 0
    ) {
        fetchList();
    }
});

const showFilterMenu = ref(false);
function toggleFilterMenu() {
    showFilterMenu.value = !showFilterMenu.value;
}
function resetFilters() {
    local.value.branch_id = "";
    local.value.department_id = "";
    local.value.employee_id = "";
    local.value.date_from = "";
    local.value.date_to = "";
    fetchList();
}

function onBranchChange() {
    // Reset department when branch changes
    local.value.department_id = "";
    // Fetch departments for the selected branch
    fetchDepartments();
}

function fetchDepartments() {
    if (local.value.branch_id) {
        // Use direct axios call to avoid Inertia issues
        axios.get(route('attendance-recap.departments'), {
            params: {
                branch_id: local.value.branch_id
            }
        }).then(response => {
            departments.value = response.data.departments || [];
        }).catch(error => {
            console.error('Error fetching departments:', error);
            departments.value = [];
        });
    } else {
        // If no branch selected, show all departments
        departments.value = props.departments || [];
    }
}
function switchTab(v) {
    if (local.value.tab !== v) {
        local.value.tab = v;
        // Selalu fetch data baru saat pindah tab
        fetchList();
    }
}

// Salary modal functions
function openSalaryModal() {
    showSalaryModal.value = true;
    // Reset form
    salaryForm.value = {
        employee_id: '',
        bulan: '',
        file: null
    };
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function closeSalaryModal() {
    showSalaryModal.value = false;
}

function handleFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.type !== 'application/pdf') {
            alert('Hanya file PDF yang diperbolehkan');
            event.target.value = '';
            return;
        }
        salaryForm.value.file = file;
    }
}

function saveSalarySlip() {
    if (!salaryForm.value.employee_id || !salaryForm.value.bulan || !salaryForm.value.file) {
        alert('Semua field harus diisi');
        return;
    }

    const formData = new FormData();
    formData.append('employee_id', salaryForm.value.employee_id);
    formData.append('bulan', salaryForm.value.bulan);
    formData.append('file', salaryForm.value.file);

    router.post(route('salary-slips.store'), formData, {
        onSuccess: () => {
            closeSalaryModal();
            fetchList(); // Refresh data
        },
        onError: (errors) => {
            console.error('Error saving salary slip:', errors);
            alert('Gagal menyimpan salary slip');
        }
    });
}

function fetchList() {
    router.get(
        route("attendance-recap.index"),
        { ...local.value },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

function formatDate(d) {
    if (!d) return "-";
    try {
        return new Date(d).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    } catch (e) {
        return d;
    }
}

function computeDuration(a, b) {
    if (!a || !b) return "-";
    try {
        const [ah, am] = a.split(":").map(Number);
        const [bh, bm] = b.split(":").map(Number);
        let minutes = bh * 60 + bm - (ah * 60 + am);
        if (minutes < 0) minutes += 24 * 60;
        const h = Math.floor(minutes / 60);
        const m = minutes % 60;
        return `${h} Jam ${String(m).padStart(2, "0")} Mnt`;
    } catch (e) {
        return "-";
    }
}

function exportToExcel() {
    // Build export URL with current filters
    const params = new URLSearchParams({
        tab: local.value.tab,
        year: local.value.year,
        month: local.value.month,
    });

    // Add optional filters
    if (local.value.department_id) {
        params.append('department_id', local.value.department_id);
    }
    if (local.value.employee_id) {
        params.append('employee_id', local.value.employee_id);
    }
    if (local.value.branch_id) {
        params.append('branch_id', local.value.branch_id);
    }
    if (local.value.status) {
        params.append('status', local.value.status);
    }
    if (local.value.q) {
        params.append('q', local.value.q);
    }

    // Open export URL in new window to trigger download
    const exportUrl = route('attendance-recap.export') + '?' + params.toString();
    window.open(exportUrl, '_blank');
}

function exportMonthlyStatusToExcel(fileName) {
    if (!props.attendances?.data || props.attendances.data.length === 0) {
        alert('Tidak ada data untuk diekspor');
        return;
    }

    // Prepare data for Excel
    const wsData = [];

    // Header row
    const headers = ['No', 'Nama Karyawan', 'Jumlah Hari Kerja', 'Departemen', 'Jabatan'];

    // Add status headers
    const statusHeaders = ['Hadir', 'Berjalan', 'Terlambat', 'Alpa', 'Cuti', 'Sakit', 'Lainnya'];
    headers.push(...statusHeaders);

    // Add day headers (1-31)
    const totalDays = props.attendances.data[0]?.days ? Object.keys(props.attendances.data[0].days).length : 0;
    for (let d = 1; d <= totalDays; d++) {
        headers.push(`Tanggal ${String(d).padStart(2, '0')}`);
    }

    wsData.push(headers);

    // Data rows
    props.attendances.data.forEach((row, idx) => {
        const rowData = [
            idx + 1,
            row.employee?.name || '-',
            row.work_days || 0,
            row.department || '-',
            row.jabatan || '-',
            row.recap?.H || 0,
            row.recap?.B || 0,
            row.recap?.T || 0,
            row.recap?.A || 0,
            row.recap?.C || 0,
            row.recap?.S || 0,
            row.recap?.I || 0
        ];

        // Add day data
        for (let d = 1; d <= totalDays; d++) {
            const dayData = row.days?.[d];
            // dayData is already a string (e.g., 'OT', 'Cuti', 'SH', etc.)
            rowData.push(dayData || '-');
        }

        wsData.push(rowData);
    });

    // Create workbook and worksheet
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);

    // Set column widths
    const colWidths = [
        { wch: 5 },   // No
        { wch: 25 },  // Nama Karyawan
        { wch: 15 },  // Jumlah Hari Kerja
        { wch: 20 },  // Departemen
        { wch: 8 },   // Jabatan
        { wch: 8 },   // Hadir
        { wch: 8 },   // Berjalan
        { wch: 10 },  // Terlambat
        { wch: 8 },   // Alpa
        { wch: 8 },   // Cuti
        { wch: 8 },   // Sakit
        { wch: 8 },   // Lainnya
    ];

    // Add day column widths
    for (let d = 1; d <= totalDays; d++) {
        colWidths.push({ wch: 12 });
    }

    ws['!cols'] = colWidths;

    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Status Absensi Bulanan');

    // Save file
    XLSX.writeFile(wb, fileName);
}

function exportLateToExcel(fileName) {
    if (!props.attendances?.data || props.attendances.data.length === 0) {
        alert('Tidak ada data untuk diekspor');
        return;
    }

    const wsData = [
        ['No', 'Nama Karyawan', 'Departemen', 'Tanggal', 'Jam Masuk', 'Jam Terlambat', 'Durasi Terlambat (Menit)', 'Status']
    ];

    props.attendances.data.forEach((row, idx) => {
        wsData.push([
            idx + 1,
            row.employee?.name || '-',
            row.department || '-',
            row.date || '-',
            row.jam_masuk || '-',
            row.jam_terlambat || '-',
            row.durasi_terlambat || 0,
            row.status || '-'
        ]);
    });

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    ws['!cols'] = [
        { wch: 5 }, { wch: 25 }, { wch: 20 }, { wch: 12 },
        { wch: 12 }, { wch: 15 }, { wch: 20 }, { wch: 10 }
    ];

    XLSX.utils.book_append_sheet(wb, ws, 'Data Keterlambatan');
    XLSX.writeFile(wb, fileName);
}

function exportOvertimeToExcel(fileName) {
    if (!props.attendances?.data || props.attendances.data.length === 0) {
        alert('Tidak ada data untuk diekspor');
        return;
    }

    const wsData = [
        ['No', 'Nama Karyawan', 'Departemen', 'Total Request', 'Request Disetujui', 'Total Jam Lembur']
    ];

    props.attendances.data.forEach((row, idx) => {
        wsData.push([
            idx + 1,
            row.employee?.name || '-',
            row.department || '-',
            row.ot_requests || 0,
            row.ot_approved || 0,
            row.ot_hours || 0
        ]);
    });

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    ws['!cols'] = [
        { wch: 5 }, { wch: 25 }, { wch: 20 }, { wch: 15 },
        { wch: 18 }, { wch: 15 }
    ];

    XLSX.utils.book_append_sheet(wb, ws, 'Data Lembur');
    XLSX.writeFile(wb, fileName);
}

function exportLeaveToExcel(fileName) {
    if (!props.leaveRows?.data || props.leaveRows.data.length === 0) {
        alert('Tidak ada data untuk diekspor');
        return;
    }

    const wsData = [
        ['No', 'Nama Karyawan', 'Departemen', 'Kuota Cuti (Hari)', 'Cuti Digunakan (Hari)', 'Sisa Cuti (Hari)']
    ];

    props.leaveRows.data.forEach((row, idx) => {
        wsData.push([
            idx + 1,
            row.employee?.name || '-',
            row.department || '-',
            row.leave_quota || 0,
            row.leave_used || 0,
            row.leave_remaining || 0
        ]);
    });

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    ws['!cols'] = [
        { wch: 5 }, { wch: 25 }, { wch: 20 }, { wch: 18 },
        { wch: 20 }, { wch: 18 }
    ];

    XLSX.utils.book_append_sheet(wb, ws, 'Data Cuti');
    XLSX.writeFile(wb, fileName);
}

function exportSalaryToExcel(fileName) {
    if (!props.salaryRows?.data || props.salaryRows.data.length === 0) {
        alert('Tidak ada data untuk diekspor');
        return;
    }

    const wsData = [
        ['No', 'Nama Karyawan', 'Departemen', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
    ];

    props.salaryRows.data.forEach((row, idx) => {
        const monthlyData = [];
        for (let month = 1; month <= 12; month++) {
            monthlyData.push(row.salary_slips?.[month] ? '✓' : '-');
        }

        wsData.push([
            idx + 1,
            row.employee?.name || '-',
            row.department || '-',
            ...monthlyData
        ]);
    });

    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    ws['!cols'] = [
        { wch: 5 }, { wch: 25 }, { wch: 20 },
        ...Array(12).fill({ wch: 8 })
    ];

    XLSX.utils.book_append_sheet(wb, ws, 'Data Gaji dan Tunjangan');
    XLSX.writeFile(wb, fileName);
}
</script>
