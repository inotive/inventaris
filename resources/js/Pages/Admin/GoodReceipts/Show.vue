<template>
    <Head :title="`Detail Penerimaan Barang - ${goodReceipt.source}`" />
    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div class="flex gap-2">
                <!-- Show Update Barang Diterima button if there are items with partial receipt -->
                <button
                    v-if="hasPartialReceipt && canUpdate"
                    @click="onReceive"
                    class="inline-flex items-center rounded bg-amber-600 px-3 py-2 text-sm font-medium text-white hover:bg-amber-700"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Update Barang Diterima
                </button>
            </div>
        </div>

        <!-- Header Information -->
        <div
            class="space-y-5 overflow-hidden rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-white/[0.03]"
        >
            <div class="flex items-center justify-between">
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Detail Penerimaan Barang
                </div>
                <div class="font-bold text-sky-400 md:text-xl px-7">
                    {{ goodReceipt.source }}
                </div>
            </div>

            <!-- Information Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Penerimaan
                    </label>
                    <div class="h-10 px-3 flex items-center border border-gray-300 rounded-md text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                        {{ formatDate(goodReceipt.received_at) }}
                    </div>
                </div>

                <div class="space-y-2" v-if="goodReceipt.order">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        No. Purchase Order
                    </label>
                    <div class="h-10 px-3 flex items-center border border-gray-300 rounded-md text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                        {{ goodReceipt.order.order_no }}
                    </div>
                </div>

                <div class="space-y-2" v-if="goodReceipt.transfer">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        No. Transfer
                    </label>
                    <div class="h-10 px-3 flex items-center border border-gray-300 rounded-md text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                        {{ goodReceipt.transfer.transfer_no }}
                    </div>
                </div>

                <div class="space-y-2" v-if="goodReceipt.order">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Vendor
                    </label>
                    <div class="h-10 px-3 flex items-center border border-gray-300 rounded-md text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                        {{ goodReceipt.order.vendor || '-' }}
                    </div>
                </div>

                <div class="space-y-2" v-if="goodReceipt.transfer">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Dari Cabang
                    </label>
                    <div class="h-10 px-3 flex items-center border border-gray-300 rounded-md text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                        {{ goodReceipt.transfer.from_branch?.name || '-' }}
                    </div>
                </div>

                <div class="space-y-2" v-if="goodReceipt.transfer">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Ke Cabang
                    </label>
                    <div class="h-10 px-3 flex items-center border border-gray-300 rounded-md text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                        {{ goodReceipt.transfer.to_branch?.name || '-' }}
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Penerima
                    </label>
                    <div class="h-10 px-3 flex items-center border border-gray-300 rounded-md text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                        {{ goodReceipt.receiver?.name || '-' }}
                    </div>
                </div>

                <div class="space-y-2 md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Catatan
                    </label>
                    <div class="px-3 py-2 border border-gray-300 rounded-md text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 min-h-[40px] flex items-center">
                        {{ goodReceipt.note || '-' }}
                    </div>
                </div>

                <div class="space-y-2 md:col-span-2 lg:col-span-3" v-if="attachmentsList.length > 0">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Lampiran Penerimaan
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <a v-for="(file, idx) in attachmentsList" :key="idx" :href="file.url" @click.prevent="triggerPreview(file.url, file.name)" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 hover:bg-blue-100 rounded-md text-sm text-blue-700 dark:bg-blue-950/20 dark:border-blue-900/50 dark:text-blue-400 dark:hover:bg-blue-950/45 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="max-w-[200px] truncate" :title="file.name">{{ file.name }}</span>
                        </a>
                    </div>
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
            </div>

            <div v-if="activeTab === 'items'" class="overflow-auto" data-simplebar>
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
                                        Jumlah Dikirim
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
                                        Jumlah Diterima
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Rusak / Cacat
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 border border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Hilang
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
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!goodReceipt.items || goodReceipt.items.length === 0">
                            <td colspan="8" class="py-8 text-center text-gray-500">
                                Tidak ada item.
                            </td>
                        </tr>
                        <tr
                            v-for="(item, i) in goodReceipt.items"
                            :key="item.id"
                            class="hover:bg-gray-100 dark:hover:bg-gray-800"
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
                                <div class="px-3 py-2">
                                    {{ item.item?.name || '-' }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center px-3 whitespace-nowrap justify-center"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ item.item?.code || '-' }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center gap-3 ps-6 whitespace-nowrap"
                                >
                                    <span class="font-medium">{{ item.quantity_transferred }}</span>
                                    {{ item.item?.unit?.short_name || '' }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center gap-3 ps-6 whitespace-nowrap"
                                >
                                    <span class="font-medium">{{ item.quantity_received }}</span>
                                    {{ item.item?.unit?.short_name || '' }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center gap-3 ps-6 whitespace-nowrap"
                                >
                                    <span :class="getDisposalQty(item.item_id, 'rusak') > 0 ? 'text-red-600 font-semibold' : 'text-gray-500'">
                                        {{ getDisposalQty(item.item_id, 'rusak') }}
                                    </span>
                                    {{ item.item?.unit?.short_name || '' }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center gap-3 ps-6 whitespace-nowrap"
                                >
                                    <span :class="getDisposalQty(item.item_id, 'hilang') > 0 ? 'text-amber-600 font-semibold' : 'text-gray-500'">
                                        {{ getDisposalQty(item.item_id, 'hilang') }}
                                    </span>
                                    {{ item.item?.unit?.short_name || '' }}
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap justify-center"
                                >
                                    <div class="w-40 h-10 text-sm px-3 py-2 flex items-center text-gray-500 dark:text-gray-400">
                                        {{ item.note || '-' }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
                                    Update Barang Diterima
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Masukkan jumlah tambahan barang yang diterima untuk setiap item:
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Dikirim</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Sudah Diterima</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Tambahan Diterima</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template v-for="(item, index) in goodReceipt.items" :key="item.id">
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ item.item?.name || '-' }}</div>
                                                    <div class="text-sm text-gray-500">{{ item.item?.code || '-' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ item.quantity_transferred }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    <span class="font-medium">{{ item.quantity_received || 0 }}</span>
                                                    <span class="text-gray-400">/ {{ item.quantity_transferred }}</span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center gap-2">
                                                        <input
                                                            v-model.number="receivedQuantities[index]"
                                                            type="number"
                                                            min="0"
                                                            :max="item.quantity_transferred - (item.quantity_received || 0)"
                                                            :class="[
                                                                'w-24 px-3 py-1.5 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm',
                                                                isNoteRequired(index) ? 'border-orange-300 bg-orange-50' : 'border-gray-300'
                                                            ]"
                                                            placeholder="0"
                                                        />
                                                        <span v-if="isNoteRequired(index)" class="text-orange-500 text-xs" title="Catatan wajib diisi">
                                                            ⚠️
                                                        </span>
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        Sisa: {{ (item.quantity_transferred || 0) - (item.quantity_received || 0) }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ item.item?.unit?.short_name || '-' }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    <input
                                                        v-model="itemNotes[index]"
                                                        type="text"
                                                        :class="[
                                                            'w-full px-3 py-1.5 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm',
                                                            isNoteRequired(index) ? 'border-red-300 bg-red-50' : 'border-gray-300'
                                                        ]"
                                                        :placeholder="isNoteRequired(index) ? 'Catatan wajib diisi!' : 'Catatan item (opsional)'"
                                                    />
                                                    <div v-if="isNoteRequired(index)" class="text-xs text-red-600 mt-1">
                                                        * Wajib diisi karena jumlah tidak sesuai
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Expandable Shortage Allocation Section -->
                                            <tr v-if="hasShortage(index)" class="bg-amber-50/20">
                                                <td colspan="6" class="px-6 py-3 border-t border-gray-100">
                                                    <div class="bg-white dark:bg-transparent border border-dashed border-amber-300 rounded-lg p-4 space-y-3">
                                                        <div class="flex items-center gap-2 text-sm font-semibold text-amber-700">
                                                            <span>⚠️ Rincian Kekurangan Barang</span>
                                                            <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold">
                                                                Kekurangan: {{ getShortageQty(index) }} {{ item.item?.unit?.short_name || 'pcs' }}
                                                            </span>
                                                        </div>
                                                        
                                                        <!-- Direct Inputs for Rusak and Hilang -->
                                                        <div class="flex flex-wrap items-center gap-6 mt-2">
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-xs text-gray-600 font-medium">Rusak / Cacat:</span>
                                                                <input 
                                                                    v-model.number="shortages[index].rusak" 
                                                                    type="number" 
                                                                    min="0" 
                                                                    :max="getShortageQty(index)"
                                                                    @input="updateRusakShortage(index, shortages[index].rusak)"
                                                                    class="w-16 h-8 text-xs rounded-md border-gray-300 text-center focus:ring-1 focus:ring-blue-500" 
                                                                />
                                                                <span class="text-xs text-gray-500">{{ item.item?.unit?.short_name || 'pcs' }}</span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-xs text-gray-600 font-medium">Hilang:</span>
                                                                <input 
                                                                    v-model.number="shortages[index].hilang" 
                                                                    type="number" 
                                                                    min="0" 
                                                                    :max="getShortageQty(index)"
                                                                    @input="updateHilangShortage(index, shortages[index].hilang)"
                                                                    class="w-16 h-8 text-xs rounded-md border-gray-300 text-center focus:ring-1 focus:ring-blue-500" 
                                                                />
                                                                <span class="text-xs text-gray-500">{{ item.item?.unit?.short_name || 'pcs' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
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
                            Update Penerimaan
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

    <!-- Reusable Preview Modal -->
    <div v-if="previewOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm transition-opacity">
        <div class="relative max-w-5xl w-full bg-white dark:bg-gray-900 rounded-lg overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 truncate">{{ previewFileName }}</span>
                <div class="flex items-center gap-2">
                    <a :href="previewUrl" target="_blank" class="px-3 py-1 text-xs bg-blue-100 text-blue-700 hover:bg-blue-200 rounded font-medium transition">Buka di Tab Baru</a>
                    <button @click="closePreview" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl font-bold px-2 line-height-1">&times;</button>
                </div>
            </div>
            <div class="flex-1 overflow-auto flex items-center justify-center p-6 min-h-[300px]">
                <!-- Image Preview -->
                <img v-if="isPreviewImage" :src="previewUrl" class="max-w-full max-h-[60vh] object-contain rounded shadow" />
                
                <!-- PDF Preview -->
                <iframe v-else-if="isPreviewPdf" :src="previewUrl" class="w-full h-[65vh] border-0 rounded" />
                
                <!-- Fallback Info -->
                <div v-else class="text-center py-10 space-y-3">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Pratinjau tidak tersedia untuk jenis file ini.</p>
                    <a :href="previewUrl" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-medium transition shadow">
                        Unduh File
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import BackIcon from "@/Components/icons/BackIcon.vue";
import { Head, Link, router } from "@inertiajs/vue3";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Penyimpanan" },
    { label: "Penerimaan Barang", href: route("good-receipts.index") },
    { label: "Detail Penerimaan" },
];

const props = defineProps({
    goodReceipt: { type: Object, required: true },
    departments: { type: Array, required: true },
    employees: { type: Array, required: true },
    activities: { type: Array, default: () => [] },
    canUpdate: { type: Boolean, default: false },
});

const getDisposalQty = (itemId, reason) => {
    if (!props.goodReceipt.disposals) return 0;
    return props.goodReceipt.disposals
        .filter(d => d.item_id === itemId && d.reason === reason)
        .reduce((sum, d) => sum + Number(d.quantity), 0);
};

const activeTab = ref('items');

const attachmentsList = computed(() => {
    let paths = props.goodReceipt.attachment_path;
    let names = props.goodReceipt.attachment_name;
    if (!paths) return [];

    if (typeof paths === 'string') {
        try {
            if (paths.trim().startsWith('[') && paths.trim().endsWith(']')) {
                paths = JSON.parse(paths);
            }
        } catch (e) {}
    }
    if (typeof names === 'string') {
        try {
            if (names.trim().startsWith('[') && names.trim().endsWith(']')) {
                names = JSON.parse(names);
            }
        } catch (e) {}
    }

    if (Array.isArray(paths)) {
        return paths.map((path, index) => {
            const name = (Array.isArray(names) && names[index]) ? names[index] : `Lampiran ${index + 1}`;
            return { path, name, url: `/attachments/view?path=${encodeURIComponent(path)}` };
        });
    }

    if (typeof paths === 'string') {
        const name = typeof names === 'string' ? names : 'Lampiran';
        return [{ path: paths, name, url: `/attachments/view?path=${encodeURIComponent(paths)}` }];
    }

    return [];
});

// Modal state
const showReceiveModal = ref(false);
const receivedQuantities = ref([]);
const itemNotes = ref([]);
const shortages = ref([]);

// Preview Modal State
const previewOpen = ref(false)
const previewUrl = ref('')
const previewFileName = ref('')

const isPreviewImage = computed(() => {
    if (!previewFileName.value) return false
    return /\.(jpg|jpeg|png|webp|gif)$/i.test(previewFileName.value) || (previewUrl.value && previewUrl.value.startsWith('data:image/'))
})

const isPreviewPdf = computed(() => {
    if (!previewFileName.value) return false
    return /\.pdf$/i.test(previewFileName.value)
})

function triggerPreview(url, name) {
    previewUrl.value = url
    previewFileName.value = name
    previewOpen.value = true
}

function closePreview() {
    previewOpen.value = false
    previewUrl.value = ''
    previewFileName.value = ''
}

// Check if there are items with partial receipt and good receipt has order_id (source = Pembelian)
const hasPartialReceipt = computed(() => {
    // Only show button if good receipt has order_id (source = Pembelian)
    if (!props.goodReceipt.order?.id) {
        return false;
    }

    return props.goodReceipt.items?.some(item => {
        const transferred = item.quantity_transferred || 0;
        const received = item.quantity_received || 0;
        return received < transferred;
    }) || false;
});

// Watch for quantity changes to update shortages and note requirements
watch(receivedQuantities, (newVal) => {
    newVal.forEach((qty, index) => {
        const item = props.goodReceipt.items[index]
        if (!item) return
        const expected = Number(item.quantity_transferred || 0) - Number(item.quantity_received || 0)
        const diff = expected - Number(qty || 0)
        
        if (diff > 0) {
            if (!shortages.value[index] || (shortages.value[index].rusak === 0 && shortages.value[index].hilang === 0)) {
                shortages.value[index] = {
                    rusak: diff,
                    hilang: 0
                }
            } else {
                const totalAllocated = Number(shortages.value[index].rusak || 0) + Number(shortages.value[index].hilang || 0)
                if (totalAllocated !== diff) {
                    shortages.value[index] = {
                        rusak: diff,
                        hilang: 0
                    }
                }
            }
        } else {
            shortages.value[index] = {
                rusak: 0,
                hilang: 0
            }
        }
    })
}, { deep: true });

function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function isNoteRequired(index) {
    const item = props.goodReceipt.items[index];
    if (!item) return false;

    const transferredQty = item.quantity_transferred || 0;
    const alreadyReceived = item.quantity_received || 0;
    const additionalQty = receivedQuantities.value[index] || 0;
    const totalReceived = alreadyReceived + additionalQty;
    const remaining = transferredQty - alreadyReceived;

    // Catatan wajib jika:
    // 1. Ada jumlah tambahan yang diisi (lebih dari 0)
    // 2. Total yang diterima tidak sama dengan yang dikirim
    // 3. Jumlah tambahan tidak sama dengan sisa yang harus diterima
    if (additionalQty > 0 && totalReceived !== transferredQty) {
        return true;
    }

    // Jika jumlah tambahan 0 tetapi masih ada sisa, tidak wajib catatan
    return false;
}

function onReceive() {
    showReceiveModal.value = true;

    // Initialize with 0 for additional quantities
    receivedQuantities.value = props.goodReceipt.items?.map(() => 0) || [];

    // Reset item notes
    itemNotes.value = props.goodReceipt.items?.map(item => item.note || '') || [];

    // Initialize shortages from existing disposals
    shortages.value = props.goodReceipt.items?.map(item => {
        const itemDisposals = props.goodReceipt.disposals || [];
        const rusak = itemDisposals
            .filter(d => d.item_id === item.item_id && d.reason === 'rusak')
            .reduce((sum, d) => sum + Number(d.quantity), 0);
        const hilang = itemDisposals
            .filter(d => d.item_id === item.item_id && d.reason === 'hilang')
            .reduce((sum, d) => sum + Number(d.quantity), 0);
        return { rusak, hilang };
    }) || [];
}

function closeReceiveModal() {
    showReceiveModal.value = false;
    receivedQuantities.value = [];
    itemNotes.value = [];
    shortages.value = [];
}

const hasShortage = (index) => {
    const item = props.goodReceipt.items[index]
    if (!item) return false
    const expected = Number(item.quantity_transferred || 0) - Number(item.quantity_received || 0)
    const receivedQty = Number(receivedQuantities.value[index] || 0)
    return receivedQty < expected
}

const getShortageQty = (index) => {
    const item = props.goodReceipt.items[index]
    if (!item) return 0
    const expected = Number(item.quantity_transferred || 0) - Number(item.quantity_received || 0)
    const receivedQty = Number(receivedQuantities.value[index] || 0)
    return Math.max(0, expected - receivedQty)
}

const getRemainingShortageToAllocate = (index) => {
    const totalShortage = getShortageQty(index)
    if (totalShortage <= 0) return 0
    const sh = shortages.value[index] || { rusak: 0, hilang: 0 }
    const allocated = Number(sh.rusak || 0) + Number(sh.hilang || 0)
    return totalShortage - allocated
}

const updateRusakShortage = (index, val) => {
    const total = getShortageQty(index)
    let r = Math.min(total, Math.max(0, Number(val) || 0))
    if (!shortages.value[index]) {
        shortages.value[index] = { rusak: 0, hilang: 0 }
    }
    shortages.value[index].rusak = r
    shortages.value[index].hilang = total - r
}

const updateHilangShortage = (index, val) => {
    const total = getShortageQty(index)
    let h = Math.min(total, Math.max(0, Number(val) || 0))
    if (!shortages.value[index]) {
        shortages.value[index] = { rusak: 0, hilang: 0 }
    }
    shortages.value[index].hilang = h
    shortages.value[index].rusak = total - h
}

function confirmReceive() {
    // Validate that additional quantities don't exceed remaining quantities
    const exceedsRemaining = receivedQuantities.value.some((qty, index) => {
        const item = props.goodReceipt.items[index];
        if (!item) return false;
        const transferredQty = item.quantity_transferred || 0;
        const alreadyReceived = item.quantity_received || 0;
        const remaining = transferredQty - alreadyReceived;
        return qty > remaining;
    });

    if (exceedsRemaining) {
        alert('Jumlah tambahan tidak boleh melebihi sisa yang belum diterima')
        return;
    }

    // Validate that shortages are fully allocated
    const unallocatedShortage = receivedQuantities.value.some((qty, index) => {
        if (hasShortage(index)) {
            return getRemainingShortageToAllocate(index) !== 0;
        }
        return false;
    });

    if (unallocatedShortage) {
        alert('Mohon alokasikan semua kekurangan barang (rusak/cacat atau hilang) secara lengkap');
        return;
    }

    // Validate that notes are filled when quantities don't match
    const missingNotes = receivedQuantities.value.some((qty, index) => {
        const item = props.goodReceipt.items[index];
        if (!item) return false;
        const transferredQty = item.quantity_transferred || 0;
        const alreadyReceived = item.quantity_received || 0;
        const totalReceived = alreadyReceived + (qty || 0);
        const note = itemNotes.value[index] || '';

        // Catatan wajib jika ada jumlah tambahan yang diisi tetapi total tidak sama dengan yang dikirim
        if ((qty || 0) > 0 && totalReceived !== transferredQty && (!note || note.trim() === '')) {
            return true;
        }
        return false;
    });

    if (missingNotes) {
        alert('Catatan penerimaan wajib diisi untuk item yang jumlahnya tidak sesuai dengan yang dikirim')
        return;
    }

    // Check if good receipt has order_id (source = Pembelian)
    if (!props.goodReceipt.order?.id) {
        alert('Good receipt ini tidak terkait dengan Purchase Order');
        return;
    }

    const orderId = props.goodReceipt.order.id;
    const purchaseOrderItems = props.goodReceipt.order?.items || [];

    if (!purchaseOrderItems || purchaseOrderItems.length === 0) {
        alert('Data Purchase Order items tidak ditemukan');
        return;
    }

    // Prepare data for submission - format sesuai purchase-orders.update-receive
    try {
        const receiveData = {
            quantities: receivedQuantities.value.map((qty, index) => {
                const goodReceiptItem = props.goodReceipt.items[index];
                // Find purchase order item by item_id
                const poItem = purchaseOrderItems.find(poItem =>
                    poItem.item_id === goodReceiptItem.item_id ||
                    poItem.item?.id === goodReceiptItem.item?.id ||
                    (poItem.item?.code && goodReceiptItem.item?.code && poItem.item.code === goodReceiptItem.item.code)
                );

                if (!poItem) {
                    throw new Error(`Item ${goodReceiptItem.item?.name || goodReceiptItem.item?.code} tidak ditemukan di Purchase Order`);
                }

                const itemPayload = {
                    item_id: poItem.id, // purchase_order_item.id
                    additional_quantity: qty || 0,
                    receive_note: itemNotes.value[index] || ''
                };

                if (hasShortage(index) && shortages.value[index]) {
                    const sh = shortages.value[index];
                    const list = [];
                    if (Number(sh.rusak || 0) > 0) {
                        list.push({
                            reason: 'rusak',
                            quantity: Number(sh.rusak),
                            description: itemNotes.value[index] || ''
                        });
                    }
                    if (Number(sh.hilang || 0) > 0) {
                        list.push({
                            reason: 'hilang',
                            quantity: Number(sh.hilang),
                            description: itemNotes.value[index] || ''
                        });
                    }
                    itemPayload.shortages = list;
                }

                return itemPayload;
            })
        };

        // Submit the data - using purchase-orders.update-receive route
        router.post(route('purchase-orders.update-receive', orderId), receiveData, {
            onSuccess: () => {
                closeReceiveModal();
                window.location.reload();
            },
            onError: (errors) => {
                console.error('Error receiving goods:', errors);
                alert('Terjadi kesalahan saat menyimpan data');
            }
        });
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'Terjadi kesalahan saat memproses data');
    }
}
</script>
