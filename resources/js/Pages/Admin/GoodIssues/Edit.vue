<template>
    <Head title="Ubah Log Barang" />
    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]">
            <h1 class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200">Ubah Log Pemakaian Barang</h1>
            <Form :form="form" :departments="departments" :materialRequests="materialRequests" :isSuperadmin="isSuperadmin" :isEdit="true" @submit="submit" @cancel="goBack" @items-loaded="handleItemsLoaded" @reset-items="handleResetItems" />
            <div v-if="form.errors.department_id" class="text-xs text-rose-600 mt-1">
                {{ form.errors.department_id }}
            </div>
            <div v-if="form.errors.request_id" class="text-xs text-rose-600 mt-1">
                {{ form.errors.request_id }}
            </div>
            <div v-if="form.errors.date" class="text-xs text-rose-600 mt-1">
                {{ form.errors.date }}
            </div>
            <div v-if="form.errors.requirement" class="text-xs text-rose-600 mt-1">
                {{ form.errors.requirement }}
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="font-semibold text-gray-700 dark:text-gray-200">Daftar Item</div>
            </div>
            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="py-2.5 border-y border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                                <div class="flex items-center justify-center">
                                    <p class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        No.
                                    </p>
                                </div>
                            </th>
                            <th class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2 px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Nama Barang
                                    </p>
                                </div>
                            </th>
                            <th class="py-3 border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                                <div class="flex cursor-pointer items-center justify-center gap-2 px-3">
                                    <p class="font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        Kode Barang
                                    </p>
                                </div>
                            </th>
                            <th class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2 px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Stok Tersedia
                                    </p>
                                </div>
                            </th>
                            <th class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2 px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Jumlah Permintaan
                                    </p>
                                </div>
                            </th>
                            <th class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2 px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Catatan
                                    </p>
                                </div>
                            </th>
                            <th class="border-y border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                                <div class="flex items-center justify-center">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Aksi
                                    </p>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(r, i) in items" :key="r.uid" class="hover:bg-gray-100 cursor-pointer dark:hover:bg-gray-800">
                            <td class="w-4 py-2.5 border-y border-gray-200 dark:border-gray-600">
                                <div class="flex items-center whitespace-nowrap justify-center">
                                    {{ i + 1 }}.
                                </div>
                            </td>
                            <td class="px-5 py-2.5 border border-gray-200 dark:border-gray-600">
                                <SelectBarang
                                    v-model="r.item_id"
                                    :items="getAvailableOptions(i)"
                                    :taggable="true"
                                    label="Cari Barang"
                                    label-key="name"
                                    search-key="name"
                                />
                                <div v-if="itemErrors[i] && itemErrors[i].item_id" class="text-xs text-rose-600 mt-1">
                                    {{ itemErrors[i].item_id }}
                                </div>
                            </td>
                            <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center px-3 whitespace-nowrap justify-center">
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ r.code }}
                                    </p>
                                </div>
                            </td>
                            <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center px-3 whitespace-nowrap justify-center">
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ r.stock }} {{ r.unit?.short_name }}
                                    </p>
                                </div>
                            </td>
                            <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center gap-3 ps-6 whitespace-nowrap">
                                    <input
                                        v-model.number="r.amount"
                                        type="number"
                                        :min="1"
                                        :max="r.stock && !isNaN(Number(r.stock)) ? Number(r.stock) : undefined"
                                        @input="validateAmount(i)"
                                        class="w-24 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                        :class="{
                                            'border-red-500': itemErrors[i] && itemErrors[i].amount
                                        }"
                                    />
                                    {{ r.unit?.short_name }}
                                </div>
                                <div v-if="itemErrors[i] && itemErrors[i].amount" class="text-xs text-rose-600 mt-1">
                                    {{ itemErrors[i].amount }}
                                </div>
                            </td>
                            <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center whitespace-nowrap justify-center">
                                    <input
                                        v-model="r.note"
                                        type="text"
                                        placeholder="Masukkan catatan"
                                        class="w-40 h-10 text-sm rounded placeholder:text-gray-600 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                </div>
                                <div v-if="itemErrors[i] && itemErrors[i].note" class="text-xs text-rose-600 mt-1">
                                    {{ itemErrors[i].note }}
                                </div>
                            </td>
                            <td class="py-2.5 border-y border-gray-200 dark:border-gray-600">
                                <div class="flex justify-center whitespace-nowrap px-4 gap-3">
                                    <button @click="removeItem(i)">
                                        <TrashIcon class="text-red-500" />
                                    </button>
                                </div>
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
            <div v-if="form.errors.items" class="px-6 py-3 text-xs text-rose-600">
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
                @click="submit"
                class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Menyimpan...</span>
                <span v-else>Simpan Perubahan</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import Breadcrumb from '@/Components/common/Breadcrumb.vue'
import Form from './Form.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import SelectBarang from "@/Components/form/SelectBarang.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import { ref, reactive, watch } from "vue";
import { v4 as uuidv4 } from "uuid";
import axios from "axios";

const props = defineProps({
    mr: { type: Object, required: true },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    statusOptions: { type: Array, required: true },
    materialRequests: { type: Array, required: true },
    items: { type: Array, default: () => [] },
    isSuperadmin: { type: Boolean, required: true },
})

const options = ref([]);

function addTag(newTag) {
    const tag = {
        id: Date.now(), // ID unik sementara
        name: newTag,
        code: "-", // default karena data tidak ada
        stock: 0, // default stok
        unit: {},
    };

    options.value.push(tag);

    return tag; // <== penting! harus return object
}

// Computed property to get available options (excluding already selected items)
const getAvailableOptions = (currentRowIndex) => {

    const selectedItemIds = items.value
        .map((r, index) => index !== currentRowIndex ? r.item_id : null)
        .filter(item => item && typeof item === 'object' && item.id)
        .map(item => item.id);

    return options.value.filter(option => !selectedItemIds.includes(option.id));
};

function bindWatcher(row) {
    watch(
        () => row.item_id,
        (newVal) => {
            if (newVal) {
                // Handle both object (selected item) and string (new tag) cases
                if (typeof newVal === 'object' && newVal !== null) {
                    row.code = newVal.code;
                    row.name = newVal.name;
                    row.stock = newVal.stock;
                    row.unit = newVal.unit;
                } else if (typeof newVal === 'string') {
                    // This is a new tag created by SelectBarang
                    const tag = addTag(newVal);
                    row.item_id = tag; // Update to the created tag object
                    row.code = tag.code;
                    row.name = tag.name;
                    row.stock = tag.stock;
                    row.unit = tag.unit;
                }
            } else {
                row.code = "";
                row.name = "";
                row.stock = "";
                row.unit = {};
            }
            // Validate amount when item changes (using row object)
            validateAmount(row);
        },
        { immediate: true }
    );

    // Watch for stock changes to re-validate amount
    watch(
        () => row.stock,
        () => {
            if (row.item_id && row.item_id.id) {
                validateAmount(row);
            }
        }
    );
}

const items = ref([]);
const itemErrors = ref([]);

const addItem = () => {
    const newRow = reactive({
        uid: uuidv4(),
        item_id: null,
        amount: 1,
        code: "",
        name: "",
        stock: "",
        unit: {},
        note: "",
    });

    bindWatcher(newRow);
    items.value.push(newRow);
    itemErrors.value.push({});
};

const removeItem = (index) => {
    items.value.splice(index, 1);
    itemErrors.value.splice(index, 1);
    // Re-validate remaining items to ensure correct validation state
    items.value.forEach((row) => {
        if (row.item_id && row.item_id.id) {
            validateAmount(row);
        }
    });
};

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Pemakaian Barang', href: route('good-issues.index') },
    { label: `Ubah Pemakaian Barang` },
]

const form = useForm({
    request_id: props.mr.reference_request_id ?? null,
    department_id: props.mr.department_id ?? null,
    date: props.mr.request_date ?? '',
    requirement: props.mr.notes ?? '',
    items: []
})

// Watch for department changes to load items by branch
watch(
    () => form.department_id,
    async (newDepartmentId) => {
        if (!newDepartmentId) {
            options.value = [];
            return;
        }

        try {
            const response = await axios.get(route('material-requests.items.by-department'), {
                params: { department_id: newDepartmentId }
            });
            options.value = response.data.items;
        } catch (error) {
            console.error('Error loading items by department:', error);
            options.value = [];
        }
    },
    { immediate: true }
);

// Real-time validation for amount input
function validateAmount(indexOrRow) {
    // Support both index (number) and row object (with uid)
    let index;
    let r;

    if (typeof indexOrRow === 'number') {
        index = indexOrRow;
        r = items.value[index];
    } else {
        // Find index by uid
        r = indexOrRow;
        index = items.value.findIndex(item => item.uid === r.uid);
    }

    if (index === -1 || !r) return;

    if (!itemErrors.value[index]) {
        itemErrors.value[index] = {};
    }

    // Clear previous amount error
    if (itemErrors.value[index].amount) {
        delete itemErrors.value[index].amount;
    }

    // Validate amount
    if (r.item_id && r.item_id.id) {
        const stock = Number(r.stock);
        const amount = Number(r.amount);

        if (r.amount === null || r.amount === "" || isNaN(amount)) {
            itemErrors.value[index].amount = "Jumlah harus berupa angka bulat.";
        } else if (!Number.isInteger(amount)) {
            itemErrors.value[index].amount = "Jumlah harus berupa angka bulat.";
        } else if (amount < 1) {
            itemErrors.value[index].amount = "Jumlah harus minimal 1.";
        } else if (!isNaN(stock) && stock >= 0 && amount > stock) {
            itemErrors.value[index].amount = "Jumlah permintaan tidak boleh lebih dari stok tersedia.";
        }
    }
}

function validateItems() {
    let valid = true;
    itemErrors.value = [];

    // Validasi array items
    if (!items.value.length) {
        form.errors.items = ["Minimal 1 item harus diisi."];
        valid = false;
    } else {
        form.errors.items = null;
    }

    items.value.forEach((r, i) => {
        const err = {};

        // items.*.item_id: required_with:items, exists:items,id
        if (!r.item_id || !r.item_id.id) {
            err.item_id = "Barang harus dipilih.";
            valid = false;
        } else if (
            r.item_id &&
            r.item_id.id &&
            options.value.length > 0 &&
            !options.value.some((opt) => opt.id === r.item_id.id)
        ) {
            err.item_id = "Barang tidak valid.";
            valid = false;
        }

        // items.*.amount: required_with:items, integer, min:1
        if (
            r.amount === null ||
            r.amount === "" ||
            isNaN(r.amount) ||
            !Number.isInteger(Number(r.amount))
        ) {
            err.amount = "Jumlah harus berupa angka bulat.";
            valid = false;
        } else if (Number(r.amount) < 1) {
            err.amount = "Jumlah harus minimal 1.";
            valid = false;
        } else {
            // Validasi jumlah tidak boleh melebihi stok tersedia
            const stock = Number(r.stock);
            const amount = Number(r.amount);

            if (!isNaN(stock) && stock >= 0 && amount > stock) {
                err.amount = "Jumlah permintaan tidak boleh lebih dari stok tersedia.";
                valid = false;
            }
        }

        // items.*.note: nullable, string, max:225
        if (r.note && typeof r.note !== "string") {
            err.note = "Catatan harus berupa teks.";
            valid = false;
        } else if (r.note && r.note.length > 225) {
            err.note = "Catatan maksimal 225 karakter.";
            valid = false;
        }

        itemErrors.value[i] = err;
    });

    return valid;
}

function validateForm() {
    let valid = true;

    // department_id: required, exists:departments,id
    if (!form.department_id) {
        form.errors.department_id = "Departemen harus dipilih.";
        valid = false;
    } else if (
        props.departments &&
        !props.departments.some((d) => d.id == form.department_id)
    ) {
        form.errors.department_id = "Departemen tidak valid.";
        valid = false;
    }

    // date: required, date
    if (!form.date) {
        form.errors.date = "Tanggal harus diisi.";
        valid = false;
    }

    // requirement: nullable, string
    if (form.requirement && typeof form.requirement !== "string") {
        form.errors.requirement = "Keterangan harus berupa teks.";
        valid = false;
    }

    // items: required, array, handled in validateItems
    if (!validateItems()) {
        valid = false;
    }

    return valid;
}

function submit() {
    if (!validateForm()) {
        return;
    }

    // Transform items data for submission
    const itemsData = items.value.map((item) => ({
        uid: item.uid,
        item_id: typeof item.item_id === 'object' ? item.item_id.id : item.item_id,
        amount: item.amount,
        note: item.note,
    }));

    // Prepare complete form data
    const submitData = {
        request_id: form.request_id,
        department_id: form.department_id,
        date: form.date,
        requirement: form.requirement,
        items: itemsData
    };

    // Use router.put directly with complete data
    router.put(route('good-issues.update', props.mr.id), submitData, {
        onStart: () => {
            form.processing = true;
        },
        onFinish: () => {
            form.processing = false;
        },
        onError: (errors) => {
            form.errors = errors;
        }
    });
}

// Handle items loaded from MR reference selection
function handleItemsLoaded(loadedItems) {
    // In edit mode, don't automatically load items unless explicitly triggered
    if (!loadedItems || loadedItems.length === 0) {
        return;
    }

    // Only load items if there are no existing items yet
    if (items.value.length === 0 || items.value.every(i => !i.item_id || !i.item_id.id)) {
        // Update options with loaded items (in case they're not in current department)
        const newOptions = loadedItems.map(item => ({
            id: item.id,
            name: item.name,
            code: item.code,
            stock: item.stock,
            unit: item.unit,
        }));

        // Merge with existing options, avoiding duplicates
        const existingIds = options.value.map(opt => opt.id);
        const uniqueNewOptions = newOptions.filter(opt => !existingIds.includes(opt.id));
        options.value = [...options.value, ...uniqueNewOptions];

        // Clear existing items and add loaded items
        items.value = [];
        itemErrors.value = [];

        loadedItems.forEach((item) => {
            const newRow = reactive({
                uid: uuidv4(),
                item_id: {
                    id: item.id,
                    name: item.name,
                    code: item.code,
                    stock: item.stock,
                    unit: item.unit,
                },
                amount: item.quantity_approved || item.quantity_requested || 1,
                code: item.code,
                name: item.name,
                stock: item.stock,
                unit: item.unit,
                note: item.note || "",
            });

            bindWatcher(newRow);
            items.value.push(newRow);
            itemErrors.value.push({});
        });
    }
}

// Handle reset items (when "Tanpa Referensi" is selected or MR is cleared)
function handleResetItems() {
    // Don't clear items in edit mode
}

// Initialize existing items on mount
if (props.items && props.items.length > 0) {
    items.value = props.items.map((item) => {
        const row = reactive({
            uid: uuidv4(),
            item_id: {
                id: item.item_id,
                name: item.item_name,
                code: item.item_code,
                stock: item.stock ? item.stock.last_stock : 0,
                unit: item.unit,
            },
            amount: item.amount || item.qty || 1,
            code: item.item_code || "",
            name: item.item_name || "",
            stock: item.stock ? item.stock.last_stock : 0,
            unit: item.unit || {},
            note: item.note || item.notes || "",
        });
        bindWatcher(row);
        return row;
    });
    // Initialize itemErrors for existing items
    itemErrors.value = new Array(items.value.length).fill({});

    // Add existing items to options so they can be validated
    const existingOptions = props.items.map(item => ({
        id: item.item_id,
        name: item.item_name,
        code: item.item_code,
        stock: item.stock ? item.stock.last_stock : 0,
        unit: item.unit,
    }));
    options.value = [...options.value, ...existingOptions];
} else {
    // Add default empty row if no items
    addItem();
}

function goBack() {
    router.get(route('good-issues.index'))
}

defineOptions({
    layout: AppLayout,
})
</script>
