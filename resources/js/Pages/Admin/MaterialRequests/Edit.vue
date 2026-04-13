<template>
    <Head title="Ubah Permintaan Barang" />
    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>


        <div
            class="space-y-5 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]"
        >
            <h1 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                Ubah Permintaan Barang
            </h1>

            <!-- Display general form errors -->
            <div v-if="form.errors && Object.keys(form.errors).length > 0" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md dark:bg-red-900/20 dark:border-red-800">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            Terjadi kesalahan validasi
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <ul class="list-disc list-inside space-y-1">
                                <li v-for="(error, field) in form.errors" :key="field">
                                    <span v-if="typeof error === 'string'">{{ error }}</span>
                                    <span v-else-if="Array.isArray(error)">{{ error[0] }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <Form
                :form="form"
                :departments="departments"
                :user="user"
                :employees="employees"
                :isEdit="true"
                @submit="submit"
                @cancel="goBack"
                @department-changed="onDepartmentChanged"
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
                                        Jumlah Permintaan
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
                                class="px-3 py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <Select
                                    v-model="r.item_id"
                                    :items="getFilteredOptions(i)"
                                    :taggable="true"
                                    :search-key="'name'"
                                    :label-key="'name'"
                                    label="Cari Barang"
                                    @tag="addTag"
                                    :class="[
                                        'w-full',
                                        form.errors[`requests.${i}.item_id`] ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                                    ]"
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
                                <div v-if="form.errors[`requests.${i}.item_id`]" class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ Array.isArray(form.errors[`requests.${i}.item_id`]) ? form.errors[`requests.${i}.item_id`][0] : form.errors[`requests.${i}.item_id`] }}</span>
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
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ r.stock }} {{ r.unit?.short_name }}
                                    </p>
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
                                        :class="[
                                            'w-24 rounded dark:bg-gray-800 dark:text-white/90',
                                            form.errors[`requests.${i}.request`]
                                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                                                : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                                        ]"
                                    />

                                    {{ r.unit?.short_name }}
                                </div>
                                <!-- Pesan error validasi untuk request (jumlah permintaan) -->
                                <div v-if="form.errors[`requests.${i}.request`]" class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ Array.isArray(form.errors[`requests.${i}.request`]) ? form.errors[`requests.${i}.request`][0] : form.errors[`requests.${i}.request`] }}</span>
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

                        <button
                            type="button"
                            @click="addRequest"
                            class="m-4 px-3 py-2 text-white bg-green-500 hover:bg-green-600 rounded whitespace-nowrap"
                        >
                            + Tambah Request
                        </button>
                    </tbody>
                </table>
            </div>
            <!-- Display requests array errors -->
            <div
                v-if="form.errors.requests"
                class="px-6 py-3 bg-red-50 border-t border-red-200 dark:bg-red-900/20 dark:border-red-800"
            >
                <div class="flex items-center gap-2 text-sm text-rose-600 dark:text-rose-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ Array.isArray(form.errors.requests) ? form.errors.requests[0] : form.errors.requests }}</span>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md dark:bg-green-900/20 dark:border-green-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ $page.props.flash.success }}
                    </p>
                </div>
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
                :disabled="form.processing"
                class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
            >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Form from "./Form.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import Select from "@/Components/form/SelectBarang.vue";
// import Select from "@/Components/form/Select.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import { Head, router, useForm, Link } from "@inertiajs/vue3";
import { ref, reactive, watch, computed, onMounted } from "vue";
import { v4 as uuidv4 } from "uuid";
import axios from "axios";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Permintaan Barang", href: route("material-requests.index") },
    { label: "Ubah Permintaan" },
];

const props = defineProps({
    mr: Object,
    items: { type: Array, default: () => [] },
    allItems: { type: Array, default: () => [] },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    user: { type: Object, required: true },
    statusOptions: { type: Array, default: () => [] },
});

const options = ref(
    props.allItems.map((i) => ({
        id: i.id,
        name: i.name,
        code: i.code,
        stock: i.stock,
        unit: i.unit,
    }))
);

console.log('Initial options from props:', options.value);

// Function to fetch items by department
const fetchItemsByDepartment = async (departmentId) => {
    if (!departmentId) {
        options.value = [];
        return;
    }

    try {
        const response = await axios.get(route('material-requests.items.by-department'), {
            params: { department_id: departmentId },
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (response.data && response.data.items) {
            options.value = response.data.items.map(item => ({
                id: item.id,
                name: item.name,
                code: item.code,
                stock: item.stock,
                unit: item.unit,
            }));
            console.log('Fetched items:', options.value);
        } else {
            options.value = [];
        }
    } catch (error) {
        console.error('Error fetching items by department:', error);
        options.value = [];
    }
};

// Function to get filtered options for a specific row (excluding already selected items)
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

    return options.value.filter(option => !selectedItemIds.includes(option.id));
};

// Reactive computed property to track selected items
const selectedItemIds = computed(() => {
    return requests.value
        .map(row => row.item_id?.id)
        .filter(id => id !== null);
});

// Force reactivity when items are selected/deselected
const forceUpdate = ref(0);
const triggerUpdate = () => {
    forceUpdate.value++;
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
    };

    options.value.push(tag);

    return tag; // <== penting! harus return object
}

const requests = ref(
    props.items?.map(item => {
        const itemData = options.value.find(opt => opt.id === item.item_id);
        return reactive({
            uid: uuidv4(),
            item_id: itemData || null,
            request: item.qty,
            code: item.item_code || "",
            name: item.item_name || "",
            stock: itemData?.stock || 0,
            unit: itemData?.unit || null,
            note: item.note || "",
        });
    }) || [
        reactive({
            uid: uuidv4(),
            item_id: null,
            request: 1,
            code: "",
            name: "",
            stock: "",
        }),
    ]
);

const addRequest = () => {
    const newRow = reactive({
        uid: uuidv4(),
        item_id: null,
        request: 1,
        code: "",
        name: "",
        stock: "",
        unit: null,
        note: "",
    });

    requests.value.push(newRow);

    // Bind watcher to the new row
    bindWatcher(newRow);

    triggerUpdate(); // Trigger update when a new row is added
};

// Function to bind watcher to a row
const bindWatcher = (row) => {
    watch(
        () => row.item_id,
        (newVal) => {
            console.log('bindWatcher - newVal:', newVal);
            console.log('bindWatcher - row before update:', { code: row.code, name: row.name, stock: row.stock });

            if (newVal) {
                row.code = newVal.code;
                row.name = newVal.name;
                row.stock = newVal.stock || 0;
                row.unit = newVal.unit;
                console.log('bindWatcher - row after update:', { code: row.code, name: row.name, stock: row.stock });
            } else {
                row.code = "";
                row.name = "";
                row.stock = "";
                row.unit = {};
            }
        },
        { immediate: true } // Run immediately to handle initial values
    );
};

// Bind watchers to existing rows
requests.value.forEach(bindWatcher);

// Watch for changes in item selections to trigger option updates
watch(requests, () => {
    triggerUpdate();
}, { deep: true });

// Function to handle department changes
const onDepartmentChanged = (departmentId) => {
    console.log('Department changed to:', departmentId);
    fetchItemsByDepartment(departmentId);

    // Clear all existing requests when department changes
    requests.value = [reactive({
        uid: uuidv4(),
        item_id: null,
        request: 1,
        code: "",
        name: "",
        stock: "",
    })];

    // Bind watcher to the new row
    bindWatcher(requests.value[0]);

    // Update form requests as well
    form.requests = [{
        uid: uuidv4(),
        item_id: null,
        request: 1,
        note: "",
    }];

    triggerUpdate();
};

const removeRequest = (index) => {
    requests.value.splice(index, 1);
    triggerUpdate(); // Trigger update when a row is removed
};

// Check if user is Superadmin
const isSuperadmin = props.user?.roles?.[0]?.name === 'Superadmin';

const form = useForm({
    request_no: props.mr?.request_no || '',
    requested_at: props.mr?.requested_at || new Date().toISOString().slice(0, 10),
    department_id: props.mr?.department_id,
    employee_id: props.mr?.employee_id,
    requirement: props.mr?.requirement || "",
    requests: props.items?.map(item => ({
        uid: uuidv4(),
        item_id: item.item_id,
        request: item.qty,
        note: item.note || "",
    })) || [
        {
            uid: uuidv4(),
            item_id: null,
            request: 1,
            note: "",
        },
    ],
});

// Watch for department changes in form
watch(() => form.department_id, (newDepartmentId, oldDepartmentId) => {
    if (newDepartmentId !== oldDepartmentId) {
        onDepartmentChanged(newDepartmentId);
    }
});

// Initialize items based on user's department on mount
onMounted(() => {
    // If user has a department, fetch items for that department
    if (form.department_id) {
        fetchItemsByDepartment(form.department_id);
    }
});

function saveRequest() {
    // Clear previous errors
    form.clearErrors();

    // Validate that there's at least one request
    if (!requests.value || requests.value.length === 0) {
        form.setError('requests', 'Minimal harus ada satu item permintaan');
        return;
    }

    // Check for duplicate items
    const itemIds = requests.value.map(r => r.item_id?.id).filter(Boolean);
    const duplicateItems = itemIds.filter((id, index) => itemIds.indexOf(id) !== index);

    if (duplicateItems.length > 0) {
        form.setError('requests', 'Tidak boleh ada item yang duplikat');
        return;
    }

    // Validate that all requests have required fields
    const hasInvalidRequests = requests.value.some((r, i) => {
        if (!r.item_id || !r.item_id.id) {
            form.setError(`requests.${i}.item_id`, 'Pilih barang yang diminta');
            return true;
        }
        if (!r.request || r.request <= 0) {
            form.setError(`requests.${i}.request`, 'Jumlah permintaan harus lebih dari 0');
            return true;
        }
        return false;
    });

    if (hasInvalidRequests) {
        return;
    }

    form.requests = requests.value.map((r, i) => ({
        uid: r.uid,
        item_id: r.item_id?.id ?? null, // karena Select pakai object
        request: r.request,
        note: r.note ?? null,
    }));

    form.put(route("material-requests.update", props.mr?.id));
}

// Local per-item inputs (static rows for all items)
const rowsQty = reactive({});
const rowsNotes = reactive({});
props.items.forEach((it) => {
    rowsQty[it.id] = 0;
    rowsNotes[it.id] = "";
});

function submit() {
    // Build items from static table: only include rows with qty > 0
    form.transform((data) => ({
        ...data,
        items: props.items
            .map((it) => ({
                item_id: it.id,
                qty: Number(rowsQty[it.id] || 0),
                notes: rowsNotes[it.id] || "",
            }))
            .filter((r) => r.qty > 0),
    })).post(route("material-requests.store"), {
        onFinish: () => form.transform((d) => d),
    });
}

function goBack() {
    router.get(route("material-requests.index"));
}

const errors = form.errors;
</script>
