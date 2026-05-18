<template>
    <Head title="Data Disposal" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <!-- Breadcrumb & Header -->
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <!-- Filter & Table Card -->
        <div class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]">
            <!-- Table Action Header -->
            <div class="flex flex-col gap-2 px-8 h-16 sm:flex-row sm:items-center sm:justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">
                    Data Barang Cacat / Rusak
                </div>

                <div class="flex gap-3 items-center">
                    <!-- Date Filter Popover -->
                    <div class="relative" data-date-filter>
                        <button
                            @click="toggleDateFilter"
                            type="button"
                            class="flex items-center gap-2 px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-blue-500"
                        >
                            <span>Filter Tanggal</span>
                            <span v-if="filtersLocal.date_from || filtersLocal.date_to" class="w-2 h-2 bg-blue-500 rounded-full"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                <path d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z" />
                            </svg>
                        </button>

                        <div
                            v-if="showDateFilter"
                            class="absolute right-0 z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                            @click.stop
                        >
                            <div class="p-4 space-y-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Awal</label>
                                    <input
                                        v-model="tempDateFrom"
                                        type="date"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Akhir</label>
                                    <input
                                        v-model="tempDateTo"
                                        type="date"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                                <div class="flex gap-2 pt-2">
                                    <button
                                        @click="applyDateFilter"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                                    >
                                        Terapkan
                                    </button>
                                    <button
                                        @click="clearDateFilter"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="relative py-2">
                        <div class="absolute left-4 top-1/2 text-gray-400 -translate-y-1/2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5">
                                <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 4.215 12.06l3.237 3.238a.75.75 0 1 0 1.06-1.061l-3.238-3.237A6.75 6.75 0 0 0 10.5 3.75Zm-5.25 6.75a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            v-model="filtersLocal.q"
                            @input="debouncedSearch"
                            type="text"
                            placeholder="Cari Barang..."
                            class="h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 xl:w-[260px]"
                        />
                    </div>
                </div>
            </div>

            <!-- Table content -->
            <div class="overflow-auto overflow-x-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 50px" />
                        <col style="width: 120px" />
                        <col style="width: 140px" />
                        <col style="width: 260px" />
                        <col style="width: 130px" />
                        <col style="width: 90px" />
                        <col style="width: 90px" />
                        <col style="width: 150px" />
                        <col style="width: 160px" />
                        <col style="width: 220px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">No.</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300 text-center">Tanggal</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300 text-center">Cabang</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300 text-left">Nama Barang</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300 text-center">Kategori</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300 text-center">Jumlah</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300 text-center">Satuan</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300 text-left">Pelapor</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300 text-center">Sumber Asal</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300 text-left">Catatan / Keterangan</div>
                            </th>
                        </tr>
                        <!-- Sub-header Filter Row (Consistent with Sinergi layout) -->
                        <tr>
                            <th class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                            <th class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                            <th class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900">
                                <select
                                    v-model="filtersLocal.branch_id"
                                    @change="applyFilters"
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua Cabang</option>
                                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                        {{ branch.name }}
                                    </option>
                                </select>
                            </th>
                            <th class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900">
                                <input
                                    v-model="filtersLocal.q"
                                    type="text"
                                    placeholder="Cari nama barang..."
                                    @input="debouncedSearch"
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </th>
                            <th class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900">
                                <select
                                    v-model="filtersLocal.category_id"
                                    @change="applyFilters"
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua Kategori</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </th>
                            <th class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                            <th class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                            <th class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                            <th class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                            <th class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!disposals?.data || disposals.data.length === 0">
                            <td colspan="10" class="py-8 text-center text-gray-500">
                                Tidak ada log barang cacat / rusak yang tercatat.
                            </td>
                        </tr>
                        <tr
                            v-else
                            v-for="(row, index) in disposals.data"
                            :key="row.id"
                            class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50/50 dark:hover:bg-white/[0.01]"
                        >
                            <td class="px-3 py-3 text-center text-gray-500">
                                {{ serialNumber(index) }}
                            </td>
                            <td class="px-3 py-3 text-center text-gray-600 dark:text-gray-400">
                                {{ formatDate(row.disposal_date) }}
                            </td>
                            <td class="px-3 py-3 text-center text-gray-600 dark:text-gray-400">
                                {{ row.branch }}
                            </td>
                            <td class="px-3 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ row.item_name }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ row.item_code }}</div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ row.category }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center font-bold text-gray-900 dark:text-white">
                                {{ row.quantity }}
                            </td>
                            <td class="px-3 py-3 text-center text-gray-700 dark:text-gray-300">
                                {{ row.unit }}
                            </td>
                            <td class="px-3 py-3 text-gray-700 dark:text-gray-300">
                                {{ row.reporter }}
                            </td>
                            <td class="px-3 py-3 text-center">
                                <Link
                                    v-if="row.source_type"
                                    :href="row.source_type === 'GoodReceipt' ? route('good-receipts.show', row.source_id) : route('good-transfers.show', row.source_id)"
                                    class="inline-flex px-2.5 py-1 text-xs font-semibold rounded bg-blue-50 hover:bg-blue-100/80 text-blue-700 hover:text-blue-900 border border-blue-200/50 transition-all dark:bg-blue-950/30 dark:hover:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800/40"
                                >
                                    {{ row.source_type === 'GoodReceipt' ? 'Penerimaan Pembelian' : 'Penerimaan Transfer' }}
                                </Link>
                                <span v-else class="text-xs text-gray-400 font-italic">Manual</span>
                            </td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-400 italic">
                                {{ row.description }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <Pagination :pagination="disposals" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/Pagination.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    disposals: Object,
    filters: Object,
    categories: Array,
    branches: Array,
});

// Setup breadcrumbs consistent with Sinergi design
const breadcrumbs = [
    { label: "Penyimpanan", path: "#" },
    { label: "Data Disposal" },
];

const filtersLocal = ref({
    q: props.filters.q || "",
    category_id: props.filters.category_id || "",
    branch_id: props.filters.branch_id || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

// Date Picker Dropdown states
const showDateFilter = ref(false);
const tempDateFrom = ref(props.filters.date_from || "");
const tempDateTo = ref(props.filters.date_to || "");

function toggleDateFilter() {
    showDateFilter.value = !showDateFilter.value;
}

function closeDateFilter(e) {
    if (!e.target.closest("[data-date-filter]")) {
        showDateFilter.value = false;
    }
}

onMounted(() => {
    window.addEventListener("click", closeDateFilter);
});

onBeforeUnmount(() => {
    window.removeEventListener("click", closeDateFilter);
});

// Pagination serial helper
function serialNumber(index) {
    return (props.disposals.current_page - 1) * props.disposals.per_page + index + 1;
}

// Date formatter
function formatDate(dateStr) {
    if (!dateStr) return "-";
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    } catch (e) {
        return dateStr;
    }
}

// Apply date filter
function applyDateFilter() {
    filtersLocal.value.date_from = tempDateFrom.value;
    filtersLocal.value.date_to = tempDateTo.value;
    showDateFilter.value = false;
    applyFilters();
}

// Clear date filter
// Main filter applier
let searchTimeout = null;

function debouncedSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 450);
}

function clearDateFilter() {
    tempDateFrom.value = "";
    tempDateTo.value = "";
    filtersLocal.value.date_from = "";
    filtersLocal.value.date_to = "";
    showDateFilter.value = false;
    applyFilters();
}

function applyFilters() {
    router.get(
        route("item-disposals.index"),
        {
            q: filtersLocal.value.q,
            category_id: filtersLocal.value.category_id,
            branch_id: filtersLocal.value.branch_id,
            date_from: filtersLocal.value.date_from,
            date_to: filtersLocal.value.date_to,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}
</script>

<style scoped>
/* Scoped custom style if needed, consistent with standard tables */
</style>
