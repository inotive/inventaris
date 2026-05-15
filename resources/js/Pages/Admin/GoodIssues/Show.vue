<template>
    <Head :title="`Pemakaian Barang ${mr.request_no}`" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <div class="flex items-center gap-3">
                <button @click="goBack" class="inline-flex items-center rounded border border-gray-300 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </button>
                <Breadcrumb :items="breadcrumbs" />
            </div>
            <div class="flex items-center gap-2">
                <button v-if="can_approve && mr.status === 'pending'" @click="onApprove" class="inline-flex items-center rounded bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">Setujui</button>
            </div>
        </div>

        <!-- Row 1: Information Card -->
        <div class="flex flex-col gap-4 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">Informasi Pemakaian Barang</div>
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
                    <div class="text-xs text-gray-500">Tanggal Pemakaian</div>
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
                            <td class="px-3 py-2">
                                <span v-if="it.quantity_approved && it.quantity_approved !== it.qty" class="text-orange-600 dark:text-orange-400">
                                    {{ it.quantity_approved }} / {{ it.qty }}
                                </span>
                                <span v-else>{{ it.qty }}</span>
                            </td>
                            <td class="px-3 py-2">{{ it.unit }}</td>
                            <td class="px-3 py-2">
                                <div v-if="it.note_received" class="text-xs text-gray-500 italic">
                                    Note: {{ it.note_received }}
                                </div>
                                <div>{{ it.notes }}</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="activeTab === 'activities'" class="p-6">
                <div v-if="!activities || activities.length === 0" class="text-sm text-gray-500">Belum ada aktivitas.</div>
                <ul v-else class="space-y-4">
                    <li v-for="(act, i) in activities" :key="i" class="flex gap-4 items-start border-l-2 border-blue-500 pl-4">
                        <div class="flex-shrink-0 mt-0.5">
                            <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ act.title }}</div>
                                <div class="text-xs text-gray-500">{{ act.time }}</div>
                            </div>
                            <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ act.description }}</div>
                            <div class="mt-1 text-xs text-gray-500">Oleh: {{ act.user }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div
        v-if="showApproveModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="closeApproveModal"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Setujui Pemakaian Barang
                    </h3>
                    <button @click="closeApproveModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="overflow-y-auto flex-1 px-6 py-4">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 text-left font-medium text-gray-700 dark:text-gray-300">Item</th>
                            <th class="py-2 text-left font-medium text-gray-700 dark:text-gray-300">Qty Diminta</th>
                            <th class="py-2 text-left font-medium text-gray-700 dark:text-gray-300">Qty Disetujui</th>
                            <th class="py-2 text-left font-medium text-gray-700 dark:text-gray-300">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id" class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ item.item_name }}</div>
                                <div class="text-xs text-gray-500">{{ item.item_code }}</div>
                            </td>
                            <td class="py-3 text-gray-700 dark:text-gray-300">{{ item.qty }}</td>
                            <td class="py-3">
                                <div class="flex flex-col gap-1">
                                    <input
                                        v-model="item.approval.quantity_approved"
                                        type="number"
                                        min="0"
                                        :max="Math.min(item.qty, item.current_stock)"
                                        class="w-20 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white px-2 py-1 text-sm"
                                        :class="{ 'border-red-500': item.approval.quantity_approved > item.current_stock }"
                                    />
                                    <div class="text-xs text-gray-500">
                                        Stok: {{ item.current_stock }}
                                    </div>
                                    <div v-if="item.approval.quantity_approved > item.current_stock" class="text-xs text-red-600">
                                        Melebihi stok yang tersedia!
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <input
                                    v-model="item.approval.note_received"
                                    type="text"
                                    placeholder="Note penerimaan"
                                    class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-700 dark:text-white px-2 py-1 text-sm"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <button
                    @click="closeApproveModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-300 dark:border-gray-500 dark:hover:bg-gray-500"
                >
                    Batal
                </button>
                <button
                    @click="confirmApprove"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                >
                    Setujui
                </button>
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
    can_approve: { type: Boolean, default: false },
})

const mr = props.mr
const items = ref(props.items.map(item => ({
    ...item,
    approval: {
        quantity_approved: item.quantity_approved ?? item.qty ?? '',
        note_received: item.note_received || ''
    },
    current_stock: item.current_stock ?? 0
})))
const activities = props.activities
const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Pemakaian Barang', href: route('good-issues.index') },
    { label: mr.request_no },
]

const activeTab = ref('items')

defineOptions({
    layout: AppLayout,
})

function statusLabel(s) {
    const map = {
        pending: 'Menunggu Persetujuan',
        draft: 'Draft',
        submitted: 'Dikirim',
        approved: 'Disetujui',
        on_progress: 'Dalam Proses',
        canceled: 'Dibatalkan',
    }
    return map[s] ?? s
}

function badgeClass(s) {
    const map = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-300',
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-500/20 dark:text-gray-300',
        submitted: 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300',
        approved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
        rejected: 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300',
        on_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300',
        canceled: 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300',
        partial_approved: 'bg-violet-100 text-violet-800 dark:bg-violet-500/20 dark:text-violet-300',
    }
    return map[s] ?? 'bg-gray-100 text-gray-700'
}

const showApproveModal = ref(false)

function onApprove() {
    if (mr.status === 'approved') {
        return
    }
    showApproveModal.value = true
}

function closeApproveModal() {
    showApproveModal.value = false
}

function confirmApprove() {
    // Validate all approvals
    const isValid = items.value.every(item => {
        if (!item.approval || item.approval.quantity_approved === null || item.approval.quantity_approved === '') {
            return false
        }
        return true
    })

    if (!isValid) {
        alert('Harap isi jumlah yang disetujui untuk semua item')
        return
    }

    // Validate stock availability
    const stockErrors = []
    items.value.forEach(item => {
        const qtyApproved = parseInt(item.approval.quantity_approved) || 0
        const currentStock = item.current_stock || 0

        if (qtyApproved > currentStock) {
            stockErrors.push(`${item.item_name}: Jumlah yang disetujui (${qtyApproved}) melebihi stok yang tersedia (${currentStock})`)
        }
    })

    if (stockErrors.length > 0) {
        alert('Validasi stok gagal:\n\n' + stockErrors.join('\n'))
        return
    }

    // Prepare approvals data
    const approvals = items.value.map(item => ({
        id: item.id,
        quantity_approved: parseInt(item.approval.quantity_approved),
        note_received: item.approval.note_received || null
    }))

    router.post(route('good-issues.approve', mr.id), { approvals }, {
        onSuccess: () => {
            window.location.reload()
            showApproveModal.value = false
        },
        onError: (errors) => {
            console.error('Error approving pemakaian barang:', errors)
        }
    })
}

function onIssue() {
    if (!confirm('Konfirmasi pengeluaran barang untuk pemakaian barang ini?')) return
    // TODO: Implement issue confirmation functionality for pemakaian barang
}

function goBack() {
    router.get(route('good-issues.index'))
}
</script>
