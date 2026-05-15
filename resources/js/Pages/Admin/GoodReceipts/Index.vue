<template>

    <Head title="Pembelian Barang" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <Link v-if="can_create" :href="route('good-receipts.create')"
                class="inline-flex items-center gap-2 text-white hover:text-blue-500 rounded-md border hover:border-gray-300 bg-blue-500 px-3 py-2 text-sm font-medium text-white-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
            <PlusSquareIcon />
            <span class="hidden md:block">Buat Penerimaan</span>
            </Link>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-2 px-8 h-16 sm:flex-row sm:items-center sm:justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">
                    Daftar Penerimaan Barang
                </div>
                <div class="flex gap-3 items-center">
                    <div>
                        <select v-model="groupByLocal"
                            class="px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <option value="">Kelompokan Berdasarkan</option>
                            <option value="source">Group: Asal Sumber</option>
                            <option value="vendor">Group: Vendor</option>
                            <option value="from_branch">
                                Group: Dari Cabang
                            </option>
                            <option value="received_at">Group: Tanggal</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-auto overflow-x-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
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
                                <button @click="changeSort('source')"
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Sumber
                                    <SortIcon :active="sortBy === 'source'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('order_no')"
                                    class="px-3 font-medium whitespace-nowrap text-gray-600 dark:text-gray-300">
                                    No. Order
                                    <SortIcon :active="sortBy === 'order_no'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('vendor')"
                                    class="px-3 w-full font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                    Vendor
                                    <SortIcon :active="sortBy === 'vendor'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('transfer_no')"
                                    class="px-3 w-full font-medium text-gray-600 dark:text-gray-300">
                                    No. Transfer
                                    <SortIcon :active="sortBy === 'transfer_no'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('branch')"
                                    class="px-3 w-full font-medium text-gray-600 dark:text-gray-300">
                                    Dari Cabang
                                    <SortIcon :active="sortBy === 'branch'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('employee')"
                                    class="px-3 w-full font-medium text-gray-600 dark:text-gray-300">
                                    Diterima Oleh
                                    <SortIcon :active="sortBy === 'employee'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('received_at')"
                                    class="px-3 w-full font-medium text-gray-600 dark:text-gray-300">
                                    Tanggal Penerimaan
                                    <SortIcon :active="sortBy === 'received_at'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Aksi
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="5"
                                class="p-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900">
                                <div class="relative space-x-4">
                                    <input v-model="filtersLocal.q" type="text" placeholder="Cari Nomor / Vendor"
                                        class="rounded-lg border border-gray-200 bg-transparent py-2.5 pl-4 pr-32 text-sm text-gray-800 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20" />
                                    <span class="absolute right-36 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
                                </div>
                            </th>
                            <th class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                                <select v-model="filtersLocal.branch_id"
                                    class="px-3 w-28 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                    <option :value="null">Semua</option>
                                    <option v-for="e in branches" :key="e.id" :value="e.id">
                                        {{ e.name }}
                                    </option>
                                </select>
                            </th>
                            <th class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                                <select v-model="filtersLocal.employee_id"
                                    class="px-3 w-28 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                    <option :value="null">Semua</option>
                                    <option v-for="e in employees" :key="e.id" :value="e.id">
                                        {{ e.name }}
                                    </option>
                                </select>
                            </th>
                            <th class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                                <div class="flex justify-center gap-2">
                                    <input v-model="filtersLocal.date_from" type="date"
                                        class="px-2 w-32 h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                                    <input v-model="filtersLocal.date_to" type="date"
                                        class="px-2 w-32 h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                                </div>
                            </th>
                            <th class="p-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900">
                                <button @click="clearFilters"
                                    class="px-3 py-2 text-xs rounded bg-red-500 text-white hover:bg-red-600">
                                    Clear
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="
                            !goodReceipts.data ||
                            goodReceipts.data.length === 0
                        ">
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                Tidak ada data penerimaan barang.
                            </td>
                        </tr>

                        <!-- Grouped rendering -->
                        <template v-if="isGrouped">
                            <template v-for="(rows, gkey) in groupedData" :key="gkey">
                                <tr>
                                    <td :colspan="7"
                                        class="px-4 py-2 font-semibold text-left text-gray-600 bg-gray-50 dark:bg-gray-900/40 dark:text-gray-300">
                                        <button class="inline-flex gap-2 items-center"
                                            @click="toggleGroupCollapse(gkey)">
                                            <span class="text-xs">{{
                                                isCollapsed(gkey) ? "▶" : "▼"
                                                }}</span>
                                            <span>
                                                {{ groupLabel(gkey) }}
                                            </span>
                                            <span class="ml-1 text-xs font-medium text-gray-500">({{ rows.length
                                                }})</span>
                                        </button>
                                    </td>
                                </tr>
                                <template v-if="!isCollapsed(gkey)">
                                    <tr v-for="(row, idx) in rows" :key="row.id"
                                        class="border-b border-gray-200 dark:border-gray-700">
                                        <td class="px-3 py-3 text-center">
                                            {{ idx + 1 }}.
                                        </td>
                                        <td :class="[
                                            'p-3 text-center font-semibold whitespace-nowrap',
                                            row.source === 'Pembelian'
                                                ? 'text-yellow-400'
                                                : 'text-green-400',
                                        ]">
                                            {{ row.source }}
                                        </td>
                                        <td :class="[
                                            'p-3 font-mono font-semibold text-center whitespace-nowrap',
                                            row.source === 'Pembelian'
                                                ? 'text-yellow-400'
                                                : 'text-green-400',
                                        ]">
                                            {{ row.order_no ?? "-" }}
                                        </td>
                                        <td :class="[
                                            'p-3 text-center  font-semibold',
                                            row.source === 'Pembelian'
                                                ? 'text-yellow-400'
                                                : 'text-green-400',
                                        ]">
                                            {{ row.vendor ?? "-" }}
                                        </td>
                                        <td :class="[
                                            'p-3 text-center font-semibold whitespace-nowrap',
                                            row.source === 'Pembelian'
                                                ? 'text-yellow-400'
                                                : 'text-green-400',
                                        ]">
                                            {{ row.transfer_no }}
                                        </td>
                                        <td :class="[
                                            'p-3 text-center font-semibold',
                                            row.source === 'Pembelian'
                                                ? 'text-yellow-400'
                                                : 'text-green-400',
                                        ]">
                                            {{ row.from_branch }}
                                        </td>
                                        <td :class="[
                                            'p-3 text-center font-semibold',
                                            row.source === 'Pembelian'
                                                ? 'text-yellow-400'
                                                : 'text-green-400',
                                        ]">
                                            {{ row.employee ?? "-" }}
                                        </td>
                                        <td class="p-3 text-center">
                                            {{ row.received_at }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="flex gap-3 items-center justify-between">
                                                <Link :href="route(
                                                    'good-receipts.show',
                                                    row.id
                                                )
                                                    ">
                                                <ShowIcon class="text-blue-400" />
                                                </Link>
                                                <Link :href="route(
                                                    'good-receipts.edit',
                                                    row.id
                                                )
                                                    ">
                                                <EditIcon class="text-yellow-500" />
                                                </Link>
                                                <button type="button" @click="onDelete(row)">
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
                            <tr v-for="(row, idx) in goodReceipts.data" :key="row.id"
                                class="border-b border-gray-200 dark:border-gray-700">
                                <td class="p-3 text-center">
                                    {{ goodReceipts.current_page * goodReceipts.per_page - goodReceipts.per_page + idx +
                                    1 }}.
                                </td>
                                <td :class="[
                                    'p-3 text-center font-semibold whitespace-nowrap',
                                    row.source === 'Pembelian'
                                        ? 'text-yellow-400'
                                        : 'text-green-400',
                                ]">
                                    {{ row.source }}
                                </td>
                                <td :class="[
                                    'p-3 font-mono font-semibold text-center whitespace-nowrap',
                                    row.source === 'Pembelian'
                                        ? 'text-yellow-400'
                                        : 'text-green-400',
                                ]">
                                    {{ row.order_no ?? "-" }}
                                </td>
                                <td :class="[
                                    'p-3 text-center  font-semibold',
                                    row.source === 'Pembelian'
                                        ? 'text-yellow-400'
                                        : 'text-green-400',
                                ]">
                                    {{ row.vendor ?? "-" }}
                                </td>
                                <td :class="[
                                    'p-3 text-center font-semibold whitespace-nowrap',
                                    row.source === 'Pembelian'
                                        ? 'text-yellow-400'
                                        : 'text-green-400',
                                ]">
                                    {{ row.transfer_no }}
                                </td>
                                <td :class="[
                                    'p-3 text-center font-semibold',
                                    row.source === 'Pembelian'
                                        ? 'text-yellow-400'
                                        : 'text-green-400',
                                ]">
                                    {{ row.from_branch }}
                                </td>
                                <td :class="[
                                    'p-3 text-center font-semibold',
                                    row.source === 'Pembelian'
                                        ? 'text-yellow-400'
                                        : 'text-green-400',
                                ]">
                                    {{ row.employee ?? "-" }}
                                </td>
                                <td class="p-3 text-center">
                                    {{ row.received_at }}
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex gap-3 items-center justify-between">
                                        <Link v-if="can_view" :href="route(
                                            'good-receipts.show',
                                            row.id
                                        )
                                            ">
                                        <ShowIcon class="text-blue-400" />
                                        </Link>
                                        <!-- <button v-if="can_delete" type="button" @click="onDelete(row)">
                                            <TrashIcon class="text-red-500" />
                                        </button> -->
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <Pagination :pagination="goodReceipts" @page-change="changePage" @per-page-change="perPageChanged"
                    class="border-t" />
            </div>

            <!-- Confirmation Modal -->
            <ConfirmModal
                :show="isConfirmModalOpen"
                :question="`Yakin ingin menghapus penerimaan barang`"
                :selected="`${selectedItem?.transfer_no || selectedItem?.order_no || 'ini'}`"
                title="Hapus Penerimaan Barang"
                confirmText="Ya, Hapus!"
                maxWidth="md"
                @close="closeConfirmModal"
                @confirm="destroyData"
            />

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
import EditIcon from "@/Components/icons/EditIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import Swal from "sweetalert2";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [{ label: "Penyimpanan" }, { label: "Penerimaan Barang" }];

const props = defineProps({
    can_edit: { type: Boolean, required: true },
    can_delete: { type: Boolean, required: true },
    can_view: { type: Boolean, required: true },
    can_create: { type: Boolean, required: true },
    goodReceipts: { type: Object, required: true },
    branches: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    sortBy: { type: String, default: "received_at" },
    employees: { type: Array, required: true },
    sortDirection: { type: String, default: "desc" },
    groupBy: { type: String, default: null },
});

const filtersLocal = ref({
    q: props.filters.q ?? "",
    branch_id: props.filters.branch_id ?? null,
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
    employee_id: props.filters.employee_id ?? null,
});

const groupByLocal = ref(props.groupBy ?? "");
const effectiveGroup = computed(
    () => groupByLocal.value || (props.groupBy ?? "")
);

const isGrouped = computed(() => !!effectiveGroup.value);

// Confirmation modal state
const isConfirmModalOpen = ref(false);
const selectedItem = ref(null);

// Grouping helpers
const collapsedGroups = ref(new Set());
const groupedData = computed(() => {
    if (!isGrouped.value) return {};
    const data = props.goodReceipts.data || [];
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
        case "source":
            return row.source ?? "-";
        case "vendor":
            return row.vendor ?? "-";
        case "from_branch":
            return row.from_branch ?? "-";
        case "received_at":
            return row.received_at ?? "-";
        default:
            return "ungrouped";
    }
}

function groupLabel(key) {
    switch (effectiveGroup.value) {
        case "source":
            return `Sumber: ${key}`;
        case "vendor":
            return `Vendor oleh: ${key}`;
        case "from_branch":
            return `Dari Cabang: ${key}`;
        case "received_at":
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

function fetchReceipts({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {

    console.log('effectiveGroup', effectiveGroup.value);
    router.get(
        route("good-receipts.index"),
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
        debounceTimer = setTimeout(() => fetchReceipts({}), 400);
    }
);
watch(
    () => [
        filtersLocal.value.branch_id,
        filtersLocal.value.date_from,
        filtersLocal.value.date_to,
        filtersLocal.value.employee_id,
    ],
    () => {
        fetchReceipts({});
    }
);
watch(groupByLocal, () => {
    fetchReceipts({});
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
    fetchReceipts({ sortBy: column, sortDirection: nextDir });
}

function clearFilters() {
    filtersLocal.value = {
        q: "",
        branch_id: null,
        date_from: "",
        date_to: "",
        employee_id: null,
    };
    groupByLocal.value = "";
    fetchReceipts({});
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
    router.delete(route("good-receipts.destroy", selectedItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeConfirmModal();
            fetchReceipts({});
        },
    });
}

function changePage(page) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('page', page);

    router.get(currentUrl.pathname + currentUrl.search, {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true
    });
}

function perPageChanged(perPage) {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('per_page', perPage);
    currentUrl.searchParams.delete('page'); // Reset to first page when changing per page

    router.get(currentUrl.pathname + currentUrl.search, {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true
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
