<template>
    <!-- Modal Overlay -->
    <div v-if="isOpen" @click="goBack"
        class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div @click.stop class="bg-white rounded-lg shadow-xl w-full" :class="modalWidthClass"
            style="max-height: 85vh; overflow-y: auto;">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Detail Pengajuan Umum</h2>
                <button @click="goBack"
                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors"
                    title="Tutup">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
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
                                {{ submission?.data?.employee?.branch_name || submission?.data?.branch?.name || '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Judul -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                        <div class="p-3 bg-gray-50 rounded border">
                            <p class="text-gray-900 text-sm">
                                {{ submission?.data?.title || '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Tag -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
                        <div class="p-3 bg-gray-50 rounded border">
                            <div class="flex flex-wrap gap-2">
                                <template v-if="Array.isArray(submission?.data?.tag)">
                                    <span v-for="(tag, i) in submission.data.tag" :key="i"
                                        class="px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                                        {{ tag }}
                                    </span>
                                </template>
                                <span v-else
                                    class="px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                                    {{ submission?.data?.tag || '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <div class="p-3 bg-gray-50 rounded border min-h-[80px]">
                            <p class="text-gray-900 text-sm leading-relaxed">
                                {{ submission?.data?.note || '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Lampiran -->
                    <div v-if="hasAttachments">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran</label>
                        <div class="p-3 bg-gray-50 rounded border cursor-pointer hover:bg-gray-100 transition-colors"
                            @click="openAttachment">
                            <div class="flex items-center justify-between">
                                <p class="text-gray-900 text-sm">
                                    {{ getAttachmentDisplayName() || 'Klik untuk membuka lampiran' }}
                                </p>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Keterangan Admin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Admin</label>
                        <div class="p-3 bg-gray-50 rounded border min-h-[80px]">
                            <p class="text-gray-900 text-sm leading-relaxed">
                                {{ submission?.data?.admin_notes || '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Row untuk Tanggal Pengajuan dan Status -->
                    <div class="flex flex-wrap gap-6 items-end">
                        <!-- Tanggal Pengajuan -->
                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengajuan</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ formatDate(submission?.data?.created_at) || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Status dengan icon dan tooltip -->
                        <div class="flex-1 min-w-[160px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="flex items-center gap-3">
                                <div class="p-3 rounded border flex-1" :class="getStatusClass(selectedStatus)">
                                    <p class="text-sm font-medium" :class="getStatusTextClass(selectedStatus)">
                                        {{ getStatusLabel(selectedStatus) }}
                                    </p>
                                </div>
                                <!-- Icon info untuk status ditolak/disetujui -->
                                <div class="relative group">
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
                                        <div class="font-medium">{{ formatDateTime(submission?.data?.approved_at) || '-' }}
                                        </div>
                                        <div class="text-gray-300 text-xs">{{ getStatusLabel(selectedStatus) }} oleh
                                        </div>
                                        <div class="font-medium">{{ submission?.data?.approver?.name || '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Layout default untuk status menunggu -->
                <div v-else class="space-y-4">
                    <!-- Grid untuk informasi dasar -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                    {{ submission?.data?.employee?.branch_name || submission?.data?.branch?.name ||
                                        '-'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Pengajuan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengajuan</label>
                        <div class="p-3 bg-gray-50 rounded border">
                            <p class="text-gray-900 text-sm">
                                {{ formatDate(submission?.data?.created_at) || '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Grid untuk Judul dan Tag -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Judul -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <p class="text-gray-900 text-sm">
                                    {{ submission?.data?.title || '-' }}
                                </p>
                            </div>
                        </div>

                        <!-- Tag -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
                            <div class="p-3 bg-gray-50 rounded border">
                                <div class="flex flex-wrap gap-2">
                                    <template v-if="Array.isArray(submission?.data?.tag)">
                                        <span v-for="(tag, i) in submission.data.tag" :key="i"
                                            class="px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                                            {{ tag }}
                                        </span>
                                    </template>
                                    <span v-else
                                        class="px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-full border border-blue-100">
                                        {{ submission?.data?.tag || '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <div class="p-3 bg-gray-50 rounded border min-h-[80px]">
                            <p class="text-gray-900 text-sm leading-relaxed">
                                {{ submission?.data?.note || '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Lampiran -->
                    <div v-if="hasAttachments">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lampiran</label>
                        <div class="p-3 bg-gray-50 rounded border cursor-pointer hover:bg-gray-100 transition-colors"
                            @click="openAttachment">
                            <div class="flex items-center justify-between">
                                <p class="text-gray-900 text-sm">
                                    {{ getAttachmentDisplayName() || 'Klik untuk membuka lampiran' }}
                                </p>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Status Radio Cards - hanya tampil jika memiliki permission approve -->
                    <div v-if="canApprove">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <label v-for="status in [
                                { value: 'pending', label: 'Menunggu Persetujuan' },
                                { value: 'approved', label: 'Disetujui' },
                                { value: 'rejected', label: 'Ditolak' },
                                { value: 'cancelled', label: 'Dibatalkan' }
                            ]" :key="status.value"
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
                        <p v-if="formErrors.status" class="text-red-500 text-xs mt-1">{{ formErrors.status }}</p>
                    </div>

                    <!-- Keterangan Admin - hanya tampil jika memiliki permission approve -->
                    <div v-if="canApprove">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Keterangan Admin
                            <span v-if="selectedStatus === 'rejected' || selectedStatus === 'ditolak'"
                                class="text-red-500">*</span>
                        </label>
                        <textarea v-model="keterangan"
                            class="w-full p-3 bg-gray-50 rounded border min-h-[80px] resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            :class="{ 'border-red-300': formErrors.keterangan }"
                            placeholder="Keterangan admin"></textarea>
                        <p v-if="formErrors.keterangan" class="text-red-500 text-xs mt-1">{{
                            formErrors.keterangan }}
                        </p>
                    </div>
                </div>
            </div>

            <div v-else-if="!props.loading && !props.error" class="text-center text-gray-500 py-8 px-4">
                Data pengajuan tidak ditemukan.
            </div>

            <!-- Action Button - hanya untuk status menunggu dan jika memiliki permission approve -->
            <div v-if="!props.loading && !props.error && submission && !isStatusApprovedOrRejected && canApprove"
                class="border-t border-gray-200 p-4">
                <button @click="showConfirmationDialog" :disabled="!canSubmit"
                    class="w-full flex items-center justify-center bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg v-if="!isProcessing" class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="w-4 h-4 mr-2 inline animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    {{ isProcessing ? 'Memproses...' : 'Konfirmasi Pengajuan' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Dialog -->
    <div v-if="showConfirmDialog"
        class="fixed inset-0 z-[10000] bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900">Konfirmasi Pengajuan</h3>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-sm text-gray-600 mb-4">
                        Apakah Anda yakin ingin mengubah status pengajuan menjadi
                        <span class="font-semibold text-gray-900">{{ getStatusLabel(selectedStatus) }}</span>?
                    </p>

                    <div v-if="selectedStatus === 'rejected' || selectedStatus === 'ditolak'"
                        class="bg-red-50 border border-red-200 rounded-md p-3">
                        <p class="text-sm text-red-800">
                            <strong>Perhatian:</strong> Status ditolak akan mengirim notifikasi kepada karyawan.
                        </p>
                    </div>

                    <div v-if="keterangan" class="mt-3">
                        <p class="text-sm text-gray-600">Keterangan admin:</p>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded border mt-1">{{ keterangan }}</p>
                    </div>
                </div>

                <div class="flex gap-3 justify-end">
                    <button @click="cancelConfirmation"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Batal
                    </button>
                    <button @click="handleConfirm" :disabled="isProcessing"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg v-if="isProcessing" class="w-4 h-4 mr-2 inline animate-spin" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        {{ isProcessing ? 'Memproses...' : 'Ya, Konfirmasi' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Attachment Gallery Modal -->
    <div v-if="showImageGallery" @click="closeImageGallery"
        class="fixed inset-0 z-[10001] bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div @click.stop class="relative bg-white rounded-lg shadow-xl w-full max-w-5xl" style="max-height: 90vh;">
            <!-- Close Button -->
            <button @click="closeImageGallery"
                class="absolute top-4 right-4 z-10 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <h3 class="text-lg font-semibold text-gray-900">Preview Lampiran</h3>
                    <span v-if="attachments.length > 1" class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded">
                        {{ currentImageIndex + 1 }} / {{ attachments.length }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                </div>
            </div>

            <!-- Modal Content -->
            <div class="p-6 overflow-auto" style="max-height: calc(90vh - 120px);">
                <!-- Navigation Arrows -->
                <button v-if="attachments.length > 1" @click="previousImage"
                    class="absolute left-4 top-1/2 transform -translate-y-1/2 z-10 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full p-2 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </button>

                <button v-if="attachments.length > 1" @click="nextImage"
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 z-10 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full p-2 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <!-- Image Display -->
                <div v-if="isCurrentAttachmentImage"
                    class="flex items-center justify-center bg-gray-50 rounded-lg border-2 border-gray-200 p-4">
                    <img :src="getImageUrl(attachments[currentImageIndex])"
                        :alt="getAttachmentName(attachments[currentImageIndex])"
                        class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-sm">
                </div>

                <!-- Other File Display -->
                <div v-else class="flex items-center justify-center">
                    <div class="bg-gray-50 rounded-lg p-8 max-w-md w-full text-center border-2 border-gray-200">
                        <!-- File Icon -->
                        <div class="mb-4">
                            <svg class="w-16 h-16 text-gray-400 mx-auto" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>

                        <!-- File Name -->
                        <p class="text-sm text-gray-900 mb-2">
                            {{ getAttachmentName(attachments[currentImageIndex]) }}
                        </p>

                        <!-- File Type -->
                        <p class="text-sm text-gray-500 mb-4">
                            {{ getFileType(attachments[currentImageIndex]) }}
                        </p>

                        <!-- File Size -->
                        <p v-if="attachments[currentImageIndex]?.file_size" class="text-xs text-gray-400 mb-4">
                            {{ formatFileSize(attachments[currentImageIndex].file_size) }}
                        </p>

                        <!-- Preview Button -->
                        <button @click="previewAttachment(attachments[currentImageIndex])"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            Preview
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useAuth } from "@/Composables/useAuth";
import { formatIndonesianDate, formatIndonesianDateTime } from "@/Helpers/dateFormat";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    isOpen: Boolean,
    submission: Object,
    type: String,
    submissionTypes: Array,
    submissionStatuses: Array,
    loading: Boolean,
    error: String,
});

const emit = defineEmits(['close', 'update-success']);
const toast = useToast();
const { can } = useAuth();

// Check if user has permission to approve general submissions
const canApprove = computed(() => {
    return can('submission_general.approve');
});

const isProcessing = ref(false);
const showConfirmDialog = ref(false);
const formErrors = ref({});

const selectedStatus = ref(
    props.submission?.data?.status || 'pending'
);

const keterangan = ref(props.submission?.data?.admin_note || "");

// Update refs when submission props change
watch(() => props.submission, (newVal) => {
    if (newVal?.data) {
        selectedStatus.value = newVal.data.status || 'pending';
        keterangan.value = newVal.data.admin_note || "";
    }
}, { deep: true });

// Computed properties untuk kondisi layout
const isStatusApprovedOrRejected = computed(() => {
    const originalStatus = props.submission?.data?.status ?? props.submission?.status ?? 'pending';
    return originalStatus === 'approved' || originalStatus === 'rejected' || originalStatus === 'disetujui' || originalStatus === 'ditolak' || originalStatus === 'cancelled' || originalStatus === 'dibatalkan';
});

// Validasi form
const isFormValid = computed(() => {
    if (selectedStatus.value === 'rejected' || selectedStatus.value === 'ditolak') {
        return !!keterangan.value && selectedStatus.value && selectedStatus.value !== 'pending';
    }
    return selectedStatus.value && selectedStatus.value !== 'pending' && selectedStatus.value !== 'menunggu persetujuan';
});

const canSubmit = computed(() => {
    return isFormValid.value && !isProcessing.value;
});

// Modal width class
const modalWidthClass = computed(() => {
    return "max-w-4xl";
});

const goBack = () => {
    emit('close');
};

const handleStatusChange = () => {
    // Reset error when status changes
    if (formErrors.value.status) delete formErrors.value.status;
};

// Method untuk mendapatkan label status
const getStatusLabel = (status) => {
    const statusMap = {
        'pending': 'Menunggu',
        'approved': 'Disetujui',
        'rejected': 'Ditolak',
        'cancelled': 'Dibatalkan',
        'menunggu persetujuan': 'Menunggu',
        'disetujui': 'Disetujui',
        'ditolak': 'Ditolak',
        'dibatalkan': 'Dibatalkan'
    };
    return statusMap[status] || 'Menunggu';
};

// Method untuk mendapatkan class background status
const getStatusClass = (status) => {
    const statusClassMap = {
        'pending': 'bg-yellow-50 border-yellow-200',
        'approved': 'bg-green-50 border-green-200',
        'rejected': 'bg-red-50 border-red-200',
        'cancelled': 'bg-gray-50 border-gray-200',
        'menunggu persetujuan': 'bg-yellow-50 border-yellow-200',
        'disetujui': 'bg-green-50 border-green-200',
        'ditolak': 'bg-red-50 border-red-200',
        'dibatalkan': 'bg-gray-50 border-gray-200'
    };
    return statusClassMap[status] || 'bg-gray-50 border-gray-200';
};

// Method untuk mendapatkan class text status
const getStatusTextClass = (status) => {
    const textClassMap = {
        'pending': 'text-yellow-800',
        'approved': 'text-green-800',
        'rejected': 'text-red-800',
        'cancelled': 'text-gray-800',
        'menunggu persetujuan': 'text-yellow-800',
        'disetujui': 'text-green-800',
        'ditolak': 'text-red-800',
        'dibatalkan': 'text-gray-800'
    };
    return textClassMap[status] || 'text-gray-800';
};

const formatDate = (dateString) => {
    return formatIndonesianDate(dateString);
};

const formatDateTime = (dateString) => {
    return formatIndonesianDateTime(dateString);
};

// Attachment handling
const attachments = computed(() => {
    return props.submission?.data?.attachments || [];
});


const hasAttachments = computed(() => {
    return attachments.value.length > 0;
});

// Image gallery state
const showImageGallery = ref(false);
const currentImageIndex = ref(0);

const isCurrentAttachmentImage = computed(() => {
    if (attachments.value.length === 0) return false;
    const currentAttachment = attachments.value[currentImageIndex.value];

    console.log(currentAttachment);
    if (currentAttachment?.file_url) {
        return isImage(currentAttachment.file_url);
    }
    return false;
});

function getAttachmentName(attachment) {
    if (attachment && attachment.file_path) {
        return attachment.file_path.split('/').pop().split('?')[0];
    }
    return 'Lampiran';
}

function getImageUrl(attachment) {
    if (attachment && attachment.file_url) {
        return attachment.file_url;
    }
    return '';
}

function getAttachmentDisplayName() {
    if (attachments.value.length === 0) return null;
    if (attachments.value.length === 1) return '1 lampiran';
    return `${attachments.value.length} lampiran`;
}

function openAttachment() {
    if (attachments.value.length > 0) {
        openImageGallery(0);
    } else {
        alert('Tidak ada lampiran tersedia');
    }
}

function openImageGallery(index) {
    console.log(index);
    currentImageIndex.value = index;
    showImageGallery.value = true;
}

function closeImageGallery() {
    showImageGallery.value = false;
}

function nextImage() {
    if (currentImageIndex.value < attachments.value.length - 1) {
        currentImageIndex.value++;
    } else {
        currentImageIndex.value = 0;
    }
}

function previousImage() {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    } else {
        currentImageIndex.value = attachments.value.length - 1;
    }
}

function getFileType(attachment) {
    const fileName = getAttachmentName(attachment).toLowerCase();
    const extension = fileName.split('.').pop();

    const extensionMap = {
        'pdf': 'PDF Document',
        'doc': 'Word Document',
        'docx': 'Word Document',
        'xls': 'Excel Spreadsheet',
        'xlsx': 'Excel Spreadsheet',
        'ppt': 'PowerPoint Presentation',
        'pptx': 'PowerPoint Presentation',
        'txt': 'Text File',
        'zip': 'ZIP Archive',
        'rar': 'RAR Archive',
        'mp4': 'Video File',
        'avi': 'Video File',
        'mov': 'Video File',
        'mp3': 'Audio File',
        'wav': 'Audio File'
    };

    return extensionMap[extension] || `${extension.toUpperCase()} File`;
}

function formatFileSize(bytes) {
    if (!bytes) return '';
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    if (bytes === 0) return '0 Bytes';
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
}


function previewAttachment(attachment) {
    const fileUrl = getImageUrl(attachment);
    if (fileUrl) {
        window.open(fileUrl, '_blank');
    } else {
        alert('URL lampiran tidak tersedia');
    }
}

const isImage = (url) => {
    if (!url) return false;
    // Remove query params if any
    const cleanUrl = url.split('?')[0];
    const extension = cleanUrl.split('.').pop().toLowerCase();
    return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(extension);
};

// Actions
const showConfirmationDialog = () => {
    // Validasi
    formErrors.value = {};
    let isValid = true;

    if (selectedStatus.value === 'pending' || !selectedStatus.value) {
        formErrors.value.status = 'Silakan pilih status baru';
        isValid = false;
    }

    // Require note for rejection
    if ((selectedStatus.value === 'rejected' || selectedStatus.value === 'ditolak') && !keterangan.value) {
        formErrors.value.keterangan = 'Keterangan wajib diisi untuk penolakan';
        isValid = false;
    }

    if (isValid) {
        showConfirmDialog.value = true;
    }
};

const cancelConfirmation = () => {
    showConfirmDialog.value = false;
};

const handleConfirm = () => {
    if (!props.submission?.data?.id) return;

    isProcessing.value = true;

    router.put(
        route("submission.general.update", props.submission.data.id),
        {
            status: selectedStatus.value,
            admin_notes: keterangan.value,
        },
        {
            onSuccess: () => {
                toast.add({
                    severity: "success",
                    summary: "Berhasil",
                    detail: `Pengajuan berhasil ${selectedStatus.value === 'approved' ? 'disetujui' : 'ditolak'}`,
                    life: 3000,
                });
                isProcessing.value = false;
                showConfirmDialog.value = false;
                emit("update-success");
            },
            onError: (errors) => {
                toast.add({
                    severity: "error",
                    summary: "Gagal",
                    detail: "Terjadi kesalahan saat memproses pengajuan",
                    life: 3000,
                });
                isProcessing.value = false;
                showConfirmDialog.value = false;
            },
        }
    );
};
</script>
