<template>

    <Head :title="`Pengajuan Pembelian ${pr.request_no}`" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div v-if="pr.status === 'draft'" class="flex items-center gap-2">
                <Link :href="route('purchase-requests.edit', pr.id)"
                    class="inline-flex items-center rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Edit</Link>
                <button @click="onCancel"
                    class="inline-flex items-center rounded bg-gray-500 px-3 py-2 text-sm font-medium text-white hover:bg-gray-600">Batalkan</button>
                <button @click="onDelete"
                    class="inline-flex items-center rounded bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">Hapus</button>
            </div>
        </div>

          <!-- Rejection Reason Card (only shown when status is rejected) -->
          <div v-if="pr.status === 'rejected' && pr.reason"
            class="flex flex-col gap-4 overflow-hidden rounded-lg border border-red-200 bg-red-50 p-6 dark:border-red-600 dark:bg-red-500/10">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div class="font-bold text-red-700 dark:text-red-300">Alasan Penolakan</div>
            </div>
            <div class="text-sm text-red-800 dark:text-red-200 whitespace-pre-line">{{ pr.reason }}</div>
        </div>

        <!-- Row 1: Information Card -->
        <div
            class="flex flex-col gap-4 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">Informasi Pengajuan Pembelian</div>
                <div>
                    <span :class="badgeClass(pr.status)" class="px-2.5 py-1 rounded-full text-xs font-medium">{{
                        statusLabel(pr.status) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <div class="text-xs text-gray-500">No. Pengajuan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ pr.request_no }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Departemen</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ pr.department?.name }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Diajukan oleh</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ pr.employee?.name }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Tanggal Pengajuan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ formatDate(pr.requested_at) }}</div>
                </div>
                <div v-if="pr.material_request">
                    <div class="text-xs text-gray-500">Referensi MR</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ pr.material_request?.request_no }}
                    </div>
                </div>
                <div v-if="pr.approved_by">
                    <div class="text-xs text-gray-500">Disetujui oleh</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ pr.approved_by }}</div>
                </div>
                <div v-if="pr.approved_at">
                    <div class="text-xs text-gray-500">Waktu Persetujuan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ formatDate(pr.approved_at) }}</div>
                </div>
                <div v-if="pr.status === 'rejected' && pr.reason">
                    <div class="text-xs text-gray-500">Alasan Penolakan</div>
                    <div class="font-semibold text-red-600 dark:text-red-400 whitespace-pre-line">{{ pr.reason }}</div>
                </div>
                <div class="md:col-span-2 xl:col-span-3">
                    <div class="text-xs text-gray-500">Keperluan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ pr.requirement ||
                        '-' }}</div>
                </div>
            </div>
        </div>



        <!-- Row 2: Tabs Items + Activity Log -->
        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex items-center gap-2 px-4 border-b border-gray-200 dark:border-gray-700">
                <button class="px-4 py-3 text-sm font-medium"
                    :class="activeTab === 'items' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 dark:text-gray-300'"
                    @click="activeTab = 'items'">
                    Daftar Item
                </button>
                <button class="px-4 py-3 text-sm font-medium"
                    :class="activeTab === 'activities' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-600 dark:text-gray-300'"
                    @click="activeTab = 'activities'">
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
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">No</div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Nama Item</div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Kode</div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Qty Diminta
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Qty Disetujui
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Satuan</div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Catatan</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!pr.items || pr.items.length === 0">
                            <td colspan="7" class="text-center py-8 text-gray-500">Tidak ada item</td>
                        </tr>
                        <tr v-for="(it, idx) in pr.items" :key="it.id"
                            class="border-b border-gray-200 dark:border-gray-700">
                            <td class="px-3 py-2">{{ idx + 1 }}</td>
                            <td class="px-3 py-2">{{ it.item?.name }}</td>
                            <td class="px-3 py-2 font-mono">{{ it.item?.code }}</td>
                            <td class="px-3 py-2">{{ it.quantity_requested }}</td>
                            <td class="px-3 py-2">{{ it.quantity_approved || '-' }}</td>
                            <td class="px-3 py-2">{{ it.item?.unit?.short_name }}</td>
                            <td class="px-3 py-2">{{ it.note || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="activeTab === 'activities'" class="p-6">
                <div v-if="!activities || activities.length === 0" class="text-sm text-gray-500">Belum ada aktivitas.
                </div>
                <ul v-else class="space-y-3">
                    <li v-for="(act, i) in activities" :key="i" class="flex gap-3 items-start">
                        <div class="mt-1 h-2 w-2 rounded-full bg-blue-500"></div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ act.title }}</div>
                            <div class="text-xs text-gray-500 flex items-center gap-2">
                                <span>{{ act.time }}</span>
                                <span v-if="act.created_by" class="text-gray-400">•</span>
                                <span v-if="act.created_by" class="font-medium">{{ act.created_by }}</span>
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ act.description }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div v-if="pr.status === 'on_progress'"
        class="flex items-center justify-end gap-4">
            <button @click="onCancel"
                class="inline-flex items-center rounded bg-gray-500 px-4 py-2 text-sm font-medium text-white hover:bg-gray-600">Batalkan</button>
            <button v-if="can_approve" @click="onRejected"
                class="inline-flex items-center rounded bg-red-500 px-4 py-2 text-sm font-medium text-white hover:bg-red-600">Tolak</button>
            <button v-if="can_approve" @click="openApprove()"
                class="inline-flex items-center rounded bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">Setujui</button>
        </div>

        <div v-if="pr.status === 'approved'"
        class="flex items-center justify-end gap-4">
            <button @click="openConvertToPO()"
                class="inline-flex items-center rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                Konversi ke PO
            </button>
        </div>


        <!-- Approve Modal -->
        <div v-if="showApprove" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="w-[700px] max-w-[95vw] rounded-lg bg-white shadow">
                <div class="px-4 py-3 border-b">
                    <div class="font-semibold">Setujui Pengajuan - Masukkan Qty Disetujui</div>
                </div>
                <div class="p-4 overflow-auto max-h-[70vh]">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-2 text-left">Nama Item</th>
                                <th class="p-2 text-left">Kode</th>
                                <th class="p-2 text-left">Diminta</th>
                                <th class="p-2 text-left">Disetujui</th>
                                <th class="p-2 text-left">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(ap) in approvals" :key="ap.id" class="border-b">
                                <td class="p-2">{{ ap.item?.name }}</td>
                                <td class="p-2 font-mono">{{ ap.item?.code }}</td>
                                <td class="p-2">{{ ap.quantity_requested }} <span class="px-1">{{
                                        ap.item?.unit?.short_name }}</span></td>
                                <td class="p-2">
                                    <input type="number" min="0" class="w-28 h-9 px-2 rounded border"
                                        v-model.number="ap.quantity_approved" />
                                    <span class="px-1">{{ ap.item?.unit?.short_name }}</span>
                                </td>
                                <td class="p-2">
                                    <input type="text" class="w-full h-9 px-2 rounded border" v-model="ap.note"
                                        placeholder="Catatan (opsional)" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t flex justify-end gap-2">
                    <button @click="showApprove" class="px-3 h-9 rounded border">Batal</button>
                    <button @click="submitApprove" class="px-3 h-9 rounded bg-emerald-600 text-white">Simpan &
                        Setujui</button>
                </div>
            </div>
        </div>

        <!-- Convert to PO Confirmation Modal -->
        <div v-if="showConvertConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="w-[500px] max-w-[95vw] rounded-lg bg-white shadow">
                <div class="px-4 py-3 border-b">
                    <div class="font-semibold">Konversi ke Purchase Order</div>
                </div>
                <div class="p-4">
                    <div class="text-sm text-gray-600 mb-4">
                        Apakah Anda yakin ingin mengkonversi pengajuan pembelian <strong>{{ pr.request_no }}</strong> menjadi Purchase Order?
                    </div>
                    <div class="text-xs text-gray-500">
                        Setelah dikonversi, Anda akan diarahkan ke form Purchase Order dengan data barang yang sudah terisi.
                    </div>
                </div>
                <div class="px-4 py-3 border-t flex justify-end gap-2">
                    <button @click="showConvertConfirm = false" class="px-3 h-9 rounded border">Batal</button>
                    <button @click="confirmConvertToPO" class="px-3 h-9 rounded bg-blue-600 text-white">Ya, Konversi</button>
                </div>
            </div>
        </div>

        <!-- PO Form Modal -->
        <div v-if="showPOForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="w-[900px] max-w-[95vw] rounded-lg bg-white shadow max-h-[90vh] overflow-hidden">
                <div class="px-4 py-3 border-b">
                    <div class="font-semibold">Buat Purchase Order dari {{ pr.request_no }}</div>
                </div>
                <div class="p-4 overflow-auto max-h-[70vh]">
                    <form @submit.prevent="submitPO">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Vendor <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="poForm.vendor"
                                    required
                                    :class="[
                                        'w-full h-9 px-3 rounded border focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                                        vendorError ? 'border-red-500' : 'border-gray-300'
                                    ]"
                                    placeholder="Nama Vendor"
                                    @blur="validateVendor"
                                    @input="vendorError = ''"
                                />
                                <p v-if="vendorError" class="mt-1 text-xs text-red-600">{{ vendorError }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal PO</label>
                                <input type="date" v-model="poForm.po_date" required
                                    class="w-full h-9 px-3 rounded border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea v-model="poForm.notes" rows="3"
                                class="w-full px-3 py-2 rounded border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Catatan untuk Purchase Order (opsional)"></textarea>
                        </div>

                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Daftar Item</h3>
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-2 text-left">Nama Item</th>
                                        <th class="p-2 text-left">Kode</th>
                                        <th class="p-2 text-left">Qty</th>
                                        <th class="p-2 text-left">Harga Satuan</th>
                                        <th class="p-2 text-left">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, idx) in poForm.items" :key="idx" class="border-b">
                                        <td class="p-2">{{ item.item?.name }}</td>
                                        <td class="p-2 font-mono">{{ item.item?.code }}</td>
                                        <td class="p-2">{{ item.quantity }} {{ item.item?.unit?.short_name }}</td>
                                        <td class="p-2">
                                            <input type="number" min="0" step="0.01"
                                                v-model.number="item.unit_price"
                                                @input="calculateItemTotal(idx)"
                                                class="w-24 h-8 px-2 rounded border text-right"
                                                placeholder="0" />
                                        </td>
                                        <td class="p-2 text-right font-medium">
                                            {{ formatCurrency(item.total_price) }}
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50 font-semibold">
                                        <td colspan="4" class="p-2 text-right">Total:</td>
                                        <td class="p-2 text-right">{{ formatCurrency(totalPOAmount) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </form>
                </div>
                <div class="px-4 py-3 border-t flex justify-end gap-2">
                    <button @click="showPOForm = false" class="px-3 h-9 rounded border">Batal</button>
                    <button @click="submitPO" class="px-3 h-9 rounded bg-blue-600 text-white">Buat PO</button>
                </div>
            </div>
        </div>

        <!-- Rejection Confirmation Modal -->
        <div v-if="showConfirmRejected" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="w-[500px] max-w-[95vw] rounded-lg bg-white shadow">
                <div class="px-4 py-3 border-b">
                    <div class="font-semibold text-red-600">Tolak Pengajuan Pembelian</div>
                </div>
                <div class="p-4">
                    <div class="text-sm text-gray-600 mb-4">
                        Apakah Anda yakin ingin menolak pengajuan pembelian <strong>{{ pr.request_no }}</strong>?
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan *</label>
                        <textarea
                            v-model="rejectionForm.reason"
                            rows="4"
                            class="w-full px-3 py-2 rounded border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            placeholder="Masukkan alasan penolakan pengajuan ini..."
                            required
                        ></textarea>
                    </div>
                    <div class="text-xs text-gray-500">
                        Alasan penolakan akan dikirimkan kepada pengaju dan tersimpan dalam log aktivitas.
                    </div>
                </div>
                <div class="px-4 py-3 border-t flex justify-end gap-2">
                    <button @click="closeConfirmRejected" class="px-3 h-9 rounded border hover:bg-gray-50">Batal</button>
                    <button @click="submitRejected" class="px-3 h-9 rounded bg-red-600 text-white hover:bg-red-700">Ya, Tolak</button>
                </div>
            </div>
        </div>

        <ConfirmModal :show="showConfirmCancel" :question="`Yakin ingin membatalkan pengajuan ini?`"
            :selected="`${pr.request_no}`" title="Batalkan Pengajuan" confirmText="Ya, Batalkan!" maxWidth="md"
            @close="closeConfirmCancel" @confirm="submitCancel" />
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Breadcrumb from '@/Components/common/Breadcrumb.vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import ConfirmModal from '@/Components/common/ConfirmModal.vue'

const props = defineProps({
    pr: { type: Object, required: true },
    activities: { type: Array, default: () => [] },
    can_approve: { type: Boolean, default: false },
    vendors: { type: Array, default: () => [] },
})

const pr = props.pr
const activities = props.activities
const can_approve = props.can_approve
const vendors = props.vendors

const breadcrumbs = [
    { label: 'Penyimpanan', href: route('dashboard') },
    { label: 'Pengajuan Pembelian', href: route('purchase-requests.index') },
    { label: pr.request_no },
]

const activeTab = ref('items')
const showApprove = ref(false)
const approvals = ref([])
const showConfirmCancel = ref(false)
const showConfirmRejected = ref(false)
const showConvertConfirm = ref(false)
const showPOForm = ref(false)

// PO Form data
const poForm = ref({
    vendor: '',
    po_date: new Date().toISOString().split('T')[0],
    notes: '',
    items: []
})

// Validation errors
const vendorError = ref('')

// Rejection form data
const rejectionForm = ref({
    reason: ''
})

// Computed properties
const totalPOAmount = computed(() => {
    return poForm.value.items.reduce((total, item) => total + (item.total_price || 0), 0)
})

defineOptions({
    layout: AppLayout,
})

function statusLabel(s) {
    const map = {
        draft: 'Draft',
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
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-500/20 dark:text-gray-300',
        on_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-300',
        approved: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
        rejected: 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300',
        canceled: 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300',
        partial_approved: 'bg-violet-100 text-violet-800 dark:bg-violet-500/20 dark:text-violet-300',
    }
    return map[s] ?? 'bg-gray-100 text-gray-700'
}

function formatDate(date) {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

function onDelete() {
    if (!confirm('Hapus pengajuan pembelian ini?')) return
    router.delete(route('purchase-requests.destroy', pr.id))
}

function onRejected() {
    rejectionForm.value.reason = ''
    showConfirmRejected.value = true
}

function closeConfirmRejected() {
    showConfirmRejected.value = false
}

function submitRejected() {
    if (!rejectionForm.value.reason.trim()) {
        alert('Alasan penolakan harus diisi')
        return
    }

    router.post(route('purchase-requests.rejected', pr.id),
        { reason: rejectionForm.value.reason },
        {
            preserveScroll: true,
            onFinish: () => {
                window.location.reload();
                showConfirmRejected.value = false
            }
        }
    )
}

function openApprove() {
    approvals.value = (pr.items || []).map(it => ({
        id: it.id,
        item: it.item,
        quantity_requested: it.quantity_requested,
        quantity_approved: it.quantity_approved ?? it.quantity_requested,
        note: it.note || '',
    }))
    showApprove.value = true
}

function submitApprove() {
    router.post(route('purchase-requests.approve', pr.id),
        { approvals: approvals.value },
        { preserveScroll: true, onFinish: () => {
            showApprove.value = false;
            window.location.reload();
        } }
    )
}

function onCancel() {
    showConfirmCancel.value = true
}

function closeConfirmCancel() {
    showConfirmCancel.value = false
}

function submitCancel() {
    router.post(route('purchase-requests.cancel', pr.id),
        {},
        { preserveScroll: true, onFinish: () => {
            showConfirmCancel.value = false;
            window.location.reload();
        } }
    )
}

// Convert to PO functions
function openConvertToPO() {
    showConvertConfirm.value = true
}

function confirmConvertToPO() {
    showConvertConfirm.value = false

    // Initialize PO form with approved items
    poForm.value.items = (pr.items || [])
        .filter(item => item.quantity_approved > 0)
        .map(item => ({
            item: item.item,
            quantity: item.quantity_approved,
            unit_price: 0,
            total_price: 0
        }))

    showPOForm.value = true
}

function calculateItemTotal(index) {
    const item = poForm.value.items[index]
    item.total_price = (item.quantity || 0) * (item.unit_price || 0)
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount)
}

function validateVendor() {
    if (!poForm.value.vendor || !poForm.value.vendor.trim()) {
        vendorError.value = 'Vendor harus diisi'
        return false
    }
    vendorError.value = ''
    return true
}

function submitPO() {
    // Validate vendor
    if (!validateVendor()) {
        return
    }

    // Validate PO date
    if (!poForm.value.po_date) {
        alert('Tanggal PO harus diisi')
        return
    }

    // Validate items have prices
    if (poForm.value.items.some(item => item.unit_price <= 0)) {
        alert('Masukkan harga untuk semua item')
        return
    }

    router.post(route('purchase-orders.store'), {
        request_id: pr.id,
        order_no: '', // Will be auto-generated
        ordered_at: poForm.value.po_date,
        vendor: poForm.value.vendor,
        branch_id: pr.department?.branch_id,
        note: poForm.value.notes,
        items: poForm.value.items.map(item => ({
            item_id: item.item.id,
            quantity_ordered: item.quantity,
            cost: item.unit_price,
            note: ''
        }))
    }, {
        onSuccess: () => {
            showPOForm.value = false
            // Redirect to PO show page or refresh current page
        }
    })
}
</script>
