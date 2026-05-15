<template>
    <Head title="Purchase Order" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div>
                <Link
                    :href="route('purchase-orders.create')"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white rounded-md bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                    Buat PO
                </Link>
            </div>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col gap-2 px-8 h-16 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Daftar Purchase Order
                </div>
                <div class="flex gap-3 items-center">
                    <div>
                        <select
                            v-model="groupByLocal"
                            class="px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                        >
                            <option value="">Kelompokan Berdasarkan</option>
                            <option value="vendor">Group: Vendor</option>
                            <option value="date">Group: Tanggal</option>
                            <option value="status">Group: Status</option>
                        </select>
                    </div>
                    <div class="relative py-2">
                        <div
                            class="absolute left-4 top-1/2 text-gray-400 -translate-y-1/2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-5"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10.5 3.75a6.75 6.75 0 1 0 4.215 12.06l3.237 3.238a.75.75 0 1 0 1.06-1.061l-3.238-3.237A6.75 6.75 0 0 0 10.5 3.75Zm-5.25 6.75a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="filtersLocal.q"
                            type="text"
                            placeholder="Cari nomor / vendor"
                            class="h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-400 dark:focus:border-blue-800 xl:w-[240px]"
                        />
                    </div>
                    <div>
                        <Link
                            :href="route('purchase-orders.create')"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-blue-500 px-4 py-2 text-sm font-medium text-white-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-4"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            Buat Permintaan
                        </Link>
                    </div>
                </div>
            </div>

            <div class="overflow-auto overflow-x-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 70px" />
                        <col style="width: 180px" />
                        <col style="width: 180px" />
                        <col style="width: 260px" />
                        <col style="width: 220px" />
                        <col style="width: 160px" />
                        <col style="width: 140px" />
                        <col style="width: 140px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    No.
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    No. PO
                                </div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">Referensi (PR)</div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button @click="changeSort('vendor')" class="px-3 w-full font-medium text-left text-gray-600 dark:text-gray-300">
                                    Vendor
                                    <SortIcon :active="sortBy === 'vendor'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button @click="changeSort('date')" class="px-3 w-full font-medium text-left text-gray-600 dark:text-gray-300">
                                    Tanggal
                                    <SortIcon :active="sortBy === 'date'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button @click="changeSort('total')" class="px-3 w-full font-medium text-left text-gray-600 dark:text-gray-300">
                                    Total
                                    <SortIcon :active="sortBy === 'total'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button
                                    @click="changeSort('status')"
                                    class="px-3 w-full font-medium text-left text-gray-600 dark:text-gray-300"
                                >
                                    Status
                                    <SortIcon
                                        :active="sortBy === 'status'"
                                        :direction="sortDirection"
                                    />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Aksi
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <input
                                    v-model="filtersLocal.q"
                                    type="text"
                                    placeholder="Cari nomor / vendor"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </th>
                            <th class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"></th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filtersLocal.vendor_id"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option :value="null">Semua</option>
                                    <option v-for="v in vendors" :key="v.id" :value="v.id">{{ v.name }}</option>
                                </select>
                            </th>
                            <th class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"></th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <div class="grid grid-cols-2 gap-2">
                                    <input
                                        v-model="filtersLocal.date_from"
                                        type="date"
                                        class="px-2 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                    <input
                                        v-model="filtersLocal.date_to"
                                        type="date"
                                        class="px-2 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                </div>
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filtersLocal.status"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua</option>
                                    <option v-for="s in statusOptions" :key="s" :value="s">{{ statusLabel(s) }}</option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!purchaseOrders.data || purchaseOrders.data.length === 0">
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                Tidak ada data
                            </td>
                        </tr>

                        <!-- Grouped rendering -->
                        <template v-if="isGrouped">
                            <template
                                v-for="(rows, gkey) in groupedData"
                                :key="gkey"
                            >
                                <tr>
                                    <td
                                        :colspan="7"
                                        class="px-4 py-2 font-semibold text-left text-gray-600 bg-gray-50 dark:bg-gray-900/40 dark:text-gray-300"
                                    >
                                        <button
                                            class="inline-flex gap-2 items-center"
                                            @click="toggleGroupCollapse(gkey)"
                                        >
                                            <span class="text-xs">{{
                                                isCollapsed(gkey) ? "▶" : "▼"
                                            }}</span>
                                            <span>
                                                {{ groupLabel(gkey) }}
                                            </span>
                                            <span
                                                class="ml-1 text-xs font-medium text-gray-500"
                                                >({{ rows.length }})</span
                                            >
                                        </button>
                                    </td>
                                </tr>
                                <template v-if="!isCollapsed(gkey)">
                                    <tr
                                        v-for="row in rows"
                                        :key="row.id"
                                        class="border-b border-gray-200 dark:border-gray-700"
                                    >
                                        <td class="px-3 py-3">
                                            {{ serialMap[row.id] ?? "-" }}
                                        </td>
                                        <td class="px-3 py-3 font-mono">{{ row.number }}</td>
                                        <td class="px-3 py-3">{{ row.from_pr?.number || '-' }}</td>
                                        <td class="px-3 py-3">
                                            {{ row.vendor?.name || '-' }}
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ row.date }}
                                        </td>
                                        <td class="px-3 py-3">{{ formatCurrency(row.total) }}</td>
                                        <td class="px-3 py-3">
                                            <span
                                                :class="badgeClass(row.status)"
                                                class="px-2.5 py-1 text-xs font-medium rounded-full"
                                                >{{
                                                    statusLabel(row.status)
                                                }}</span
                                            >
                                        </td>
                                        <td class="px-3 py-3">
                                            <div
                                                class="flex gap-2 items-center"
                                            >
                                                <Link :href="route('purchase-orders.show', row.id)"
                                                    class="text-primary-600 hover:underline"
                                                    >Lihat</Link
                                                >
                                                <Link :href="route('purchase-orders.edit', row.id)"
                                                    class="inline-flex items-center px-2 py-1 text-xs text-gray-700 rounded border border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                                    >Edit</Link
                                                >
                                                <Link :href="route('purchase-orders.print', row.id)" class="inline-flex items-center px-2 py-1 text-xs text-gray-700 rounded border border-gray-300 hover:bg-gray-50">Cetak</Link>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </template>

                        <!-- Flat rendering when no groupBy -->
                        <template v-else>
                            <tr
                                v-for="row in purchaseOrders.data"
                                :key="row.id"
                                class="border-b border-gray-200 dark:border-gray-700"
                            >
                                <td class="px-3 py-3">
                                    {{ serialMap[row.id] ?? "-" }}
                                </td>
                                <td class="px-3 py-3 font-mono">{{ row.number }}</td>
                                <td class="px-3 py-3">{{ row.from_pr?.number || '-' }}</td>
                                <td class="px-3 py-3">{{ row.vendor?.name || '-' }}</td>
                                <td class="px-3 py-3">{{ row.date }}</td>
                                <td class="px-3 py-3">{{ formatCurrency(row.total) }}</td>
                                <td class="px-3 py-3">
                                    <span
                                        :class="badgeClass(row.status)"
                                        class="px-2.5 py-1 text-xs font-medium rounded-full"
                                        >{{ statusLabel(row.status) }}</span
                                    >
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex gap-2 items-center">
                                        <Link :href="route('purchase-orders.show', row.id)"
                                            class="text-primary-600 hover:underline"
                                            >Lihat</Link
                                        >
                                        <Link :href="route('purchase-orders.edit', row.id)"
                                            class="inline-flex items-center px-2 py-1 text-xs text-gray-700 rounded border border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300"
                                            >Edit</Link
                                        >
                                        <Link :href="route('purchase-orders.print', row.id)" class="inline-flex items-center px-2 py-1 text-xs text-gray-700 rounded border border-gray-300 hover:bg-gray-50">Cetak</Link>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center px-8 py-3">
                <div class="text-xs text-gray-500">
                    Total: {{ purchaseOrders.total }}
                </div>
                <Pagination :pagination="purchaseOrders" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import Pagination from "@/Components/common/Pagination.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    purchaseOrders: { type: Object, required: true },
    vendors: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    sortBy: { type: String, default: "date" },
    sortDirection: { type: String, default: "desc" },
    groupBy: { type: String, default: null },
});

const breadcrumbs = [
    { label: "Dashboard", href: route("dashboard") },
    { label: "Purchase Order" },
];

defineOptions({
    layout: AppLayout,
});

const filtersLocal = ref({
    q: props.filters.q ?? "",
    vendor_id: props.filters.vendor_id ?? null,
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
    status: props.filters.status ?? "",
});

const groupByLocal = ref(props.groupBy ?? "");
const effectiveGroup = computed(
    () => groupByLocal.value || (props.groupBy ?? "")
);
const isGrouped = computed(() => !!effectiveGroup.value);

// Serial number map to support grouped view
const serialMap = computed(() => {
    const map = {};
    const base =
        ((props.purchaseOrders.current_page || 1) - 1) *
        (props.purchaseOrders.per_page || 20);
    const arr = props.purchaseOrders.data || [];
    arr.forEach((row, idx) => {
        map[row.id] = base + idx + 1;
    });
    return map;
});

// Grouping helpers
const collapsedGroups = ref(new Set());
const groupedData = computed(() => {
    if (!isGrouped.value) return {};
    const data = props.purchaseOrders.data || [];
    const groups = {};
    for (const row of data) {
        const k = keyOf(row);
        if (!groups[k]) groups[k] = [];
        groups[k].push(row);
    }
    return groups;
});

function keyOf(row) {
    switch (effectiveGroup.value) {
        case "vendor":
            return row.vendor?.name ?? "Tanpa Vendor";
        case "date":
            return row.date ?? "-";
        case "status":
            return statusLabel(row.status);
        default:
            return "ungrouped";
    }
}

function groupLabel(key) {
    switch (effectiveGroup.value) {
        case "vendor":
            return `Vendor: ${key}`;
        case "date":
            return `Tanggal: ${key}`;
        case "status":
            return `Status: ${key}`;
        default:
            return key;
    }
}

function isCollapsed(key) {
    return collapsedGroups.value.has(key);
}

function toggleGroupCollapse(key) {
    const s = new Set(collapsedGroups.value);
    if (s.has(key)) s.delete(key);
    else s.add(key);
    collapsedGroups.value = s;
}

function fetchRequests({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    router.get(
        route("purchase-orders.index"),
        {
            ...filtersLocal.value,
            groupBy: effectiveGroup.value || null,
            sortBy,
            sortDirection,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onStart: () => {
                if (typeof window !== "undefined")
                    window.__suppressProgress = true;
            },
            onFinish: () => {
                if (typeof window !== "undefined")
                    window.__suppressProgress = false;
            },
        }
    );
}

let debounceTimer;
watch(
    () => filtersLocal.value.q,
    () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchRequests({}), 400);
    }
);
watch(
    () => [
        filtersLocal.value.vendor_id,
        filtersLocal.value.status,
        filtersLocal.value.date_from,
        filtersLocal.value.date_to,
    ],
    () => {
        fetchRequests({});
    }
);

watch(groupByLocal, () => {
    fetchRequests({});
});
watch(
    () => props.groupBy,
    (val) => {
        // Sync local state if navigation updates prop (e.g., back/forward)
        if (!groupByLocal.value && val) groupByLocal.value = val;
    }
);

function changeSort(column) {
    const nextDir =
        props.sortBy === column && props.sortDirection === "asc"
            ? "desc"
            : "asc";
    fetchRequests({ sortBy: column, sortDirection: nextDir });
}

function statusLabel(s) {
    const map = {
        draft: 'Draft',
        sent: 'Terkirim',
        approved: 'Disetujui',
        received_partial: 'Diterima Sebagian',
        received_full: 'Diterima Penuh',
        canceled: 'Dibatalkan',
    };
    return map[s] ?? s;
}

function badgeClass(s) {
    const map = {
        draft: 'bg-slate-100 text-slate-700',
        sent: 'bg-blue-100 text-blue-700',
        approved: 'bg-emerald-100 text-emerald-800',
        received_partial: 'bg-amber-100 text-amber-700',
        received_full: 'bg-emerald-200 text-emerald-800',
        canceled: 'bg-rose-100 text-rose-700',
    };
    return map[s] ?? 'bg-gray-100 text-gray-700';
}

function formatCurrency(n){
    try { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n || 0); } catch(e){ return n; }
}

function onDelete(row) {
    if (!row?.id) return;
    if (!confirm(`Hapus PO ${row.number}?`)) return;
    router.delete(route("purchase-orders.destroy", row.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh current list
            fetchRequests({});
        },
    });
}
</script>

<script>
export default {
    components: {
        SortIcon: {
            props: { active: Boolean, direction: String },
            template: `
                <span class="inline-block ml-2 text-xs">
                    <template v-if="active">
                        {{ direction === 'asc' ? '▲' : '▼' }}
                    </template>
                    <template v-else>
                        ⇅
                    </template>
                </span>
            `,
        },
    },
};
</script>
