<template>
    <Head :title="`Permintaan Barang ${mr.request_no}`" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div v-if="mr.status === 'on_progress'" class="flex items-center gap-2">
                <button @click="onApprove" class="inline-flex items-center rounded bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">Setujui</button>
                <button @click="onIssue" class="inline-flex items-center rounded bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Keluarkan Barang</button>
            </div>
        </div>

        <!-- Row 1: Information Card -->
        <div class="flex flex-col gap-4 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">Informasi Permintaan</div>
                <div>
                    <span :class="badgeClass(mr.status)" class="px-2.5 py-1 rounded-full text-xs font-medium">{{ statusLabel(mr.status) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <div class="text-xs text-gray-500">No. Request</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ mr.request_no }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Departemen</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ mr.department }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Diminta oleh</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ mr.requested_by }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Tanggal Permintaan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ mr.request_date }}</div>
                </div>
                <div v-if="mr.approved_by">
                    <div class="text-xs text-gray-500">Disetujui oleh</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ mr.approved_by }}</div>
                </div>
                <div v-if="mr.approved_at">
                    <div class="text-xs text-gray-500">Waktu Persetujuan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ mr.approved_at }}</div>
                </div>
                <div class="md:col-span-2 xl:col-span-3">
                    <div class="text-xs text-gray-500">Catatan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ mr.notes || '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Row 2: Tabs Items + Activity Log -->
        <div class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex items-center gap-2 px-4 border-b border-gray-200 dark:border-gray-700">
                <button
                    class="px-4 py-3 text-sm font-medium"
                    :class="activeTab === 'items' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 dark:text-gray-300'"
                    @click="activeTab = 'items'"
                >
                    Daftar Item
                </button>
                <button
                    class="px-4 py-3 text-sm font-medium"
                    :class="activeTab === 'activities' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 dark:text-gray-300'"
                    @click="activeTab = 'activities'"
                >
                    Aktivitas Log
                </button>
            </div>

            <div v-if="activeTab === 'items'" class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width:60px" />
                        <col style="width:220px" />
                        <col style="width:180px" />
                        <col style="width:120px" />
                        <col style="width:120px" />
                        <col />
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">No</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Nama Item</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Kode</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Qty</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Satuan</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Catatan</div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!items || items.length === 0">
                            <td colspan="6" class="text-center py-8 text-gray-500">Tidak ada item</td>
                        </tr>
                        <tr v-for="(it, idx) in items" :key="it.id" class="border-b border-gray-200 dark:border-gray-700">
                            <td class="px-3 py-2">{{ idx + 1 }}</td>
                            <td class="px-3 py-2">{{ it.item_name }}</td>
                            <td class="px-3 py-2 font-mono">{{ it.item_code }}</td>
                            <td class="px-3 py-2">{{ it.qty }}</td>
                            <td class="px-3 py-2">{{ it.unit }}</td>
                            <td class="px-3 py-2">{{ it.notes }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="activeTab === 'activities'" class="p-6">
                <div v-if="!activities || activities.length === 0" class="text-sm text-gray-500">Belum ada aktivitas.</div>
                <ul v-else class="space-y-3">
                    <li v-for="(act, i) in activities" :key="i" class="flex gap-3 items-start">
                        <div class="mt-1 h-2 w-2 rounded-full bg-blue-500"></div>
                        <div>
                            <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ act.title }}</div>
                            <div class="text-xs text-gray-500">{{ act.time }}</div>
                            <div class="text-sm text-gray-700 dark:text-gray-300">{{ act.description }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import Breadcrumb from '@/Components/common/Breadcrumb.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    mr: { type: Object, required: true },
    items: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
})

const mr = props.mr
const items = props.items
const activities = props.activities

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Permintaan Barang', href: route('material-requests.index') },
    { label: mr.request_no },
]

const activeTab = ref('items')

defineOptions({
    layout: AppLayout,
})

function statusLabel(s) {
    const map = {
        on_progress: 'Menunggu Persetujuan',
        approved: 'Disetujui',
        rejected: 'Ditolak',
        canceled: 'Dibatalkan',
        partial_approved: 'Disetujui Sebagian',
    }
    return map[s] ?? s
}

function badgeClass(s) {
    const map = {
        on_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300',
        approved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
        rejected: 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300',
        canceled: 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300',
        partial_approved: 'bg-violet-100 text-violet-800 dark:bg-violet-500/20 dark:text-violet-300',
    }
    return map[s] ?? 'bg-gray-100 text-gray-700'
}

function onApprove() {
    if (!confirm('Setujui permintaan ini?')) return
    router.post(route('material-requests.approve', mr.id), {}, { preserveScroll: true })
}

function onIssue() {
    if (!confirm('Mulai proses keluarkan barang untuk permintaan ini?')) return
    router.post(route('material-requests.issue', mr.id), {}, { preserveScroll: true })
}
</script>
