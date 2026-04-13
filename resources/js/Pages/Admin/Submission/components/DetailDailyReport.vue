<template>
    <Toast />
    <!-- Modal Overlay -->
    <div v-if="isOpen" @click="goBack" class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div @click.stop class="bg-white rounded-lg shadow-xl w-full" :class="modalWidthClass"
                :style="isStatusApprovedOrRejected ? 'max-height:80vh; max-width: 50vw; margin-top: 3vh; overflow-y: auto;' : 'max-height:88vh; max-width: 40vw; margin-top: 5vh; overflow-y: auto;'">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Detail Pengajuan Karyawan Harian</h2>
                    <button @click="goBack"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors"
                        title="Tutup">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Loading State -->
                <div v-if="props.loading" class="flex items-center justify-center p-8">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                        <p class="text-gray-600">Memuat detail pengajuan...</p>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="props.error" class="flex items-center justify-center p-8">
                    <div class="text-center">
                        <div class="text-red-500 mb-4">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <p class="text-red-600 mb-4">{{ props.error }}</p>
                        <button @click="emit('close')" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Tutup
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div v-else-if="submission && submission.data" class="space-y-5 p-6">
                    <!-- Layout untuk status ditolak/disetujui -->
                    <div v-if="isStatusApprovedOrRejected" class="space-y-3">
                        <!-- Nama -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ submission?.data?.employee?.name || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Cabang -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cabang</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ submission?.data?.employee?.branch || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Departemen -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ submission?.data?.employee?.department || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Tanggal Pengajuan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengajuan</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ formatDisplayDate(submission?.data?.submission_date) }}
                                </p>
                            </div>
                        </div>

                        <!-- Periode Laporan -->
                        <div class="flex flex-wrap gap-6 items-end">
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <div class="p-3 bg-gray-50 rounded border">
                                    <p class="text-gray-900 text-sm">
                                        {{ formatDisplayDate(submission?.data?.start_date) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                <div class="p-3 bg-gray-50 rounded border">
                                    <p class="text-gray-900 text-sm">
                                        {{ formatDisplayDate(submission?.data?.end_date) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Nama Karyawan Harian & Gaji -->
                        <div class="flex flex-wrap gap-6 items-end">
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Karyawan Harian</label>
                                <div class="p-3 bg-gray-50 rounded border">
                                    <p class="text-gray-900 text-sm">
                                        {{ submission?.data?.name || '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Harian</label>
                                <div class="p-3 bg-gray-50 rounded border">
                                    <p class="text-gray-900 text-sm">
                                        {{ formatCurrency(submission?.data?.salary) || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Alasan Laporan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Laporan</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ submission?.data?.reason || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Admin</label>
                            <div class="p-3 bg-gray-50 rounded border min-h-[80px]">
                                <p class="text-gray-900 text-sm leading-relaxed">
                                    {{ submission?.data?.notes || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <div class="flex gap-2 items-center justify-between">
                                    <div class="flex gap-2 items-center px-3 py-1 rounded-full text-sm font-medium" :class="getStatusClass(submission?.data?.status)">
                                        <span class="w-2 h-2 rounded-full" :class="getStatusDotClass(submission?.data?.status)"></span>
                                        <span>{{ getStatusLabel(submission?.data?.status) }}</span>
                                    </div>
                                    <!-- Icon info untuk status ditolak/disetujui -->
                                    <div v-if="submission?.data?.approved || submission?.data?.approved_at" class="relative group">
                                        <div
                                            class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-200 transition-colors">
                                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <!-- Tooltip -->
                                        <div
                                            class="absolute right-0 top-9 bg-gray-800 text-white text-xs rounded-lg px-3 py-2 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-20 shadow-lg">
                                            <div class="font-medium">{{ formatDisplayDateTime(submission?.data?.approved_at) }}</div>
                                            <div class="text-gray-300 text-xs">Oleh</div>
                                            <div class="font-medium">{{ submission?.data?.approved?.name || '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Layout untuk status menunggu persetujuan -->
                    <div v-else class="space-y-3">
                        <!-- Nama -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ submission?.data?.employee?.name || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Cabang -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cabang</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ submission?.data?.employee?.branch || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Departemen -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ submission?.data?.employee?.department || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Tanggal Pengajuan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengajuan</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ formatDisplayDate(submission?.data?.submission_date) }}
                                </p>
                            </div>
                        </div>

                        <!-- Periode Laporan -->
                        <div class="flex flex-wrap gap-6 items-end">
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                <div class="p-3 bg-gray-50 rounded border">
                                    <p class="text-gray-900 text-sm">
                                        {{ formatDisplayDate(submission?.data?.start_date) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                <div class="p-3 bg-gray-50 rounded border">
                                    <p class="text-gray-900 text-sm">
                                        {{ formatDisplayDate(submission?.data?.end_date) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Nama Karyawan Harian & Gaji -->
                        <div class="flex flex-wrap gap-6 items-end">
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Karyawan Harian</label>
                                <div class="p-3 bg-gray-50 rounded border">
                                    <p class="text-gray-900 text-sm">
                                        {{ submission?.data?.name || '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gaji Harian</label>
                                <div class="p-3 bg-gray-50 rounded border">
                                    <p class="text-gray-900 text-sm">
                                        {{ formatCurrency(submission?.data?.salary) || '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Alasan Laporan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Laporan</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ submission?.data?.reason || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <div class="flex gap-2 items-center px-3 py-1 rounded-full text-sm font-medium" :class="getStatusClass(submission?.data?.status)">
                                    <span class="w-2 h-2 rounded-full" :class="getStatusDotClass(submission?.data?.status)"></span>
                                    <span>{{ getStatusLabel(submission?.data?.status) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form Konfirmasi - hanya tampil jika memiliki permission approve -->
                        <div v-if="canApprove" class="border-t pt-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Konfirmasi Pengajuan</h3>

                            <!-- Status Radio Cards -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">Status</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <label v-for="status in submission.submission_statuses" :key="status.value"
                                        class="relative flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all"
                                        :class="selectedStatus === status.value
                                            ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500'
                                            : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'"
                                    >
                                        <input type="radio" :value="status.value" v-model="selectedStatus"
                                            @change="handleStatusChange"
                                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500" />
                                        <span class="text-sm font-medium" :class="selectedStatus === status.value ? 'text-blue-700' : 'text-gray-700'">
                                            {{ status.label }}
                                        </span>
                                    </label>
                                </div>
                                <div v-if="formErrors.status" class="mt-1 text-sm text-red-500">
                                    {{ formErrors.status }}
                                </div>
                            </div>

                            <!-- Keterangan Admin -->
                            <div class="mt-4">
                                <label class="block mb-1 text-sm font-medium text-gray-700">
                                    Keterangan Admin
                                    <span v-if="selectedStatus === 'rejected' || selectedStatus === 'ditolak'" class="text-red-500">*</span>
                                </label>
                                <textarea v-model="keterangan"
                                    @input="handleKeteranganInput"
                                    class="px-3 py-2 w-full rounded-md border resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="formErrors.notes ? 'border-red-300' : 'border-gray-300'"
                                    rows="3" placeholder="Masukkan keterangan admin..."></textarea>
                                <div v-if="formErrors.notes" class="mt-1 text-sm text-red-500">
                                    {{ formErrors.notes }}
                                </div>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="flex justify-end gap-3 mt-6">
                                <button @click="goBack"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Batal
                                </button>
                                <button @click="openConfirmDialog" :disabled="!canSubmit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    {{ isProcessing ? 'Memproses...' : 'Konfirmasi' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Dialog -->
        <div v-if="showConfirmDialog" class="fixed inset-0 z-[10000] bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Konfirmasi Aksi</h3>
                    <p class="text-gray-600 mb-6">
                        Apakah Anda yakin ingin {{ getStatusLabel(selectedStatus).toLowerCase() }} pengajuan ini?
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="showConfirmDialog = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Batal
                        </button>
                        <button @click="handleConfirm" :disabled="isProcessing"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ isProcessing ? 'Memproses...' : 'Ya, Konfirmasi' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
</template>

<script setup>
import { router } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import { useAuth } from "@/Composables/useAuth";
import { formatIndonesianDate, formatIndonesianDateTime } from "@/Helpers/dateFormat";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    submission: { type: Object, required: true },
    submission_statuses: { type: Array, required: true },
    loading: {
        type: Boolean,
        default: false
    },
    error: {
        type: String,
        default: null
    },
});

const emit = defineEmits(['close', 'update-success']);

const { can } = useAuth();
const toast = useToast();

const formatDisplayDate = (value) => formatIndonesianDate(value);
const formatDisplayDateTime = (value, fallback = '-') => formatIndonesianDateTime(value, fallback);

const isProcessing = ref(false);
const showConfirmDialog = ref(false);
const formErrors = ref({});

// Check if user has permission to approve daily report submissions
const canApprove = computed(() => {
    return can('submission_daily.approve');
});

// Ambil status dari submission?.data?.status
const selectedStatus = ref(
    typeof props.submission?.data?.status !== "undefined"
        ? props.submission?.data?.status
        : (props.submission?.status ?? 'pending')
);

const keterangan = ref(props.submission?.data?.notes || "");

// Remove breadcrumbs as it's now a modal

// Computed properties untuk kondisi layout
const isStatusApprovedOrRejected = computed(() => {
    const originalStatus = props.submission?.data?.status ?? props.submission?.status ?? 'pending';
    return originalStatus === 'approved' || originalStatus === 'rejected';
});

// Modal width class
const modalWidthClass = computed(() => {
    return isStatusApprovedOrRejected.value ? 'max-w-2xl' : 'max-w-4xl';
});

// Validasi form
const isFormValid = computed(() => {
    if (!selectedStatus.value || selectedStatus.value === 'pending') {
        return false;
    }

    // If status is rejected, keterangan must be filled
    if (selectedStatus.value === 'rejected' || selectedStatus.value === 'ditolak') {
        return keterangan.value && keterangan.value.trim() !== '';
    }

    return true;
});

const canSubmit = computed(() => {
    return isFormValid.value && !isProcessing.value;
});

// Method untuk format currency
const formatCurrency = (amount) => {
    if (!amount) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Method untuk mendapatkan label status
const getStatusLabel = (status) => {
    const statusMap = {
        'pending': 'Menunggu',
        'approved': 'Disetujui',
        'rejected': 'Ditolak',
        'cancelled': 'Dibatalkan',
    };
    return statusMap[status] || 'Menunggu';
};

// Method untuk mendapatkan class background status
const getStatusClass = (status) => {
    const statusMap = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-green-100 text-green-800',
        'rejected': 'bg-red-100 text-red-800',
        'cancelled': 'bg-gray-100 text-gray-800',
    };
    return statusMap[status] || 'bg-gray-100 text-gray-800';
};

// Method untuk mendapatkan class dot status
const getStatusDotClass = (status) => {
    const dotMap = {
        'pending': 'bg-yellow-500',
        'approved': 'bg-green-500',
        'rejected': 'bg-red-500',
        'cancelled': 'bg-gray-500',
    };
    return dotMap[status] || 'bg-gray-500';
};

function handleStatusChange() {
    // Clear errors when status changes
    formErrors.value = {};

    // Validate keterangan if status is rejected
    if (selectedStatus.value === 'rejected' || selectedStatus.value === 'ditolak') {
        if (!keterangan.value || keterangan.value.trim() === '') {
            formErrors.value.notes = 'Keterangan admin wajib diisi untuk status ditolak';
        }
    }
}

function handleKeteranganInput() {
    // Clear error when user starts typing
    if (formErrors.value.notes) {
        delete formErrors.value.notes;
    }
}

function validateForm() {
    formErrors.value = {};

    if (!selectedStatus.value || selectedStatus.value === 'pending') {
        formErrors.value.status = 'Status harus dipilih';
        return false;
    }

    // Validate keterangan if status is rejected
    if (selectedStatus.value === 'rejected' || selectedStatus.value === 'ditolak') {
        if (!keterangan.value || keterangan.value.trim() === '') {
            formErrors.value.notes = 'Keterangan admin wajib diisi untuk status ditolak';
            return false;
        }
    }

    return true;
}

function openConfirmDialog() {
    if (!props.submission) return;

    if (!validateForm()) {
        return;
    }

    showConfirmDialog.value = true;
}

function goBack() {
    emit('close');
}

function handleConfirm() {
    if (!props.submission) return;

    if (!validateForm()) {
        return;
    }

    isProcessing.value = true;
    showConfirmDialog.value = false;

    // Use Inertia link for form submission
    router.put(route('submission.employee.update', props.submission?.data?.id), {
        status: selectedStatus.value,
        notes: keterangan.value || "",
    }, {
        onStart: () => {
            isProcessing.value = true;
        },
        onSuccess: (page) => {
            // Show success message
            const successMessage = page.props.flash?.success ||
                `Pengajuan berhasil ${getStatusLabel(selectedStatus.value).toLowerCase()}`;
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: successMessage,
                life: 3000,
            });
            emit('close');
            emit('update-success');
        },
        onError: (errors) => {
            console.error('Error confirming submission:', errors);
            formErrors.value = errors;

            // Get error message from errors object or use default
            const errorMessage = errors.message ||
                (typeof errors === 'string' ? errors : 'Terjadi kesalahan saat mengkonfirmasi pengajuan');

            toast.add({
                severity: "error",
                summary: "Error",
                detail: errorMessage,
                life: 5000,
            });
        },
        onFinish: () => {
            isProcessing.value = false;
        }
    });
}

// Remove layout options as it's now a modal component
</script>
