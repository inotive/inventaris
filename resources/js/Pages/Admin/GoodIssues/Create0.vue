<template>
    <Head title="Tambah Permintaan Barang" />
    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>
        <div
            class="overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]"
        >
            <h1
                class="mb-4 text-lg font-semibold text-gray-700 dark:text-gray-200"
            >
                Tambah Permintaan Barang
            </h1>
            <Form
                :form="form"
                :departments="departments"
                :employees="employees"
                :statusOptions="statusOptions"
                @submit="submit"
                @cancel="goBack"
            />
        </div>

        <div
            class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-white/[0.03]"
        >
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700"
            >
                <div class="font-semibold text-gray-700 dark:text-gray-200">
                    Daftar Item
                </div>
            </div>
            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 60px" />
                        <col style="width: 180px" />
                        <col />
                        <col style="width: 120px" />
                        <col style="width: 100px" />
                        <col style="width: 220px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 text-left font-medium text-gray-600 dark:text-gray-300"
                                >
                                    No
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 text-left font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Kode
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 text-left font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Nama Item
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 text-left font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Satuan
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 text-left font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Qty
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 text-left font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Catatan
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(it, idx) in items"
                            :key="it.id"
                            class="border-b border-gray-200 dark:border-gray-700"
                        >
                            <td class="px-3 py-2">{{ idx + 1 }}</td>
                            <td class="px-3 py-2 font-mono">{{ it.code }}</td>
                            <td class="px-3 py-2">{{ it.name }}</td>
                            <td class="px-3 py-2">{{ it.unit }}</td>
                            <td class="px-3 py-2">
                                <input
                                    type="number"
                                    min="0"
                                    v-model.number="rowsQty[it.id]"
                                    class="w-24 h-10 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </td>
                            <td class="px-3 py-2">
                                <input
                                    type="text"
                                    v-model="rowsNotes[it.id]"
                                    class="w-full h-10 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div
                v-if="form.errors.items"
                class="px-6 py-3 text-xs text-rose-600"
            >
                {{ form.errors.items }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, router, useForm, Link } from "@inertiajs/vue3";
import { reactive } from "vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Form from "./Form0.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    statusOptions: { type: Array, required: true },
    items: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { label: "Dashboard", href: route("dashboard") },
    { label: "Permintaan Barang", href: route("material-requests.index") },
    { label: "Tambah" },
];

const form = useForm({
    request_no: "",
    request_date: new Date().toISOString().slice(0, 10),
    department_id: null,
    request_by: null,
    status: "on_progress",
    notes: "",
    items: [],
});

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

defineOptions({
    layout: AppLayout,
});

const errors = form.errors;
</script>
