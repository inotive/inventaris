<template>
    <Head title="Pembelian Barang" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
            <Link
                v-if="canCreate"
                :href="route('purchase-orders.create')"
                class="inline-flex items-center gap-2 rounded-md border hover:border-gray-300 bg-blue-500 px-3 py-2 text-sm font-medium text-white hover:text-blue-500 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            >
                <PlusSquareIcon />
                <span class="hidden md:block">Buat Pembelian</span>
            </Link>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex flex-col h-16 gap-2 px-8 sm:flex-row sm:items-center sm:justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">
                    Daftar Pembelian Barang
                </div>
                <div class="flex items-center gap-3">
                    <div>
                        <select v-model="groupByLocal"
                            class="h-10 px-3 text-sm text-gray-800 bg-transparent border border-gray-200 rounded-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <option value="all">Kelompokan Berdasarkan</option>
                            <option value="vendor">Group: Vendor</option>
                            <option value="ordered_by">
                                Group: Dibuat oleh
                            </option>
                            <option value="ordered_at">Group: Tanggal</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-auto overflow-x-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 30px" />
                        <col style="width: 160px" />
                        <col style="width: 200px" />
                        <col style="width: 220px" />
                        <col style="width: 160px" />
                        <col style="width: 140px" />
                        <col style="width: 140px" />
                        <col style="width: 150px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">
                                    No.
                                </div>
                            </th>
                            <th
                                class="py-2.5 whitespace-nowrap border-y border-gray-200 bg-gray-100 dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('order_no')"
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300">
                                    No. Pemesanan
                                    <SortIcon :active="sortBy === 'order_no'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('request_no')"
                                    class="px-3 font-medium text-gray-600 whitespace-nowrap dark:text-gray-300">
                                    No. Referensi (PR)
                                    <SortIcon :active="sortBy === 'request_no'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('vendor')"
                                    class="w-full px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Vendor
                                    <SortIcon :active="sortBy === 'vendor'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('status_invoice')"
                                    class="w-full px-3 font-medium text-left text-gray-600 dark:text-gray-300">
                                    Status Pembayaran
                                    <SortIcon :active="sortBy === 'status_invoice'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('status_delivered')"
                                    class="w-full px-3 font-medium text-left text-gray-600 dark:text-gray-300">
                                    Status Pengiriman
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('ordered_by')"
                                    class="w-full px-3 font-medium text-left text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    Dibuat oleh
                                    <SortIcon :active="sortBy === 'ordered_by'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('ordered_at')"
                                    class="w-full px-3 font-medium text-left text-gray-600 dark:text-gray-300">
                                    Tanggal Pesan
                                    <SortIcon :active="sortBy === 'ordered_at'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Cabang
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Aksi
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="4"
                                class="p-2 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <div class="relative space-x-4">
                                    <input v-model="filtersLocal.q" type="text"
                                        placeholder="Cari nomor request / nama pemohon"
                                        class="rounded-lg border border-gray-200 bg-transparent py-2.5 pl-4 pr-32 text-sm text-gray-800 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20" />
                                    <span class="absolute text-gray-400 -translate-y-1/2 right-36 top-1/2">🔍</span>
                                </div>
                            </th>

                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900">
                                <Select v-model="filtersLocal.status_invoice" :items="[
                                    { id: null, name: 'Semua Status' },
                                    { id: 'lunas', name: 'Lunas' },
                                    { id: 'belum_lunas', name: 'Belum Lunas' }
                                ]" label="Pilih Status" class="w-full text-xs sm:text-sm" />
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900">
                                <Select v-model="filtersLocal.status_delivered" :items="[
                                    { id: null, name: 'Semua Status' },
                                    { id: 'delivered', name: 'Diterima' },
                                    { id: 'partial', name: 'Diterima Sebagian' },
                                    { id: 'pending', name: 'Belum Diterima' }
                                ]" label="Pilih Status Pengiriman" class="w-full text-xs sm:text-sm" />
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900">
                                <Select v-model="filtersLocal.employee_id" :items="[
                                    { id: null, name: 'Semua' },
                                    ...employees
                                ]" label="Pilih Pemohon" class="w-full text-xs sm:text-sm" />
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900">
                                <div class="grid grid-cols-2 gap-2 w-72">
                                    <input v-model="filtersLocal.date_from" type="date"
                                        class="w-full h-10 px-2 text-xs border-gray-300 rounded sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                                    <input v-model="filtersLocal.date_to" type="date"
                                        class="w-full h-10 px-2 text-xs border-gray-300 rounded sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                                </div>
                            </th>
                            <th
                                class="px-2 py-2 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                            </th>
                            <th
                                class="px-2 py-2 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <button @click="clearAllFilters"
                                    class="px-3 py-2 text-xs text-white bg-red-500 rounded hover:bg-red-600">
                                    Clear
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!purchaseOrders.data || purchaseOrders.data.length === 0">
                            <td colspan="9" class="py-8 text-center text-gray-500">
                                Tidak ada data pembelian.
                            </td>
                        </tr>

                        <!-- Grouped rendering -->
                        <template v-if="isGrouped">
                            <template v-for="(rows, gkey) in groupedData" :key="gkey">
                                <tr v-if="effectiveGroup !== 'all'">
                                    <td :colspan="9"
                                        class="px-4 py-2 font-semibold text-left text-gray-600 bg-gray-50 dark:bg-gray-900/40 dark:text-gray-300">
                                        <button class="inline-flex items-center gap-2"
                                            @click="toggleGroupCollapse(gkey)">
                                            <span class="text-xs">{{ isCollapsed(gkey) ? '▶' : '▼' }}</span>
                                            <span>{{ groupLabel(gkey) }}</span>
                                            <span class="ml-1 text-xs font-medium text-gray-500">({{ rows.length }})</span>
                                        </button>
                                    </td>
                                </tr>
                                <template v-if="!isCollapsed(gkey)">
                                    <tr v-for="row in rows" :key="row.id"
                                        class="border-b border-gray-200 dark:border-gray-700">
                                        <td class="px-3 py-3 text-center">
                                            {{ serialMap[row.id] ?? "-" }}.
                                        </td>
                                        <td class="px-3 py-3 font-mono text-center whitespace-nowrap">
                                            {{ row.order_no }}
                                        </td>
                                        <td class="px-3 py-3 font-mono text-center whitespace-nowrap">
                                            {{ row.pr_no ?? "-" }}
                                        </td>

                                        <td class="px-3 py-3">
                                            {{ row.vendor }}
                                        </td>
                                        <td class="px-3 py-3">
                                            <span
                                                :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${badgeClass(row.status_invoice)}`">
                                                {{ statusLabel(row.status_invoice) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span
                                                :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${badgeClassDelivered(row.status_delivered)}`">
                                                {{ statusDeliveredLabel(row.status_delivered) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ row.ordered_by }}
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            {{ row.ordered_at }}
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ row.branch ?? "-" }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="flex items-center gap-2">
                                                <Link v-if="canView" :href="route('purchase-orders.show', row.id)">
                                                    <ShowIcon class="text-blue-400" />
                                                </Link>
                                                <Link v-if="canUpdate && row.status_invoice !== 'lunas' && row.status_delivered !== 'delivered'" :href="route('purchase-orders.edit', row.id)">
                                                    <EditIcon class="text-yellow-500" />
                                                </Link>
                                                <button v-if="canDelete && row.status_invoice !== 'lunas' && row.status_delivered !== 'delivered'" type="button" @click="onDelete(row)">
                                                    <TrashIcon class="text-red-500" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </template>

                        <!-- Flat rendering when no groupBy -->
                        <template v-else>
                            <tr v-for="row in purchaseOrders.data" :key="row.id"
                                class="border-b border-gray-200 dark:border-gray-700">
                                <td class="px-3 py-3 text-center">
                                    {{ serialMap[row.id] ?? "-" }}.
                                </td>
                                <td class="px-3 py-3 font-mono text-center whitespace-nowrap">
                                    {{ row.order_no }}
                                </td>
                                <td class="px-3 py-3 font-mono text-center whitespace-nowrap">
                                    {{ row.pr_no ?? "-" }}
                                </td>
                                <td class="px-3 py-3">
                                    {{ row.vendor }}
                                </td>
                                <td class="px-3 py-3">
                                    <span
                                        :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${badgeClass(row.status_invoice)}`">
                                        {{ statusLabel(row.status_invoice) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    <span
                                        :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${badgeClassDelivered(row.status_delivered)}`">
                                        {{ statusDeliveredLabel(row.status_delivered) }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    {{ row.ordered_by }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    {{ row.ordered_at }}
                                </td>
                                <td class="px-3 py-3">
                                    {{ row.branch ?? "-" }}
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <Link v-if="canView" :href="route('purchase-orders.show', row.id)">
                                            <ShowIcon class="text-blue-400" />
                                        </Link>
                                        <Link v-if="canUpdate && row.status_invoice !== 'lunas' && row.status_delivered !== 'delivered'" :href="route('purchase-orders.edit', row.id)">
                                            <EditIcon class="text-yellow-500" />
                                        </Link>
                                        <button v-if="canDelete && row.status_invoice !== 'lunas' && row.status_delivered !== 'delivered'" type="button" @click="onDelete(row)">
                                            <TrashIcon class="text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <Pagination :pagination="purchaseOrders" @page-change="changePage" @per-page-change="perPageChanged"
                    class="border-t" />
            </div>

            <ConfirmModal :show="isConfirmModalOpen" :question="`Yakin ingin menghapus purchase order`"
                :selected="`${selectedItem?.order_no || 'ini'}`" title="Hapus Purchase Order" confirmText="Ya, Hapus!"
                maxWidth="md" @close="closeConfirmModal" @confirm="destroyData" />
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/Pagination.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import Select from "@/Components/form/SelectPemakaian.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [{ label: "Penyimpanan" }, { label: "Pembelian Barang" }];

const props = defineProps({
    purchaseOrders: { type: Object, required: true },
    employees: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    sortBy: { type: String, default: "ordered_at" },
    sortDirection: { type: String, default: "desc" },
    groupBy: { type: String, default: null },
    canUpdate: { type: Boolean, default: false },
    canView: { type: Boolean, default: false },
    canDelete: { type: Boolean, default: false },
    canCreate: { type: Boolean, default: false },
});

const filtersLocal = ref({
    q: props.filters.q ?? "",
    employee_id: props.filters.employee_id ?? null,
    status_invoice: props.filters.status_invoice ?? 'belum_lunas',
    status_delivered: props.filters.status_delivered ?? null,
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
});

const groupByLocal = ref(props.groupBy ?? "all");
watch(groupByLocal, () => {
    clearFilters();
});

const effectiveGroup = computed(
    () => groupByLocal.value || (props.groupBy ?? "all")
);
const isGrouped = computed(() => !!effectiveGroup.value);

// Confirmation modal state
const isConfirmModalOpen = ref(false);
const selectedItem = ref(null);

// Serial number map
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
            return row.vendor ?? "Tanpa Pemesan";
        case "ordered_by":
            return row.ordered_by ?? "Tanpa Pemesan";
        case "ordered_at":
            return row.ordered_at ?? "-";
        default:
            return "ungrouped";
    }
}

function groupLabel(key) {
    switch (effectiveGroup.value) {
        case "vendor":
            return `Vendor oleh: ${key}`;
        case "ordered_by":
            return `Diminta oleh: ${key}`;
        case "ordered_at":
            return `Tanggal: ${key}`;
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

function fetchOrders({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
    page = null,
    perPage = null,
} = {}) {
    const params = {
        ...filtersLocal.value,
        groupBy: effectiveGroup.value || null,
        sortBy,
        sortDirection,
    };

    if (page !== null) params.page = page;
    if (perPage !== null) params.per_page = perPage;

    router.get(
        route("purchase-orders.index"),
        params,
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
        debounceTimer = setTimeout(() => fetchOrders({}), 400);
    }
);
watch(
    () => [
        filtersLocal.value.employee_id,
        filtersLocal.value.status_invoice ?? 'belum_lunas',
        filtersLocal.value.status_delivered,
        filtersLocal.value.date_from,
        filtersLocal.value.date_to,
    ],
    () => {
        fetchOrders({});
    }
);
watch(groupByLocal, () => {
    fetchOrders({});
});
watch(
    () => props.groupBy,
    (val) => {
        if (!groupByLocal.value && val) groupByLocal.value = val;
    }
);

function changeSort(column) {
    const nextDir =
        props.sortBy === column && props.sortDirection === "asc"
            ? "desc"
            : "asc";
    fetchOrders({ sortBy: column, sortDirection: nextDir });
}

function clearFilters() {
    filtersLocal.value = {
        q: "",
        employee_id: null,
        status_invoice: 'belum_lunas',
        status_delivered: null,
        date_from: "",
        date_to: "",
    };
    fetchOrders({});
}

function clearAllFilters() {
    filtersLocal.value = {
        q: "",
        employee_id: null,
        status_invoice: 'belum_lunas',
        status_delivered: null,
        date_from: "",
        date_to: "",
    };
    groupByLocal.value = "";
    fetchOrders({});
}

function onDelete(row) {
    if (!row?.id) return;
    selectedItem.value = row;
    isConfirmModalOpen.value = true;
}

function closeConfirmModal() {
    isConfirmModalOpen.value = false;
    selectedItem.value = null;
}

function destroyData() {
    if (!selectedItem.value?.id) return;
    router.delete(route("purchase-orders.destroy", selectedItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeConfirmModal();
            fetchOrders({});
        },
    });
}

function changePage(page) {
    fetchOrders({ page });
}

function perPageChanged(perPage) {
    fetchOrders({ perPage });
}

function statusLabel(s) {
    const map = {
        belum_dibayar: 'Belum Dibayar',
        lunas: 'Lunas',
        belum_lunas: 'Belum Lunas',
    }
    return map[s] ?? 'Belum Dibayar'
}

function statusDeliveredLabel(s) {
    const map = {
        delivered: 'Diterima',
        partial: 'Diterima Sebagian',
        pending: 'Belum Diterima',
    }
    return map[s] ?? s
}

function badgeClass(s) {
    const map = {
        belum_dibayar: 'bg-gray-100 text-gray-800',
        lunas: 'bg-green-100 text-green-800',
        belum_lunas: 'bg-yellow-100 text-yellow-800',
    }
    return map[s] ?? 'bg-gray-100 text-gray-800'
}

function badgeClassDelivered(s) {
    const map = {
        delivered: 'bg-emerald-100 text-emerald-800',
        partial: 'bg-amber-100 text-amber-800',
        pending: 'bg-gray-100 text-gray-800',
    }
    return map[s] ?? 'bg-gray-100 text-gray-700'
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
