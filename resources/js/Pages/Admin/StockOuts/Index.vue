<template>
    <Head title="Stok Keluar" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
            <button
                v-if="can('stock_out.create')"
                @click="openAddModal"
                type="button"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-white bg-blue-500 border rounded-md hover:text-blue-500 hover:border-gray-300"
            >
                Tambah Data
            </button>
        </div>

        <div class="flex flex-col overflow-hidden bg-white border border-gray-200 rounded-lg">
            <div class="flex flex-col h-16 gap-2 px-8 sm:flex-row sm:items-center sm:justify-between">
                <div class="font-bold text-gray-700 md:text-xl">Daftar Stok Keluar</div>
                <div class="flex items-center gap-3">
                    <select v-model="groupByLocal" class="h-10 px-3 text-sm border border-gray-200 rounded-lg">
                        <option value="">Kelompokan Berdasarkan</option>
                        <option value="item">Group: Nama Barang</option>
                        <option value="created_at">Group: Tanggal Keluar</option>
                    </select>
                </div>
            </div>

            <div class="overflow-auto overflow-x-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 50px" />
                        <col style="width: 100px" />
                        <col style="width: 220px" />
                        <col style="width: 180px" />
                        <col style="width: 100px" />
                        <col style="width: 80px" />
                        <col style="width: 80px" />
                        <col style="width: 120px" />
                        <col style="width: 180px" />
                        <col style="width: 80px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="py-2.5 bg-gray-100 border-y">
                                <div class="px-3 font-medium text-gray-600">No.</div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-y">
                                <button @click="changeSort('id')" class="w-full px-3 font-medium text-center text-gray-600">
                                    No. Referensi
                                    <SortIcon :active="sortBy === 'id'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-y">
                                <div class="w-full px-3 font-medium text-center text-gray-600">
                                    Nama Barang
                                </div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-y">
                                <div class="w-full px-3 font-medium text-center text-gray-600">
                                    Cabang
                                </div>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-y">
                                <button @click="changeSort('initial_stock')" class="w-full px-3 font-medium text-center text-gray-600">
                                    Stok Sebelumnya
                                    <SortIcon :active="sortBy === 'initial_stock'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-y">
                                <button @click="changeSort('amount')" class="w-full px-3 font-medium text-gray-600">
                                    Qty
                                    <SortIcon :active="sortBy === 'amount'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-y">
                                <button @click="changeSort('last_stock')" class="w-full px-3 font-medium text-gray-600">
                                    Stok Akhir
                                    <SortIcon :active="sortBy === 'last_stock'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th class="py-2.5 bg-gray-100 border-y">
                                <button @click="changeSort('created_at')" class="w-full px-3 font-medium text-gray-600">
                                    Tanggal Keluar
                                    <SortIcon :active="sortBy === 'created_at'" :direction="sortDirection" />
                                </button>
                            </th>

                            <!-- Kolom Catatan -->
                            <th class="py-2.5 bg-gray-100 border-y">
                                <div class="px-3 font-medium text-gray-600">Catatan</div>
                            </th>

                            <th class="py-2.5 bg-gray-100 border-y">
                                <div class="px-3 font-medium text-gray-600">Aksi</div>
                            </th>
                        </tr>

                        <tr>
                            <th colspan="2" class="p-2 bg-gray-50 border-y">
                                <div class="relative w-full max-w-xs mx-auto">
                                    <input v-model="filtersLocal.q" type="text" placeholder="Cari nomor referensi" class="py-2.5 pr-12 pl-4 w-full text-sm rounded-lg border border-gray-200" />
                                    <span class="absolute text-gray-400 -translate-y-1/2 right-3 top-1/2">🔍</span>
                                </div>
                            </th>

                            <th class="px-2 text-left border bg-gray-50">
                                <select v-model="filtersLocal.item_id" class="w-full px-3 py-2 text-xs border-gray-300 rounded">
                                    <option :value="null">Semua Barang</option>
                                    <option v-for="d in items" :key="d.id" :value="d.id">{{ d.name }}</option>
                                </select>
                            </th>

                            <th class="px-2 text-left border bg-gray-50">
                                <select v-model="filtersLocal.branch_id" class="w-full px-3 py-2 text-xs border-gray-300 rounded">
                                    <option :value="null">Semua Cabang</option>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                            </th>

                            <th colspan="3" class="px-2 py-2 bg-gray-50 border-y"></th>

                            <th class="p-2 border bg-gray-50">
                                <div class="space-x-2 whitespace-nowrap">
                                    <input v-model="filtersLocal.date_from" type="date" class="h-10 text-xs border-gray-300 rounded w-36" />
                                    <input v-model="filtersLocal.date_to" type="date" class="h-10 text-xs border-gray-300 rounded w-36" />
                                </div>
                            </th>

                            <!-- Placeholder untuk kolom Catatan -->
                            <th class="px-2 py-2 bg-gray-50 border-y"></th>

                            <th class="px-2 py-2 bg-gray-50 border-y">
                                <button @click="clearFilters" class="px-3 py-2 text-xs text-white bg-red-500 rounded">Clear</button>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="!itemStocks.data || itemStocks.data.length === 0">
                            <td colspan="10" class="py-8 text-center text-gray-500">Stok keluar tidak ditemukan.</td>
                        </tr>

                        <template v-else-if="isGrouped">
                            <template v-for="(rows, gkey) in groupedData" :key="gkey">
                                <tr>
                                    <td :colspan="10" class="px-4 py-2 font-semibold text-gray-600 bg-gray-50">
                                        <button class="inline-flex items-center gap-2" @click="toggleGroupCollapse(gkey)">
                                            <span class="text-xs">{{ isCollapsed(gkey) ? '▶' : '▼' }}</span>
                                            <span>{{ groupLabel(gkey) }}</span>
                                            <span class="ml-1 text-xs text-gray-500">({{ rows.length }})</span>
                                        </button>
                                    </td>
                                </tr>

                                <template v-if="!isCollapsed(gkey)">
                                    <tr v-for="(row, idx) in rows" :key="row.id" class="border-b border-gray-200">
                                        <td class="p-3 text-center">{{ idx + 1 }}.</td>
                                        <td class="p-3 font-mono text-center">
                                            <span v-if="row.reference_no" class="font-mono">{{ row.reference_no }}</span>
                                            <span v-else class="inline-flex px-2 py-0.5 text-xs font-medium text-gray-600 bg-gray-100 rounded">Manual</span>
                                        </td>
                                        <td class="p-3 text-center">{{ row.item.name }}</td>
                                        <td class="p-3 text-center">
                                            <span v-if="row.item.branch" class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">{{ row.item.branch.name }}</span>
                                            <span v-else class="italic text-gray-400">—</span>
                                        </td>
                                        <td class="p-3 text-center">{{ row.initial_stock }} {{ row.item.unit?.name }}</td>
                                        <td class="p-3 font-semibold text-center text-red-500">- {{ row.amount }} {{ row.item.unit?.name }}</td>
                                        <td class="p-3 text-center">{{ row.last_stock }} {{ row.item.unit?.name }}</td>
                                        <td class="p-3 text-center whitespace-nowrap">{{ row.created_at }}</td>

                                        <!-- Catatan -->
                                        <td class="p-3 text-center">
                                            <span v-if="row.note && row.note.trim()">{{ row.note }}</span>
                                            <span v-else class="italic text-gray-400">—</span>
                                        </td>

                                        <td class="p-3 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <button v-if="can('stock_out.edit')" @click="openEditModal(row)" class="text-yellow-500 hover:text-yellow-600" title="Edit">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </button>

                                                <button v-if="can('stock_out.delete')" @click="confirmDelete(row.id)" class="text-red-500 hover:text-red-600" title="Hapus">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </template>

                        <template v-else>
                            <tr v-for="(row, idx) in itemStocks.data" :key="row.id" class="border-b border-gray-200">
                                <td class="p-3 text-center">{{ idx + (itemStocks.current_page - 1) * itemStocks.per_page + 1 }}.</td>
                                <td class="p-3 text-center">
                                    <span v-if="row.reference_no" class="font-mono">{{ row.reference_no }}</span>
                                    <span v-else class="inline-flex px-2 py-0.5 text-xs font-medium text-gray-600 bg-gray-100 rounded">Manual</span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-3 whitespace-nowrap">
                                        <span class="font-semibold">{{ row.item.name }}</span>
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    <span v-if="row.item.branch" class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded">{{ row.item.branch.name }}</span>
                                    <span v-else class="italic text-gray-400">—</span>
                                </td>
                                <td class="p-3 text-center">{{ row.initial_stock }} {{ row.item.unit?.name }}</td>
                                <td class="p-3 font-semibold text-center text-red-500">- {{ row.amount }} {{ row.item.unit?.name }}</td>
                                <td class="p-3 text-center">{{ row.last_stock }} {{ row.item.unit?.name }}</td>
                                <td class="p-3 text-center whitespace-nowrap">{{ row.created_at }}</td>

                                <!-- Catatan -->
                                <td class="p-3 text-center">
                                    <span v-if="row.note && row.note.trim()">{{ row.note }}</span>
                                    <span v-else class="italic text-gray-400">—</span>
                                </td>

                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <button v-if="can('stock_out.edit')" @click="openEditModal(row)" class="text-yellow-500 hover:text-yellow-600" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>

                                        <button v-if="can('stock_out.delete')" @click="confirmDelete(row.id)" class="text-red-500 hover:text-red-600" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Komponen pagination (pakai link dari paginator Laravel) -->
                <Pagination :pagination="itemStocks" class="border-t" />
            </div>

            <!-- Modal Tambah/Edit -->
            <Modal
                :show="isModalOpen"
                :title="isEditMode ? 'Edit Stok Keluar' : 'Tambah Stok Keluar'"
                :confirmText="isSaving ? 'Menyimpan...' : 'Simpan'"
                maxWidth="md"
                @close="onCloseModal"
                @confirm="saveStock"
            >
                <div v-if="isSaving" class="mb-3 text-xs text-blue-600">Menyimpan, mohon tunggu…</div>

                <div class="space-y-4 text-sm">
                    <div>
                        <label class="font-semibold text-gray-900">Barang <span class="text-red-500">*</span></label>
                        <Select
                            v-model="form.item_id"
                            :items="items"
                            label="Pilih barang"
                            :disabled="form.processing || isSaving"
                            class="mt-1"
                            :isSuperadmin="isSuperadmin"
                        />
                        <div v-if="form.errors.item_id" class="text-sm text-red-500">{{ form.errors.item_id }}</div>
                    </div>

                    <div v-if="currentStock !== null">
                        <label class="font-semibold text-gray-900">Stock Saat Ini</label>
                        <div class="mt-1 px-3 py-2 text-sm border border-gray-300 bg-gray-50 rounded text-gray-700">
                            {{ formatStock(currentStock) }} {{ selectedItemUnit }}
                        </div>
                    </div>

                    <div>
                        <label class="font-semibold text-gray-900">Qty <span class="text-red-500">*</span></label>
                        <input
                            v-model.number="form.amount"
                            type="number"
                            step="0.0001"
                            min="0"
                            :max="currentStock !== null ? currentStock : undefined"
                            :disabled="form.processing || isSaving || !form.item_id"
                            class="w-full mt-1 text-sm border-gray-400 rounded"
                            placeholder="Masukkan jumlah"
                        />
                        <div v-if="form.errors.amount" class="text-sm text-red-500">{{ form.errors.amount }}</div>
                        <div v-if="currentStock !== null && form.amount > currentStock" class="text-sm text-red-500 mt-1">
                            Qty tidak boleh melebihi stock saat ini ({{ formatStock(currentStock) }} {{ selectedItemUnit }})
                        </div>
                    </div>

                    <div>
                        <label class="font-semibold text-gray-900">Tanggal</label>
                        <input v-model="form.tanggal" type="date" :disabled="form.processing || isSaving" class="w-full mt-1 text-sm border-gray-400 rounded" />
                        <div v-if="form.errors.tanggal" class="text-sm text-red-500">{{ form.errors.tanggal }}</div>
                    </div>

                    <div>
                        <label class="font-semibold text-gray-900">Catatan</label>
                        <textarea v-model="form.note" rows="3" :disabled="form.processing || isSaving" class="w-full mt-1 text-sm border-gray-400 rounded" placeholder="Catatan"></textarea>
                        <div v-if="form.errors.note" class="text-sm text-red-500">{{ form.errors.note }}</div>
                    </div>
                </div>
            </Modal>

            <!-- Modal Notifikasi Sukses -->
            <Modal
                :show="showSuccess"
                title="Berhasil"
                confirmText="Tutup"
                maxWidth="sm"
                @close="showSuccess = false"
                @confirm="showSuccess = false"
            >
                <div class="text-sm">
                    <div class="mb-2 text-green-600">✔️ Data berhasil {{ successMode === 'update' ? 'diperbarui' : 'disimpan' }}.</div>
                </div>
            </Modal>

            <!-- Modal Error Umum / Network -->
            <Modal
                :show="showError"
                title="Terjadi Kesalahan"
                confirmText="Coba Lagi"
                maxWidth="sm"
                @close="showError = false"
                @confirm="retryLastAction"
            >
                <div class="text-sm">
                    <div class="mb-2 text-red-600">Gagal menyimpan data. Periksa koneksi internet Anda lalu coba lagi.</div>
                    <div v-if="genericErrorMessage" class="text-gray-600">{{ genericErrorMessage }}</div>
                </div>
            </Modal>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Select from "@/Components/form/SelectBrng.vue";
import Pagination from "@/Components/common/Pagination.vue";
import Modal from "@/Components/common/Modal.vue";
import { useAuth } from "@/Composables/useAuth";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

const { user, is, can } = useAuth();
defineOptions({ layout: AppLayout });

const breadcrumbs = [{ label: "Penyimpanan" }, { label: "Stok Keluar" }];

const props = defineProps({
    user: { type: String, required: true },
    itemStocks: { type: Object, required: true },
    items: { type: Array, required: true },
    branches: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    sortBy: { type: String, default: "created_at" },
    sortDirection: { type: String, default: "desc" },
    groupBy: { type: String, default: null },
    isSuperadmin: { type: Boolean, default: false },
});

const filtersLocal = ref({
    q: props.filters.q ?? "",
    branch_id: props.filters.branch_id ?? (props.isSuperadmin ? null : user?.employee?.branch_id ?? null),
    item_id: props.filters.item_id ?? null,
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
});

const groupByLocal = ref(props.groupBy ?? "");
const effectiveGroup = computed(() => groupByLocal.value || (props.groupBy ?? ""));
const isGrouped = computed(() => !!effectiveGroup.value);

// Group helper
const collapsedGroups = ref(new Set());
const groupedData = computed(() => {
    if (!isGrouped.value) return {};
    const data = props.itemStocks.data || [];
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
        case "created_at": return row.created_at ?? "Tanggal Keluar";
        case "item": return row.item.name;
        default: return "ungrouped";
    }
}

function groupLabel(key) {
    switch (effectiveGroup.value) {
        case "created_at": return `Tanggal Keluar: ${key}`;
        case "item": return `Nama Barang: ${key}`;
        default: return key;
    }
}

function isCollapsed(key) { return collapsedGroups.value.has(key); }
function toggleGroupCollapse(key) {
    const s = new Set(collapsedGroups.value);
    if (s.has(key)) s.delete(key);
    else s.add(key);
    collapsedGroups.value = s;
}

function fetchStocks({ sortBy = props.sortBy, sortDirection = props.sortDirection } = {}) {
    router.get(route("stock-out.index"), {
        ...filtersLocal.value,
        groupBy: effectiveGroup.value || null,
        sortBy,
        sortDirection,
    }, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onStart: () => { if (typeof window !== "undefined") window.__suppressProgress = true; },
        onFinish: () => { if (typeof window !== "undefined") window.__suppressProgress = false; },
    });
}

let debounceTimer;
watch(() => filtersLocal.value.q, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchStocks({}), 400);
});
watch(() => [filtersLocal.value.branch_id, filtersLocal.value.item_id, filtersLocal.value.date_from, filtersLocal.value.date_to], () => fetchStocks({}));
watch(groupByLocal, () => fetchStocks({}));
watch(() => props.groupBy, (val) => { if (!groupByLocal.value && val) groupByLocal.value = val; });

function changeSort(column) {
    const nextDir = props.sortBy === column && props.sortDirection === "asc" ? "desc" : "asc";
    fetchStocks({ sortBy: column, sortDirection: nextDir });
}

function clearFilters() {
    filtersLocal.value = { q: "", branch_id: null, item_id: null, date_from: "", date_to: "" };
    groupByLocal.value = "";
    fetchStocks({});
}

// ===== Modal & form =====
const isModalOpen = ref(false);
const isEditMode  = ref(false);
const isSaving    = ref(false); // loading state custom

const showSuccess = ref(false);
const successMode = ref('update'); // 'create' | 'update'
const showError   = ref(false);
const genericErrorMessage = ref("");
let lastAction = null; // simpan fungsi terakhir untuk retry

const form = useForm({
    id: null,
    item_id: null,
    amount: null,
    tanggal: new Date().toISOString().split('T')[0],
    note: "",
});

// Simpan oldStockAmount untuk validasi saat edit
const oldStockAmount = ref(0);
const oldItemId = ref(null);

// Computed property untuk mendapatkan stock saat ini berdasarkan item yang dipilih
// Untuk edit: jika item sama, tambahkan kembali amount lama
const currentStock = computed(() => {
    if (!form.item_id) return null;
    const selectedItem = props.items.find(item => item.id === form.item_id);
    if (!selectedItem) return null;

    let stock = selectedItem?.current_stock ?? 0;

    // Jika mode edit dan item yang dipilih sama dengan item lama, tambahkan kembali amount lama
    if (isEditMode.value && oldItemId.value === form.item_id && oldStockAmount.value > 0) {
        stock = stock + oldStockAmount.value;
    }

    return stock;
});

// Computed property untuk mendapatkan unit dari item yang dipilih
const selectedItemUnit = computed(() => {
    if (!form.item_id) return '';
    const selectedItem = props.items.find(item => item.id === form.item_id);
    return selectedItem?.unit?.name ?? '';
});

// Helper function untuk format stock
function formatStock(stock) {
    if (stock === null || stock === undefined) return '0';
    return Number(stock).toLocaleString('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 4
    });
}

// helper error
function setFieldError(field, message) {
    form.setError(field, message);
}
function clearFieldErrors() {
    form.clearErrors();
}

// Validasi client-side
function validateForm() {
    clearFieldErrors();
    let valid = true;

    if (!form.item_id) {
        setFieldError('item_id', 'Barang wajib dipilih.');
        valid = false;
    }
    if (form.amount === null || form.amount === '' || Number(form.amount) <= 0) {
        setFieldError('amount', 'Qty harus lebih besar dari 0.');
        valid = false;
    }
    if (!form.note) {
        setFieldError('note', 'Catatan wajib diisi.');
        valid = false;
    }
    // Validasi qty tidak boleh melebihi stock saat ini
    if (form.item_id && currentStock.value !== null && Number(form.amount) > currentStock.value) {
        setFieldError('amount', `Qty tidak boleh melebihi stock saat ini (${formatStock(currentStock.value)} ${selectedItemUnit.value}).`);
        valid = false;
    }
    if (form.tanggal && isNaN(new Date(form.tanggal).getTime())) {
        setFieldError('tanggal', 'Tanggal tidak valid.');
        valid = false;
    }
    if (form.note && String(form.note).length > 500) {
        setFieldError('note', 'Catatan maksimal 500 karakter.');
        valid = false;
    }
    return valid;
}

function openAddModal() {
    if (!can('stock_out.create')) return;
    isModalOpen.value = true;
    isEditMode.value = false;
    form.reset();
    clearFieldErrors();
    form.tanggal = new Date().toISOString().split('T')[0];
    // Reset oldStockAmount dan oldItemId
    oldStockAmount.value = 0;
    oldItemId.value = null;
}

function openEditModal(row) {
    if (!can('stock_out.edit')) return;
    isModalOpen.value = true;
    isEditMode.value = true;
    clearFieldErrors();
    // Prefill dari baris
    form.id      = row.id;
    form.item_id = row.item_id ?? row.item?.id ?? null;
    form.amount  = row.amount;
    form.tanggal = row.tanggal || new Date().toISOString().split('T')[0];
    form.note    = row.note || "";
    // Simpan oldStockAmount dan oldItemId untuk validasi
    oldStockAmount.value = parseFloat(row.amount) || 0;
    oldItemId.value = row.item_id ?? row.item?.id ?? null;
}

function onCloseModal() {
    if (form.processing || isSaving.value) return; // cegah close saat submit
    isModalOpen.value = false;
    isEditMode.value  = false;
    form.reset();
    clearFieldErrors();
    // Reset oldStockAmount dan oldItemId
    oldStockAmount.value = 0;
    oldItemId.value = null;
}

function doSubmitPut() {
    lastAction = doSubmitPut;
    form.put(route("stock-out.update", form.id), {
        onStart: () => { isSaving.value = true; },
        onSuccess: () => {
            isModalOpen.value = false;
            successMode.value = 'update';
            form.reset();
            fetchStocks({});
        },
        onError: (errors) => {
            if (!errors || Object.keys(errors).length === 0) {
                genericErrorMessage.value = "Tidak dapat terhubung ke server.";
                showError.value = true;
            }
        },
        onFinish: () => { isSaving.value = false; },
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function doSubmitPost() {
    lastAction = doSubmitPost;
    form.post(route("stock-out.store"), {
        onStart: () => { isSaving.value = true; },
        onSuccess: () => {
            isModalOpen.value = false;
            successMode.value = 'create';
            form.reset();
            fetchStocks({});
        },
        onError: (errors) => {
            if (!errors || Object.keys(errors).length === 0) {
                genericErrorMessage.value = "Tidak dapat terhubung ke server.";
                showError.value = true;
            }
        },
        onFinish: () => { isSaving.value = false; },
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function saveStock() {
    if (form.processing || isSaving.value) return; // cegah double submit
    if (!validateForm()) return;

    if (isEditMode.value) {
        if (!can('stock_out.edit')) return;
        doSubmitPut();
    } else {
        if (!can('stock_out.create')) return;
        doSubmitPost();
    }
}

function retryLastAction() {
    showError.value = false;
    if (typeof lastAction === 'function') {
        lastAction();
    }
}

function confirmDelete(id) {
    if (!can('stock_out.delete')) return;
    if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
        router.delete(route("stock-out.destroy", id), {
            onSuccess: () => { fetchStocks({}); },
        });
    }
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
