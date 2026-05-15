<template>
    <Head title="Permintaan Barang" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <Link
                v-if="can_create"
                :href="route('material-requests.create')"
                class="inline-flex items-center gap-2 text-white hover:text-blue-500 rounded-md border hover:border-gray-300 bg-blue-500 px-3 py-2 text-sm font-medium text-white-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            >
                <PlusSquareIcon />
                <span class="hidden md:block">Buat Permintaan</span>
            </Link>
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
                    Daftar Permintaan Barang
                </div>
                <div class="flex gap-3 items-center">
                    <div>
                        <select
                            v-model="groupByLocal"
                            class="px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                        >
                            <option value="all">Kelompokan Berdasarkan</option>
                            <option value="department">
                                Group: Departemen
                            </option>
                            <option value="requested_by">
                                Group: Diminta oleh
                            </option>
                            <option value="status">Group: Status</option>
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
                                    No. Request
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button
                                    @click="changeSort('requested_by')"
                                    class="px-3 w-full font-medium text-left text-gray-600 whitespace-nowrap dark:text-gray-300"
                                >
                                    Permintaan oleh
                                    <SortIcon
                                        :active="sortBy === 'requested_by'"
                                        :direction="sortDirection"
                                    />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button
                                    @click="changeSort('department')"
                                    class="px-3 w-full font-medium text-left text-gray-600 whitespace-nowrap dark:text-gray-300"
                                >
                                    Departemen
                                    <SortIcon
                                        :active="sortBy === 'department'"
                                        :direction="sortDirection"
                                    />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button
                                    @click="changeSort('requested_at')"
                                    class="px-3 w-full font-medium text-left text-gray-600 dark:text-gray-300"
                                >
                                    Tanggal Permintaan
                                    <SortIcon
                                        :active="sortBy === 'requested_at'"
                                        :direction="sortDirection"
                                    />
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
                                colspan="2"
                                class="p-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <!-- <input
                                    v-model="filtersLocal.q"
                                    type="text"
                                    placeholder="Cari nomor / pemohon"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                /> -->

                                <div class="relative space-x-4">
                                    <input
                                        v-model="filtersLocal.q"
                                        type="text"
                                        placeholder="Cari nomor request / nama pemohon"
                                        class="py-2.5 pr-12 pl-4 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                                    />
                                    <span
                                        class="absolute right-4 top-1/2 text-gray-400 -translate-y-1/2"
                                        >🔍</span
                                    >
                                </div>
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <Select
                                    v-model="filtersLocal.employee_id"
                                    :items="[
                                        { id: null, name: 'Semua' },
                                        ...employees,
                                    ]"
                                    label="Pilih Pemohon"
                                    class="w-full text-xs sm:text-sm"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <Select
                                    v-model="filtersLocal.department_id"
                                    :items="[
                                        { id: null, name: 'Semua' },
                                        ...departments,
                                    ]"
                                    label="Pilih Departemen"
                                    class="w-full text-xs sm:text-sm"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <div class="grid grid-cols-2 gap-2 w-72">
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
                                    class="px-3 h-10 text-xs rounded border-gray-300 w-54 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua</option>
                                    <option value="pending">
                                        Menunggu Persetujuan
                                    </option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                    <option value="cancelled">Dibatalkan</option>
                                    <option value="partial_approved">
                                        Disetujui Sebagian
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <button
                                    @click="clearFilters"
                                    class="px-3 py-2 text-xs text-white bg-red-500 rounded hover:bg-red-600"
                                >
                                    Clear
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="
                                !materialRequests.data ||
                                materialRequests.data.length === 0
                            "
                        >
                            <td
                                colspan="7"
                                class="py-8 text-center text-gray-500"
                            >
                                Tidak ada data
                            </td>
                        </tr>

                        <!-- Grouped rendering -->
                        <template v-if="isGrouped">
                            <template
                                v-for="(rows, gkey) in groupedData"
                                :key="gkey"
                            >
                                <tr v-if="effectiveGroup !== 'all'">
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
                                        <td class="px-3 py-3 text-center">
                                            {{ serialMap[row.id] ?? "-" }}.
                                        </td>
                                        <td class="px-3 py-3 font-mono">
                                            {{ row.request_no }}
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ row.requested_by }}
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ row.department }}
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            {{ row.requested_at }}
                                        </td>
                                        <td class="px-3 py-3">
                                            <span
                                                :class="badgeClass(row.status)"
                                                class="px-2.5 py-1 text-xs font-medium rounded-full"
                                                >{{
                                                    statusLabel(row.status)
                                                }}</span
                                            >
                                        </td>
                                        <td class="px-6 py-3">
                                            <div
                                                class="flex gap-2 items-center"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'material-requests.show',
                                                            row.id
                                                        )
                                                    "
                                                >
                                                    <ShowIcon
                                                        class="text-blue-400"
                                                    />
                                                </Link>
                                                <Link
                                                    v-if="can_edit && row.status !== 'approved' && row.status !== 'cancelled' && row.status !== 'rejected'"
                                                    :href="
                                                        route(
                                                            'material-requests.edit',
                                                            row.id
                                                        )
                                                    "
                                                >
                                                    <EditIcon
                                                        class="text-yellow-500"
                                                    />
                                                </Link>
                                                <button
                                                    v-if="can_delete && row.status !== 'approved' && row.status !== 'cancelled' && row.status !== 'rejected'"
                                                    type="button"
                                                    @click="onDelete(row)"
                                                >
                                                    <TrashIcon
                                                        class="text-red-500"
                                                    />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </template>

                        <!-- Flat rendering when no groupBy -->
                        <template v-else>
                            <tr
                                v-for="row in materialRequests.data"
                                :key="row.id"
                                class="border-b border-gray-200 dark:border-gray-700"
                            >
                                <td class="px-3 py-3 text-center">
                                    {{ serialMap[row.id] ?? "-" }}.
                                </td>
                                <td class="px-3 py-3 font-mono">
                                    {{ row.request_no }}
                                </td>
                                <td class="px-3 py-3">
                                    {{ row.requested_by }}
                                </td>
                                <td class="px-3 py-3 w-72">
                                    {{ row.department }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    {{ row.requested_at }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span
                                        :class="badgeClass(row.status)"
                                        class="px-2.5 py-1 text-xs font-medium rounded-full"
                                        >{{ statusLabel(row.status) }}</span
                                    >
                                </td>
                                <td class="px-6 py-3">
                                    <div
                                        class="flex gap-2 justify-between items-center"
                                    >
                                        <Link
                                            :href="
                                                route(
                                                    'material-requests.show',
                                                    row.id
                                                )
                                            "
                                        >
                                            <ShowIcon class="text-blue-400" />
                                        </Link>
                                        <Link
                                            v-if="can_edit && row.status !== 'approved' && row.status !== 'cancelled' && row.status !== 'rejected'"
                                            :href="
                                                route(
                                                    'material-requests.edit',
                                                    row.id
                                                )
                                            "
                                        >
                                            <EditIcon class="text-yellow-500" />
                                        </Link>
                                        <button
                                            v-if="can_delete && row.status !== 'approved' && row.status !== 'cancelled' && row.status !== 'rejected'"
                                            type="button"
                                            @click="onDelete(row)"
                                        >
                                            <TrashIcon class="text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <Pagination
                    :pagination="materialRequests"
                    @page-change="changePage"
                    @per-page-change="perPageChanged"
                    class="border-t"
                />
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <ConfirmModal
        :show="isConfirmModalOpen"
        :question="`Yakin ingin menghapus material request`"
        :selected="`${selectedItem?.request_no || 'ini'}`"
        title="Hapus Material Request"
        confirmText="Ya, Hapus!"
        maxWidth="md"
        @close="closeConfirmModal"
        @confirm="destroyData"
    />
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/Pagination.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import Select from "@/Components/form/SelectPemakaian.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [{ label: "Penyimpanan" }, { label: "Permintaan Barang" }];

const props = defineProps({
    materialRequests: { type: Object, required: true },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    sortBy: { type: String, default: "requested_at" },
    sortDirection: { type: String, default: "desc" },
    groupBy: { type: String, default: 'all' },
    can_approve: { type: Boolean, default: false },
    can_edit: { type: Boolean, default: false },
    can_delete: { type: Boolean, default: false },
    can_view: { type: Boolean, default: false },
    can_create: { type: Boolean, default: false },
});

const filtersLocal = ref({
    q: props.filters.q ?? "",
    employee_id: props.filters.employee_id ?? null,
    department_id: props.filters.department_id ?? null,
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
    status: props.filters.status ?? "",
});

const groupBy = ref("");

// Confirmation modal state
const isConfirmModalOpen = ref(false);
const selectedItem = ref(null);

const groupByLocal = ref(props.groupBy ?? "all");

const effectiveGroup = computed(
    () => groupByLocal.value || (props.groupBy ?? "")
);

const isGrouped = computed(() => !!effectiveGroup.value);

// Serial number map to support grouped view
const serialMap = computed(() => {
    const map = {};
    const base =
        ((props.materialRequests.current_page || 1) - 1) *
        (props.materialRequests.per_page || 20);
    const arr = props.materialRequests.data || [];
    arr.forEach((row, idx) => {
        map[row.id] = base + idx + 1;
    });
    return map;
});

// Grouping helpers
const collapsedGroups = ref(new Set());
const groupedData = computed(() => {
    if (!isGrouped.value) return {};
    if(groupBy.value === null) {
        isGrouped.value = false;
        console.log('groupBy', groupBy.value);
    }

    console.log('groupedData', isGrouped.value);
    const data = props.materialRequests.data || [];
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
        case "department":
            return row.department ?? "Tanpa Departemen";
        case "requested_by":
            return row.requested_by ?? "Tanpa Pemohon";
        case "status":
            return statusLabel(row.status);
    }
}

function groupLabel(key) {
    switch (effectiveGroup.value) {
        case "department":
            return `Departemen: ${key}`;
        case "requested_by":
            return `Diminta oleh: ${key}`;
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
    groupBy.value = effectiveGroup.value;
    if(groupBy.value === "all") {
        groupBy.value = null;
    }
    console.log('groupBy', groupBy.value);
    router.get(
        route("material-requests.index"),
        {
            ...filtersLocal.value,
            groupBy: groupBy.value || null,
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
        filtersLocal.value.department_id,
        filtersLocal.value.employee_id,
        filtersLocal.value.status,
        filtersLocal.value.date_from,
        filtersLocal.value.date_to,
    ],
    () => {
        fetchRequests({});
    }
);

watch(groupByLocal, () => {
    clearFilters();
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

function clearFilters() {
    filtersLocal.value = {
        q: "",
        employee_id: null,
        department_id: null,
        date_from: "",
        date_to: "",
        status: "",
    };
    fetchRequests({});
}

function statusLabel(s) {
    const map = {
        pending: "Menunggu Persetujuan",
        approved: "Disetujui",
        rejected: "Ditolak",
        cancelled: "Dibatalkan",
        partial_approved: "Disetujui Sebagian",
    };
    return map[s] ?? s;
}

function badgeClass(s) {
    const map = {
        pending:
            "bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-300",
        approved:
            "bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300",
        rejected:
            "bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300",
        cancelled:
            "bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300",
        partial_approved:
            "bg-violet-100 text-violet-800 dark:bg-violet-500/20 dark:text-violet-300",
    };
    return map[s] ?? "bg-gray-100 text-gray-700";
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
    router.delete(route("material-requests.destroy", selectedItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeConfirmModal();
            fetchRequests({});
        },
    });
}

// Pagination event handlers
function changePage(page) {
    fetchRequests({});
}

function perPageChanged(perPage) {
    // Update per page parameter and fetch
    router.get(
        route("material-requests.index"),
        {
            ...filtersLocal.value,
            groupBy: effectiveGroup.value || null,
            sortBy: props.sortBy,
            sortDirection: props.sortDirection,
            per_page: perPage,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
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
