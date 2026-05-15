<script setup>
import { Head, Link } from "@inertiajs/vue3";
import FlashMessage from "@/Components/layout/FlashMessage.vue";
import ThemeProvider from "@/Components/layout/ThemeProvider.vue";
import SidebarProvider from "@/Components/layout/SidebarProvider.vue";
import LayoutShell from "@/Components/layout/LayoutShell.vue";

defineProps({
    shiftWork: Object,
});

// Fungsi format tanggal
const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    });
};

// Fungsi format waktu
const formatTime = (time) => {
    if (!time) return "-";
    return time;
};
</script>

<template>
    <ThemeProvider>
        <SidebarProvider>
            <LayoutShell>
                <FlashMessage />

                <Head title="Detail Shift Work" />

                <div class="p-6">
                    <h1 class="text-3xl font-bold mb-6 text-gray-800">
                        Detail Penugasan Shift
                    </h1>

                    <div class="bg-white shadow-md rounded-lg p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-500 text-sm mb-1">
                                    Nama Pekerja
                                </p>
                                <h2 class="text-lg font-semibold text-gray-800">
                                    {{ shiftWork.user?.name || "-" }}
                                </h2>
                            </div>

                            <div>
                                <p class="text-gray-500 text-sm mb-1">
                                    Departemen
                                </p>
                                <h2 class="text-lg font-semibold text-gray-800">
                                    {{ shiftWork.department?.name || "-" }}
                                </h2>
                            </div>

                            <div>
                                <p class="text-gray-500 text-sm mb-1">Shift</p>
                                <span
                                    class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium"
                                >
                                    {{ shiftWork.shift?.name || "-" }}
                                </span>
                            </div>

                            <div>
                                <p class="text-gray-500 text-sm mb-1">
                                    Jam Kerja
                                </p>
                                <span class="text-gray-700 text-sm">
                                    {{
                                        formatTime(shiftWork.shift?.start_time)
                                    }}
                                    -
                                    {{ formatTime(shiftWork.shift?.end_time) }}
                                </span>
                            </div>

                            <div>
                                <p class="text-gray-500 text-sm mb-1">
                                    Tanggal Kerja
                                </p>
                                <span class="text-gray-700 text-sm">
                                    {{ formatDate(shiftWork.work_date) }}
                                </span>
                            </div>

                            <div>
                                <p class="text-gray-500 text-sm mb-1">
                                    Waktu Ditambahkan
                                </p>
                                <span class="text-gray-700 text-sm">
                                    {{ formatDate(shiftWork.created_at) }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <Link
                                :href="route('shift-works.index')"
                                class="inline-flex items-center px-3 py-2 rounded-md bg-blue-500 text-white"
                            >
                                Kembali
                            </Link>
                        </div>
                    </div>
                </div>
            </LayoutShell>
        </SidebarProvider>
    </ThemeProvider>
</template>
