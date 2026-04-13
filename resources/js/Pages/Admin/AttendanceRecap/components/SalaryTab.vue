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
                            Nama Karyawan
                        </div>
                    </th>
                    <th
                        v-for="month in 12"
                        :key="month"
                        class="p-3 bg-gray-100 border border-gray-200"
                    >
                        <div class="font-medium text-center text-gray-600">
                            {{ month }}
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template v-if="rows && rows.data && rows.data.length">
                    <tr
                        v-for="(row, idx) in rows.data"
                        :key="row.id"
                        class="border-b border-gray-200"
                    >
                        <td class="p-3 text-center">
                            {{
                                (rows.current_page - 1) * rows.per_page +
                                idx +
                                1
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
                        <td
                            v-for="month in 12"
                            :key="month"
                            class="p-3 text-center"
                        >
                            <div class="flex justify-center">
                                <button
                                    v-if="
                                        row.salary_slips &&
                                        row.salary_slips[month] === true
                                    "
                                    @click="
                                        previewSalarySlip(
                                            row.employee.id,
                                            month,
                                            row.employee.name
                                        )
                                    "
                                    class="inline-flex justify-center items-center w-6 h-6 text-green-600 bg-green-100 rounded-full transition-colors cursor-pointer hover:bg-green-200"
                                    title="Klik untuk preview slip gaji"
                                >
                                    ✓
                                </button>
                                <span
                                    v-else
                                    class="inline-flex justify-center items-center w-6 h-6 text-gray-400 bg-gray-100 rounded-full"
                                >
                                    -
                                </span>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr v-else>
                    <td :colspan="14" class="py-6 text-center text-gray-500">
                        Tidak ada data
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <Pagination
        v-if="rows && rows.data && rows.data.length"
        :pagination="rows"
        class="border-t"
    />

    <!-- PDF Preview Modal -->
    <Modal
        :show="showPreviewModal"
        :title="previewData.title"
        maxWidth="2xl"
        @close="closePreviewModal"
    >
        <div class="space-y-4">
            <!-- Header Info -->
            <div
                class="flex justify-between items-center p-4 bg-gray-50 rounded-lg"
            >
                <div class="flex items-center space-x-3">
                    <div
                        class="flex justify-center items-center w-10 h-10 bg-blue-100 rounded-full"
                    >
                        <svg
                            class="w-5 h-5 text-blue-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            ></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-900">
                            Slip Gaji PDF
                        </h3>
                        <p class="text-xs text-gray-500">
                            Dokumen dapat di-scroll dan di-zoom
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        @click="downloadPdf"
                        class="px-3 py-1.5 text-xs text-blue-600 bg-blue-50 rounded-md transition-colors hover:bg-blue-100"
                    >
                        Download
                    </button>
                    <button
                        v-if="can('attendance_recap.delete_salary_slip')"
                        @click="openConfirmModal"
                        class="px-3 py-1.5 text-xs text-red-600 bg-red-50 rounded-md transition-colors hover:bg-red-100"
                    >
                        Hapus
                    </button>
                </div>
            </div>

            <!-- PDF Viewer Container -->
            <div
                class="relative w-full h-[600px] bg-gray-100 rounded-lg overflow-hidden"
            >
                <iframe
                    v-if="previewData.url"
                    :src="previewData.url"
                    class="w-full h-full border-0"
                    type="application/pdf"
                    frameborder="0"
                ></iframe>
                <div
                    v-else
                    class="flex justify-center items-center h-full text-gray-500"
                >
                    <div class="text-center">
                        <div
                            class="mx-auto mb-4 w-12 h-12 rounded-full border-b-2 border-blue-600 animate-spin"
                        ></div>
                        <p class="text-sm font-medium">Memuat dokumen PDF...</p>
                        <p class="mt-1 text-xs text-gray-400">
                            Mohon tunggu sebentar
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div
                class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
            >
                <div class="flex items-center space-x-4 text-xs text-gray-500">
                    <span class="flex items-center">
                        <svg
                            class="mr-1 w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            ></path>
                        </svg>
                        Scroll untuk melihat halaman
                    </span>
                    <span class="flex items-center">
                        <svg
                            class="mr-1 w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            ></path>
                        </svg>
                        Gunakan zoom untuk memperbesar
                    </span>
                </div>
                <div class="text-xs text-gray-400">PDF Viewer</div>
            </div>
        </div>
    </Modal>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
        :show="isConfirmModalOpen"
        :question="`Yakin ingin menghapus slip gaji`"
        :selected="getDeleteConfirmationText()"
        title="Hapus Slip Gaji"
        confirmText="Ya, Hapus!"
        maxWidth="md"
        @close="closeConfirmModal"
        @confirm="destroySalarySlip"
    />
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";
import Pagination from "@/Components/common/Pagination.vue";
import Modal from "@/Components/common/Modal.vue";
import AvatarInitials from "@/Components/common/AvatarInitials.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import { useAuth } from "@/Composables/useAuth";

const { can } = useAuth();

const props = defineProps({
    rows: Object,
    year: Number,
});

// Preview modal state
const showPreviewModal = ref(false);
const previewData = ref({
    title: "",
    url: "",
    employeeId: null,
    month: null,
    employeeName: null,
});

// Confirm delete modal state
const isConfirmModalOpen = ref(false);

function previewSalarySlip(employeeId, month, employeeName) {
    const year = props.year;
    const monthKey = `${year}-${String(month).padStart(2, "0")}`;
    const monthName = getMonthName(month);

    // Set preview data
    previewData.value = {
        title: `Slip Gaji - ${employeeName} (${monthName} ${year})`,
        url: "",
        employeeId: employeeId,
        month: month,
        employeeName: employeeName,
    };

    // Show modal first
    showPreviewModal.value = true;

    // Create preview URL
    const previewUrl = route("salary-slips.preview", {
        employee_id: employeeId,
        bulan: monthKey,
    });

    // Set URL after modal is shown
    setTimeout(() => {
        previewData.value.url = previewUrl;
    }, 100);
}

function downloadPdf() {
    if (previewData.value.employeeId && previewData.value.month) {
        const year = props.year;
        const monthKey = `${year}-${String(previewData.value.month).padStart(
            2,
            "0"
        )}`;

        const downloadUrl = route("salary-slips.download", {
            employee_id: previewData.value.employeeId,
            bulan: monthKey,
        });

        // Force download by opening in new tab
        const link = document.createElement("a");
        link.href = downloadUrl;
        link.download = `salary-slip-${previewData.value.employeeId}-${monthKey}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function closePreviewModal() {
    showPreviewModal.value = false;
    previewData.value = {
        title: "",
        url: "",
        employeeId: null,
        month: null,
        employeeName: null,
    };
}

// Delete salary slip functions
function openConfirmModal() {
    if (!previewData.value.employeeId || !previewData.value.month) {
        return;
    }
    isConfirmModalOpen.value = true;
}

function closeConfirmModal() {
    isConfirmModalOpen.value = false;
}

function getDeleteConfirmationText() {
    if (!previewData.value.employeeName || !previewData.value.month) {
        return "";
    }
    const year = props.year;
    const monthName = getMonthName(previewData.value.month);
    return `${previewData.value.employeeName} (${monthName} ${year})`;
}

function destroySalarySlip() {
    if (!previewData.value.employeeId || !previewData.value.month) {
        closeConfirmModal();
        return;
    }

    const year = props.year;
    const monthKey = `${year}-${String(previewData.value.month).padStart(2, "0")}`;

    // Build URL with query parameters
    const url = route("salary-slips.destroy") +
        `?employee_id=${previewData.value.employeeId}&bulan=${monthKey}`;

    router.delete(url, {
        onSuccess: () => {
            closeConfirmModal();
            closePreviewModal();
            // Reload the page to refresh the data
            router.reload({ only: ["rows"] });
        },
        onError: (errors) => {
            alert(errors?.message || "Gagal menghapus slip gaji.");
        },
        preserveScroll: true,
    });
}

function getMonthName(month) {
    const months = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
    ];
    return months[month - 1] || `Bulan ${month}`;
}
</script>
