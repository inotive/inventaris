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
                            Total Pengajuan
                        </div>
                    </th>
                    <th class="p-3 bg-gray-100 border border-gray-200">
                        <div class="font-medium text-left text-gray-600">
                            Total Disetujui
                        </div>
                    </th>
                    <th class="p-3 bg-gray-100 border border-gray-200">
                        <div class="font-medium text-left text-gray-600">
                            Total Jam Lembur
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
                    v-if="rows.data && rows.data.length"
                    v-for="(row, idx) in rows.data"
                    :key="row.id"
                    class="border-b border-gray-200"
                >
                    <td class="p-3 text-center">
                        {{ (rows.current_page - 1) * rows.per_page + idx + 1 }}
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
                                    row.employee?.name || "-"
                                }}</span>
                                <span class="text-xs text-gray-500">{{
                                    row.department || "-"
                                }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="p-3">{{ row.ot_requests ?? 0 }} Hari</td>
                    <td class="p-3">{{ row.ot_approved ?? 0 }} Hari</td>
                    <td class="p-3">{{ row.ot_hours ?? 0 }} Jam</td>
                    <td class="p-3">
                        <button
                            @click="showDetail(row.employee.id, row.employee.name)"
                            class="text-blue-600 hover:underline focus:outline-none"
                        >
                            Lihat Detail
                        </button>
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
    <Pagination
        v-if="rows.data && rows.data.length"
        :pagination="rows"
        class="border-t"
    />

    <!-- Overtime Detail Modal -->
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
                        Detail Lembur - {{ selectedEmployeeName }}
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
                        v-else-if="!loading && overtimeDetails.length === 0"
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
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <p class="mt-4 text-gray-600">
                            Tidak ada data lembur yang disetujui
                        </p>
                    </div>

                    <!-- Overtime List -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="overtime in overtimeDetails"
                            :key="overtime.id"
                            class="p-4 rounded-lg border border-gray-200 transition-shadow hover:shadow-md"
                        >
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-gray-900">
                                        {{ formatDate(overtime.date) }}
                                    </h4>
                                    <span
                                        :class="getStatusBadgeClass(overtime.status)"
                                        class="inline-flex items-center px-2.5 py-0.5 mt-1 text-xs font-medium rounded-full"
                                    >
                                        {{ getStatusLabel(overtime.status) }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ overtime.duration_hours }} Jam
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-500">Waktu Mulai</p>
                                    <p class="font-medium text-gray-900">
                                        {{ overtime.start_time || '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Waktu Selesai</p>
                                    <p class="font-medium text-gray-900">
                                        {{ overtime.end_time || '-' }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="overtime.reason && overtime.reason !== '-'"
                                class="mt-3"
                            >
                                <p class="text-sm text-gray-500">Alasan</p>
                                <p class="text-sm text-gray-900">
                                    {{ overtime.reason }}
                                </p>
                            </div>

                            <div
                                v-if="overtime.approved_by"
                                class="mt-3 text-xs text-gray-500"
                            >
                                Disetujui oleh: {{ overtime.approved_by }}
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
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import Pagination from "@/Components/common/Pagination.vue";
import AvatarInitials from "@/Components/common/AvatarInitials.vue";

const props = defineProps({ rows: { type: Object, required: true } });

// Modal state
const showModal = ref(false);
const loading = ref(false);
const overtimeDetails = ref([]);
const selectedEmployeeId = ref(null);
const selectedEmployeeName = ref("");

// Get current year and month from URL or default to current
const getCurrentYearMonth = () => {
    const urlParams = new URLSearchParams(window.location.search);
    return {
        year: parseInt(urlParams.get("year")) || new Date().getFullYear(),
        month: parseInt(urlParams.get("month")) || new Date().getMonth() + 1,
    };
};

// Show detail modal
const showDetail = async (employeeId, employeeName) => {
    selectedEmployeeId.value = employeeId;
    selectedEmployeeName.value = employeeName;
    showModal.value = true;
    loading.value = true;
    overtimeDetails.value = [];

    try {
        const { year, month } = getCurrentYearMonth();
        const url = `/attendance-recap/overtime-details/${employeeId}?year=${year}&month=${month}`;
        const response = await axios.get(url);

        if (response.data.success) {
            overtimeDetails.value = response.data.data;
        }
    } catch (error) {
        console.error("Error fetching overtime details:", error);
        alert("Gagal memuat data lembur");
    } finally {
        loading.value = false;
    }
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    selectedEmployeeId.value = null;
    selectedEmployeeName.value = "";
    overtimeDetails.value = [];
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

// Get status label
const getStatusLabel = (status) => {
    const labels = {
        approved: "Disetujui",
        pending: "Menunggu",
        rejected: "Ditolak",
    };
    return labels[status] || status;
};

// Get status badge class
const getStatusBadgeClass = (status) => {
    const classes = {
        approved: "text-green-800 bg-green-100",
        pending: "text-yellow-800 bg-yellow-100",
        rejected: "text-red-800 bg-red-100",
    };
    return classes[status] || "text-gray-800 bg-gray-100";
};
</script>
