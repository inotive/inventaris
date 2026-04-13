<template>
    <Head title="Buat Pengajuan Pembelian" />
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
                    Buat Pengajuan Pembelian
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
                :isSuperadmin="isSuperadmin"
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
                Buat Pengajuan
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
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import { Head, router, useForm, Link } from "@inertiajs/vue3";
import { ref, reactive, watch, computed } from "vue";
import { nextTick } from "vue";
import { v4 as uuidv4 } from "uuid";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Pengajuan Pembelian", href: route("purchase-requests.index") },
    { label: "Buat Pengajuan" },
];

const props = defineProps({
    requestNo: String,
    user: Object,
    materialRequests: { type: Array, required: true },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    items: { type: Array, default: () => [] },
    sortBy: String,
    sortDirection: String,
    isSuperadmin: { type: Boolean, required: true },
});

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
        (newVal) => {
            console.log('bindWatcher - newVal:', newVal);
            if (newVal) {
                row.code = newVal.code;
                row.name = newVal.name;
                row.stock = newVal.stock ? newVal.stock.last_stock : 0;
                row.unit = newVal.unit;
            } else {
                row.code = "";
                row.name = "";
                row.stock = 0;
                row.unit = {};
            }
        },
        { immediate: true }
    );
}

const requests = ref([]);

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
    request_no: props.requestNo,
    requested_at: new Date().toISOString().slice(0, 10),
    department_id: props.user?.employee?.department?.id,
    employee_id: props.user?.employee?.id,
    request_id: null,
    requirement: "",
    requests: [],
});

const isSettingFromMR = ref(false);
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

    form.post(route("purchase-requests.store"));
}

function goBack() {
    router.get(route("purchase-requests.index"));
}
</script>
