<template>
    <Head :title="`Pemindahan Barang ${goodTransfer.transfer_no}`" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div class="flex items-center gap-2">
                <button v-if="goodTransfer.status === 'Shipped' && isReceived" @click="onReceive" class="inline-flex items-center rounded bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700">Terima Barang</button>
                <button v-if="isCancelable " @click="onCancel" class="inline-flex items-center rounded bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">Batalkan</button>
                <button @click="onPrintReceipt" class="inline-flex items-center rounded bg-gray-600 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Cetak Nota
                </button>
            </div>
        </div>

        <!-- Row 1: Information Card -->
        <div class="flex flex-col gap-4 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">Informasi Pemindahan Barang</div>
                <div>
                    <span :class="badgeClass(goodTransfer.status)" class="px-2.5 py-1 rounded-full text-xs font-medium">{{ statusLabel(goodTransfer.status) }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <div class="text-xs text-gray-500">No. Transfer</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ goodTransfer.transfer_no }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Dari Cabang</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ goodTransfer.from_branch }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Ke Cabang</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ goodTransfer.to_branch }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Dikirim oleh</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ goodTransfer.sent_by }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Tanggal Transfer</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ goodTransfer.transferred_at }}</div>
                </div>
                <div v-if="goodTransfer.received_by">
                    <div class="text-xs text-gray-500">Diterima oleh</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ goodTransfer.received_by }}</div>
                </div>
                <div class="md:col-span-2 xl:col-span-3">
                    <div class="text-xs text-gray-500">Tujuan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ goodTransfer.purpose || '-' }}</div>
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
                        <col style="width:200px" />
                        <col style="width:150px" />
                        <col style="width:100px" />
                        <col style="width:100px" />
                        <col style="width:100px" />
                        <col style="width:120px" />
                        <col />
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">No</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Nama Item</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Kode</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Qty Dikirim</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Qty Diterima</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Satuan</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Catatan</div></th>
                            <th class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800"><div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Catatan Penerimaan</div></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!items || items.length === 0">
                            <td colspan="8" class="text-center py-8 text-gray-500">Tidak ada item</td>
                        </tr>
                        <tr v-for="(it, idx) in items" :key="it.id" class="border-b border-gray-200 dark:border-gray-700">
                            <td class="px-3 py-2">{{ idx + 1 }}</td>
                            <td class="px-3 py-2">{{ it.item_name }}</td>
                            <td class="px-3 py-2 font-mono">{{ it.item_code }}</td>
                            <td class="px-3 py-2">{{ it.quantity_transferred }}</td>
                            <td class="px-3 py-2">
                                <span v-if="it.quantity_received !== null && it.quantity_received !== undefined"
                                      :class="it.quantity_received > 0 ? 'text-green-600 font-semibold' : 'text-gray-500'">
                                    {{ it.quantity_received || 0 }}
                                </span>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="px-3 py-2">{{ it.unit }}</td>
                            <td class="px-3 py-2">{{ it.note || '-' }}</td>
                            <td class="px-3 py-2">
                                <span v-if="it.note_received" class="text-sm text-gray-700 bg-green-50 px-2 py-1 rounded">
                                    {{ it.note_received }}
                                </span>
                                <span v-else class="text-gray-400">-</span>
                            </td>
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

        <!-- Confirmation Modal -->
        <ConfirmModal
            :show="isConfirmModalOpen"
            :question="modalConfig.question"
            :selected="goodTransfer.transfer_no"
            :title="modalConfig.title"
            :confirmText="modalConfig.confirmText"
            maxWidth="md"
            @close="closeConfirmModal"
            @confirm="executeAction"
        />

        <!-- Receive Goods Modal -->
        <div v-if="showReceiveModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeReceiveModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Konfirmasi Penerimaan Barang
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Masukkan jumlah barang yang diterima untuk setiap item:
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Dikirim</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Diterima</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(item, index) in items" :key="item.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ item.item_name }}</div>
                                                <div class="text-sm text-gray-500">{{ item.item_code }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <!-- Tambahkan input quantity_transferred readonly -->
                                                <input
                                                    type="number"
                                                    :value="item.quantity_transferred"
                                                    readonly
                                                    class="w-20 px-3 py-1 border rounded-md bg-gray-100 text-gray-700 border-gray-300 focus:outline-none"
                                                />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <input
                                                        v-model="receivedQuantities[index]"
                                                        type="number"
                                                        min="0"
                                                        :max="item.quantity_transferred"
                                                        :class="[
                                                            'w-20 px-3 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent',
                                                            isNoteRequired(index) ? 'border-orange-300 bg-orange-50' : 'border-gray-300'
                                                        ]"
                                                        :placeholder="item.quantity_transferred"
                                                    />
                                                    <span v-if="isNoteRequired(index)" class="text-orange-500 text-xs" title="Catatan wajib diisi">
                                                        ⚠️
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ item.unit }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <input
                                                    v-model="itemNotes[index]"
                                                    type="text"
                                                    :class="[
                                                        'w-full px-3 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm',
                                                        isNoteRequired(index) ? 'border-red-300 bg-red-50' : 'border-gray-300'
                                                    ]"
                                                    :placeholder="isNoteRequired(index) ? 'Catatan wajib diisi!' : 'Catatan item (opsional)'"
                                                />
                                                <div v-if="isNoteRequired(index)" class="text-xs text-red-600 mt-1">
                                                    * Wajib diisi karena jumlah tidak sesuai
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            @click="confirmReceive"
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Konfirmasi Diterima
                        </button>
                        <button
                            @click="closeReceiveModal"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
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
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import Breadcrumb from '@/Components/common/Breadcrumb.vue'
import ConfirmModal from '@/Components/common/ConfirmModal.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    goodTransfer: { type: Object, required: true },
    items: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    user: { type: Object, required: true },
    isSuperadmin: { type: Boolean, required: true },
})

console.log("goodTransfer", props.goodTransfer)
console.log("items", props.items)
console.log("activities", props.activities)
console.log("user", props.user.employee)
console.log("isSuperadmin", props.isSuperadmin)

//jika user superadmin atau branch_id sama dengan to_branch_id maka true
const isReceived = computed(() => {
    return props.isSuperadmin || props.user.employee?.branch_id == props.goodTransfer.to_branch_id
})

const isCancelable = computed(() => {
    return props.isSuperadmin || props.user.employee?.branch_id == props.goodTransfer.from_branch_id && props.goodTransfer.status == 'Shipped'
})


const goodTransfer = props.goodTransfer
const items = props.items
const activities = props.activities

const breadcrumbs = [
    { label: 'Penyimpanan', href: route('dashboard') },
    { label: 'Pemindahan Barang', href: route('good-transfers.index') },
    { label: goodTransfer.transfer_no },
]

const activeTab = ref('items')

// Confirmation modal state
const isConfirmModalOpen = ref(false)
const selectedAction = ref(null)
const modalConfig = ref({
    title: '',
    question: '',
    confirmText: '',
    action: null
})

// Receive goods modal state
const showReceiveModal = ref(false)
const receivedQuantities = ref([])
const itemNotes = ref([])

// Watch for quantity changes to update note requirements
watch(receivedQuantities, () => {
    // This will trigger reactivity for isNoteRequired function
}, { deep: true })

// Check if user can cancel the transfer
const canCancel = computed(() => {
    // Superadmin can always cancel
    if (props.user.roles?.includes('Superadmin')) {
        return true
    }

    // Only the user who created the transfer can cancel
    return props.goodTransfer.sent_by_id === props.user.employee?.id
})

defineOptions({
    layout: AppLayout,
})

function statusLabel(s) {
    const map = {
        Shipped: 'Dikirim',
        Received: 'Diterima',
        Canceled: 'Dibatalkan',
    }
    return map[s] ?? s
}

function badgeClass(s) {
    const map = {
        Shipped: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-300',
        Received: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
        Canceled: 'bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-300',
    }
    return map[s] ?? 'bg-gray-100 text-gray-700'
}

function onReceive() {
    showReceiveModal.value = true
    // Initialize received quantities with transferred quantities as default
    receivedQuantities.value = items?.map(item => item.quantity_transferred || 0) || []
    // Initialize item notes as empty
    itemNotes.value = items?.map(() => '') || []
}

function onCancel() {
    modalConfig.value = {
        title: 'Batalkan Pemindahan Barang',
        question: 'Yakin ingin membatalkan pemindahan barang ini?',
        confirmText: 'Ya, Batalkan!',
        action: 'cancel'
    }
    isConfirmModalOpen.value = true
}

function closeConfirmModal() {
    isConfirmModalOpen.value = false
    selectedAction.value = null
    modalConfig.value = {
        title: '',
        question: '',
        confirmText: '',
        action: null
    }
}

function executeAction() {
    if (modalConfig.value.action === 'receive') {
        router.post(route('good-transfers.receive', goodTransfer.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                closeConfirmModal()
            }
        })
    } else if (modalConfig.value.action === 'cancel') {
        router.post(route('good-transfers.cancel', goodTransfer.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                closeConfirmModal()
            }
        })
    }
}

function isNoteRequired(index) {
    const transferredQty = items[index]?.quantity_transferred || 0
    const receivedQty = receivedQuantities.value[index] || 0
    return receivedQty !== transferredQty
}

function closeReceiveModal() {
    showReceiveModal.value = false
    receivedQuantities.value = []
    itemNotes.value = []
}

function confirmReceive() {
    // Validate that all quantities are filled
    const hasEmptyQuantities = receivedQuantities.value.some(qty => qty === null || qty === undefined || qty === '')

    if (hasEmptyQuantities) {
        alert('Mohon isi semua jumlah barang yang diterima')
        return
    }

    // Validate that received quantities don't exceed transferred quantities
    const exceedsTransferred = receivedQuantities.value.some((qty, index) => {
        const transferredQty = items[index]?.quantity_transferred || 0
        return qty > transferredQty
    })

    if (exceedsTransferred) {
        alert('Jumlah diterima tidak boleh melebihi jumlah yang dikirim')
        return
    }

    // Validate that notes are filled when quantities don't match
    const missingNotes = receivedQuantities.value.some((qty, index) => {
        const transferredQty = items[index]?.quantity_transferred || 0
        const note = itemNotes.value[index] || ''
        return qty !== transferredQty && (!note || note.trim() === '')
    })

    if (missingNotes) {
        alert('Catatan penerimaan wajib diisi untuk item yang jumlahnya tidak sesuai dengan yang dikirim')
        return
    }

    // Prepare data for submission
    const receiveData = {
        quantities: receivedQuantities.value.map((qty, index) => ({
            item_id: items[index].id,
            quantity_transferred: items[index].quantity_transferred,
            quantity_received: qty,
            receive_note: itemNotes.value[index] || ''
        }))
    }

    // Submit the data
    router.post(route('good-transfers.receive', goodTransfer.id), receiveData, {
        onSuccess: () => {
            closeReceiveModal()
            window.location.reload()
        },
        onError: (errors) => {
            console.error('Error receiving goods:', errors)
            alert('Terjadi kesalahan saat menyimpan data')
        }
    })
}

function onPrintReceipt() {
    window.open(route('good-transfers.print-receipt', goodTransfer.id), '_blank')
}
</script>
