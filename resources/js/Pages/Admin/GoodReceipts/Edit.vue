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
                                Catatan Diterima
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
                                />
                            </td>
                            <td class="py-2.5 px-3 align-middle border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-2 justify-center">
                                    <input
                                        v-model="r.quantity_received"
                                        type="number"
                                        min="0"
                                        class="w-24 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                    <span class="text-gray-700 dark:text-gray-300">{{ r.unit?.short_name }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 align-middle border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-2 justify-center">
                                    <input
                                        v-model="r.quantity_actually_received"
                                        type="number"
                                        min="0"
                                        placeholder="0"
                                        class="w-24 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                    <span class="text-gray-700 dark:text-gray-300">{{ r.unit?.short_name }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 align-middle border-b border-gray-200 dark:border-gray-700">
                                <input
                                    v-model="r.note"
                                    type="text"
                                    placeholder="Masukkan catatan"
                                    class="w-40 h-10 text-sm rounded placeholder:text-gray-600 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
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
import { ref, reactive, watch, onMounted } from "vue";
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
    }))
);

// Computed property to get available options (excluding already selected items)
const getAvailableOptions = (currentRowIndex) => {
    const selectedItemIds = items.value
        .map((r, index) => index !== currentRowIndex ? r.item_id : null)
        .filter(item => item && typeof item === 'object' && item.id)
        .map(item => item.id);

    return options.value.filter(option => !selectedItemIds.includes(option.id));
};

// Computed property to check if received note is required for each item
const isReceivedNoteRequired = (item) => {
    return item.quantity_received !== item.quantity_actually_received;
};

// Computed property to check if all required fields are filled
const isFormValid = () => {
    return items.value.every(item => {
        if (isReceivedNoteRequired(item)) {
            return item.received_note && item.received_note.trim() !== '';
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
function handleItemsLoaded(items) {
    if (!items || items.length === 0) {
        items.value = [];
        return;
    }

    items.value = items.map((item) => {
        const row = reactive({
            uid: uuidv4(),
            item_id:
                options.value.find((o) => o.id === item.item_id) ??
                null,
            code: item.code,
            name: item.name,
            unit: item.unit,
            quantity_received: item.quantity_received,
            quantity_actually_received: item.quantity_received, // Default to received quantity
            note: item.note,
            received_note: "",
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
            const row = reactive({
                uid: uuidv4(),
                item_id: options.value.find((o) => o.id === item.item_id) ?? null,
                code: item.item?.code || '',
                name: item.item?.name || '',
                unit: item.item?.unit || {},
                quantity_received: item.quantity_received,
                quantity_actually_received: item.quantity_actually_received || item.quantity_received,
                note: item.note || '',
                received_note: item.received_note || '',
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

    form.items = items.value.map((r) => ({
        uid: r.uid,
        item_id: r.item_id?.id ?? null,
        quantity_received: r.quantity_received,
        quantity_actually_received: r.quantity_actually_received,
        note: r.note ?? null,
        received_note: r.received_note ?? null,
    }));

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
