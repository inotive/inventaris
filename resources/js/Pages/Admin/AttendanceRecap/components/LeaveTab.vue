<template>
    <div class="overflow-auto" data-simplebar>
        <table class="min-w-full text-sm">
            <thead>
                <tr>
                    <th class="p-3 bg-gray-100 border-gray-200 border-y">
                        <div class="font-medium text-center text-gray-600">
                            No
                        </div>
                    </th>
                    <th class="p-3 bg-gray-100 border border-gray-200">
                        <div class="font-medium text-left text-gray-600">
                            Nama & Jabatan Pekerja
                        </div>
                    </th>
                    <th class="p-3 bg-gray-100 border border-gray-200">
                        <div class="font-medium text-left text-gray-600">
                            Total Hak Cuti
                        </div>
                    </th>
                    <th class="p-3 bg-gray-100 border border-gray-200">
                        <div class="font-medium text-left text-gray-600">
                            Cuti Terpakai
                        </div>
                    </th>
                    <th class="p-3 bg-gray-100 border border-gray-200">
                        <div class="font-medium text-left text-gray-600">
                            Sisa Cuti
                        </div>
                    </th>
                    <th class="p-3 bg-gray-100 border-gray-200 border-y">
                        <div class="font-medium text-left text-gray-600">
                            Aksi
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-if="hasRows"
                    v-for="(row, idx) in items"
                    :key="row.id"
                    class="border-b border-gray-200"
                >
                    <td class="p-3 text-center">
                        {{
                            (rows?.current_page - 1) * rows?.per_page + idx + 1
                        }}
                    </td>
                    <td class="p-3">
                        <div class="flex gap-3 items-center">
                            <template v-if="row.employee.photo_url">
                                <img
                                    :src="
                                        row.employee.photo_url
                                            ? `/storage/${row.employee.photo_url}`
                                            : ''
                                    "
                                    alt="avatar"
                                    class="object-cover w-8 h-8 rounded-full"
                                />
                            </template>
                            <template v-else>
                                <AvatarInitials
                                    :name="row.employee.name"
                                    :gender="row.employee.gender || ''"
                                    :size="32"
                                />
                            </template>
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-800">{{
                                    row.employee.name
                                }}</span>
                                <span class="text-xs text-gray-500">{{
                                    row.employee.role || "Staff"
                                }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="p-3">{{ row.leave_annual ?? 0 }} Hari</td>
                    <td class="p-3">{{ row.leave_annual_used ?? 0 }} Hari</td>
                    <td class="p-3">
                        {{
                            (row.leave_annual ?? 0) -
                            (row.leave_annual_used ?? 0)
                        }}
                        Hari
                    </td>
                    <td class="p-3">
                        <div class="flex gap-2">
                            <button
                                @click="
                                    showDetail(
                                        row.employee.id,
                                        row.employee.name,
                                    )
                                "
                                class="text-blue-600 hover:underline focus:outline-none"
                            >
                                Lihat Detail
                            </button>
                            <span class="text-gray-300">|</span>
                            <button
                                @click="
                                    openManageLeaveModal(
                                        row.employee.id,
                                        row.employee.name,
                                        row.leave_annual || 0,
                                        row.leave_annual_used || 0,
                                    )
                                "
                                class="text-green-600 hover:underline focus:outline-none"
                            >
                                Atur Hak Cuti
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-else>
                    <td colspan="6" class="py-6 text-center text-gray-500">
                        Tidak ada data
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <Pagination v-if="hasRows" :pagination="rows" class="border-t" />

    <!-- Leave Detail Modal -->
    <teleport to="body">
        <div
            v-if="showModal"
            class="flex fixed inset-0 z-50 justify-center items-center bg-black bg-opacity-50"
            @click.self="closeModal"
        >
            <div
                class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden"
            >
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Detail Cuti - {{ selectedEmployeeName }}
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <!-- Loading State -->
                    <div
                        v-if="loading"
                        class="flex justify-center items-center py-12"
                    >
                        <div
                            class="w-12 h-12 rounded-full border-b-2 border-blue-600 animate-spin"
                        ></div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else-if="!loading && leaveDetails.length === 0"
                        class="py-12 text-center"
                    >
                        <svg
                            class="mx-auto w-12 h-12 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        <p class="mt-4 text-gray-600">
                            Tidak ada data cuti yang disetujui
                        </p>
                    </div>

                    <!-- Leave List -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="leave in leaveDetails"
                            :key="leave.id"
                            class="p-4 rounded-lg border border-gray-200 transition-shadow hover:shadow-md"
                        >
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900">
                                        {{ leave.leave_type }}
                                    </h4>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 mt-1 text-xs font-medium text-green-800 bg-green-100 rounded-full"
                                    >
                                        Disetujui
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ leave.total_days }} Hari
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-500">Tanggal Mulai</p>
                                    <p class="font-medium text-gray-900">
                                        {{ formatDate(leave.start_date) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Tanggal Selesai</p>
                                    <p class="font-medium text-gray-900">
                                        {{ formatDate(leave.end_date) }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="leave.reason && leave.reason !== '-'"
                                class="mt-3"
                            >
                                <p class="text-sm text-gray-500">Alasan</p>
                                <p class="text-sm text-gray-900">
                                    {{ leave.reason }}
                                </p>
                            </div>

                            <div
                                v-if="leave.approved_at"
                                class="mt-3 text-xs text-gray-500"
                            >
                                Disetujui pada:
                                {{ formatDateTime(leave.approved_at) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end p-6 border-t">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg transition-colors hover:bg-gray-200"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </teleport>

    <!-- Manage Leave Balance Modal -->
    <teleport to="body">
        <div
            v-if="showManageLeaveModal"
            class="flex fixed inset-0 z-50 justify-center items-center bg-black bg-opacity-50"
            @click.self="closeManageLeaveModal"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <!-- Modal Header -->
                <div class="flex justify-between items-center p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Atur Hak Cuti - {{ manageEmployeeName }}
                    </h3>
                    <button
                        @click="closeManageLeaveModal"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form @submit.prevent="submitManageLeave">
                    <div class="p-6 space-y-4">
                        <div>
                            <label
                                class="block mb-2 text-sm font-medium text-gray-700"
                            >
                                Tahun <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model.number="leaveForm.year"
                                type="number"
                                min="2000"
                                max="2100"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            />
                        </div>
                        <div>
                            <label
                                class="block mb-2 text-sm font-medium text-gray-700"
                            >
                                Jumlah Hari Cuti Tahunan
                                <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model.number="leaveForm.total_quota"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            />
                            <div class="mt-2 text-xs text-gray-600 space-y-1">
                                <p>
                                    Nilai saat ini:
                                    <span class="font-semibold"
                                        >{{ leaveForm.total_quota }} hari</span
                                    >
                                </p>
                                <p>
                                    Cuti terpakai:
                                    <span class="font-semibold"
                                        >{{ leaveForm.used_quota }} hari</span
                                    >
                                </p>
                                <p>
                                    Sisa cuti:
                                    <span class="font-semibold"
                                        >{{
                                            leaveForm.total_quota -
                                            leaveForm.used_quota
                                        }}
                                        hari</span
                                    >
                                </p>
                            </div>
                        </div>
                        <div
                            class="p-3 bg-blue-50 border border-blue-200 rounded-md"
                        >
                            <p class="text-xs text-blue-800">
                                <i class="mr-1 lni lni-information"></i>
                                Jika sudah ada data cuti tahunan untuk tahun
                                ini, quota akan diperbarui.
                            </p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end gap-2 p-6 border-t">
                        <button
                            type="button"
                            @click="closeManageLeaveModal"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg transition-colors hover:bg-gray-200"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmittingLeave"
                            class="px-4 py-2 text-white bg-green-600 rounded-lg transition-colors hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="isSubmittingLeave">Menyimpan...</span>
                            <span v-else>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </teleport>
</template>

<script setup>
import { computed, ref } from "vue";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import Pagination from "@/Components/common/Pagination.vue";
import AvatarInitials from "@/Components/common/AvatarInitials.vue";

const props = defineProps({ rows: { type: Object, default: null } });

const items = computed(() => props.rows?.data ?? []);
const hasRows = computed(
    () => Array.isArray(items.value) && items.value.length > 0,
);

// Modal state
const showModal = ref(false);
const loading = ref(false);
const leaveDetails = ref([]);
const selectedEmployeeId = ref(null);
const selectedEmployeeName = ref("");

// Manage Leave Balance Modal state
const showManageLeaveModal = ref(false);
const isSubmittingLeave = ref(false);
const manageEmployeeId = ref(null);
const manageEmployeeName = ref("");
const leaveForm = ref({
    leave_type_id: 1, // Hardcode untuk Cuti Tahunan
    year: new Date().getFullYear(),
    total_quota: 0,
    used_quota: 0, // Tambah field untuk info cuti terpakai
});

// Get current year from URL or default to current year
const getCurrentYear = () => {
    const urlParams = new URLSearchParams(window.location.search);
    return parseInt(urlParams.get("year")) || new Date().getFullYear();
};

// Show detail modal
const showDetail = async (employeeId, employeeName) => {
    selectedEmployeeId.value = employeeId;
    selectedEmployeeName.value = employeeName;
    showModal.value = true;
    loading.value = true;
    leaveDetails.value = [];

    try {
        const year = getCurrentYear();
        const url = `/attendance-recap/leave-details/${employeeId}?year=${year}`;
        const response = await axios.get(url);

        if (response.data.success) {
            leaveDetails.value = response.data.data;
        }
    } catch (error) {
        console.error("Error fetching leave details:", error);
        alert("Gagal memuat data cuti");
    } finally {
        loading.value = false;
    }
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    selectedEmployeeId.value = null;
    selectedEmployeeName.value = "";
    leaveDetails.value = [];
};

// Open manage leave modal
const openManageLeaveModal = (
    employeeId,
    employeeName,
    currentQuota = 0,
    usedQuota = 0,
) => {
    manageEmployeeId.value = employeeId;
    manageEmployeeName.value = employeeName;
    leaveForm.value = {
        leave_type_id: 1, // Hardcode untuk Cuti Tahunan
        year: getCurrentYear(),
        total_quota: currentQuota, // Pre-fill dengan nilai saat ini
        used_quota: usedQuota, // Simpan info cuti terpakai
    };
    showManageLeaveModal.value = true;
};

// Close manage leave modal
const closeManageLeaveModal = () => {
    showManageLeaveModal.value = false;
    manageEmployeeId.value = null;
    manageEmployeeName.value = "";
    leaveForm.value = {
        leave_type_id: 1, // Hardcode untuk Cuti Tahunan
        year: new Date().getFullYear(),
        total_quota: 0,
        used_quota: 0,
    };
};

// Submit manage leave form
const submitManageLeave = async () => {
    // Validate form
    if (!leaveForm.value.year || leaveForm.value.total_quota < 0) {
        const Swal = (await import("sweetalert2")).default;
        const errors = [];
        if (!leaveForm.value.year) errors.push("Tahun wajib diisi");
        if (leaveForm.value.total_quota < 0)
            errors.push("Jumlah hari tidak boleh negatif");

        Swal.fire({
            title: "Validasi Gagal",
            html: `<ul style="text-align: left; padding-left: 20px;">${errors.map((e) => `<li>${e}</li>`).join("")}</ul>`,
            icon: "error",
            confirmButtonText: "OK",
        });
        return;
    }

    isSubmittingLeave.value = true;

    try {
        const response = await axios.post(
            `/employees/${manageEmployeeId.value}/leave-balances`,
            leaveForm.value,
        );

        // Show success message
        const Swal = (await import("sweetalert2")).default;
        Swal.fire({
            title: "Berhasil!",
            text: "Hak cuti berhasil ditambahkan",
            icon: "success",
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false,
        });

        closeManageLeaveModal();

        // Refresh the page to show updated data
        router.reload({ preserveScroll: true });
    } catch (error) {
        console.error("Error saving leave balance:", error);

        // Show error with SweetAlert2
        const Swal = (await import("sweetalert2")).default;
        const errorMessages = error.response?.data?.errors
            ? Object.values(error.response.data.errors).flat()
            : [error.response?.data?.message || "Gagal menyimpan hak cuti"];

        Swal.fire({
            title: "Gagal Menyimpan",
            html: `<ul style="text-align: left; padding-left: 20px;">${errorMessages.map((e) => `<li>${e}</li>`).join("")}</ul>`,
            icon: "error",
            confirmButtonText: "OK",
        });
    } finally {
        isSubmittingLeave.value = false;
    }
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
};

// Format datetime
const formatDateTime = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>
