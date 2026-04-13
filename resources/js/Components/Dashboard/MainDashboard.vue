<template>
    <div class="space-y-6">
        <!-- Cards Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Jumlah Karyawan Aktif -->
            <div
                class="bg-white rounded-2xl shadow p-6 flex items-center gap-4"
            >
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <Users class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">{{ metrics.active_employees }}</h2>
                    <p class="text-gray-500">Jumlah Karyawan Aktif</p>
                </div>
            </div>

            <!-- Total Barang Stok -->
            <div
                class="bg-white rounded-2xl shadow p-6 flex items-center gap-4"
            >
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <Package class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">{{ metrics.total_items_stock }}</h2>
                    <p class="text-gray-500">Total Barang Stok</p>
                </div>
            </div>

            <!-- Jumlah Barang Menipis -->
            <div
                class="bg-white rounded-2xl shadow p-6 flex items-center gap-4"
            >
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <BarChart3 class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">{{ metrics.low_stock_items }}</h2>
                    <p class="text-gray-500">Jumlah Barang Menipis</p>
                </div>
            </div>

            <!-- Aktivitas Hari ini -->
            <div
                class="bg-white rounded-2xl shadow p-6 flex items-center gap-4"
            >
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <ClipboardList class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">{{ metrics.today_activities }}</h2>
                    <p class="text-gray-500">Aktivitas Hari ini</p>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Grafik Barang -->
            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="font-semibold mb-4">Grafik Barang</h3>
                <apexchart
                    type="bar"
                    height="300"
                    :options="barangOptions"
                    :series="barangSeries"
                />
            </div>

            <!-- Grafik Absensi -->
            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="font-semibold mb-4">Grafik Absensi Karyawan</h3>
                <apexchart
                    type="line"
                    height="300"
                    :options="absensiOptions"
                    :series="absensiSeries"
                />
            </div>
        </div>

        <!-- Aktivitas + Kalender -->
        <div class="space-y-6">
            <!-- Aktivitas -->
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">Aktivitas</h3>
                    <span
                        class="text-sm bg-blue-100 text-blue-600 px-2 py-1 rounded-lg"
                    >
                        {{ recentActivities.length }}
                    </span>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li
                        v-for="activity in displayedActivities"
                        :key="activity.date"
                        class="py-2 flex justify-between text-sm"
                    >
                        <span>{{ activity.description }}</span>
                        <span class="text-gray-500">{{ activity.date }}</span>
                    </li>
                    <li v-if="recentActivities.length === 0" class="py-2 text-sm text-gray-500">
                        Tidak ada aktivitas terbaru
                    </li>
                </ul>
                <div v-if="recentActivities.length > 5" class="mt-4">
                    <button
                        @click="toggleShowAllActivities"
                        class="text-blue-600 text-sm hover:underline"
                    >
                        {{ showAllActivities ? 'Lihat Lebih Sedikit' : 'Lihat Selengkapnya' }}
                    </button>
                </div>
            </div>

            <!-- Kalender -->
            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="font-semibold mb-4">Kalender</h3>
                <FullCalendar :options="calendarOptions" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { Users, Package, BarChart3, ClipboardList } from "lucide-vue-next";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import interactionPlugin from "@fullcalendar/interaction";
import { ref, computed } from "vue";

// Props from backend
const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({
            active_employees: 0,
            total_items_stock: 0,
            low_stock_items: 0,
            today_activities: 0
        })
    },
    barangData: {
        type: Object,
        default: () => ({
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            series: {
                masuk: Array(12).fill(0),
                keluar: Array(12).fill(0)
            }
        })
    },
    absensiData: {
        type: Object,
        default: () => ({
            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            series: {
                total_masuk: Array(12).fill(0)
            }
        })
    },
    recentActivities: {
        type: Array,
        default: () => []
    },
    calendarEvents: {
        type: Array,
        default: () => []
    },
});

// Activities state
const showAllActivities = ref(false);

// Computed properties
const displayedActivities = computed(() => {
    const activities = props.recentActivities || [];
    if (showAllActivities.value) {
        return activities;
    }
    return activities.slice(0, 5);
});

// Methods
const toggleShowAllActivities = () => {
    showAllActivities.value = !showAllActivities.value;
};

// Data Grafik Barang - menggunakan computed agar reactive
const barangOptions = computed(() => ({
    chart: { toolbar: { show: false } },
    xaxis: {
        categories: props.barangData?.categories || [
            "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
            "Jul", "Agu", "Sep", "Okt", "Nov", "Des",
        ],
    },
    legend: { position: "top" },
    dataLabels: { enabled: false },
    tooltip: {
        enabled: true,
        y: { formatter: (val) => `${val}` },
    },
    colors: ["#1B84FF", "#0F4C75"],
}));

const barangSeries = computed(() => [
    {
        name: "Barang Masuk",
        data: props.barangData?.series?.masuk || Array(12).fill(0),
    },
    {
        name: "Barang Keluar",
        data: props.barangData?.series?.keluar || Array(12).fill(0),
    },
]);

// Data Grafik Absensi - menggunakan computed agar reactive
const absensiOptions = computed(() => ({
    chart: { toolbar: { show: false } },
    xaxis: {
        categories: props.absensiData?.categories || [
            "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
            "Jul", "Agu", "Sep", "Okt", "Nov", "Des",
        ],
    },
    stroke: { curve: "smooth", width: 3 },
    dataLabels: { enabled: false },
    colors: ["#1B84FF"],
}));

const absensiSeries = computed(() => [
    {
        name: "Total Karyawan Masuk",
        data: props.absensiData?.series?.total_masuk || Array(12).fill(0),
    },
]);

// Kalender - menggunakan computed agar reactive
const calendarOptions = computed(() => ({
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: "dayGridMonth",
    headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,dayGridWeek,dayGridDay",
    },
    editable: true,
    selectable: true,
    events: props.calendarEvents || [],
    dateClick(info) {
        // Emit event to parent for modal handling
        emit('dateClick', info.dateStr);
    },
}));

// Emits
const emit = defineEmits(['dateClick']);
</script>
