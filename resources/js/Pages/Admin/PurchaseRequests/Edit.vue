<template>
    <Head title="Ubah Pengajuan Pembelian" />
    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>
        <div
            class="space-y-5 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]"
        >
            <div class="flex items-center justify-between">
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Ubah Pengajuan Pembelian
                </div>
                <div class="font-bold text-sky-400 md:text-xl px-7">
                    {{ form.request_no }}
                </div>
            </div>
            <Form
                :form="form"
                :materialRequests="materialRequests"
                :departments="departments"
                :employees="employees"
                :user="user"
                @submit="saveRequest"
                @cancel="goBack"
            />
        </div>

        <div
            class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03]"
        >
            <div class="flex items-center justify-between px-6 py-4">
                <div class="font-semibold text-gray-700 dark:text-gray-200">
                    Daftar Item
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 border-y border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
                                    <p
                                        class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        No.
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Nama Barang
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex cursor-pointer items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium whitespace-nowrap text-gray-500 dark:text-gray-400"
                                    >
                                        Kode Barang
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Stok Tersedia
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Jumlah Pengajuan
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Catatan
                                    </p>
                                </div>
                            </th>
                            <th
                                class="border-y border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Aksi
                                    </p>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(r, i) in requests"
                            :key="r.uid"
                            class="hover:bg-gray-100 cursor-pointer dark:hover:bg-gray-800"
                        >
                            <td
                                class="w-4 py-2.5 border-y border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap justify-center"
                                >
                                    {{ i + 1 }}.
                                </div>
                            </td>
                            <td
                                class="px-5 py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <Select
                                    v-model="r.item_id"
                                    :items="getFilteredOptions(i)"
                                    :taggable="true"
                                    :search-key="'name'"
                                    :label-key="'name'"
                                    label="Cari Barang"
                                    @tag="addTag"
                                    class="w-full"
                                >
                                    <template #item="{ item }">
                                        <div class="flex flex-col py-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ item.name }}</span>
                                                <span v-if="isItemSelected(item.id, i)" class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                                                    Dipilih
                                                </span>
                                            </div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ item.code || '-' }}</span>
                                        </div>
                                    </template>
                                    <template #no-options>
                                        <div class="text-sm text-gray-500 p-2">
                                            Semua barang telah dipilih di baris lain
                                        </div>
                                    </template>
                                </Select>
                                <!-- Pesan error validasi untuk item_id -->
                                <div v-if="form.errors[`requests.${i}.item_id`]" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors[`requests.${i}.item_id`][0] }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center px-3 whitespace-nowrap justify-center"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ r.code }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center px-3 whitespace-nowrap justify-center"
                                >
                                    <div v-if="r.isLoadingStock" class="flex items-center gap-1.5 text-xs text-blue-600 dark:text-blue-400">
                                        <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Memuat...</span>
                                    </div>
                                    <button
                                        v-else-if="r.item_id"
                                        type="button"
                                        @click="openStockBreakdown(r)"
                                        class="px-2.5 py-1 text-xs font-semibold text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-900/30 rounded border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition flex items-center gap-1 mx-auto cursor-pointer"
                                        title="Klik untuk detail stok per gudang"
                                    >
                                        <span>{{ r.stock }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <span v-else class="text-gray-400">-</span>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center gap-3 ps-6 whitespace-nowrap"
                                >
                                    <input
                                        v-model="r.request"
                                        type="number"
                                        min="0"
                                        class="w-24 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                    {{ r.unit?.short_name }}
                                </div>
                                <!-- Pesan error validasi untuk request (jumlah pengajuan) -->
                                <div v-if="form.errors[`requests.${i}.request`]" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors[`requests.${i}.request`] }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap justify-center"
                                >
                                    <input
                                        v-model="r.note"
                                        type="text"
                                        placeholder="Masukkan catatan"
                                        class="w-40 h-10 text-sm rounded placeholder:text-gray-600 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                </div>
                                <!-- Pesan error validasi untuk note (catatan) -->
                                <div v-if="form.errors[`requests.${i}.note`]" class="text-xs text-rose-600 mt-1">
                                    {{ form.errors[`requests.${i}.note`] }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border-y border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center whitespace-nowrap px-4 gap-3"
                                >
                                    <button @click="removeRequest(i)">
                                        <TrashIcon class="text-red-500" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <button
                    type="button"
                    @click="addRequest"
                    class="m-4 px-3 py-2 text-white bg-green-500 hover:bg-green-600 rounded whitespace-nowrap"
                >
                    + Tambah Item
                </button>
            </div>
            <div
                v-if="form.errors.requests"
                class="px-6 py-3 text-xs text-rose-600"
            >
                {{ form.errors.requests[0] }}
            </div>
        </div>

        <div class="flex items-center gap-5 justify-end">
            <button
                type="button"
                @click="goBack"
                class="inline-flex items-center rounded border bg-gray-400 border-gray-300 px-5 py-2 text-sm text-white hover:text-gray-800 hover:bg-gray-300 dark:border-gray-700 dark:text-gray-300"
            >
                Batal
            </button>
            <button
                @click="saveRequest"
                class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
            >
                Simpan Perubahan
            </button>
        </div>
    </div>

    <!-- Modal Detail Stok Per Gudang -->
    <Modal
        :show="showStockModal"
        @close="showStockModal = false"
        title="Detail Stok Per Gudang"
        close-text="Tutup"
        max-width="lg"
    >
        <div class="space-y-4">
            <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Nama Barang:</span>
                    <span class="font-semibold text-gray-950 dark:text-white">{{ selectedStockDetails.itemName }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Kode Barang:</span>
                    <span class="font-mono text-gray-950 dark:text-white">{{ selectedStockDetails.itemCode }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 dark:border-gray-800 pt-1.5 mt-1.5">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Total Stok Keseluruhan:</span>
                    <span class="font-bold text-blue-600 dark:text-blue-400">{{ selectedStockDetails.totalStock }} {{ selectedStockDetails.unit }}</span>
                </div>
            </div>

            <div class="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-500 dark:text-gray-400">Gudang / Cabang</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-500 dark:text-gray-400">Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-800">
                        <tr v-if="selectedStockDetails.breakdown.length === 0">
                            <td colspan="2" class="px-4 py-3 text-center text-gray-500">Tidak ada data breakdown</td>
                        </tr>
                        <tr
                            v-else
                            v-for="b in selectedStockDetails.breakdown"
                            :key="b.branch_id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
                        >
                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">{{ b.branch_name }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-950 dark:text-white">
                                <span :class="b.stock > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
                                    {{ b.stock }} {{ selectedStockDetails.unit }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Form from "./Form.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import Select from "@/Components/form/SelectBarang.vue";
import Modal from "@/Components/common/Modal.vue";
import { Head, router, useForm, Link } from "@inertiajs/vue3";
import { ref, reactive, watch, computed } from "vue";
import { nextTick } from "vue";
import { v4 as uuidv4 } from "uuid";
import axios from "axios";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Pengajuan Pembelian", href: route("purchase-requests.index") },
    { label: "Ubah Pengajuan" },
];

const props = defineProps({
    pr: { type: Object, required: true },
    requestNo: String,
    user: Object,
    materialRequests: { type: Array, required: true },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    items: { type: Array, default: () => [] },
    sortBy: String,
    sortDirection: String,
});

console.log(props.pr);

// State for Stock Breakdown Modal
const showStockModal = ref(false);
const selectedStockDetails = ref({
    itemName: "",
    itemCode: "",
    totalStock: 0,
    breakdown: [],
    unit: ""
});

const openStockBreakdown = (row) => {
    if (!row.item_id) return;
    
    selectedStockDetails.value = {
        itemName: row.name || row.item_id.name,
        itemCode: row.code || row.item_id.code || "-",
        totalStock: row.stock || 0,
        breakdown: row.stockBreakdown || [],
        unit: row.unit?.short_name || ""
    };
    showStockModal.value = true;
};

const options = ref(
    props.items.map((i) => ({
        id: i.id,
        name: i.name,
        code: i.code,
        stock: i.stock,
        unit: i.unit,
        branch_id: i.branch_id,
    }))
);

// Force reactivity when items are selected/deselected
const forceUpdate = ref(0);
const triggerUpdate = () => {
    forceUpdate.value++;
};

const selectedDepartment = computed(() =>
    props.departments.find((d) => d.id === form.department_id)
);
const selectedBranchId = computed(() => selectedDepartment.value?.branch_id ?? null);

// Function to get filtered options for a specific row (excluding already selected items and mismatched branch)
const getFilteredOptions = (currentRowIndex) => {
    // Use forceUpdate to ensure reactivity
    forceUpdate.value;

    const selectedItemIds = requests.value
        .map((row, index) => {
            // Skip the current row and only include rows with selected items
            if (index !== currentRowIndex && row.item_id?.id) {
                return row.item_id.id;
            }
            return null;
        })
        .filter(id => id !== null);

    return options.value.filter(option => {
        const notSelected = !selectedItemIds.includes(option.id);
        const branchMatch = selectedBranchId.value ? option.branch_id === selectedBranchId.value : true;
        return notSelected && branchMatch;
    });
};

// Check if an item is already selected in another row
const isItemSelected = (itemId, currentRowIndex) => {
    return requests.value.some((row, index) =>
        index !== currentRowIndex && row.item_id?.id === itemId
    );
};

function addTag(newTag) {
    const tag = {
        id: Date.now(), // ID unik sementara
        name: newTag,
        code: "-", // default karena data tidak ada
        stock: 0, // default stok
        branch_id: selectedBranchId.value ?? null,
    };

    options.value.push(tag);

    return tag; // <== penting! harus return object
}

function bindWatcher(row) {
    watch(
        () => row.item_id,
        async (newVal) => {
            console.log('bindWatcher - newVal:', newVal);
            if (newVal) {
                row.code = newVal.code;
                row.name = newVal.name;
                row.unit = newVal.unit;
                
                // Fetch overall stock breakdown
                row.isLoadingStock = true;
                row.stockBreakdown = [];
                row.stock = 0;
                
                try {
                    const response = await axios.get(route('items.stock-breakdown'), {
                        params: { item_id: newVal.id }
                    });
                    if (response.data) {
                        row.stock = response.data.total_stock;
                        row.stockBreakdown = response.data.breakdown;
                    }
                } catch (error) {
                    console.error('Error fetching stock breakdown:', error);
                    row.stock = (newVal.stock && newVal.stock.last_stock) ? newVal.stock.last_stock : 0;
                } finally {
                    row.isLoadingStock = false;
                }
            } else {
                row.code = "";
                row.name = "";
                row.stock = 0;
                row.unit = {};
                row.stockBreakdown = [];
                row.isLoadingStock = false;
            }
        },
        { immediate: true }
    );
}

const requests = ref([]);

// Initialize requests with existing data
if (props.pr.items && props.pr.items.length > 0) {
    requests.value = props.pr.items.map((it) => {
        const row = reactive({
            uid: uuidv4(),
            item_id: options.value.find((o) => o.id === it.item_id) ?? null,
            code: it.code,
            name: it.name,
            stock: it.stock ? it.stock.last_stock : 0,
            unit: it.unit,
            request: it.quantity_requested,
            note: it.note,
            isLoadingStock: false,
            stockBreakdown: [],
        });
        bindWatcher(row);
        return row;
    });
}

const addRequest = () => {
    const newRow = reactive({
        uid: uuidv4(),
        item_id: null,
        request: 1,
        code: "",
        name: "",
        stock: 0,
        unit: {},
        note: "",
        isLoadingStock: false,
        stockBreakdown: [],
    });
    bindWatcher(newRow);
    requests.value.push(newRow);
    triggerUpdate(); // Trigger update when a new row is added
};

const removeRequest = (index) => {
    requests.value.splice(index, 1);
    triggerUpdate(); // Trigger update when a row is removed
};

const form = useForm({
    request_no: props.pr.request_no,
    requested_at: props.pr.requested_at,
    department_id: props.pr.department_id,
    employee_id: props.pr.employee_id,
    request_id: props.pr.request_id,
    requirement: props.pr.requirement,
    requests: [],
});

watch(
    () => form.request_id,
    async (newVal) => {
        if (!newVal) {
            requests.value = [];
            return;
        }

        const mr = props.materialRequests.find((m) => m.id === newVal);
        if (mr) {
            isSettingFromMR.value = true;
            form.department_id = mr.department_id ?? "";
            form.requirement = mr.requirement ?? "";
            requests.value = mr.items.map((it) => {
                const row = reactive({
                    uid: uuidv4(),
                    item_id:
                        options.value.find((o) => o.id === it.item_id) ?? null,
                    code: it.code,
                    name: it.name,
                    stock: it.stock ? it.stock.last_stock : 0,
                    unit: it.unit,
                    request: it.quantity_requested,
                    note: it.note,
                });
                bindWatcher(row);
                return row;
            });
            await nextTick();
            isSettingFromMR.value = false;
        }
    }
);

// Watch for changes in item selections to trigger option updates
watch(requests, () => {
    triggerUpdate();
}, { deep: true });

const isSettingFromMR = ref(false);
// Reset selected items when department changes to enforce branch-based filtering
watch(
    () => form.department_id,
    () => {
        if (isSettingFromMR.value) {
            // Skip clearing when department changes due to MR selection
            triggerUpdate();
            return;
        }
        requests.value.forEach((row) => {
            row.item_id = null;
            row.code = "";
            row.name = "";
            row.stock = 0;
            row.unit = {};
        });
        triggerUpdate();
    }
);

function saveRequest() {
    form.requests = requests.value.map((r) => ({
        uid: r.uid,
        item_id: r.item_id?.id ?? null, // ambil id
        request: r.request,
        note: r.note ?? null,
    }));

    form.put(route("purchase-requests.update", props.pr.id));
}

function goBack() {
    router.get(route("purchase-requests.index"));
}
</script>
