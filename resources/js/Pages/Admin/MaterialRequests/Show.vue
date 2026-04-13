<template>
    <Head :title="`Permintaan Barang ${mr.request_no}`" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <!-- Approve Modal -->
        <div
            v-if="showApprove"
            class="flex fixed inset-0 z-50 justify-center items-center bg-black/40"
        >
            <div class="w-[700px] max-w-[95vw] rounded-lg bg-white shadow">
                <div class="px-4 py-3 border-b">
                    <div class="font-semibold">
                        Setujui Permintaan - Masukkan Qty Disetujui
                    </div>
                </div>
                <div class="p-4 overflow-auto max-h-[70vh]">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-2 text-left">Nama Item</th>
                                <th class="p-2 text-left">Kode</th>
                                <th class="p-2 text-left">
                                    Jumlah yang dibutuhkan
                                </th>
                                <th class="p-2 text-left">
                                    Jumlah yang disetujui
                                </th>
                                <th class="p-2 text-left">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="ap in approvals"
                                :key="ap.id"
                                class="border-b"
                            >
                                <td class="p-2">{{ ap.item_name }}</td>
                                <td class="p-2 font-mono">
                                    {{ ap.item_code }}
                                </td>
                                <td class="p-2">
                                    {{ ap.quantity_requested }}
                                    <span class="px-1">{{ ap.unit }}</span>
                                </td>
                                <td class="p-2">
                                    <input
                                        type="number"
                                        min="0"
                                        class="px-2 w-28 h-9 rounded border"
                                        v-model.number="ap.quantity_approved"
                                    />
                                    <span class="px-1">{{ ap.unit }}</span>
                                </td>
                                <td class="p-2">
                                    <input
                                        type="text"
                                        class="px-2 w-full h-9 rounded border"
                                        v-model="ap.note"
                                        placeholder="Catatan (opsional)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex gap-2 justify-end px-4 py-3 border-t">
                    <button
                        @click="showApprove = false"
                        class="px-3 h-9 rounded border"
                    >
                        Batal
                    </button>
                    <button
                        @click="submitApprove"
                        class="px-3 h-9 text-white bg-emerald-600 rounded"
                    >
                        Simpan & Setujui
                    </button>
                </div>
            </div>
        </div>

        <!-- Row 1: Information Card -->
        <div
            class="flex flex-col gap-4 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div class="flex justify-between items-center">
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Informasi Permintaan Barang
                </div>
                <div>
                    <span
                        :class="badgeClass(mr.status)"
                        class="px-2.5 py-1 text-xs font-medium rounded-full"
                        >{{ statusLabel(mr.status) }}</span
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <div class="text-xs text-gray-500">No. Request</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ mr.request_no }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Departemen</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ mr.department }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Diminta oleh</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ mr.requested_by }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Tanggal Permintaan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ mr.requested_at }}
                    </div>
                </div>
                <div v-if="mr.approved_by">
                    <div class="text-xs text-gray-500">Disetujui oleh</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ mr.approved_by }}
                    </div>
                </div>
                <div v-if="mr.approved_at">
                    <div class="text-xs text-gray-500">Waktu Persetujuan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ mr.approved_at }}
                    </div>
                </div>
                <div class="md:col-span-2 xl:col-span-3">
                    <div class="text-xs text-gray-500">Catatan</div>
                    <div
                        class="font-semibold text-gray-800 whitespace-pre-line dark:text-gray-200"
                    >
                        {{ mr.requirement || "-" }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Tabs Items + Activity Log -->
        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex gap-2 items-center px-4 border-b border-gray-200 dark:border-gray-700"
            >
                <button
                    class="px-4 py-3 text-sm font-medium"
                    :class="
                        activeTab === 'items'
                            ? 'border-b-2 border-blue-500 text-blue-600'
                            : 'text-gray-600 dark:text-gray-300'
                    "
                    @click="activeTab = 'items'"
                >
                    Daftar Item
                </button>
                <button
                    class="px-4 py-3 text-sm font-medium"
                    :class="
                        activeTab === 'activities'
                            ? 'border-b-2 border-blue-500 text-blue-600'
                            : 'text-gray-600 dark:text-gray-300'
                    "
                    @click="activeTab = 'activities'"
                >
                    Aktivitas Log
                </button>
            </div>

            <div
                v-if="activeTab === 'items'"
                class="overflow-auto"
                data-simplebar
            >
                <table class="min-w-full text-sm table-fixed">
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-2 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    No.
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-left text-gray-600 dark:text-gray-300"
                                >
                                    Nama Item
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-left text-gray-600 dark:text-gray-300"
                                >
                                    Kode Item
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-left text-gray-600 dark:text-gray-300"
                                >
                                    Jumlah yang dibutuhkan
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-left text-gray-600 dark:text-gray-300"
                                >
                                    Jumlah yang disetujui
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-left text-gray-600 dark:text-gray-300"
                                >
                                    Catatan
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!items || items.length === 0">
                            <td
                                colspan="6"
                                class="py-8 text-center text-gray-500"
                            >
                                Tidak ada item
                            </td>
                        </tr>
                        <tr
                            v-for="(it, idx) in items"
                            :key="it.id"
                            class="border-b border-gray-200 dark:border-gray-700"
                        >
                            <td class="px-3 py-2 text-center">
                                {{ idx + 1 }}.
                            </td>
                            <td class="px-3 py-2">{{ it.item_name }}</td>
                            <td class="px-3 py-2 font-mono">
                                {{ it.item_code }}
                            </td>
                            <td class="px-3 py-2">
                                {{ it.quantity_requested }}
                                <span class="px-2">{{ it.unit }}</span>
                            </td>
                            <td class="px-3 py-2">
                                {{ it.quantity_approved }}
                                <span class="px-2">{{ it.unit }}</span>
                            </td>
                            <td class="px-3 py-2">{{ it.note }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="activeTab === 'activities'" class="p-6">
                <div
                    v-if="!activities || activities.length === 0"
                    class="text-sm text-gray-500"
                >
                    Belum ada aktivitas.
                </div>
                <ul v-else class="space-y-3">
                    <li
                        v-for="(act, i) in activities"
                        :key="i"
                        class="flex gap-3 items-start"
                    >
                        <div
                            class="mt-1 w-2 h-2 bg-blue-500 rounded-full"
                        ></div>
                        <div>
                            <div
                                class="text-sm font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ act.title }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ act.time }}
                            </div>
                            <div
                                class="text-sm text-gray-700 dark:text-gray-300"
                            >
                                {{ act.description }}
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div
            v-if="mr.status === 'approved'"
            class="flex gap-4 justify-end items-center"
        >
            <button
                @click="openProcessPR()"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded hover:bg-indigo-700"
            >
                Kirim ke Pengajuan Pembelian
            </button>
            <!-- <button
                @click="openIssueConfirm()"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-violet-600 rounded hover:bg-violet-700"
            >
                Kirim ke Pemakaian Barang
            </button> -->
        </div>
        <div v-else-if="mr.status === 'pending'" class="flex gap-2 justify-end items-center">
            <button v-if="can_approve"
                @click="openApprove()"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded hover:bg-indigo-700"
            >
                Setujui
            </button>
            <button v-if="can_approve"
                @click="openRejected()"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-red-600 rounded hover:bg-red-700"
            >
                Tolak
            </button>
            <button
                @click="onCancel"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-yellow-500 rounded hover:bg-yellow-700"
            >
                Batalkan
            </button>
        </div>
        <div v-else class="flex gap-2 justify-end items-center">

        </div>
    </div>

    <ConfirmModal
        :show="showConfirmCancel"
        :question="`Yakin ingin membatalkan permintaan ini?`"
        :selected="`${mr.request_no}`"
        title="Batalkan Permintaan"
        confirmText="Ya, Batalkan!"
        maxWidth="md"
        @close="closeConfirmCancel"
        @confirm="submitCancel"
    />

    <ConfirmModal
        :show="showConfirmPR"
        :question="`Buat Pengajuan Pembelian dari ${mr.request_no}?`"
        :selected="`${mr.request_no}`"
        title="Proses ke Pengajuan Pembelian"
        confirmText="Ya, Buat Pengajuan"
        maxWidth="md"
        @close="closeProcessPR"
        @confirm="submitProcessPR"
    />

    <ConfirmModal
        :show="showConfirmIssue"
        :question="`Keluarkan barang berdasarkan ${mr.request_no}?`"
        :selected="`${mr.request_no}`"
        title="Kirim ke Pemakaian Barang"
        confirmText="Ya, Keluarkan"
        maxWidth="md"
        @close="closeIssueConfirm"
        @confirm="submitIssue"
    />

    <ConfirmModal
        :show="showConfirmRejected"
        :question="`Tolak permintaan ini?`"
        :selected="`${mr.request_no}`"
        title="Tolak Permintaan"
        confirmText="Ya, Tolak"
        maxWidth="md"
        @close="closeConfirmRejected"
        @confirm="submitRejected"
    />

</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

import ConfirmModal from "@/Components/common/ConfirmModal.vue";
const props = defineProps({
    mr: { type: Object, required: true },
    items: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    can_approve: { type: Boolean, default: false },
});

const mr = props.mr;
const items = props.items;
const activities = props.activities;
const can_approve = props.can_approve;

console.log("mr status:", mr.status);
const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Permintaan Barang", href: route("material-requests.index") },
    { label: mr.request_no },
];

const activeTab = ref("items");
const showApprove = ref(false);
const approvals = ref([]);
const showConfirmRejected = ref(false);
const showConfirmCancel = ref(false);
const showConfirmPR = ref(false);
const showConfirmIssue = ref(false);
defineOptions({
    layout: AppLayout,
});

function statusLabel(s) {
    const map = {
        pending: "Menunggu Persetujuan",
        approved: "Disetujui",
        rejected: "Ditolak",
        cancelled: "Dibatalkan",
        partial_approved: "Disetujui Sebagian",
    };
    return map[s] ?? s;
}

function badgeClass(s) {
    const map = {
        pending:
            "bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-300",
        approved:
            "bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300",
        rejected:
            "bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300",
        cancelled:
            "bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300",
        partial_approved:
            "bg-violet-100 text-violet-800 dark:bg-violet-500/20 dark:text-violet-300",
    };
    return map[s] ?? "bg-gray-100 text-gray-700";
}

function onRejected() {
    if (!confirm("Tolak permintaan ini?")) return;
    router.post(
        route("material-requests.rejected", mr.id),
        {},
        { preserveScroll: true }
    );
}

function openRejected() {
    showConfirmRejected.value = true;
}

function closeConfirmRejected() {
    showConfirmRejected.value = false;
}

function openApprove() {
    approvals.value = (items || []).map((it) => ({
        id: it.id,
        item_name: it.item_name,
        item_code: it.item_code,
        unit: it.unit,
        quantity_requested: it.quantity_requested,
        quantity_approved: it.quantity_approved ?? it.quantity_requested,
        note: it.note || "",
    }));
    showApprove.value = true;
}

function submitApprove() {
    router.post(
        route("material-requests.approve", mr.id),
        { approvals: approvals.value },
        {
            preserveScroll: true,
            onFinish: () => {
                showApprove.value = false;
            },
        }
    );
}

function submitRejected() {
    router.post(
        route("material-requests.rejected", mr.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                showConfirmRejected.value = false;
            },
        }
    );
}

function onCancel() {
    showConfirmCancel.value = true;
}

function closeConfirmCancel() {
    showConfirmCancel.value = false;
}

function submitCancel() {
    router.post(
        route("material-requests.cancel", mr.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                showConfirmCancel.value = false;
            },
        }
    );
}

function openProcessPR() {
    showConfirmPR.value = true;
}

function closeProcessPR() {
    showConfirmPR.value = false;
}

function submitProcessPR() {
    router.post(
        route("purchase-requests.from-material-request", mr.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                showConfirmPR.value = false;
            },
        }
    );
}

function openIssueConfirm() {
    showConfirmIssue.value = true;
}

function closeIssueConfirm() {
    showConfirmIssue.value = false;
}

function submitIssue() {
    router.post(
        route("material-requests.issue", mr.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                showConfirmIssue.value = false;
            },
        }
    );
}
</script>
