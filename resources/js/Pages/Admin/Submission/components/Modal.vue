<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative max-h-[90vh] overflow-y-auto">
      <button
        class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
        @click="close"
        title="Tutup"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Detail Pengajuan</h2>
        <button
          class="text-gray-400 hover:text-gray-600 transition-colors"
          @click="close"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div v-if="submission" class="space-y-6">
        <!-- Nama -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Nama</label>
          <div class="p-3 bg-gray-50 rounded-lg border">
            <p class="text-gray-900 font-medium">{{ submission.employee?.name || submission.name || '-' }}</p>
          </div>
        </div>

        <!-- Cabang -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Cabang</label>
          <div class="p-3 bg-gray-50 rounded-lg border">
            <p class="text-gray-900 font-medium">{{ submission.employee?.branch_name || submission.branch_name || submission.branch?.name || '-' }}</p>
          </div>
        </div>

        <!-- Tanggal Pengajuan -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Tanggal Pengajuan</label>
          <div class="p-3 bg-gray-50 rounded-lg border">
            <p class="text-gray-900 font-medium">{{ formatDate(submission.tanggal_pengajuan) || '-' }}</p>
          </div>
        </div>

        <!-- Kategori Shift -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Kategori Shift</label>
          <div class="p-3 bg-gray-50 rounded-lg border">
            <p class="text-gray-900 font-medium">{{ submission.shift_category || submission.shift || 'One Shift Distribusi' }}</p>
          </div>
        </div>

        <!-- Keterangan Pengajuan -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Keterangan Pengajuan</label>
          <div class="p-3 bg-gray-50 rounded-lg border min-h-[80px]">
            <p class="text-gray-900">{{ submission.description || submission.reason || '-' }}</p>
          </div>
        </div>

        <!-- Mulai -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Mulai</label>
          <div class="p-3 bg-gray-50 rounded-lg border">
            <p class="text-gray-900 font-medium">{{ formatDate(submission.start_date) || '-' }}</p>
          </div>
        </div>

        <!-- Selesai -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Selesai</label>
          <div class="p-3 bg-gray-50 rounded-lg border">
            <p class="text-gray-900 font-medium">{{ formatDate(submission.end_date) || '-' }}</p>
          </div>
        </div>

        <!-- Status -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Status</label>
          <div class="p-3 bg-gray-50 rounded-lg border">
            <select
              v-model="selectedStatus"
              class="w-full bg-transparent text-gray-900 font-medium focus:outline-none"
              :disabled="!canApprove && !canReject"
            >
              <option value="0">Menunggu</option>
              <option value="1">Disetujui</option>
              <option value="2">Ditolak</option>
              <option value="3">Dibatalkan</option>
            </select>
          </div>
        </div>

        <!-- Keterangan Admin -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Keterangan Admin</label>
          <textarea
            v-model="adminNotes"
            class="w-full p-3 bg-gray-50 rounded-lg border min-h-[100px] resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Izin Lembur saya terima"
          ></textarea>
        </div>
      </div>
      <div v-else class="text-center text-gray-500 py-8">
        Data pengajuan tidak ditemukan.
      </div>

      <!-- Action Button -->
      <div v-if="submission" class="mt-8 pt-6">
        <button
          @click="handleConfirm"
          :disabled="isProcessing"
          class="w-full bg-blue-600 text-white text-lg font-medium py-3 px-6 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <svg v-if="!isProcessing" class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <svg v-else class="w-5 h-5 mr-2 inline animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isProcessing ? 'Memproses...' : 'Konfirmasi Pengajuan' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { formatIndonesianDate } from '@/Helpers/dateFormat';

const typeMap = {
  sick: 'Sakit',
  annual_leave: 'Cuti Tahunan',
  overtime: 'Lembur',
  permission: 'Izin',
  others: 'Lain-lain',
  submission: 'Pengajuan'
};

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  submission: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'confirmed']);

// Processing state
const isProcessing = ref(false);
const processingAction = ref('');

// Form data
const selectedStatus = ref('0');
const adminNotes = ref('');

// Computed properties
const canApprove = computed(() => {
  return props.submission && (props.submission.status === 0 || props.submission.status === 'pending');
});

const canReject = computed(() => {
  return props.submission && (props.submission.status === 0 || props.submission.status === 'pending');
});

// Watch for submission changes to update form data
watch(() => props.submission, (newSubmission) => {
  if (newSubmission) {
    selectedStatus.value = newSubmission.status?.toString() || '0';
    adminNotes.value = newSubmission.admin_notes || '';
  }
}, { immediate: true });

function close() {
  emit('close');
}

function getTypeLabel(type) {
  return typeMap[type] || type || '-';
}

function formatDate(dateString) {
  return formatIndonesianDate(dateString, '');
}

function getStatusClass(status) {
  const statusClasses = {
    0: 'bg-yellow-100 text-yellow-800', // pending
    1: 'bg-green-100 text-green-800',   // approved
    2: 'bg-red-100 text-red-800',       // rejected
    3: 'bg-purple-100 text-purple-800'  // cancelled
  };
  return statusClasses[status] || 'bg-gray-100 text-gray-800';
}

function getStatusDotClass(status) {
  const dotClasses = {
    0: 'bg-yellow-500', // pending
    1: 'bg-green-500',   // approved
    2: 'bg-red-500',     // rejected
    3: 'bg-purple-500'   // cancelled
  };
  return dotClasses[status] || 'bg-gray-500';
}

async function handleConfirm() {
  if (!props.submission) return;

  isProcessing.value = true;

  try {
    // Prepare confirmation data
    const confirmationData = {
      ...props.submission,
      status: parseInt(selectedStatus.value),
      admin_notes: adminNotes.value,
      confirmed_at: new Date().toISOString()
    };

    // Emit confirmation event
    emit('confirmed', confirmationData);

    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));

    // Show success message based on status
    const statusText = {
      '0': 'Pengajuan berhasil diperbarui!',
      '1': 'Pengajuan berhasil disetujui!',
      '2': 'Pengajuan berhasil ditolak!',
      '3': 'Pengajuan berhasil dibatalkan!'
    };

    alert(statusText[selectedStatus.value] || 'Pengajuan berhasil diperbarui!');

    // Close modal after successful confirmation
    close();
  } catch (error) {
    console.error('Error confirming submission:', error);
    alert('Terjadi kesalahan saat mengkonfirmasi pengajuan');
  } finally {
    isProcessing.value = false;
  }
}
</script>
