<template>
    <Head title="Tambah Permintaan Barang" />
    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>
        <div
            class="space-y-5 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03] relative z-1"
        >
            <h1 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                Buat Log Pemakaian Barang
            </h1>
            <Form
                :form="form"
                :departments="departments"
                :materialRequests="materialRequests"
                :isSuperadmin="isSuperadmin"
                @cancel="goBack"
                @items-loaded="handleItemsLoaded"
                @reset-items="handleResetItems"
            />
            <div v-if="formErrors.department_id" class="text-xs text-rose-600 mt-1">
                {{ formErrors.department_id }}
            </div>
            <div v-if="formErrors.request_id" class="text-xs text-rose-600 mt-1">
                {{ formErrors.request_id }}
            </div>
            <div v-if="formErrors.date" class="text-xs text-rose-600 mt-1">
                {{ formErrors.date }}
            </div>
            <div v-if="formErrors.requirement" class="text-xs text-rose-600 mt-1">
                {{ formErrors.requirement }}
            </div>
        </div>

        <div
            class="rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03] relative"
        >
            <div class="flex items-center justify-between px-6 py-4">
                <div class="font-semibold text-gray-700 dark:text-gray-200">
                    Daftar Item
                </div>
            </div>

            <div class="overflow-auto relative" data-simplebar>
                <table class="min-w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 text-center font-medium text-gray-500 dark:text-gray-400 w-10">
                                No.
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 text-center font-medium text-gray-500 dark:text-gray-400 min-w-[170px]">
                                Nama Barang
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 text-center font-medium text-gray-500 dark:text-gray-400 min-w-[110px]">
                                Kode Barang
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 text-center font-medium text-gray-500 dark:text-gray-400 min-w-[120px]">
                                Stok Tersedia
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 text-center font-medium text-gray-500 dark:text-gray-400 min-w-[140px]">
                                Jumlah Permintaan
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 text-center font-medium text-gray-500 dark:text-gray-400 min-w-[140px]">
                                Catatan
                            </th>
                            <th class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 text-center font-medium text-gray-500 dark:text-gray-400 w-16">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(r, i) in items"
                            :key="r.uid"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        >
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 text-center align-middle">
                                {{ i + 1 }}
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle">
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
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle text-center">
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ r.code }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle text-center">
                                <span class="text-gray-500 dark:text-gray-400">
                                    {{ r.stock }} {{ r.unit?.short_name }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle">
                                <div class="flex items-center gap-2 justify-center w-full">
                                    <input
                                        v-model.number="r.amount"
                                        type="number"
                                        :min="1"
                                        :max="r.stock && !isNaN(Number(r.stock)) ? Number(r.stock) : undefined"
                                        @input="validateAmount(i)"
                                        class="w-20 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                        :class="{
                                            'border-red-500': itemErrors[i] && itemErrors[i].amount
                                        }"
                                    />
                                    <span class="text-gray-500 dark:text-gray-400">{{ r.unit?.short_name }}</span>
                                </div>
                                <div v-if="itemErrors[i] && itemErrors[i].amount" class="text-xs text-rose-600 mt-1">
                                    {{ itemErrors[i].amount }}
                                </div>
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle">
                                <input
                                    v-model="r.note"
                                    type="text"
                                    placeholder="Masukkan catatan"
                                    class="w-full h-10 text-sm rounded placeholder:text-gray-600 border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                                <div v-if="itemErrors[i] && itemErrors[i].note" class="text-xs text-rose-600 mt-1">
                                    {{ itemErrors[i].note }}
                                </div>
                            </td>
                            <td class="py-2.5 px-3 border-b border-gray-200 dark:border-gray-600 align-middle text-center">
                                <button @click="removeItem(i)" type="button" class="flex items-center justify-center mx-auto">
                                    <TrashIcon class="text-red-500" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div
                v-if="form.errors.items"
                class="px-6 py-3 text-xs text-rose-600"
            >
                {{ form.errors.items[0] }}
            </div>

            <!-- Add Item Button - Fixed Position -->
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                <button
                    type="button"
                    @click="addItem"
                    class="px-4 py-2 text-white bg-green-500 hover:bg-green-600 rounded whitespace-nowrap transition-colors duration-200"
                >
                    + Tambah Item
                </button>
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
                @click="showConfirmation"
                class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60"
            >
                Simpan
            </button>
        </div>

        <!-- Confirmation Modal -->
        <div
            v-if="showConfirmModal"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    aria-hidden="true"
                    @click="showConfirmModal = false"
                ></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10 dark:bg-blue-900"
                            >
                                <svg
                                    class="h-6 w-6 text-blue-600 dark:text-blue-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                                    />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3
                                    class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100"
                                    id="modal-title"
                                >
                                    Konfirmasi Simpan
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Apakah Anda yakin ingin menyimpan data pemakaian barang ini?
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-700">
                        <button
                            type="button"
                            @click="confirmSave"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Ya, Simpan
                        </button>
                        <button
                            type="button"
                            @click="showConfirmModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-500"
                        >
                            Batal
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
import Form from "./Form.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import SelectBarang from "@/Components/form/SelectBarang.vue";
import { Head, router, useForm, Link } from "@inertiajs/vue3";
import { ref, reactive, watch } from "vue";
import { v4 as uuidv4 } from "uuid";
import axios from "axios";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Pemakaian Barang", href: route("good-issues.index") },
    { label: "Buat Log" },
];

const props = defineProps({
    user: Object,
    departments: { type: Array, required: true },
    materialRequests: { type: Array, required: true },
    items: { type: Array, default: () => [] },
    sortBy: String,
    sortDirection: String,
    isSuperadmin: { type: Boolean, required: true },
});

const options = ref([]);
const showConfirmModal = ref(false);

function addTag(newTag) {
    const tag = {
        id: Date.now(), // ID unik sementara
        name: newTag,
        code: "-", // default karena data tidak ada
        stock: 0, // default stok
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

const items = ref([
    reactive({
        uid: uuidv4(),
        item_id: null,
        amount: 1,
        code: "",
        name: "",
        stock: "",
        unit: {},
        note: "",
    }),
]);

const itemErrors = ref([]);
const formErrors = ref({});

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

items.value.forEach((row, idx) => {
    bindWatcher(row);
    // Ensure itemErrors has the same length
    if (!itemErrors.value[idx]) itemErrors.value[idx] = {};
});

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

// Handle items loaded from MR selection
function handleItemsLoaded(loadedItems) {
    if (!loadedItems || loadedItems.length === 0) {
        // Keep the default empty row if no items loaded
        if (items.value.length === 0) {
            addItem();
        }
        return;
    }

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

// Handle reset items when "Tanpa Referensi" is selected
function handleResetItems() {
    // Clear all items and reset to default empty row
    items.value = [];
    itemErrors.value = [];

    // Add one empty row
    addItem();
}

const form = useForm({
    department_id: props.user?.employee?.department_id,
    request_id: null,
    date: new Date().toISOString().slice(0, 10),
    requirement: "",
    items: [
        {
            uid: uuidv4(),
            item_id: null,
            amount: 1,
            note: "",
        },
    ],
});

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
        formErrors.value.items = ["Minimal 1 item harus diisi."];
        valid = false;
    } else {
        formErrors.value.items = null;
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
    formErrors.value = {};

    // department_id: required, exists:departments,id
    if (!form.department_id) {
        formErrors.value.department_id = "Departemen harus dipilih.";
        valid = false;
    } else if (
        props.departments &&
        !props.departments.some((d) => d.id == form.department_id)
    ) {
        formErrors.value.department_id = "Departemen tidak valid.";
        valid = false;
    }

    // requirement: nullable, string
    if (form.requirement && typeof form.requirement !== "string") {
        formErrors.value.requirement = "Keterangan harus berupa teks.";
        valid = false;
    }

    // items: required, array, handled in validateItems
    if (!validateItems()) {
        valid = false;
    }

    return valid;
}

function showConfirmation() {
    if (!validateForm()) {
        return;
    }
    showConfirmModal.value = true;
}

function confirmSave() {
    showConfirmModal.value = false;
    saveLog();
}

function saveLog() {
    form.items = items.value.map((r) => ({
        uid: r.uid,
        item_id: r.item_id?.id ?? null, // karena Select pakai object
        amount: r.amount,
        note: r.note ?? null,
    }));

    form.post(route("good-issues.store"));
}

function goBack() {
    router.get(route("good-issues.index"));
}
</script>
