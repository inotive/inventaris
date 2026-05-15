<template>
    <Head :title="`Purchase Order ${po.number}`" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">

        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div class="flex items-center gap-2">
                <!-- Show Update Payment button for unpaid orders -->
                <button v-if="po.status_invoice === 'belum_dibayar' || po.status_invoice === 'belum_lunas'" @click="onUpdatePayment"
                    class="inline-flex items-center rounded bg-orange-600 px-3 py-2 text-sm font-medium text-white hover:bg-orange-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    Update Pembayaran
                </button>

                <!-- Show Barang Diterima button when status is pending -->
                <button v-if="po.status_delivered === 'pending'" @click="onReceive"
                    class="inline-flex items-center rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Barang Diterima
                </button>

                <!-- Show Update Barang Diterima button when status is partial -->
                <button v-if="po.status_delivered === 'partial'" @click="onReceive"
                    class="inline-flex items-center rounded bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Update Barang Diterima
                </button>
            </div>
        </div>

        <!-- Row 1: Information Card -->
        <div
            class="flex flex-col gap-4 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">Informasi Purchase Order</div>

                <div class="flex items-center gap-2">
                    <span :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${badgeClass(po.status_delivered)}`">
                        {{ statusLabel(po.status_delivered) }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <div class="text-xs text-gray-500">No. PO</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ po.number }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Vendor</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ po.vendor?.name || '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Dibuat oleh</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ po.ordered_by || '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Cabang</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ po.branch || '-' }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Tanggal PO</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ po.date }}</div>
                </div>
                <div v-if="po.from_pr">
                    <div class="text-xs text-gray-500">Referensi PR</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ po.from_pr?.number }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Total</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ formatCurrency(po.total) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Jumlah Dibayar</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ formatCurrency(po.paid_amount || 0) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Sisa Pembayaran</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ formatCurrency((po.total || 0) - (po.paid_amount || 0)) }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Status Pembayaran</div>
                    <div class="font-semibold">
                        <span :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${paymentBadgeClass(po.status_invoice)}`">
                            {{ paymentStatusLabel(po.status_invoice) }}
                        </span>
                    </div>
                </div>
                <div class="md:col-span-2 xl:col-span-3" v-if="po.note">
                    <div class="text-xs text-gray-500">Catatan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ po.note }}</div>
                </div>
                <div class="md:col-span-2 xl:col-span-3" v-if="po.receive_notes">
                    <div class="text-xs text-gray-500">Catatan Penerimaan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ po.receive_notes }}</div>
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
                        <col style="width:180px" />
                        <col style="width:140px" />
                        <col style="width:90px" />
                        <col style="width:90px" />
                        <col style="width:90px" />
                        <col style="width:90px" />
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
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Qty Dipesan
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Qty Diterima
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Harga Satuan
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Subtotal</div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Satuan</div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Catatan</div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 text-left font-medium text-gray-600 dark:text-gray-300">Catatan Penerimaan</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!po.items || po.items.length === 0">
                            <td colspan="10" class="text-center py-8 text-gray-500">Tidak ada item</td>
                        </tr>
                        <tr v-for="(it, idx) in po.items" :key="it.id"
                            class="border-b border-gray-200 dark:border-gray-700">
                            <td class="px-3 py-2">{{ idx + 1 }}</td>
                            <td class="px-3 py-2">{{ it.item_name }}</td>
                            <td class="px-3 py-2 font-mono">{{ it.item_code }}</td>
                            <td class="px-3 py-2">{{ it.quantity_ordered }}</td>
                            <td class="px-3 py-2">
                                <span :class="it.quantity_received > 0 ? 'text-green-600 font-semibold' : 'text-gray-500'">
                                    {{ it.quantity_received || 0 }}
                                </span>
                            </td>
                            <td class="px-3 py-2">{{ formatCurrency(it.cost) }}</td>
                            <td class="px-3 py-2">{{ formatCurrency(it.quantity_ordered * it.cost) }}</td>
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

        <!-- Print Receipt Button -->
        <div class="flex justify-center mt-4">
            <button @click="onPrintReceipt"
                class="inline-flex items-center rounded bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Nota
            </button>
        </div>
    </div>

    <!-- Modal Barang Diterima -->
    <div v-if="showReceiveModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeReceiveModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ po.status_delivered === 'partial' ? 'Update Barang Diterima' : 'Konfirmasi Barang Diterima' }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ po.status_delivered === 'partial' ? 'Masukkan jumlah tambahan barang yang diterima untuk setiap item:' : 'Masukkan jumlah barang yang diterima untuk setiap item:' }}
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Dipesan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ po.status_delivered === 'partial' ? 'Qty Tambahan Diterima' : 'Qty Diterima' }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in po.items" :key="item.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ item.item_name }}</div>
                                            <div class="text-sm text-gray-500">{{ item.item_code }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ item.quantity_ordered }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <!-- Show current received quantity for partial status -->
                                            <div v-if="po.status_delivered === 'partial'" class="text-xs text-gray-600 mb-2">
                                                <span class="font-medium">Sudah diterima: {{ item.quantity_received || 0 }}</span>
                                                <span class="text-gray-400">/ {{ item.quantity_ordered }}</span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <input
                                                    v-model="receivedQuantities[index]"
                                                    type="number"
                                                    min="0"
                                                    :max="po.status_delivered === 'partial' ? (item.quantity_ordered - (item.quantity_received || 0)) : item.quantity_ordered"
                                                    :class="[
                                                        'w-20 px-3 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                                        isNoteRequired(index) ? 'border-orange-300 bg-orange-50' : 'border-gray-300'
                                                    ]"
                                                    :placeholder="po.status_delivered === 'partial' ? '0' : item.quantity_ordered"
                                                />
                                                <span v-if="isNoteRequired(index)" class="text-orange-500 text-xs" title="Catatan wajib diisi">
                                                    ⚠️
                                                </span>
                                            </div>

                                            <!-- Show remaining quantity for partial status -->
                                            <div v-if="po.status_delivered === 'partial'" class="text-xs text-gray-500 mt-1">
                                                Sisa: {{ (item.quantity_ordered || 0) - (item.quantity_received || 0) }}
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
                                                    'w-full px-3 py-1 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm',
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
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        {{ po.status_delivered === 'partial' ? 'Update Penerimaan' : 'Konfirmasi Diterima' }}
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

    <!-- Modal Update Pembayaran -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="payment-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closePaymentModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="payment-modal-title">
                                Update Pembayaran
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Masukkan jumlah pembayaran yang telah diterima:
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Order</label>
                                <div class="text-lg font-semibold text-gray-900">{{ formatCurrency(po.total) }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah yang Sudah Dibayar</label>
                                <div class="text-sm text-gray-600">{{ formatCurrency(po.paid_amount || 0) }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Pembayaran Tambahan *</label>
                                <input
                                    v-model.number="paymentForm.additional_amount"
                                    type="number"
                                    min="0"
                                    :max="(po.total || 0) - (po.paid_amount || 0)"
                                    step="0.01"
                                    :class="[
                                        'w-full px-3 py-2 rounded border focus:ring-2 focus:ring-orange-500 focus:border-orange-500',
                                        paymentForm.additional_amount > ((po.total || 0) - (po.paid_amount || 0)) ? 'border-red-500 bg-red-50' : 'border-gray-300'
                                    ]"
                                    placeholder="Masukkan jumlah pembayaran tambahan"
                                    required
                                />
                                <div v-if="paymentForm.additional_amount > 0" class="text-xs mt-1" :class="paymentForm.additional_amount > ((po.total || 0) - (po.paid_amount || 0)) ? 'text-red-600' : 'text-gray-500'">
                                    <span v-if="paymentForm.additional_amount > ((po.total || 0) - (po.paid_amount || 0))" class="font-medium">
                                        Jumlah pembayaran melebihi sisa yang harus dibayar!
                                    </span>
                                    <span v-else>
                                        Total setelah pembayaran: {{ formatCurrency((po.paid_amount || 0) + paymentForm.additional_amount) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button
                        @click="confirmPayment"
                        type="button"
                        :disabled="!paymentForm.additional_amount || paymentForm.additional_amount <= 0 || paymentForm.additional_amount > ((po.total || 0) - (po.paid_amount || 0))"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Update Pembayaran
                    </button>
                    <button
                        @click="closePaymentModal"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, reactive, watch } from 'vue'
import Breadcrumb from '@/Components/common/Breadcrumb.vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    po: { type: Object, required: true },
    activities: { type: Array, default: () => [] },
})

const po = props.po
const activities = props.activities

// Modal state
const showReceiveModal = ref(false)
const showPaymentModal = ref(false)
const receivedQuantities = ref([])
const itemNotes = ref([])
const activeTab = ref('items')

// Payment form
const paymentForm = ref({
    additional_amount: 0,
    note: ''
})

// Initialize received quantities and notes arrays
receivedQuantities.value = po.items?.map(item => item.quantity_received || 0) || []
itemNotes.value = po.items?.map(item => item.note_received || '') || []

// Watch for quantity changes to update note requirements
watch(receivedQuantities, () => {
    // This will trigger reactivity for isNoteRequired function
}, { deep: true })

const breadcrumbs = [
    { label: 'Dashboard', href: route('dashboard') },
    { label: 'Purchase Order', href: route('purchase-orders.index') },
    { label: po.number },
]

defineOptions({
    layout: AppLayout,
})

function isNoteRequired(index) {
    const orderedQty = po.items[index]?.quantity_ordered || 0
    const receivedQty = receivedQuantities.value[index] || 0

    if (po.status_delivered === 'partial') {
        // For partial status, check if total received (existing + new) equals ordered
        const alreadyReceived = po.items[index]?.quantity_received || 0
        const totalReceived = alreadyReceived + receivedQty
        return totalReceived !== orderedQty
    } else {
        // For pending status, check if received equals ordered
        return receivedQty !== orderedQty
    }
}

function statusLabel(s) {
    const map = {
        pending: 'Belum Diterima',
        delivered: 'Barang Diterima',
        partial: 'Barang Diterima Sebagian',
    }
    return map[s] ?? s
}

function badgeClass(s) {
    const map = {
        pending: 'bg-blue-100 text-blue-800',
        delivered: 'bg-emerald-100 text-emerald-800',
        partial: 'bg-amber-100 text-amber-800',
    }
    return map[s] ?? 'bg-gray-100 text-gray-700'
}

function paymentStatusLabel(s) {
    const map = {
        belum_dibayar: 'Belum Dibayar',
        belum_lunas: 'Belum Lunas',
        lunas: 'Lunas',
    }
    return map[s] ?? s
}

function paymentBadgeClass(s) {
    const map = {
        belum_dibayar: 'bg-red-100 text-red-800',
        belum_lunas: 'bg-orange-100 text-orange-800',
        lunas: 'bg-green-100 text-green-800',
    }
    return map[s] ?? 'bg-gray-100 text-gray-700'
}

function formatCurrency(n) {
    try {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(n || 0);
    } catch(e){
        return n;
    }
}

function onPrint() {
    window.print()
}

function onPrintReceipt() {
    window.open(route('purchase-orders.print-receipt', po.id), '_blank')
}

function onDelete() {
    if (!confirm('Hapus Purchase Order ini?')) return
    router.delete(route('purchase-orders.destroy', po.id))
}

function onUpdatePayment() {
    showPaymentModal.value = true
    // Reset payment form
    paymentForm.value = {
        additional_amount: 0,
        note: ''
    }
}

function onReceive() {
    showReceiveModal.value = true

    if (po.status_delivered === 'partial') {
        // For partial status, start with 0 for additional quantities
        receivedQuantities.value = po.items?.map(() => 0) || []
    } else {
        // For pending status, use ordered quantities as starting point
        receivedQuantities.value = po.items?.map(item => item.quantity_ordered || 0) || []
    }

    // Reset item notes
    itemNotes.value = po.items?.map(item => item.note_received || '') || []
}

function closeReceiveModal() {
    showReceiveModal.value = false
}

function closePaymentModal() {
    showPaymentModal.value = false
}

function confirmPayment() {
    if (!paymentForm.value.additional_amount || paymentForm.value.additional_amount <= 0) {
        alert('Jumlah pembayaran tambahan harus diisi dan lebih dari 0')
        return
    }

    if (paymentForm.value.additional_amount > ((po.total || 0) - (po.paid_amount || 0))) {
        alert('Jumlah pembayaran tidak boleh melebihi sisa yang harus dibayar')
        return
    }

    // Prepare data for submission
    const paymentData = {
        additional_amount: paymentForm.value.additional_amount,
        note: paymentForm.value.note || ''
    }

    // Submit the data
    router.post(route('purchase-orders.update-payment', po.id), paymentData, {
        onSuccess: () => {
            closePaymentModal()
            window.location.reload()
        },
        onError: (errors) => {
            console.error('Error updating payment:', errors)
            alert('Terjadi kesalahan saat menyimpan data pembayaran')
        }
    })
}

function confirmReceive() {
    if (po.status_delivered === 'partial') {
        // For partial status, validate additional quantities
        const hasEmptyQuantities = receivedQuantities.value.some(qty => qty === null || qty === undefined || qty === '')

        if (hasEmptyQuantities) {
            alert('Mohon isi semua jumlah barang tambahan yang diterima')
            return
        }

        // Validate that additional quantities don't exceed remaining quantities
        const exceedsRemaining = receivedQuantities.value.some((qty, index) => {
            const orderedQty = po.items[index]?.quantity_ordered || 0
            const alreadyReceived = po.items[index]?.quantity_received || 0
            const remaining = orderedQty - alreadyReceived
            return qty > remaining
        })

        if (exceedsRemaining) {
            alert('Jumlah tambahan tidak boleh melebihi sisa yang belum diterima')
            return
        }

        // Validate that notes are filled when quantities don't match
        const missingNotes = receivedQuantities.value.some((qty, index) => {
            const orderedQty = po.items[index]?.quantity_ordered || 0
            const alreadyReceived = po.items[index]?.quantity_received || 0
            const totalReceived = alreadyReceived + qty
            const note = itemNotes.value[index] || ''
            return totalReceived !== orderedQty && (!note || note.trim() === '')
        })

        if (missingNotes) {
            alert('Catatan penerimaan wajib diisi untuk item yang jumlahnya tidak sesuai dengan pesanan')
            return
        }

        // Prepare data for submission - send raw additional quantities
        const receiveData = {
            quantities: receivedQuantities.value.map((qty, index) => ({
                item_id: po.items[index].id,
                additional_quantity: qty,
                receive_note: itemNotes.value[index] || ''
            }))
        }

        // Submit the data
        router.post(route('purchase-orders.update-receive', po.id), receiveData, {
            onSuccess: () => {
                closeReceiveModal()
                window.location.reload()
            },
            onError: (errors) => {
                console.error('Error receiving goods:', errors)
                alert('Terjadi kesalahan saat menyimpan data')
            }
        })
    } else {
        // For pending status, use original validation
        const hasEmptyQuantities = receivedQuantities.value.some(qty => qty === null || qty === undefined || qty === '')

        if (hasEmptyQuantities) {
            alert('Mohon isi semua jumlah barang yang diterima')
            return
        }

        // Validate that received quantities don't exceed ordered quantities
        const exceedsOrdered = receivedQuantities.value.some((qty, index) => {
            const orderedQty = po.items[index]?.quantity_ordered || 0
            return qty > orderedQty
        })

        // Validate that notes are filled when quantities don't match
        const missingNotes = receivedQuantities.value.some((qty, index) => {
            const orderedQty = po.items[index]?.quantity_ordered || 0
            const note = itemNotes.value[index] || ''
            return qty !== orderedQty && (!note || note.trim() === '')
        })

        if (missingNotes) {
            alert('Catatan penerimaan wajib diisi untuk item yang jumlahnya tidak sesuai dengan pesanan')
            return
        }

        // Prepare data for submission
        const receiveData = {
            quantities: receivedQuantities.value.map((qty, index) => ({
                item_id: po.items[index].id,
                quantity_received: qty,
                receive_note: itemNotes.value[index] || ''
            }))
        }

        // Submit the data
        router.post(route('purchase-orders.receive', po.id), receiveData, {
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
}
</script>
