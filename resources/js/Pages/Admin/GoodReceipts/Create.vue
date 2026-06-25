<template>
    <Head :title="selectedType === 'pembelian' ? 'Buat Penerimaan Barang (Pembelian)' : 'Buat Penerimaan Barang (Pemindahan)'" />
    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <!-- Gabung Card Tipe Transaksi dan Form -->
        <div
            class="space-y-5 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]"
        >
            <div class="flex items-center justify-between">
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300 m-5"
                >
                    Form Penerimaan Barang
                </div>
                <div class="font-bold text-sky-400 md:text-xl px-7">
                    {{ form.order_no }}
                </div>
            </div>

            <!-- Type Selection & Form dalam satu card -->
            <div class="space-y-5">
                <div class="space-y-2 m-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sumber Penerimaan
                    </label>
                    <select
                        v-model="selectedType"
                        class="h-10 px-3 w-full border border-gray-300 rounded-md text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                    >
                        <option value="pembelian">Pembelian</option>
                        <option value="pemindahan">Pemindahan</option>
                    </select>
                </div>

                <!-- Form Component based on type selection, didalam card yang sama! -->
                <div v-if="selectedType === 'pembelian'">
                    <CreateReceiptForm
                        :form="form"
                        :orders="order"
                        :employees="employees"
                        :user="user"
                        @items-loaded="handleItemsLoaded"
                        @attachments-changed="handleAttachmentsChanged"
                        :isSuperadmin="isSuperadmin"
                        :isEdit="false"
                    />
                </div>
                <div v-if="selectedType === 'pemindahan'">
                    <CreateTranferForm
                        :form="form"
                        :transfers="transfer"
                        :employees="employees"
                        :user="user"
                        @items-loaded="handleItemsLoaded"
                        @attachments-changed="handleAttachmentsChanged"
                        :isSuperadmin="isSuperadmin"
                        :isEdit="false"
                    />
                </div>
            </div>
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
                <table class="min-w-full text-sm border border-gray-200 dark:border-gray-600 rounded-lg">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400 w-10">
                                No.
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                Nama Barang
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                Jml Dikirim
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                Jml Diterima
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                Catatan Diterima
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                Kekurangan
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800 text-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400 w-16">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(r, i) in requests"
                            :key="r.uid"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        >
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 text-center align-middle">
                                {{ i + 1 }}
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle min-w-[180px]">
                                <SelectBarang
                                    v-model="r.item_id"
                                    :items="getAvailableOptions(i)"
                                    :taggable="true"
                                    label="Cari Barang"
                                    label-key="name"
                                    search-key="name"
                                    :readonly="r.is_preloaded"
                                    :disabled="r.is_preloaded"
                                />
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle min-w-[120px]">
                                <div class="flex items-center gap-2 justify-center">
                                    <input
                                        v-model="r.quantity_received"
                                        type="text"
                                        inputmode="decimal"
                                        class="w-20 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 text-center readonly:bg-gray-100 dark:readonly:bg-gray-700"
                                        :readonly="r.is_preloaded"
                                        :disabled="r.is_preloaded"
                                    />
                                    <span>{{ r.unit?.short_name }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle min-w-[140px]">
                                <div class="flex items-center gap-2 justify-center">
                                    <input
                                        v-model="r.quantity_actually_received"
                                        type="text"
                                        inputmode="decimal"
                                        placeholder="0"
                                        class="w-20 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 text-center"
                                    />
                                    <span>{{ r.unit?.short_name }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle min-w-[180px]">
                                <div class="relative">
                                    <input
                                        v-model="r.received_note"
                                        type="text"
                                        :placeholder="isReceiptNoteRequired(r) ? 'Catatan penerimaan (wajib diisi)' : 'Catatan penerimaan'"
                                        :class="[
                                            'w-full h-9 px-2 text-sm rounded placeholder:text-gray-600 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90',
                                            isReceiptNoteRequired(r) && (!r.received_note || r.received_note.trim() === '')
                                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                                : 'border-gray-300 dark:border-gray-700'
                                        ]"
                                    />
                                    <span
                                        v-if="isReceiptNoteRequired(r)"
                                        class="absolute -top-1 -right-1 text-red-500 text-xs font-bold"
                                    >
                                        *
                                    </span>
                                </div>
                            </td>
                            <!-- Kekurangan: muncul jika qty diterima < qty dikirim -->
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle min-w-[220px]">
                                <div v-if="hasShortage(r)" class="bg-amber-50/50 dark:bg-amber-950/10 border border-dashed border-amber-300 rounded-lg p-2 space-y-2">
                                    <div class="text-xs font-semibold text-amber-700 dark:text-amber-400">
                                        ⚠️ Rincian Kekurangan ({{ getShortageMax(r) }} {{ r.unit?.short_name || 'pcs' }})
                                    </div>
                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[11px] text-gray-600 dark:text-gray-400 font-medium">Rusak / Cacat:</span>
                                            <div class="flex items-center gap-1">
                                                <input
                                                    v-model="r.shortage_rusak"
                                                    type="text"
                                                    inputmode="decimal"
                                                    @input="updateRusakShortage(r, r.shortage_rusak)"
                                                    class="w-14 h-7 text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 text-center focus:ring-1 focus:ring-blue-500"
                                                />
                                                <span class="text-[10px] text-gray-500">{{ r.unit?.short_name || 'pcs' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-[11px] text-gray-600 dark:text-gray-400 font-medium">Hilang:</span>
                                            <div class="flex items-center gap-1">
                                                <input
                                                    v-model="r.shortage_hilang"
                                                    type="text"
                                                    inputmode="decimal"
                                                    @input="updateHilangShortage(r, r.shortage_hilang)"
                                                    class="w-14 h-7 text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 text-center focus:ring-1 focus:ring-blue-500"
                                                />
                                                <span class="text-[10px] text-gray-500">{{ r.unit?.short_name || 'pcs' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span v-else class="text-xs text-gray-400">-</span>
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 text-center align-middle">
                                <button @click="removeRequest(i)" class="hover:bg-red-50 dark:hover:bg-red-900 p-1 rounded transition-colors">
                                    <TrashIcon class="text-red-500" />
                                </button>
                            </td>
                        </tr>

                        <button
                            type="button"
                            @click="addRequest"
                            class="m-4 px-3 py-2 text-white bg-green-500 hover:bg-green-600 rounded whitespace-nowrap"
                        >
                            + Tambah Item
                        </button>
                    </tbody>
                </table>
            </div>
            <div
                v-if="form.errors.requests"
                class="px-6 py-3 text-xs text-rose-600"
            >
                {{ form.errors.requests[0] }}
            </div>
            <div
                v-if="!isFormValid()"
                class="px-6 py-3 text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/20"
            >
                ⚠️ Catatan penerimaan wajib diisi untuk item yang jumlah diterimanya berbeda dengan jumlah barang, dan semua kekurangan barang harus dialokasikan ke 'Rusak' atau 'Hilang'.
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
                @click="openConfirmModal"
                :disabled="!isFormValid()"
                class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
            >
                Simpan Penerimaan
            </button>
        </div>

        <!-- Confirmation Modal -->
        <div
            v-if="showConfirmModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="closeConfirmModal"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Konfirmasi Simpan
                            </h3>
                        </div>
                    </div>
                    <div class="mb-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Apakah Anda yakin ingin menyimpan penerimaan barang ini?
                        </p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeConfirmModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-500"
                        >
                            Batal
                        </button>
                        <button
                            @click="confirmSave"
                            :disabled="form.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                            <span v-else>Ya, Simpan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Form from "./Form0.vue";
import CreateReceiptForm from "./CreateReceiptForm.vue";
import CreateTranferForm from "./CreateTranferForm.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import SelectBarang from "@/Components/form/SelectBarang.vue";
import { Head, router, useForm, Link } from "@inertiajs/vue3";
import { ref, reactive, watch, onMounted, onUnmounted, computed } from "vue";
import { v4 as uuidv4 } from "uuid";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Penerimaan Barang", href: route("good-receipts.index") },
    { label: "Buat Penerimaan" },
];

// Reactive variable for type selection
const selectedType = ref('pembelian');

// Confirmation modal state
const showConfirmModal = ref(false);

const props = defineProps({
    orderNo: String,
    user: Object,
    employees: { type: Array, required: true },
    items: { type: Array, default: () => [] },
    sortBy: String,
    sortDirection: String,
    order: { type: Array, required: true },
    transfer: { type: Array, required: true },
    departments: { type: Array, required: true },
    isSuperadmin: { type: Boolean, required: true },
});

const options = ref(
    props.items.map((i) => ({
        id: i.id,
        name: i.name,
        code: i.code,
        unit: i.unit,
        branch_id: i.branch_id,
    }))
);

const targetBranchId = computed(() => {
    if (selectedType.value === 'pembelian') {
        const order = props.order.find(o => o.id == form.order_id);
        return order ? order.branch_id : null;
    } else {
        const transfer = props.transfer.find(t => t.id == form.transfer_id);
        return transfer ? transfer.to_branch_id : null;
    }
});

// Computed property to get available options (excluding already selected items and mismatched branch)
const getAvailableOptions = (currentRowIndex) => {
    if (!targetBranchId.value) {
        return [];
    }

    const selectedItemIds = requests.value
        .map((r, index) => index !== currentRowIndex ? r.item_id : null)
        .filter(item => item && typeof item === 'object' && item.id)
        .map(item => item.id);

    return options.value.filter(option => {
        const notSelected = !selectedItemIds.includes(option.id);
        const branchMatch = option.branch_id == targetBranchId.value;
        return notSelected && branchMatch;
    });
};

// Computed property to check if receipt note is required for each item
const isReceiptNoteRequired = (item) => {
    return item.quantity_actually_received !== item.quantity_received;
};

// Computed property to check if all required receipt notes are filled
const isFormValid = () => {
    return requests.value.every(item => {
        if (isReceiptNoteRequired(item)) {
            if (!item.received_note || item.received_note.trim() === '') {
                return false;
            }
        }
        if (hasShortage(item)) {
            const total = getShortageMax(item);
            const allocated = Number(item.shortage_rusak || 0) + Number(item.shortage_hilang || 0);
            if (allocated !== total) {
                return false;
            }
        }
        return true;
    });
};

function addTag(newTag) {
    const tag = {
        id: Date.now(), // ID unik sementara
        name: newTag,
        code: "-", // default karena data tidak ada
    };

    options.value.push(tag);

    return tag; // <== penting! harus return object
}

function bindWatcher(row) {
    watch(
        () => row.item_id,
        (newVal) => {
            if (newVal) {
                // Handle both object (selected item) and string (new tag) cases
                if (typeof newVal === 'object' && newVal !== null) {
                    row.code = newVal.code;
                    row.name = newVal.name;
                    row.unit = newVal.unit;
                } else if (typeof newVal === 'string') {
                    // This is a new tag created by SelectBarang
                    const tag = addTag(newVal);
                    row.item_id = tag; // Update to the created tag object
                    row.code = tag.code;
                    row.name = tag.name;
                    row.unit = tag.unit;
                }
            } else {
                row.code = "";
                row.name = "";
                row.unit = {};
            }
        },
        { immediate: true }
    );
}

const requests = ref([]);
const attachmentFiles = ref([]);

function handleAttachmentsChanged(files) {
    attachmentFiles.value = files;
}

const hasShortage = (r) => {
    let qty = r.quantity_actually_received;
    let sent = r.quantity_received;
    if (typeof qty === 'string') qty = qty.replace(',', '.');
    if (typeof sent === 'string') sent = sent.replace(',', '.');
    return (qty ? Number(qty) : 0) < (sent ? Number(sent) : 0);
};

const getShortageMax = (r) => {
    let qty = r.quantity_actually_received;
    let sent = r.quantity_received;
    if (typeof qty === 'string') qty = qty.replace(',', '.');
    if (typeof sent === 'string') sent = sent.replace(',', '.');
    return (sent ? Number(sent) : 0) - (qty ? Number(qty) : 0);
};

const updateRusakShortage = (r, val) => {
    const total = getShortageMax(r);
    let valStr = String(val);
    if (valStr.includes(',')) valStr = valStr.replace(',', '.');
    let valNum = Number(valStr) || 0;
    if (valNum < 0) valNum = 0;
    if (valNum > total) valNum = total;
    
    r.shortage_rusak = valNum;
    r.shortage_hilang = total - valNum;
};

const updateHilangShortage = (r, val) => {
    const total = getShortageMax(r);
    let valStr = String(val);
    if (valStr.includes(',')) valStr = valStr.replace(',', '.');
    let valNum = Number(valStr) || 0;
    if (valNum < 0) valNum = 0;
    if (valNum > total) valNum = total;
    
    r.shortage_hilang = valNum;
    r.shortage_rusak = total - valNum;
};

// Watch quantities changes to auto-allocate shortages when quantity changes
watch(
    () => requests.value.map(r => ({ received: r.quantity_received, actually: r.quantity_actually_received })),
    (newVal) => {
        requests.value.forEach((r) => {
            if (hasShortage(r)) {
                const total = getShortageMax(r);
                if (r.shortage_rusak === undefined) r.shortage_rusak = 0;
                if (r.shortage_hilang === undefined) r.shortage_hilang = 0;
                
                const allocated = Number(r.shortage_rusak || 0) + Number(r.shortage_hilang || 0);
                if (allocated !== total) {
                    // Default allocate everything to rusak
                    r.shortage_rusak = total;
                    r.shortage_hilang = 0;
                }
            } else {
                r.shortage_rusak = 0;
                r.shortage_hilang = 0;
            }
        });
    },
    { deep: true }
);

const addRequest = () => {
    const newRow = reactive({
        uid: uuidv4(),
        item_id: null,
        quantity_received: 1,
        quantity_actually_received: 0,
        code: "",
        name: "",
        unit: {},
        note: "",
        received_note: "",
        shortage_rusak: 0,
        shortage_hilang: 0,
        is_preloaded: false,
    });
    bindWatcher(newRow);
    requests.value.push(newRow);
};

const removeRequest = (index) => {
    requests.value.splice(index, 1);
};

const form = useForm({
    employee_id: props.user?.employee?.id,
    order_id: null,
    transfer_id: null,
    source: "",
    note: "",
    received_at: new Date().toISOString().slice(0, 10),
    items: [],
});

// Handle items loaded from form components
function handleItemsLoaded(items) {
    if (!items || items.length === 0) {
        requests.value = [];
        return;
    }

    requests.value = items.map((item) => {
        const row = reactive({
            uid: uuidv4(),
            item_id:
                options.value.find((o) => o.id == item.item_id) ??
                null,
            code: item.code,
            name: item.name,
            unit: item.unit,
            quantity_received: item.quantity_requested ? parseFloat(item.quantity_requested) : 0,
            quantity_actually_received: item.quantity_requested ? parseFloat(item.quantity_requested) : 0,
            note: item.note,
            received_note: "",
            shortage_rusak: 0,
            shortage_hilang: 0,
            is_preloaded: true,
        });
        bindWatcher(row);
        return row;
    });
}

// Watch for order_id changes (for pembelian) - now handled by form component
watch(
    () => form.order_id,
    (newVal) => {
        if (!newVal || selectedType.value !== 'pembelian') {
            requests.value = [];
            return;
        }
        // Items will be loaded by the form component via handleItemsLoaded
    }
);

// Watch for transfer_id changes (for pemindahan) - now handled by form component
watch(
    () => form.transfer_id,
    (newVal) => {
        if (!newVal || selectedType.value !== 'pemindahan') {
            requests.value = [];
            return;
        }
        // Items will be loaded by the form component via handleItemsLoaded
    }
);

// Modal functions
function openConfirmModal() {
    // Validate form before showing modal
    if (!isFormValid()) {
        alert('Catatan penerimaan wajib diisi untuk item yang jumlah diterimanya berbeda dengan jumlah barang.');
        return;
    }
    showConfirmModal.value = true;
}

function closeConfirmModal() {
    showConfirmModal.value = false;
}

function confirmSave() {
    if (!isFormValid()) {
        alert('Catatan penerimaan wajib diisi untuk item yang jumlah diterimanya berbeda dengan jumlah barang.');
        return;
    }

    form.items = requests.value.map((r) => {
        let qr = r.quantity_received;
        let qar = r.quantity_actually_received;
        if (typeof qr === 'string') qr = qr.replace(',', '.');
        if (typeof qar === 'string') qar = qar.replace(',', '.');
        const numQr = qr ? Number(qr) : 0;
        const numQar = qar ? Number(qar) : 0;

        let r_rusak = r.shortage_rusak;
        let r_hilang = r.shortage_hilang;
        if (typeof r_rusak === 'string') r_rusak = r_rusak.replace(',', '.');
        if (typeof r_hilang === 'string') r_hilang = r_hilang.replace(',', '.');
        const numRusak = r_rusak ? Number(r_rusak) : 0;
        const numHilang = r_hilang ? Number(r_hilang) : 0;

        const itemPayload = {
            uid: r.uid,
            item_id: r.item_id?.id ?? null,
            quantity_received: numQr,
            quantity_actually_received: numQar,
            note: r.note ?? null,
            received_note: r.received_note ?? null,
        };

        if (hasShortage(r)) {
            const list = [];
            if (numRusak > 0) {
                list.push({
                    reason: 'rusak',
                    quantity: numRusak,
                    description: r.received_note || ''
                });
            }
            if (numHilang > 0) {
                list.push({
                    reason: 'hilang',
                    quantity: numHilang,
                    description: r.received_note || ''
                });
            }
            itemPayload.shortages = list;
        } else {
            itemPayload.shortages = null;
        }

        return itemPayload;
    });

    if (selectedType.value === 'pembelian') {
        form.transfer_id = null;
    } else {
        form.order_id = null;
    }

    // Use forceFormData to send multiple file attachments
    form.transform((data) => {
        const payload = { ...data };
        if (attachmentFiles.value.length > 0) {
            payload.attachments = attachmentFiles.value;
        }
        return payload;
    }).post(route("good-receipts.store"), {
        forceFormData: true,
        onSuccess: () => { closeConfirmModal(); },
        onError: () => { closeConfirmModal(); }
    });
}

function saveRequest() {
    // This function is now replaced by openConfirmModal
    openConfirmModal();
}

function goBack() {
    router.get(route("good-receipts.index"));
}

// Keyboard event handler for ESC key
function handleKeydown(event) {
    if (event.key === 'Escape' && showConfirmModal.value) {
        closeConfirmModal();
    }
}

// Add keyboard event listeners
onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>
