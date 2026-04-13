<template>

    <Head title="Detail Riwayat Service" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <Link :href="route('vehicle-services.index')"
                class="flex gap-2 items-center px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </Link>
        </div>

        <div
            class="flex flex-col overflow-auto rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    Detail Riwayat Service
                </h1>
            </div>

            <div v-if="vehicleService" class="space-y-8 px-6 py-6">
                <!-- Detail Kendaraan -->
                <div>
                    <h3 class="mb-2 text-base font-semibold text-gray-900 dark:text-white">
                        Detail Kendaraan
                    </h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="flex items-center gap-4">
                            <img class="object-cover w-14 h-14 rounded-full border border-gray-300 dark:border-gray-700"
                                :src="vehicleService.photo
                                    ? `/storage/${vehicleService.photo}`
                                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                        vehicleService.name || ''
                                    )}&background=3b82f6&color=fff`
                                    " alt="Foto Kendaraan" loading="lazy" />
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white text-lg">
                                    {{ vehicleService.name }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ vehicleService.category }}
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col justify-center">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Plat Nomor</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ vehicleService.vehicle?.license_code || '-' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Service -->
                <div>
                    <h3 class="mb-2 text-base font-semibold text-gray-900 dark:text-white">
                        Detail Service
                    </h3>
                    <div class="grid md:grid-cols-3 gap-6">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Tanggal Service</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ formatTime(vehicleService.date) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Jarak Tempuh</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ vehicleService.distance ? `${vehicleService.distance} KM` : '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Biaya Service</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ formatRupiah(vehicleService.cost) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Kategori</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ Array.isArray(vehicleService.category_name) ? vehicleService.category_name.join(', ')
                                    : (vehicleService.category_name || '-') }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Sub Kategori</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ Array.isArray(vehicleService.sub_category_name) ?
                                    vehicleService.sub_category_name.join(', ') : (vehicleService.sub_category_name || '-')
                                }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Service -->
                <div>
                    <h3 class="mb-2 text-base font-semibold text-gray-900 dark:text-white">
                        Catatan Service
                    </h3>
                    <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-800 min-h-[68px]">
                        <div class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                            {{ vehicleService.note || '-' }}
                        </div>
                    </div>
                </div>

                <!-- Lampiran / Bukti Service -->
                <div>
                    <h3 class="mb-2 text-base font-semibold text-gray-900 dark:text-white">
                        Lampiran / Bukti Service
                    </h3>
                    <div v-if="vehicleService.attachments && vehicleService.attachments.length > 0">
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                            <div v-for="(attachment, index) in vehicleService.attachments" :key="attachment.id"
                                class="relative group cursor-pointer"
                                @click="openImageCarousel(vehicleService.attachments, index)">
                                <img :src="getAttachmentUrl(attachment)" :alt="`Attachment ${attachment.id}`"
                                    class="object-cover w-full h-32 rounded-lg border border-gray-300 dark:border-gray-600 hover:opacity-80 transition-opacity"
                                    @error="handleImageError" />
                                <div
                                    class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-30 rounded-lg transition-all">
                                    <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div class="text-gray-500 dark:text-gray-400">Tidak ada lampiran</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Carousel Modal -->
        <Modal :show="isImageCarouselOpen" title="Preview Gambar" maxWidth="4xl" @close="closeImageCarousel">
            <div v-if="carouselImages.length > 0" class="relative">
                <div
                    class="relative w-full h-[500px] bg-gray-100 rounded-lg dark:bg-gray-800 flex items-center justify-center">
                    <img :src="getAttachmentUrl(carouselImages[currentImageIndex])"
                        :alt="`Image ${currentImageIndex + 1}`" class="max-w-full max-h-full object-contain rounded-lg"
                        @error="handleImageError" />
                </div>

                <!-- Navigation -->
                <div class="flex justify-between items-center mt-4">
                    <button @click="prevImage"
                        class="flex items-center gap-2 px-4 py-2 text-white bg-blue-500 rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="currentImageIndex === 0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                        Sebelumnya
                    </button>
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ currentImageIndex + 1 }} / {{ carouselImages.length }}
                    </span>
                    <button @click="nextImage"
                        class="flex items-center gap-2 px-4 py-2 text-white bg-blue-500 rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="currentImageIndex === carouselImages.length - 1">
                        Selanjutnya
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Kendaraan" },
    { label: "Riwayat Service", href: route("vehicle-services.index") },
    { label: "Detail" },
];

const props = defineProps({
    vehicleService: Object,
});

function formatTime(time) {
    const date = new Date(time);
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function formatRupiah(value) {
    if (!value) return "Rp 0";
    return "Rp " + Number(value).toLocaleString("id-ID");
}

// Get attachment URL
function getAttachmentUrl(attachment) {
    if (attachment.url) {
        return attachment.url.startsWith('http') || attachment.url.startsWith('/')
            ? attachment.url
            : `/storage/${attachment.url}`;
    }
    return `/storage/${attachment.file_path}`;
}

// Handle error saat load gambar
function handleImageError(event) {
    event.target.src = '/images/placeholder.png'; // Fallback image
}

// Image carousel state
const isImageCarouselOpen = ref(false);
const carouselImages = ref([]);
const currentImageIndex = ref(0);

// Image carousel functions
function openImageCarousel(attachments, index = 0) {
    carouselImages.value = attachments;
    currentImageIndex.value = index;
    isImageCarouselOpen.value = true;
}

function closeImageCarousel() {
    isImageCarouselOpen.value = false;
    carouselImages.value = [];
    currentImageIndex.value = 0;
}

function prevImage() {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    }
}

function nextImage() {
    if (currentImageIndex.value < carouselImages.value.length - 1) {
        currentImageIndex.value++;
    }
}
</script>
