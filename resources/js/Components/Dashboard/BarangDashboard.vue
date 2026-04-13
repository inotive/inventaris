<template>
    <div class="space-y-6">
        <!-- Top Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Barang Stok -->
            <div
                class="bg-white rounded-2xl shadow p-6 flex items-center gap-4"
            >
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <Package class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">
                        {{ barangMetric?.total_stock || 0 }}
                    </h2>
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
                    <h2 class="text-2xl font-bold">
                        {{ barangMetric?.low_stock_items || 0 }}
                    </h2>
                    <p class="text-gray-500">Jumlah Barang Menipis</p>
                </div>
            </div>

            <!-- Jumlah Barang Habis -->
            <div
                class="bg-white rounded-2xl shadow p-6 flex items-center gap-4"
            >
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <Users class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">
                        {{ barangMetric?.out_of_stock || 0 }}
                    </h2>
                    <p class="text-gray-500">Jumlah Barang Habis</p>
                </div>
            </div>

            <!-- Total Transaksi -->
            <div
                class="bg-white rounded-2xl shadow p-6 flex items-center gap-4"
            >
                <div class="p-3 rounded-xl" style="background-color: #1b84ff1a">
                    <Grid3X3 class="w-6 h-6 text-[#1B84FF]" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold">
                        {{ barangMetric?.total_transactions || 0 }}
                    </h2>
                    <p class="text-gray-500">Total Transaksi Keluar & Masuk</p>
                </div>
            </div>
        </div>

        <!-- Request and Receipt Sections -->
        <!-- <div class="grid grid-cols-1 lg:grid-cols-2 gap-6"> -->
        <!-- Permintaan Barang -->
        <!-- <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="font-semibold mb-4">Permintaan Barang</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Permintaan saat ini</span>
                        <span class="text-lg font-semibold">{{ requestData?.current || 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total permintaan 2026</span>
                        <span class="text-lg font-semibold">{{ requestData?.total_2026 || 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total selesai</span>
                        <span class="text-lg font-semibold">{{ requestData?.completed || 0 }}</span>
                    </div>
                    <div class="border-2 border-dashed border-blue-300 rounded-lg p-4 bg-blue-50">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total permintaan 2024</span>
                            <span class="text-lg font-semibold text-blue-600">{{ requestData?.total_2024 || 0 }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-sm text-red-600 font-medium">↓ 15.0%</span>
                        </div>
                    </div>
                </div>
            </div> -->

        <!-- Penerimaan Barang -->
        <!-- <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="font-semibold mb-4">Penerimaan Barang</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Penerimaan saat ini</span>
                        <span class="text-lg font-semibold">{{ receiptData?.current || 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total penerimaan 2026</span>
                        <span class="text-lg font-semibold">{{ receiptData?.total_2026 || 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Total selesai</span>
                        <span class="text-lg font-semibold">{{ receiptData?.completed || 0 }}</span>
                    </div>
                    <div class="border-2 border-dashed border-blue-300 rounded-lg p-4 bg-blue-50">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total penerimaan 2024</span>
                            <span class="text-lg font-semibold text-blue-600">{{ receiptData?.total_2024 || 0 }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-sm text-green-600 font-medium">↑ 30.0%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

        <!-- Charts Section -->
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

            <!-- Proporsi Barang -->
            <div class="bg-white rounded-2xl shadow p-6">
                <h3 class="font-semibold mb-4">Proporsi Barang</h3>
                <apexchart
                    type="donut"
                    height="300"
                    :options="proportionOptions"
                    :series="proportionSeries"
                />
            </div>
        </div>

        <!-- Most Frequently Used Items -->
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold mb-4">Paling Sering Digunakan</h3>
            <apexchart
                type="bar"
                height="250"
                :options="frequentItemsOptions"
                :series="frequentItemsSeries"
            />
        </div>

        <!-- Low Stock Items Table -->
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex gap-2">
                    <button
                        @click="setActiveFilter('all')"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            activeFilter === 'all'
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-600 hover:bg-gray-100',
                        ]"
                    >
                        Semua Barang
                    </button>
                    <button
                        @click="setActiveFilter('low_stock')"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            activeFilter === 'low_stock'
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-600 hover:bg-gray-100',
                        ]"
                    >
                        Barang Stok Menipis
                    </button>
                    <button
                        @click="setActiveFilter('out_of_stock')"
                        :class="[
                            'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            activeFilter === 'out_of_stock'
                                ? 'bg-blue-600 text-white'
                                : 'text-gray-600 hover:bg-gray-100',
                        ]"
                    >
                        Barang Habis
                    </button>
                </div>
                <div class="flex items-center gap-4">
                    <input
                        type="text"
                        placeholder="Search Items"
                        v-model="searchQuery"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th
                                class="text-left py-3 text-sm font-medium text-gray-500"
                            >
                                No
                            </th>
                            <th
                                class="text-left py-3 text-sm font-medium text-gray-500"
                            >
                                Kode
                            </th>
                            <th
                                class="text-left py-3 text-sm font-medium text-gray-500"
                            >
                                Nama Barang
                            </th>
                            <th
                                class="text-left py-3 text-sm font-medium text-gray-500"
                            >
                                Stok Aktual
                            </th>
                            <th
                                class="text-left py-3 text-sm font-medium text-gray-500"
                            >
                                Stok Minimum
                            </th>
                            <th
                                class="text-left py-3 text-sm font-medium text-gray-500"
                            >
                                Selisih
                            </th>
                            <th
                                class="text-left py-3 text-sm font-medium text-gray-500"
                            >
                                Harga
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="filteredItems.length === 0"
                            class="border-b border-gray-100"
                        >
                            <td
                                colspan="7"
                                class="py-8 text-center text-gray-500"
                            >
                                <div class="flex flex-col items-center gap-2">
                                    <svg
                                        class="w-12 h-12 text-gray-300"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                        ></path>
                                    </svg>
                                    <p class="text-sm">Tidak ada data barang</p>
                                    <p class="text-xs text-gray-400">
                                        {{
                                            !props.lowStockItems ||
                                            props.lowStockItems.length === 0
                                                ? "Data barang belum tersedia"
                                                : "Coba ubah filter atau cari dengan kata kunci lain"
                                        }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                        <tr
                            v-for="(item, index) in paginatedItems"
                            :key="item.id"
                            class="border-b border-gray-100"
                        >
                            <td class="py-3 text-sm text-gray-500">
                                {{ (currentPage - 1) * perPage + index + 1 }}
                            </td>
                            <td class="py-3 text-sm font-mono text-gray-600">
                                {{ item.code || "-" }}
                            </td>
                            <td class="py-3 text-sm font-medium">
                                {{ item.name }}
                            </td>
                            <td class="py-3 text-sm">
                                {{ item.actual_stock }}
                            </td>
                            <td class="py-3 text-sm">
                                {{ item.minimum_stock }}
                            </td>
                            <td class="py-3 text-sm">
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs rounded-full',
                                        item.difference < 0
                                            ? 'bg-red-100 text-red-600'
                                            : 'bg-green-100 text-green-600',
                                    ]"
                                >
                                    {{ item.difference }}
                                </span>
                            </td>
                            <td class="py-3 text-sm text-gray-600">
                                {{
                                    item.price
                                        ? `Rp ${Number(item.price).toLocaleString("id-ID")}`
                                        : "-"
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="filteredItems.length > 0"
                class="flex items-center justify-between mt-4"
            >
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Show</span>
                    <select
                        :value="perPage"
                        @change="changePerPage($event.target.value)"
                        class="px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        <option :value="5">5</option>
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                    <span class="text-sm text-gray-500">per page</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">
                        {{ paginationInfo.start }}-{{ paginationInfo.end }} of
                        {{ paginationInfo.total }}
                    </span>
                    <div class="flex gap-1">
                        <!-- Previous Button -->
                        <button
                            @click="changePage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            :class="[
                                'px-2 py-1 text-sm border border-gray-300 rounded transition-colors',
                                currentPage === 1
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    : 'hover:bg-gray-100 text-gray-700',
                            ]"
                        >
                            ‹
                        </button>

                        <!-- Page Numbers -->
                        <template
                            v-for="(page, index) in getPageNumbers"
                            :key="index"
                        >
                            <button
                                v-if="page !== '...'"
                                @click="changePage(page)"
                                :class="[
                                    'px-2 py-1 text-sm border rounded transition-colors min-w-[32px]',
                                    page === currentPage
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'border-gray-300 hover:bg-gray-100 text-gray-700',
                                ]"
                            >
                                {{ page }}
                            </button>
                            <span
                                v-else
                                class="px-2 py-1 text-sm text-gray-400"
                            >
                                ...
                            </span>
                        </template>

                        <!-- Next Button -->
                        <button
                            @click="changePage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            :class="[
                                'px-2 py-1 text-sm border border-gray-300 rounded transition-colors',
                                currentPage === totalPages
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    : 'hover:bg-gray-100 text-gray-700',
                            ]"
                        >
                            ›
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Package, BarChart3, Users, Grid3X3 } from "lucide-vue-next";
import { ref, computed, watch } from "vue";

// Props
const props = defineProps({
    barangMetrics: Object,
    barangData: Object,
    barangMetric: Object,
    requestData: {
        type: Object,
        default: () => ({}),
    },
    receiptData: {
        type: Object,
        default: () => ({}),
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
});

// Debug: Log props to check data structure (can be removed in production)
if (import.meta.env.DEV) {
    console.log("BarangDashboard Props:", {
        barangMetric: props.barangMetric,
        barangData: props.barangData,
        lowStockItems: props.lowStockItems,
        lowStockItemsLength: props.lowStockItems?.length || 0,
    });
}

// Filter state
const activeFilter = ref("all");
const searchQuery = ref("");

// Pagination state
const currentPage = ref(1);
const perPage = ref(10);

// Computed property for filtered items
const filteredItems = computed(() => {
    if (!props.lowStockItems || !Array.isArray(props.lowStockItems)) {
        return [];
    }

    let items = [...props.lowStockItems];

    // Apply search filter
    if (searchQuery.value && searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        items = items.filter(
            (item) =>
                (item.name && item.name.toLowerCase().includes(query)) ||
                (item.code && item.code.toLowerCase().includes(query)),
        );
    }

    // Apply status filter
    switch (activeFilter.value) {
        case "low_stock":
            items = items.filter((item) => {
                const actualStock = item.actual_stock ?? 0;
                const minimumStock = item.minimum_stock ?? 0;
                return actualStock < minimumStock && actualStock > 0;
            });
            break;
        case "out_of_stock":
            items = items.filter((item) => (item.actual_stock ?? 0) === 0);
            break;
        case "all":
        default:
            // Show all items
            break;
    }

    return items;
});

// Computed property for paginated items
const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredItems.value.slice(start, end);
});

// Computed property for total pages
const totalPages = computed(() => {
    return Math.ceil(filteredItems.value.length / perPage.value);
});

// Computed property for pagination info
const paginationInfo = computed(() => {
    const start = (currentPage.value - 1) * perPage.value + 1;
    const end = Math.min(
        currentPage.value * perPage.value,
        filteredItems.value.length,
    );
    return { start, end, total: filteredItems.value.length };
});

// Watch for filter/search changes to reset to page 1
watch([activeFilter, searchQuery], () => {
    currentPage.value = 1;
});

// Methods
const setActiveFilter = (filter) => {
    activeFilter.value = filter;
    currentPage.value = 1; // Reset to first page when filter changes
};

const changePage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const changePerPage = (newPerPage) => {
    perPage.value = parseInt(newPerPage);
    currentPage.value = 1; // Reset to first page when per page changes
};

// Generate page numbers for pagination
const getPageNumbers = computed(() => {
    const pages = [];
    const total = totalPages.value;
    const current = currentPage.value;

    if (total <= 7) {
        // Show all pages if total pages <= 7
        for (let i = 1; i <= total; i++) {
            pages.push(i);
        }
    } else {
        // Show first page, last page, and pages around current
        if (current <= 3) {
            // Show first 5 pages and last page
            for (let i = 1; i <= 5; i++) {
                pages.push(i);
            }
            pages.push("...");
            pages.push(total);
        } else if (current >= total - 2) {
            // Show first page and last 5 pages
            pages.push(1);
            pages.push("...");
            for (let i = total - 4; i <= total; i++) {
                pages.push(i);
            }
        } else {
            // Show first page, pages around current, and last page
            pages.push(1);
            pages.push("...");
            for (let i = current - 1; i <= current + 1; i++) {
                pages.push(i);
            }
            pages.push("...");
            pages.push(total);
        }
    }

    return pages;
});

// Data Grafik Barang - menggunakan computed agar reactive
const barangOptions = computed(() => ({
    chart: { toolbar: { show: false } },
    xaxis: {
        categories: props.barangData?.categories || [
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
    },
    colors: ["#1B84FF", "#0F4C75"],
    legend: { position: "top" },
    dataLabels: { enabled: false },
    plotOptions: {
        bar: {
            borderRadius: 4,
        },
    },
    yaxis: {
        labels: {
            formatter: function (val) {
                return val;
            },
        },
    },
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

const proportionOptions = computed(() => ({
    chart: { toolbar: { show: false } },
    labels: props.itemProportions?.labels || ["Uncategorized"],
    colors: props.itemProportions?.colors || ["#1B84FF"],
    legend: { position: "bottom" },
    dataLabels: { enabled: true },
    plotOptions: {
        pie: {
            donut: {
                size: "70%",
            },
        },
    },
}));

const proportionSeries = computed(() => props.itemProportions?.data || [0]);

const frequentItemsOptions = computed(() => ({
    chart: {
        toolbar: { show: false },
        type: "bar",
    },
    plotOptions: {
        bar: {
            horizontal: true,
            borderRadius: 4,
        },
    },
    xaxis: {
        categories:
            props.mostFrequentlyUsedItems?.map((item) => item.name) || [],
    },
    colors: ["#1B84FF", "#10B981", "#F59E0B", "#EF4444", "#8B5CF6", "#F59E0B"],
    dataLabels: { enabled: false },
    legend: { show: false },
}));

const frequentItemsSeries = computed(() => [
    {
        name: "Usage",
        data: props.mostFrequentlyUsedItems?.map((item) => item.usage) || [],
    },
]);
</script>
