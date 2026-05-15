<template>
    <Head title="Dashboard" />

    <div class="p-6 space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <p class="text-gray-500">
                Ringkasan aktivitas dan stok penyimpanan
            </p>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex gap-4">
            <button
                @click="setActiveTab('barang')"
                :class="[
                    'px-6 py-3 rounded-lg font-medium transition-colors',
                    activeTab === 'barang'
                        ? 'bg-orange-600 text-white'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                ]"
            >
                Barang
            </button>
        </div>

        <!-- Dashboard Content -->
        <BarangDashboard
            v-if="activeTab === 'barang'"
            :barang-data="barangData"
            :barang-metric="barangMetric"
            :request-data="requestData"
            :receipt-data="receiptData"
            :low-stock-items="lowStockItems"
            :most-frequently-used-items="mostFrequentlyUsedItems"
            :item-proportions="itemProportions"
        />
    </div>

    <!-- Modal untuk menampilkan libur -->
    <div
        v-if="showHolidayModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click="closeModal"
    >
        <div
            class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 max-h-[80vh] overflow-hidden"
            @click.stop
        >
            <!-- Header Modal -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">
                        Libur pada {{ selectedDateFormatted }}
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
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
                            ></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content Modal -->
            <div class="p-6 max-h-96 overflow-y-auto">
                <div v-if="selectedDateHolidays.length > 0">
                    <ul class="space-y-3">
                        <li
                            v-for="holiday in selectedDateHolidays"
                            :key="holiday.title"
                            class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg"
                        >
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span class="text-sm font-medium text-gray-900">{{
                                holiday.title
                            }}</span>
                        </li>
                    </ul>
                </div>
                <div v-else class="text-center py-8">
                    <div
                        class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center"
                    >
                        <svg
                            class="w-8 h-8 text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            ></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">
                        Tidak ada hari libur pada tanggal ini
                    </p>
                </div>
            </div>

            <!-- Footer Modal -->
            <div class="p-6 border-t border-gray-200">
                <button
                    @click="closeModal"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import MainDashboard from "@/Components/Dashboard/MainDashboard.vue";
import KaryawanDashboard from "@/Components/Dashboard/KaryawanDashboard.vue";
import BarangDashboard from "@/Components/Dashboard/BarangDashboard.vue";

defineOptions({
    layout: AppLayout,
});

// Props from backend
const props = defineProps({
    // Main dashboard props
    metrics: {
        type: Object,
        default: () => ({
            active_employees: 0,
            total_items_stock: 0,
            low_stock_items: 0,
            today_activities: 0,
        }),
    },
    barangData: {
        type: Object,
        default: () => ({
            categories: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Agu",
                "Sep",
                "Okt",
                "Nov",
                "Des",
            ],
            series: {
                masuk: Array(12).fill(0),
                keluar: Array(12).fill(0),
            },
        }),
    },
    absensiData: {
        type: Object,
        default: () => ({
            categories: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Agu",
                "Sep",
                "Okt",
                "Nov",
                "Des",
            ],
            series: {
                total_masuk: Array(12).fill(0),
            },
        }),
    },
    recentActivities: {
        type: Array,
        default: () => [],
    },
    calendarEvents: {
        type: Array,
        default: () => [],
    },
    // Barang dashboard props
    barangMetric: {
        type: Object,
        default: () => ({
            total_stock: 0,
            low_stock_items: 0,
            out_of_stock: 0,
            total_transactions: 0,
        }),
    },
    requestData: {
        type: Object,
        default: () => ({
            current: 0,
            total_2026: 0,
            completed: 0,
            total_2024: 0,
        }),
    },
    receiptData: {
        type: Object,
        default: () => ({
            current: 0,
            total_2026: 0,
            completed: 0,
            total_2024: 0,
        }),
    },
    lowStockItems: {
        type: Array,
        default: () => [],
    },
    mostFrequentlyUsedItems: {
        type: Array,
        default: () => [],
    },
    itemProportions: {
        type: Object,
        default: () => ({
            labels: ["Uncategorized"],
            data: [0],
            colors: ["#1B84FF"],
        }),
    },
    // Karyawan dashboard props
    karyawanMetrics: {
        type: Object,
        default: () => ({
            total_karyawan: 0,
            active_karyawan: 0,
            late_karyawan: 0,
            on_leave: 0,
            performance: "0%",
        }),
    },
    karyawanTodayActivities: {
        type: Array,
        default: () => [],
    },
    exemplaryEmployees: {
        type: Array,
        default: () => [],
    },
    topPerformers: {
        type: Array,
        default: () => [],
    },
    upcomingBirthdays: {
        type: Array,
        default: () => [],
    },
    employeeProportionData: {
        type: Object,
        default: () => ({
            categories: [
                "Balikpapan",
                "Samarinda",
                "Kantor Pusat",
                "Bontang",
                "IKN",
            ],
            data: [0, 0, 0, 0, 0],
        }),
    },
    genderProportionData: {
        type: Array,
        default: () => [0, 0],
    },
    employeeGrowthData: {
        type: Object,
        default: () => ({
            categories: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Agu",
                "Sep",
                "Okt",
                "Nov",
                "Des",
            ],
            data: Array(12).fill(0),
        }),
    },
    branches: {
        type: Array,
        default: () => [],
    },
    departments: {
        type: Array,
        default: () => [],
    },
});

// Tab state
const activeTab = ref("barang");

// Modal state
const showHolidayModal = ref(false);
const selectedDate = ref(null);

// Computed properties
const selectedDateFormatted = computed(() => {
    if (!selectedDate.value) return "";
    return new Date(selectedDate.value).toLocaleDateString("id-ID", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
});

const selectedDateHolidays = computed(() => {
    if (!selectedDate.value) return [];
    const selectedDateStr = new Date(selectedDate.value)
        .toISOString()
        .split("T")[0];
    return (
        props.calendarEvents?.filter(
            (event) => event.date === selectedDateStr,
        ) || []
    );
});

// Methods
const setActiveTab = (tab) => {
    activeTab.value = tab;
};

const handleDateClick = (date) => {
    selectedDate.value = date;
    showHolidayModal.value = true;
};

const openModal = (date) => {
    selectedDate.value = date;
    showHolidayModal.value = true;
};

const closeModal = () => {
    showHolidayModal.value = false;
    selectedDate.value = null;
};
</script>
