<template>

    <Head title="Tambah Permintaan Barang" />
    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>
        <div
            class="space-y-5 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]">
            <h1 class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                Buat Pemindahan Barang
            </h1>

            <!-- Display general form errors -->
            <div v-if="form.errors && Object.keys(form.errors).length > 0"
                class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md dark:bg-red-900/20 dark:border-red-800">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
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

            <Form :form="form" :branches="branches" :employees="employees" :user="user" :roles="roles"
                @cancel="goBack" />
        </div>

        <div
            class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03]">
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
                                class="py-2.5 border-y border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                                <div class="flex items-center justify-center">
                                    <p
                                        class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        No.
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
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
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2 px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Stok Tersedia
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2 px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Jumlah Pengiriman
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border cursor-pointer border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
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
                        <tr v-for="(r, i) in items" :key="r.uid"
                            class="hover:bg-gray-100 cursor-pointer dark:hover:bg-gray-800">
                            <td class="w-4 py-2.5 border-y border-gray-200 dark:border-gray-600">
                                <div class="flex items-center whitespace-nowrap justify-center">
                                    {{ i + 1 }}.
                                </div>
                            </td>
                            <td class="px-5 py-2.5 border border-gray-200 dark:border-gray-600">
                                <Select v-model="r.item_id" :items="getAvailableOptions(i)" :taggable="true" :search-key="'name'"
                                    :label-key="'name'" label="Cari Barang" @tag="addTag" :class="[
                                        'w-full',
                                        form.errors[`items.${i}.item_id`] ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                                    ]">
                                    <template #item="{ item }">
                                        <div class="flex flex-col py-1">
                                            <span class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{
                                                item.name }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ item.code || '-'
                                                }}</span>
                                        </div>
                                    </template>
                                </Select>
                                <!-- Pesan error validasi untuk item_id -->
                                <div v-if="form.errors[`items.${i}.item_id`]"
                                    class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ Array.isArray(form.errors[`items.${i}.item_id`]) ?
                                        form.errors[`items.${i}.item_id`][0] :
                                        form.errors[`items.${i}.item_id`] }}</span>
                                </div>

                                <!-- <div class="card flex justify-center">
                                    <Select
                                        filter
                                        v-model="r.item_id"
                                        :options="items"
                                        showClear
                                        optionLabel="name"
                                        placeholder="Select a City"
                                        class="w-full md:w-72"
                                    >
                                        <template #value="slotProps">
                                            <div
                                                v-if="slotProps.value"
                                                class="flex items-center"
                                            >
                                                <div>
                                                    {{ slotProps.value.name }}
                                                </div>
                                            </div>
                                            <span v-else>
                                                {{ slotProps.placeholder }}
                                            </span>
                                        </template>
                                        <template #option="slotProps">
                                            <div class="flex items-center">
                                                <div>
                                                    {{ slotProps.option.name }}
                                                </div>
                                            </div>
                                        </template>
                                    </Select>
                                </div> -->

                                <!-- <Select v-model="r.item_id" :items="items" /> -->

                                <!-- <select v-model="r.item_id" name="" id="">
                                    <option v-for="item in items" >{{ item.code }}</option>
                                </select> -->
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
                                    <input v-model="r.quantity_transferred" type="number" min="0" :class="[
                                        'w-24 rounded dark:bg-gray-800 dark:text-white/90',
                                        form.errors[`items.${i}.quantity_transferred`] || isQuantityExceedStock(r)
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                                            : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                                    ]" />

                                    {{ r.unit?.short_name }}
                                </div>
                                <!-- Pesan error validasi untuk quantity_transferred -->
                                <div v-if="form.errors[`items.${i}.quantity_transferred`]"
                                    class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ Array.isArray(form.errors[`items.${i}.quantity_transferred`]) ?
                                        form.errors[`items.${i}.quantity_transferred`][0] :
                                        form.errors[`items.${i}.quantity_transferred`] }}</span>
                                </div>
                                <!-- Real-time validation for stock exceed -->
                                <div v-else-if="isQuantityExceedStock(r)"
                                    class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Jumlah pengiriman tidak boleh melebihi stok tersedia ({{ r.stock }} {{ r.unit?.short_name || '' }})</span>
                                </div>
                            </td>
                            <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center whitespace-nowrap justify-center">
                                    <input v-model="r.note" type="text" placeholder="Masukkan catatan" :class="[
                                        'w-40 h-10 text-sm rounded placeholder:text-gray-600 dark:bg-gray-800 dark:text-white/90',
                                        form.errors[`items.${i}.note`]
                                            ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                                            : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
                                    ]" />
                                </div>
                                <!-- Pesan error validasi untuk note -->
                                <div v-if="form.errors[`items.${i}.note`]"
                                    class="text-xs text-rose-600 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ Array.isArray(form.errors[`items.${i}.note`]) ?
                                        form.errors[`items.${i}.note`][0] :
                                        form.errors[`items.${i}.note`] }}</span>
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

                        <button type="button" @click="addItem"
                            class="m-4 px-3 py-2 text-white bg-green-500 hover:bg-green-600 rounded whitespace-nowrap">
                            + Tambah Item
                        </button>
                    </tbody>
                </table>
            </div>
            <!-- Display items array errors -->
            <div v-if="form.errors.items"
                class="px-6 py-3 bg-red-50 border-t border-red-200 dark:bg-red-900/20 dark:border-red-800">
                <div class="flex items-center gap-2 text-sm text-rose-600 dark:text-rose-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>{{ Array.isArray(form.errors.items) ? form.errors.items[0] : form.errors.items }}</span>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div v-if="$page.props.flash?.success"
            class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md dark:bg-green-900/20 dark:border-green-800">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
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
            <button type="button" @click="goBack"
                class="inline-flex items-center rounded border bg-gray-400 border-gray-300 px-5 py-2 text-sm text-white hover:text-gray-800 hover:bg-gray-300 dark:border-gray-700 dark:text-gray-300">
                Batal
            </button>
            <button @click="showConfirmationModal = true"
                class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60">
                Kirim Barang
            </button>
        </div>

        <!-- Confirmation Modal -->
        <div v-if="showConfirmationModal" class="fixed inset-0 z-50 overflow-y-auto mt-12" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showConfirmationModal = false"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/20 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                    Konfirmasi Pengiriman Barang
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Apakah Anda yakin ingin mengirim barang ini? Tindakan ini tidak dapat dibatalkan.
                                    </p>
                                    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Detail Pengiriman:</p>
                                        <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                            <li v-for="item in items.filter(item => item.item_id)" :key="item.uid">
                                                • {{ item.name }} - {{ item.quantity_transferred }} {{ item.unit?.short_name || '' }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="confirmSend" type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="form.processing">
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Mengirim...' : 'Ya, Kirim Barang' }}
                        </button>
                        <button @click="showConfirmationModal = false" type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
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
import Select from "@/Components/form/SelectBarang.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import { Head, router, useForm, Link } from "@inertiajs/vue3";
import { ref, reactive, watch } from "vue";
import { v4 as uuidv4 } from "uuid";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Pemindahan Barang", href: route("good-transfers.index") },
    { label: "Buat Pemindahan" },
];

const props = defineProps({
    user: Object,
    branches: { type: Array, required: true },
    employees: { type: Array, required: true },
    items: { type: Array, default: () => [] },
    roles: { type: String, default: () => "" },
    sortBy: String,
    sortDirection: String,
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

// Modal state
const showConfirmationModal = ref(false);

// Computed property to get available options for each row (excluding already selected items and mismatched branch)
const getAvailableOptions = (currentRowIndex) => {
    const selectedItemIds = items.value
        .map((row, index) => index !== currentRowIndex ? row.item_id?.id : null)
        .filter(Boolean);

    const fromBranchId = form.from_branch_id;

    return options.value.filter(option => {
        const notSelected = !selectedItemIds.includes(option.id);
        const branchMatch = fromBranchId ? option.branch_id === fromBranchId : true;
        return notSelected && branchMatch;
    });
};

// Function to check if quantity exceeds stock
const isQuantityExceedStock = (row) => {
    return row.quantity_transferred > row.stock;
};

function addTag(newTag) {
    const tag = {
        id: Date.now(), // ID unik sementara
        name: newTag,
        code: "-", // default karena data tidak ada
        stock: 0, // default stok
        unit: null, // default unit
        branch_id: form.from_branch_id ?? null,
    };

    options.value.push(tag);

    return tag; // <== penting! harus return object
}

const items = ref([
    reactive({
        uid: uuidv4(),
        item_id: null,
        quantity_transferred: 1,
        code: "",
        name: "",
        stock: 0,
        unit: null,
        note: "",
    }),
]);

const addItem = () => {
    const newRow = reactive({
        uid: uuidv4(),
        item_id: null,
        quantity_transferred: 1,
        code: "",
        name: "",
        stock: 0,
        unit: null,
        note: "",
    });

    watch(
        () => newRow.item_id,
        (newVal) => {
            if (newVal) {
                newRow.code = newVal.code;
                newRow.name = newVal.name;
                newRow.stock = newVal.stock?.last_stock || 0;
                newRow.unit = newVal.unit;
            } else {
                newRow.code = "";
                newRow.name = "";
                newRow.stock = 0;
                newRow.unit = null;
            }
        }
    );

    items.value.push(newRow);
};

items.value.forEach((row) => {
    watch(
        () => row.item_id,
        (newVal) => {
            if (newVal) {
                row.code = newVal.code;
                row.name = newVal.name;
                row.stock = newVal.stock?.last_stock || 0;
                row.unit = newVal.unit;
            } else {
                row.code = "";
                row.name = "";
                row.stock = 0;
                row.unit = null;
            }
        }
    );
});

const removeItem = (index) => {
    items.value.splice(index, 1);
};

const form = useForm({
    from_branch_id: props.user?.employee?.branch?.id ?? null,
    to_branch_id: null,
    employee_id: props.user?.employee?.id ?? null,
    transferred_at: new Date().toISOString().slice(0, 10),
    purpose: "",
    items: [
        {
            uid: uuidv4(),
            item_id: null,
            quantity_transferred: 1,
            note: "",
        },
    ],
});

// Reset item selections when from_branch_id changes to enforce branch-based filtering
watch(
    () => form.from_branch_id,
    () => {
        items.value.forEach((row) => {
            row.item_id = null;
            row.code = "";
            row.name = "";
            row.stock = 0;
            row.unit = null;
        });
    }
);

function saveLog() {
    // Clear previous errors
    form.clearErrors();

    // Validate that there's at least one item
    if (!items.value || items.value.length === 0) {
        form.setError('items', 'Minimal harus ada satu item pemindahan');
        return;
    }

    // Check for duplicate items
    const itemIds = items.value.map(r => r.item_id?.id).filter(Boolean);
    const duplicateItems = itemIds.filter((id, index) => itemIds.indexOf(id) !== index);

    if (duplicateItems.length > 0) {
        form.setError('items', 'Tidak boleh ada item yang duplikat');
        return;
    }

    // Validate that all items have required fields
    const hasInvalidItems = items.value.some((r, i) => {
        if (!r.item_id || !r.item_id.id) {
            form.setError(`items.${i}.item_id`, 'Pilih barang yang akan dipindahkan');
            return true;
        }
        if (!r.quantity_transferred || r.quantity_transferred <= 0) {
            form.setError(`items.${i}.quantity_transferred`, 'Jumlah pengiriman harus lebih dari 0');
            return true;
        }
        if (r.quantity_transferred > r.stock) {
            form.setError(`items.${i}.quantity_transferred`, `Jumlah pengiriman tidak boleh melebihi stok tersedia (${r.stock} ${r.unit?.short_name || ''})`);
            return true;
        }
        if (r.note && r.note.length > 225) {
            form.setError(`items.${i}.note`, 'Catatan tidak boleh lebih dari 225 karakter');
            return true;
        }
        return false;
    });

    if (hasInvalidItems) {
        return;
    }

    form.items = items.value.map((r) => ({
        uid: r.uid,
        item_id: r.item_id?.id ?? null, // karena Select pakai object
        quantity_transferred: r.quantity_transferred,
        note: r.note ?? null,
    }));

    form.post(route("good-transfers.store"));
}

function confirmSend() {
    showConfirmationModal.value = false;
    saveLog();
}

function goBack() {
    router.get(route("good-transfers.index"));
}
</script>
