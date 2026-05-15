<template>
    <Head title="Pemakaian Barang" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div>
                <Link
                    v-if="can('good_issues.create')"
                    :href="route('good-issues.create')"
                    class="inline-flex items-center gap-2 text-white hover:text-blue-500 rounded-md border hover:border-gray-300 bg-blue-500 px-3 py-2 text-sm font-medium text-white-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                >
                    <PlusSquareIcon class="w-4 h-4" />
                    Buat Log Pemakaian
                </Link>
            </div>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col h-16 gap-2 px-8 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Daftar Pemakaian Barang
                </div>
                <div class="flex items-center gap-3">
                    <select
                        v-model="groupByLocal"
                        class="h-10 px-3 text-sm text-gray-800 bg-transparent border border-gray-200 rounded-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                    >
                        <option value="all">Kelompokan Berdasarkan</option>
                        <option value="date">Group: Tanggal</option>
                        <option value="department">Group: Departemen</option>
                        <option value="status">Group: Status</option>
                    </select>
                </div>
            </div>

            <div class="overflow-auto overflow-x-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 70px" />
                        <col style="width: 180px" />
                        <col style="width: 200px" />
                        <col style="width: 160px" />
                        <col style="width: 180px" />
                        <col style="width: 220px" />
                        <col style="width: 200px" />
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
                                    Referensi (MR)
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button
                                    @click="changeSort('date')"
                                    class="w-full px-3 font-medium text-center text-gray-600 dark:text-gray-300"
                                >
                                    Tanggal
                                    <SortIcon
                                        :active="sortBy === 'date'"
                                        :direction="sortDirection"
                                    />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button
                                    @click="changeSort('department')"
                                    class="w-full px-3 font-medium text-left text-gray-600 dark:text-gray-300"
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
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Dibuat Oleh
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button
                                    @click="changeSort('status')"
                                    class="w-full px-3 font-medium text-left text-gray-600 dark:text-gray-300"
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
                                    Total Item
                                </div>
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
                                class="p-2 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <div class="relative space-x-4">
                                    <input
                                        v-model="filtersLocal.q"
                                        type="text"
                                        placeholder="Cari nomor IU atau MR"
                                        class="py-2.5 pr-12 pl-4 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                                    />
                                    <span
                                        class="absolute text-gray-400 -translate-y-1/2 right-4 top-1/2"
                                        >🔍</span
                                    >
                                </div>
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <div class="grid grid-cols-2 gap-2 w-72">
                                    <input
                                        v-model="filtersLocal.date_from"
                                        type="date"
                                        class="w-full h-10 px-2 text-xs border-gray-300 rounded sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                    <input
                                        v-model="filtersLocal.date_to"
                                        type="date"
                                        class="w-full h-10 px-2 text-xs border-gray-300 rounded sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                </div>
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filtersLocal.department_id"
                                    class="px-3 text-xs border-gray-300 rounded w-28 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option :value="null">Semua</option>
                                    <option
                                        v-for="d in departments"
                                        :key="d.id"
                                        :value="d.id"
                                    >
                                        {{ d.name }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <!-- placeholder "Dibuat Oleh" filter -->
                            </th>
                            <th
                                class="px-6 py-2 text-left border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filtersLocal.status"
                                    class="h-10 px-3 text-xs border-gray-300 rounded w-36 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua</option>
                                    <option value="pending">Menunggu Persetujuan</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </th>
                            <th
                                colspan="2"
                                class="px-2 py-2 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900"
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
                                !goodIssues.data || goodIssues.data.length === 0
                            "
                        >
                            <td
                                colspan="8"
                                class="py-8 text-center text-gray-500"
                            >
                                Tidak ada data
                            </td>
                        </tr>
                        <template v-else-if="isGrouped">
                            <template
                                v-for="(rows, gkey) in groupedData"
                                :key="gkey"
                            >
                                <tr v-if="effectiveGroup !== 'all'">
                                    <td
                                        :colspan="8"
                                        class="px-4 py-2 font-semibold text-left text-gray-600 bg-gray-50 dark:bg-gray-900/40 dark:text-gray-300"
                                    >
                                        <button
                                            class="inline-flex items-center gap-2"
                                            @click="toggleGroupCollapse(gkey)"
                                        >
                                            <span class="text-xs">{{
                                                isCollapsed(gkey) ? '▶' : '▼'
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
                                            {{ serialMap[row.id] }}.
                                        </td>
                                        <td class="px-3 py-3 font-mono">
                                            <template v-if="row.request">
                                                {{ row.request }}
                                            </template>
                                            <template v-else>
                                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                                    Manual (Tanpa Referensi)
                                                </span>
                                            </template>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            {{ row.date }}
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ row.department }}
                                        </td>
                                        <td class="px-3 py-3">
                                            {{ row.created_by ?? '-' }}
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
                                        <td class="px-3 py-3">
                                            {{ row.items_count }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="flex items-center gap-2">
                                                <Link
                                                    v-if="can('good_issues.view')"
                                                    :href="route('good-issues.show', row.id)"
                                                    title="Lihat Detail"
                                                >
                                                    <ShowIcon class="text-blue-400" />
                                                </Link>
                                                <Link
                                                    v-if="can('good_issues.edit') && row.status !== 'approved'"
                                                    :href="route('good-issues.edit', row.id)"
                                                    title="Edit"
                                                >
                                                    <EditIcon class="text-yellow-500" />
                                                </Link>
                                                <button
                                                    v-if="can('good_issues.delete') && row.status !== 'approved'"
                                                    type="button"
                                                    title="Hapus"
                                                    @click="onDelete(row.id, row.request)"
                                                >
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
                            <tr
                                v-for="row in goodIssues.data"
                                :key="row.id"
                                class="border-b border-gray-200 dark:border-gray-700"
                            >
                                <td class="px-3 py-3 text-center">
                                    {{ serialMap[row.id] }}.
                                </td>
                                <td class="px-3 py-3 font-mono">
                                    <template v-if="row.request">
                                        {{ row.request }}
                                    </template>
                                    <template v-else>
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                            Manual (Tanpa Referensi)
                                        </span>
                                    </template>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    {{ row.date }}
                                </td>
                                <td class="px-3 py-3">
                                    {{ row.department }}
                                </td>
                                <td class="px-3 py-3">
                                    {{ row.created_by ?? '-' }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span
                                        :class="badgeClass(row.status)"
                                        class="px-2.5 py-1.5 text-xs font-medium rounded-full"
                                        >{{ statusLabel(row.status) }}</span
                                    >
                                </td>
                                <td class="px-3 py-3 text-center">
                                    {{ row.items_count }}
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            v-if="can('good_issues.view')"
                                            :href="route('good-issues.show', row.id)"
                                            title="Lihat Detail"
                                        >
                                            <ShowIcon class="text-blue-400" />
                                        </Link>
                                        <Link
                                            v-if="can('good_issues.edit') && row.status !== 'approved'"
                                            :href="route('good-issues.edit', row.id)"
                                            title="Edit"
                                        >
                                            <EditIcon class="text-yellow-500" />
                                        </Link>
                                        <button
                                            v-if="can('good_issues.delete') && row.status !== 'approved'"
                                            type="button"
                                            title="Hapus"
                                            @click="onDelete(row.id, row.request)"
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
                    :pagination="goodIssues"
                    @page-change="changePage"
                    @per-page-change="perPageChanged"
                    class="border-t"
                />
            </div>

            <ConfirmModal
                :show="isConfirmModalOpen"
                :question="`Yakin ingin menghapus permintaan`"
                :selected="`${selectedItem?.request || 'ini'}`"
                title="Hapus Permintaan Barang"
                confirmText="Ya, Hapus!"
                maxWidth="md"
                @close="closeConfirmModal"
                @confirm="destroyData"
            />
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import Pagination from "@/Components/common/Pagination.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useAuth } from "@/Composables/useAuth";

const { can } = useAuth();

const props = defineProps({
    goodIssues: { type: Object, required: true },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    sortBy: { type: String, default: "date" },
    sortDirection: { type: String, default: "desc" },
    groupBy: { type: String, default: null },
});

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [{ label: "Penyimpanan" }, { label: "Pemakaian Barang" }];

const filtersLocal = ref({
    q: props.filters.q ?? "",
    department_id: props.filters.department_id ?? null,
    employee_id: props.filters.employee_id ?? null,
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
    status: props.filters.status ?? "",
});

const groupByLocal = ref(props.groupBy ?? "all");
const effectiveGroup = computed(
    () => groupByLocal.value || (props.groupBy ?? "all")
);
const isGrouped = computed(() => !!effectiveGroup.value);

const isConfirmModalOpen = ref(false);
const selectedItem = ref(null);

const serialMap = computed(() => {
    const map = {};
    const base =
        ((props.goodIssues.current_page || 1) - 1) *
        (props.goodIssues.per_page || 20);
    const arr = props.goodIssues.data || [];
    arr.forEach((row, idx) => {
        map[row.id] = base + idx + 1;
    });
    return map;
});

const collapsedGroups = ref(new Set());
const groupedData = computed(() => {
    if (!isGrouped.value) return {};
    const data = props.goodIssues.data || [];
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
        case "date":
            return row.date;
        case "status":
            return statusLabel(row.status);
        default:
            return "ungrouped";
    }
}

function groupLabel(key) {
    switch (effectiveGroup.value) {
        case "department":
            return `Departemen: ${key}`;
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
        route("good-issues.index"),
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
    fetchRequests({});
});
watch(
    () => props.groupBy,
    (val) => {
        clearFilters({});
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
        submitted: "Diajukan",
        approved: "Disetujui",
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
    };
    return map[s] ?? "bg-gray-100 text-gray-700";
}

function onDelete(rowId, rowRequest) {
    if (!rowId) return;
    selectedItem.value = { id: rowId, request: rowRequest };
    isConfirmModalOpen.value = true;
}

function closeConfirmModal() {
    isConfirmModalOpen.value = false;
    selectedItem.value = null;
}

function destroyData() {
    if (!selectedItem.value?.id) return;
    router.delete(route("good-issues.destroy", selectedItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeConfirmModal();
            fetchRequests({});
        },
    });
}

function changePage(page) {
    fetchRequests({});
}

function perPageChanged(perPage) {
    router.get(
        route("good-issues.index"),
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
