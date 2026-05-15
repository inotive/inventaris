<template>
    <Head title="Perpindahan Barang" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div>
                <Link
                    v-if="can('good_transfers.create')"
                    :href="route('good-transfers.create')"
                    class="inline-flex items-center gap-2 text-white rounded-md border hover:border-gray-300 bg-blue-500 px-3 py-2 text-sm font-medium hover:text-blue-500 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                >
                    <PlusSquareIcon />
                    Pindahkan Barang
                </Link>
            </div>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col h-16 gap-2 px-8 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">
                    Daftar Perpindahan Barang
                </div>
                <div class="flex items-center gap-3">
                    <select
                        v-model="groupByLocal"
                        class="h-10 px-3 text-sm text-gray-800 bg-transparent border border-gray-200 rounded-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                    >
                        <option value="all">Kelompokan Berdasarkan</option>
                        <option value="transferred_at">Group: Tanggal Kirim</option>
                        <option value="status">Group: Status</option>
                    </select>
                </div>
            </div>

            <div class="overflow-auto overflow-x-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <thead>
                        <tr>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">No.</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('transfer_no')" class="w-full px-3 font-medium text-center text-gray-600 dark:text-gray-300">
                                    No. Transfer
                                    <SortIcon :active="sortBy === 'transfer_no'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th colspan="1" class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-center text-gray-600 dark:text-gray-300">Dari Cabang</div>
                            </th>
                            <th colspan="1" class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-center text-gray-600 dark:text-gray-300">Ke Cabang</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('transfer_date')" class="w-full px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Tanggal Transfer
                                    <SortIcon :active="sortBy === 'transfer_date'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('status')" class="w-full px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Status
                                    <SortIcon :active="sortBy === 'status'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('send_by')" class="w-full px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Dikirim Oleh
                                    <SortIcon :active="sortBy === 'send_by'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('received_by')" class="w-full px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Diterima Oleh
                                    <SortIcon :active="sortBy === 'received_by'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th
                                v-if="can('good_transfers.view') || can('good_transfers.edit') || can('good_transfers.delete')"
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Aksi
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="2" class="p-2 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <div class="relative space-x-4">
                                    <input
                                        v-model="filtersLocal.q"
                                        type="text"
                                        placeholder="Cari nomor transfer"
                                        class="rounded-lg border border-gray-200 bg-transparent py-2.5 pl-4 pr-12 text-sm text-gray-800 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                                    />
                                    <span class="absolute text-gray-400 -translate-y-1/2 right-4 top-1/2">🔍</span>
                                </div>
                            </th>
                            <th colspan="2" class="px-2 text-center border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900">
                                <div class="flex items-center justify-center gap-2">
                                    <Select
                                        v-model="filtersLocal.from_branch"
                                        :items="branchesWithAll"
                                        label="Dari Cabang"
                                        :class="'w-40 text-xs sm:text-sm'"
                                    >
                                        <template #item="{ item }">
                                            <span>{{ item.name }}</span>
                                        </template>
                                    </Select>
                                    <Select
                                        v-model="filtersLocal.to_branch"
                                        :items="branch_to"
                                        label="Ke Cabang"
                                        :class="'w-40 text-xs sm:text-sm'"
                                    >
                                        <template #item="{ item }">
                                            <span>{{ item.name }}</span>
                                        </template>
                                    </Select>
                                </div>
                            </th>
                            <th class="p-2 border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900">
                                <div class="grid grid-cols-2 gap-2">
                                    <input v-model="filtersLocal.date_from" type="date" class="h-10 px-2 text-xs border-gray-300 rounded sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                                    <input v-model="filtersLocal.date_to" type="date" class="h-10 px-2 text-xs border-gray-300 rounded sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                                </div>
                            </th>
                            <th class="px-6 py-2 border border-gray-200 bg-gray-50 dark:border-gray-600 dark:bg-gray-900">
                                <select v-model="filtersLocal.status" class="h-10 px-3 text-xs border-gray-300 rounded w-36 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                    <option value="">Semua</option>
                                    <option value="Shipped">Dikirim</option>
                                    <option value="Received">Diterima</option>
                                    <option value="Canceled">Dibatalkan</option>
                                </select>
                            </th>
                            <th class="px-2 py-2 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <div class="flex items-center h-10 px-3 text-xs text-gray-500">-</div>
                            </th>
                            <th class="px-2 py-2 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <div class="flex items-center h-10 px-3 text-xs text-gray-500">-</div>
                            </th>
                            <th class="px-2 py-2 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <button @click="clearFilters" class="px-3 py-2 text-xs text-white bg-red-500 rounded hover:bg-red-600">
                                    Clear
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="!goodTransfers.data || goodTransfers.data.length === 0">
                            <td colspan="10" class="py-8 text-center text-gray-500">
                                Tidak ada pemindahan barang.
                            </td>
                        </tr>

                        <!-- Grouped -->
                        <template v-else-if="isGrouped">
                            <template v-for="(rows, gkey) in groupedData" :key="gkey">
                                <tr v-if="groupByLocal != 'all'">
                                    <td :colspan="10" class="px-4 py-2 font-semibold text-gray-600 bg-gray-50 dark:bg-gray-900/40 dark:text-gray-300">
                                        <button class="inline-flex items-center gap-2" @click="toggleGroupCollapse(gkey)">
                                            <span class="text-xs">{{ isCollapsed(gkey) ? "▶" : "▼" }}</span>
                                            <span>{{ groupLabel(gkey) }}</span>
                                            <span class="ml-1 text-xs font-medium text-gray-500">({{ rows.length }})</span>
                                        </button>
                                    </td>
                                </tr>
                                <template v-if="!isCollapsed(gkey)">
                                    <tr v-for="(row, idx) in rows" :key="row.id" class="border-b border-gray-200 dark:border-gray-700">
                                        <td class="px-3 py-3 text-center">{{ idx + 1 }}.</td>
                                        <td class="px-3 py-3 font-mono text-center">{{ row.transfer_no }}</td>
                                        <td colspan="2" class="px-3 py-3 font-mono text-center">
                                            <div class="flex items-center justify-between gap-3 whitespace-nowrap">
                                                <span class="font-semibold text-red-500">{{ row.from_branch }}</span>
                                                ➡
                                                <span class="font-semibold text-green-500">{{ row.to_branch }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center">{{ row.transferred_at }}</td>
                                        <td class="px-3 py-3 text-center">
                                            <span :class="badgeClass(row.status)" class="px-2.5 py-1 text-xs font-medium rounded-full">
                                                {{ statusLabel(row.status) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">{{ row.sent_by || '-' }}</td>
                                        <td class="px-3 py-3 text-center">{{ row.received_by || '-' }}</td>
                                        <td
                                            v-if="can('good_transfers.view') || can('good_transfers.edit') || can('good_transfers.delete')"
                                            class="px-6 py-3"
                                        >
                                            <div class="flex items-center justify-center gap-2">
                                                <Link v-if="can('good_transfers.view')" :href="route('good-transfers.show', row.id)">
                                                    <ShowIcon class="text-blue-400" />
                                                </Link>
                                                <Link v-if="can('good_transfers.edit') && row.status !== 'Received' && row.status !== 'Canceled'" :href="route('good-transfers.edit', row.id)">
                                                    <EditIcon class="text-yellow-500" />
                                                </Link>
                                                <button v-if="can('good_transfers.delete') && row.status !== 'Received' && row.status !== 'Canceled'" type="button" @click="onDelete(row)">
                                                    <TrashIcon class="text-red-500" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </template>

                        <!-- Flat -->
                        <template v-else>
                            <tr
                                v-for="(row, idx) in goodTransfers.data"
                                :key="row.id"
                                class="border-b border-gray-200 dark:border-gray-700"
                            >
                                <td class="p-3 text-center">{{ idx + (goodTransfers.current_page - 1) * goodTransfers.per_page + 1 }}.</td>
                                <td class="p-3 text-center">{{ row.transfer_no }}</td>
                                <td colspan="2" class="p-3 font-mono text-center">
                                    <div class="flex items-center justify-between gap-3 whitespace-nowrap">
                                        <span class="font-semibold text-red-500">{{ row.from_branch }}</span>
                                        ➡
                                        <span class="font-semibold text-green-500">{{ row.to_branch }}</span>
                                    </div>
                                </td>
                                <td class="p-3 text-center">{{ row.transferred_at }}</td>
                                <td class="p-3 text-center">
                                    <span :class="badgeClass(row.status)" class="px-2.5 py-1.5 text-xs font-medium rounded-full">
                                        {{ statusLabel(row.status) }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">{{ row.sent_by || '-' }}</td>
                                <td class="p-3 text-center">{{ row.received_by || '-' }}</td>
                                <td
                                    v-if="can('good_transfers.view') || can('good_transfers.edit') || can('good_transfers.delete')"
                                    class="px-6 py-3"
                                >
                                    <div class="flex items-center justify-center gap-2">
                                        <Link v-if="can('good_transfers.view')" :href="route('good-transfers.show', row.id)">
                                            <ShowIcon class="text-blue-400" />
                                        </Link>
                                        <Link v-if="can('good_transfers.edit') && row.status !== 'Received' && row.status !== 'Canceled'" :href="route('good-transfers.edit', row.id)">
                                            <EditIcon class="text-yellow-500" />
                                        </Link>
                                        <button v-if="can('good_transfers.delete') && row.status !== 'Received' && row.status !== 'Canceled'" type="button" @click="onDelete(row)">
                                            <TrashIcon class="text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <Pagination
                    :pagination="goodTransfers"
                    @page-change="changePage"
                    @per-page-change="perPageChanged"
                    class="border-t"
                />
            </div>

            <!-- Confirmation Modal -->
            <ConfirmModal
                :show="isConfirmModalOpen"
                :question="`Yakin ingin menghapus pemindahan barang`"
                :selected="`${selectedItem?.transfer_no || 'ini'}`"
                title="Hapus Pemindahan Barang"
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
import Select from "@/Components/form/SelectPemakaian.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useAuth } from "@/Composables/useAuth"; // ⬅️ tambahkan

const { can } = useAuth(); // ⬅️ gunakan seperti di branches

const props = defineProps({
    goodTransfers: { type: Object, required: true },
    branches: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    sortBy: { type: String, default: "transferred_at" },
    sortDirection: { type: String, default: "desc" },
    groupBy: { type: String, default: null },
    branch_to: { type: Array, required: true },
    employees: { type: Array, required: true },
});

defineOptions({ layout: AppLayout });

const breadcrumbs = [{ label: "Penyimpanan" }, { label: "Perpindahan Barang" }];

const filtersLocal = ref({
    q: props.filters.q ?? "",
    from_branch: props.filters.from_branch ?? null,
    to_branch: props.filters.to_branch ?? null,
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
    status: props.filters.status ?? "",
});

const groupByLocal = ref(props.groupBy ?? "all");
const effectiveGroup = computed(() => groupByLocal.value || (props.groupBy ?? "all"));
const isGrouped = computed(() => !!effectiveGroup.value);

// branches + opsi Semua
const branchesWithAll = computed(() => [{ id: null, name: 'Semua' }, ...props.branches]);

// Konfirmasi hapus
const isConfirmModalOpen = ref(false);
const selectedItem = ref(null);

// Grouping helpers
const collapsedGroups = ref(new Set());
const groupedData = computed(() => {
    if (!isGrouped.value) return {};
    const data = props.goodTransfers.data || [];
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
        case "from_branch":
            return row.from_branch?.name ?? "Dari Cabang";
        case "to_branch":
            return row.to_branch?.name ?? "Ke Cabang";
        case "transferred_at":
            return row.transferred_at ?? "Tanggal Pengiriman";
        case "status":
            return statusLabel(row.status);
        default:
            return "ungrouped";
    }
}

function groupLabel(key) {
    switch (effectiveGroup.value) {
        case "from_branch":
            return `Dari Cabang: ${key}`;
        case "to_branch":
            return `Ke Cabang: ${key}`;
        case "transferred_at":
            return `Dikirim Tanggal: ${key}`;
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

function fetchTransfers({ sortBy = props.sortBy, sortDirection = props.sortDirection } = {}) {
    router.get(
        route("good-transfers.index"),
        { ...filtersLocal.value, groupBy: effectiveGroup.value || null, sortBy, sortDirection },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onStart: () => { if (typeof window !== "undefined") window.__suppressProgress = true; },
            onFinish: () => { if (typeof window !== "undefined") window.__suppressProgress = false; },
        }
    );
}

let debounceTimer;
watch(() => filtersLocal.value.q, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchTransfers({}), 400);
});
watch(() => [
    filtersLocal.value.from_branch,
    filtersLocal.value.to_branch,
    filtersLocal.value.status,
    filtersLocal.value.date_from,
    filtersLocal.value.date_to,
], () => { fetchTransfers({}); });

watch(groupByLocal, () => { fetchTransfers({}); });
watch(() => props.groupBy, (val) => { if (!groupByLocal.value && val) groupByLocal.value = val; });

function changeSort(column) {
    const nextDir = props.sortBy === column && props.sortDirection === "asc" ? "desc" : "asc";
    fetchTransfers({ sortBy: column, sortDirection: nextDir });
}

function clearFilters() {
    filtersLocal.value = { q: "", from_branch: null, to_branch: null, date_from: "", date_to: "", status: "" };
    groupByLocal.value = "";
    fetchTransfers({});
}

function statusLabel(s) {
    const map = { Shipped: "Dikirim", Received: "Diterima", Canceled: "Dibatalkan" };
    return map[s] ?? s;
}

function badgeClass(s) {
    const map = {
        Shipped: "bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-300",
        Received:"bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300",
        Canceled:"bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300",
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
    router.delete(route("good-transfers.destroy", selectedItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeConfirmModal();
            fetchTransfers({});
        },
    });
}

// Pagination handlers
function changePage(page) { fetchTransfers({}); }

function perPageChanged(perPage) {
    router.get(
        route("good-transfers.index"),
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
            onStart: () => { if (typeof window !== "undefined") window.__suppressProgress = true; },
            onFinish: () => { if (typeof window !== "undefined") window.__suppressProgress = false; },
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
