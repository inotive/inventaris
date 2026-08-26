<template>
    <Head :title="selectedType === 'pembelian' ? 'Edit Penerimaan Barang (Pembelian)' : 'Edit Penerimaan Barang (Pemindahan)'" />
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
                    Form Ubah Penerimaan Barang
                </div>
                <div class="font-bold text-sky-400 md:text-xl px-7">
                    {{ form.source }}
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
                        :disabled="true"
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
                        :isSuperadmin="isSuperadmin"
                        :isEdit="true"
                    />
                </div>
                <div v-if="selectedType === 'pemindahan'">
                    <CreateTranferForm
                        :form="form"
                        :transfers="transfer"
                        :employees="employees"
                        :user="user"
                        @items-loaded="handleItemsLoaded"
                        :isSuperadmin="isSuperadmin"
                        :isEdit="true"
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

            <div class="overflow-x-auto" data-simplebar>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="py-2.5 px-3 text-center font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                No.
                            </th>
                            <th class="py-2.5 px-3 text-center font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Nama Barang
                            </th>
                            <th class="py-2.5 px-3 text-center font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Jumlah barang
                            </th>
                            <th class="py-2.5 px-3 text-center font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Jumlah Diterima
                            </th>
                            <th class="py-2.5 px-3 text-center font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Catatan Penerimaan
                            </th>
                            <th class="py-2.5 px-3 text-center font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Kekurangan
                            </th>
                            <th class="py-2.5 px-3 text-center font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-transparent">
                        <tr
                            v-for="(r, i) in items"
                            :key="r.uid"
                            class="hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        >
                            <td class="py-2.5 px-3 text-center align-middle border-b border-gray-200 dark:border-gray-700">
                                {{ i + 1 }}
                            </td>
                            <td class="py-2.5 px-3 align-middle border-b border-gray-200 dark:border-gray-700">
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
                            <td class="py-2.5 px-3 align-middle border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-2 justify-center">
                                    <input
                                        v-model="r.quantity_received"
                                        type="text"
                                        inputmode="decimal"
                                        class="w-24 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 text-center readonly:bg-gray-100 dark:readonly:bg-gray-700"
                                        :readonly="r.is_preloaded"
                                        :disabled="r.is_preloaded"
                                    />
                                    <span class="text-gray-700 dark:text-gray-300">{{ r.unit?.short_name }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 align-middle border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-2 justify-center">
                                    <input
                                        v-model="r.quantity_actually_received"
                                        type="text"
                                        inputmode="decimal"
                                        placeholder="0"
                                        class="w-24 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 text-center"
                                    />
                                    <span class="text-gray-700 dark:text-gray-300">{{ r.unit?.short_name }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-700 align-middle min-w-[180px]">
                                <div class="relative">
                                    <input
                                        v-model="r.received_note"
                                        type="text"
                                        :placeholder="isReceivedNoteRequired(r) ? 'Catatan penerimaan (wajib diisi)' : 'Catatan penerimaan'"
                                        :class="[
                                            'w-full h-10 px-2 text-sm rounded placeholder:text-gray-600 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90',
                                            isReceivedNoteRequired(r) && (!r.received_note || r.received_note.trim() === '')
                                                ? 'border-red-500 focus:border-red-500 focus:ring-red-500'
                                                : 'border-gray-300 dark:border-gray-700'
                                        ]"
                                    />
                                    <span
                                        v-if="isReceivedNoteRequired(r)"
                                        class="absolute -top-1 -right-1 text-red-500 text-xs font-bold"
                                    >
                                        *
                                    </span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-700 align-middle min-w-[220px]">
                                <div v-if="hasShortage(r)" class="bg-amber-50/50 dark:bg-amber-950/10 border border-dashed border-amber-300 rounded-lg p-2 space-y-2">
                                    <div class="text-xs font-semibold text-amber-700 dark:text-amber-400">
                                        ⚠️ Rincian Kekurangan ({{ getShortageMax(r) }} {{ r.unit?.short_name || r.unit?.name || '' }})
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
                                                <span class="text-[10px] text-gray-500">{{ r.unit?.short_name || r.unit?.name || '' }}</span>
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
                                                <span class="text-[10px] text-gray-500">{{ r.unit?.short_name || r.unit?.name || '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span v-else class="text-xs text-gray-400">-</span>
                            </td>
                            <td class="py-2.5 px-3 text-center align-middle border-b border-gray-200 dark:border-gray-700">
                                <button @click="removeItem(i)" class="focus:outline-none">
                                    <TrashIcon class="text-red-500" />
                                </button>
                            </td>
                        </tr>

                        <button
                            type="button"
                            @click="addItem"
                            class="m-4 px-3 py-2 text-white bg-green-500 hover:bg-green-600 rounded whitespace-nowrap"
                        >
                            + Tambah Item
                        </button>
                    </tbody>
                </table>
            </div>
            <div
                v-if="form.errors.items"
                class="px-6 py-3 text-xs text-rose-600"
            >
                {{ form.errors.items[0] }}
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
                @click="updateRequest"
                :disabled="!isFormValid()"
                :class="[
                    'inline-flex items-center rounded px-4 py-2 text-sm font-medium text-white disabled:opacity-60',
                    isFormValid()
                        ? 'bg-blue-600 hover:bg-blue-700'
                        : 'bg-gray-400 cursor-not-allowed'
                ]"
            >
                Update Penerimaan
            </button>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import CreateReceiptForm from "./CreateReceiptForm.vue";
import CreateTranferForm from "./CreateTranferForm.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import SelectBarang from "@/Components/form/SelectBarang.vue";
import { Head, router, useForm, Link } from "@inertiajs/vue3";
import { ref, reactive, watch, onMounted, computed } from "vue";
import { v4 as uuidv4 } from "uuid";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Penerimaan Barang", href: route("good-receipts.index") },
    { label: "Edit Penerimaan" },
];

// Reactive variable for type selection
const selectedType = ref('pembelian');

const props = defineProps({
    goodReceipt: { type: Object, required: true },
    user: Object,
    employees: { type: Array, required: true },
    items: { type: Array, default: () => [] },
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

    const selectedItemIds = items.value
        .map((r, index) => index !== currentRowIndex ? r.item_id : null)
        .filter(item => item && typeof item === 'object' && item.id)
        .map(item => item.id);

    return options.value.filter(option => {
        const notSelected = !selectedItemIds.includes(option.id);
        const branchMatch = option.branch_id == targetBranchId.value;
        return notSelected && branchMatch;
    });
};

// Computed property to check if received note is required for each item
const isReceivedNoteRequired = (item) => {
    let qr = item.quantity_received;
    let qar = item.quantity_actually_received;
    if (typeof qr === 'string') qr = qr.replace(',', '.');
    if (typeof qar === 'string') qar = qar.replace(',', '.');
    return Number(qr) !== Number(qar);
};

const hasShortage = (item) => {
    let qty = item.quantity_actually_received;
    let sent = item.quantity_received;
    if (typeof qty === 'string') qty = qty.replace(',', '.');
    if (typeof sent === 'string') sent = sent.replace(',', '.');
    return (qty ? Number(qty) : 0) < (sent ? Number(sent) : 0);
};

const getShortageMax = (item) => {
    let qty = item.quantity_actually_received;
    let sent = item.quantity_received;
    if (typeof qty === 'string') qty = qty.replace(',', '.');
    if (typeof sent === 'string') sent = sent.replace(',', '.');
    return (sent ? Number(sent) : 0) - (qty ? Number(qty) : 0);
};

const updateRusakShortage = (item, val) => {
    const total = getShortageMax(item);
    let valStr = String(val);
    if (valStr.includes(',')) valStr = valStr.replace(',', '.');
    let valNum = Number(valStr) || 0;
    if (valNum < 0) valNum = 0;
    if (valNum > total) valNum = total;
    
    item.shortage_rusak = valNum;
    item.shortage_hilang = total - valNum;
};

const updateHilangShortage = (item, val) => {
    const total = getShortageMax(item);
    let valStr = String(val);
    if (valStr.includes(',')) valStr = valStr.replace(',', '.');
    let valNum = Number(valStr) || 0;
    if (valNum < 0) valNum = 0;
    if (valNum > total) valNum = total;
    
    item.shortage_hilang = valNum;
    item.shortage_rusak = total - valNum;
};

// Watch quantities changes to auto-allocate shortages when quantity changes
watch(
    () => items.value.map(item => ({ received: item.quantity_received, actually: item.quantity_actually_received })),
    (newVal) => {
        items.value.forEach((item) => {
            if (hasShortage(item)) {
                const total = getShortageMax(item);
                if (item.shortage_rusak === undefined) item.shortage_rusak = 0;
                if (item.shortage_hilang === undefined) item.shortage_hilang = 0;
                
                const allocated = Number(item.shortage_rusak || 0) + Number(item.shortage_hilang || 0);
                if (allocated !== total) {
                    // Default allocate everything to rusak
                    item.shortage_rusak = total;
                    item.shortage_hilang = 0;
                }
            } else {
                item.shortage_rusak = 0;
                item.shortage_hilang = 0;
            }
        });
    },
    { deep: true }
);

// Computed property to check if all required fields are filled
const isFormValid = () => {
    return items.value.every(item => {
        if (isReceivedNoteRequired(item)) {
            const hasNote = item.received_note && item.received_note.trim() !== '';
            let r_rusak = item.shortage_rusak;
            let r_hilang = item.shortage_hilang;
            if (typeof r_rusak === 'string') r_rusak = r_rusak.replace(',', '.');
            if (typeof r_hilang === 'string') r_hilang = r_hilang.replace(',', '.');
            const allocated = Number(r_rusak || 0) + Number(r_hilang || 0);
            const total = getShortageMax(item);
            return hasNote && allocated === total;
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

const items = ref([]);

const addItem = () => {
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
    items.value.push(newRow);
};

const removeItem = (index) => {
    items.value.splice(index, 1);
};

const form = useForm({
    employee_id: props.goodReceipt.employee_id,
    order_id: props.goodReceipt.order_id,
    transfer_id: props.goodReceipt.transfer_id,
    source: props.goodReceipt.source,
    note: props.goodReceipt.note,
    received_at: props.goodReceipt.received_at ? new Date(props.goodReceipt.received_at).toISOString().slice(0, 10) : new Date().toISOString().slice(0, 10),
    items: [],
});

// Handle items loaded from form components
function handleItemsLoaded(itemsLoaded) {
    if (!itemsLoaded || itemsLoaded.length === 0) {
        items.value = [];
        return;
    }

    items.value = itemsLoaded.map((item) => {
        const row = reactive({
            uid: uuidv4(),
            item_id:
                options.value.find((o) => o.id == item.item_id) ??
                null,
            code: item.code,
            name: item.name,
            unit: item.unit,
            quantity_received: item.quantity_received ? parseFloat(item.quantity_received) : 0,
            quantity_actually_received: item.quantity_received ? parseFloat(item.quantity_received) : 0, // Default to received quantity
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

// Initialize form data on mount
onMounted(() => {
    // Determine type based on source
    selectedType.value = props.goodReceipt.source === 'Pembelian' ? 'pembelian' : 'pemindahan';

    // Load existing items
    if (props.goodReceipt.items && props.goodReceipt.items.length > 0) {
        items.value = props.goodReceipt.items.map((item) => {
            const itemDisposals = props.goodReceipt.disposals || [];
            const rusak = itemDisposals
                .filter(d => d.item_id === item.item_id && d.reason === 'rusak')
                .reduce((sum, d) => sum + Number(d.quantity), 0);
            const hilang = itemDisposals
                .filter(d => d.item_id === item.item_id && d.reason === 'hilang')
                .reduce((sum, d) => sum + Number(d.quantity), 0);

            const row = reactive({
                uid: uuidv4(),
                item_id: options.value.find((o) => o.id == item.item_id) ?? null,
                code: item.item?.code || '',
                name: item.item?.name || '',
                unit: item.item?.unit || {},
                quantity_received: item.quantity_received ? parseFloat(item.quantity_received) : 0,
                quantity_actually_received: item.quantity_actually_received ? parseFloat(item.quantity_actually_received) : (item.quantity_received ? parseFloat(item.quantity_received) : 0),
                note: item.note || '',
                received_note: item.received_note || '',
                shortage_rusak: parseFloat(rusak),
                shortage_hilang: parseFloat(hilang),
                is_preloaded: true,
            });
            bindWatcher(row);
            return row;
        });
    }
});

// Watch for order_id changes (for pembelian) - now handled by form component
watch(
    () => form.order_id,
    (newVal) => {
        if (!newVal || selectedType.value !== 'pembelian') {
            items.value = [];
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
            items.value = [];
            return;
        }
        // Items will be loaded by the form component via handleItemsLoaded
    }
);

function updateRequest() {
    // Validate form before submitting
    if (!isFormValid()) {
        // Find items that need received notes
        const invalidItems = items.value.filter(item =>
            isReceivedNoteRequired(item) && (!item.received_note || item.received_note.trim() === '')
        );

        if (invalidItems.length > 0) {
            alert('Mohon lengkapi catatan penerimaan untuk item yang jumlah diterima tidak sama dengan jumlah barang.');
            return;
        }
    }

    form.items = items.value.map((r) => {
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

    // Clear the opposite ID based on selected type
    if (selectedType.value === 'pembelian') {
        form.transfer_id = null;
    } else {
        form.order_id = null;
    }

    form.put(route("good-receipts.update", props.goodReceipt.id));
}

function goBack() {
    router.get(route("good-receipts.index"));
}
</script>
