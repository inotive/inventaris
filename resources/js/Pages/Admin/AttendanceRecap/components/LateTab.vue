<template>
    <div>
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
                    <!-- <th class="p-3 bg-gray-100 border border-gray-200">
                        <div class="font-medium text-left text-gray-600">
                            Total Hari Terlambat
                        </div>
                    </th>
                    <th class="p-3 bg-gray-100 border border-gray-200">
                        <div class="font-medium text-left text-gray-600">
                            Total Menit
                        </div>
                    </th> -->
                    <th class="p-3 bg-gray-100 border border-gray-200">
                        <div class="font-medium text-left text-gray-600">
                            Total Hari Terlambat Dalam Toleransi
                        </div>
                    </th>
                    <th class="p-3 bg-gray-100 border border-gray-200">
                        <div class="font-medium text-left text-gray-600">
                            Total Hari Terlambat Lewat Toleransi
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
                <template v-if="hasRows">
                    <tr
                        v-for="(row, idx) in items"
                        :key="row.id ?? idx"
                        class="border-b border-gray-200"
                    >
                        <td class="p-3 text-center">
                            {{
                                ((rows?.current_page ?? 1) - 1) *
                                    (rows?.per_page ?? items.length) +
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
                            <!-- <div class="flex gap-3 items-center">
                            <div
                                class="flex justify-center items-center w-8 h-8 text-xs font-medium text-white bg-blue-500 rounded-full"
                            >
                                {{
                                    (row.employee?.name || "-")
                                        .split(" ")
                                        .slice(0, 2)
                                        .map((w) => w.charAt(0))
                                        .join("")
                                        .toUpperCase()
                                }}
                            </div>
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-800">{{
                                    row.employee?.name || "-"
                                }}</span>
                                <span class="text-xs text-gray-500">{{
                                    row.department || "-"
                                }}</span>
                            </div>
                        </div> -->
                        </td>
                        <td class="p-3">
                            {{ row.late_within_tolerance_days ?? 0 }} Hari ({{
                                row.late_within_tolerance_minutes ?? 0
                            }}
                            Menit)
                        </td>
                        <td class="p-3">
                            {{ row.late_exceed_tolerance_days ?? 0 }} Hari ({{
                                row.late_exceed_tolerance_minutes ?? 0
                            }}
                            Menit)
                        </td>
                        <td class="p-3">
                            <button
                                @click="showDetail(row)"
                                class="text-blue-600 hover:underline"
                            >
                                Lihat Detail
                            </button>
                        </td>
                    </tr>
                </template>
                <tr v-else>
                    <td colspan="5" class="py-6 text-center text-gray-500">
                        Tidak ada data
                    </td>
                </tr>
            </tbody>
        </table>
        </div>
        <Pagination v-if="hasRows" :pagination="rows" class="border-t" />

        <!-- Modal Detail Keterlambatan -->
        <teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                @click.self="closeModal"
            >
                <div
                    class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden"
                >
                    <!-- Modal Header -->
                    <div
                        class="flex items-center justify-between p-6 border-b border-gray-200"
                    >
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                Detail Keterlambatan
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ selectedEmployee?.employee?.name }}
                            </p>
                        </div>
                        <button
                            @click="closeModal"
                            class="text-gray-400 hover:text-gray-600"
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
                        <div v-if="loading" class="text-center py-8">
                            <div
                                class="inline-block w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"
                            ></div>
                            <p class="mt-2 text-gray-500">Memuat data...</p>
                        </div>

                        <div v-else-if="lateDetails.length === 0" class="text-center py-8">
                            <p class="text-gray-500">Tidak ada data keterlambatan</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="(detail, idx) in lateDetails"
                                :key="detail.id"
                                class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full"
                                                :class="
                                                    detail.status === 'within_tolerance'
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : 'bg-red-100 text-red-800'
                                                "
                                            >
                                                {{ detail.status_label }}
                                            </span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ detail.date_formatted }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Shift:</span>
                                                <span class="ml-2 font-medium text-gray-900">
                                                    {{ detail.shift_name }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Waktu Shift:</span>
                                                <span class="ml-2 font-medium text-gray-900">
                                                    {{ detail.shift_start }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Jam Masuk:</span>
                                                <span class="ml-2 font-medium text-gray-900">
                                                    {{ detail.jam_masuk }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Batas Toleransi:</span>
                                                <span class="ml-2 font-medium text-gray-900">
                                                    {{ detail.late_tolerance }}
                                                </span>
                                            </div>
                                            <div class="col-span-2">
                                                <span class="text-gray-500">Durasi Terlambat:</span>
                                                <span class="ml-2 font-semibold text-red-600">
                                                    {{ detail.late_minutes }} menit
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex justify-end p-6 border-t border-gray-200">
                        <button
                            @click="closeModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import axios from "axios";
import Pagination from "@/Components/common/Pagination.vue";
import AvatarInitials from "@/Components/common/AvatarInitials.vue";

const props = defineProps({ rows: { type: Object, default: null } });
const page = usePage();

const items = computed(() => props.rows?.data ?? []);
const hasRows = computed(
    () => Array.isArray(items.value) && items.value.length > 0
);

// Modal state
const showModal = ref(false);
const selectedEmployee = ref(null);
const lateDetails = ref([]);
const loading = ref(false);

// Get current filters from URL
const currentFilters = computed(() => {
    const url = new URL(window.location.href);
    return {
        year: url.searchParams.get('year') || new Date().getFullYear(),
        month: url.searchParams.get('month') || (new Date().getMonth() + 1),
    };
});

const showDetail = async (row) => {
    selectedEmployee.value = row;
    showModal.value = true;
    loading.value = true;
    lateDetails.value = [];

    try {
        const response = await axios.get(
            `/attendance-recap/late-details/${row.employee.id}`,
            {
                params: {
                    year: currentFilters.value.year,
                    month: currentFilters.value.month,
                },
            }
        );

        if (response.data.success) {
            lateDetails.value = response.data.data;
        }
    } catch (error) {
        console.error("Error fetching late details:", error);
        alert("Gagal memuat detail keterlambatan");
    } finally {
        loading.value = false;
    }
};

const closeModal = () => {
    showModal.value = false;
    selectedEmployee.value = null;
    lateDetails.value = [];
};
</script>
