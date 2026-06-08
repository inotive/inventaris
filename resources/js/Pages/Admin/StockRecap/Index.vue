<template>
    <Head title="Rekap Stok" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div>
                <Link
                    :href="route('stock-recap.index')"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-white rounded-md bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                >
                    Tambahkan Data
                </Link>
            </div>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col gap-2 px-8 h-16 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Rekap Stok Barang
                </div>
                <div class="flex gap-3 items-center">
                    <!-- Collapsed Filter Button & Dropdown -->
                    <div class="relative" data-filters-dropdown>
                        <button
                            @click="showFiltersDropdown = !showFiltersDropdown"
                            type="button"
                            class="flex items-center gap-2 px-4 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                            </svg>
                            <span>Filter</span>
                            <!-- Badge for active filter count -->
                            <span v-if="activeFiltersCount > 0" class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-500 rounded-full">
                                {{ activeFiltersCount }}
                            </span>
                        </button>

                        <!-- Dropdown Content -->
                        <div
                            v-if="showFiltersDropdown"
                            class="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-gray-200 bg-white p-4 shadow-xl dark:border-gray-700 dark:bg-gray-800 space-y-4"
                            @click.stop
                        >
                            <!-- Kelompokkan -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelompokkan</label>
                                <select
                                    v-model="groupByLocal"
                                    class="w-full px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Kelompokan Berdasarkan</option>
                                    <option value="source">Group: Kategori</option>
                                </select>
                            </div>

                            <!-- Kategori -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</label>
                                <select
                                    v-model="filtersLocal.category_id"
                                    class="w-full px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua Kategori</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Satuan -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Satuan</label>
                                <select
                                    v-model="filtersLocal.unit_id"
                                    class="w-full px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua Satuan</option>
                                    <option v-for="unit in units" :key="unit.id" :value="unit.id">
                                        {{ unit.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Tanggal Awal -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Awal</label>
                                <input
                                    v-model="tempDateFrom"
                                    type="date"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                />
                            </div>

                            <!-- Tanggal Akhir -->
                            <div class="space-y-1.5">
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Akhir</label>
                                <input
                                    v-model="tempDateTo"
                                    type="date"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                />
                            </div>

                            <!-- Checkbox Hide Empty -->
                            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sembunyikan Stok 0</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="filtersLocal.hide_empty" class="sr-only peer" @change="fetchRequests({})">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <!-- Action Buttons for Dates -->
                            <div class="flex gap-2 pt-2">
                                <button
                                    @click="applyDateFilter"
                                    class="flex-1 px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none"
                                >
                                    Terapkan Tanggal
                                </button>
                                <button
                                    @click="clearDateFilter"
                                    class="flex-1 px-4 py-2 text-xs font-bold text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="relative py-2">
                        <div
                            class="absolute left-4 top-1/2 text-gray-400 -translate-y-1/2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-5"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10.5 3.75a6.75 6.75 0 1 0 4.215 12.06l3.237 3.238a.75.75 0 1 0 1.06-1.061l-3.238-3.237A6.75 6.75 0 0 0 10.5 3.75Zm-5.25 6.75a5.25 5.25 0 1 1 10.5 0 5.25 5.25 0 0 1-10.5 0Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <input
                            v-model="filtersLocal.q"
                            type="text"
                            placeholder="Cari Barang"
                            class="h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-400 dark:focus:border-blue-800 xl:w-[240px]"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-auto overflow-x-auto" data-simplebar>
                <table class="min-w-full text-sm border-collapse">
                    <thead>
                        <tr>
                            <th
                                class="sticky left-0 z-30 py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800 min-w-[50px] max-w-[50px] w-[50px]"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    No.
                                </div>
                            </th>
                            <th
                                class="sticky z-30 py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800 min-w-[220px] max-w-[220px] w-[220px]"
                                style="left: 50px"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300 text-left"
                                >
                                    Nama Barang
                                </div>
                            </th>
                            <th
                                class="sticky z-30 py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800 min-w-[160px] max-w-[160px] w-[160px]"
                                style="left: 270px"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Kategori
                                </div>
                            </th>
                            <th
                                class="sticky z-30 py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800 min-w-[100px] max-w-[100px] w-[100px]"
                                style="left: 430px"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Satuan
                                </div>
                            </th>
                            <th
                                v-for="branch in branches"
                                :key="branch.id"
                                class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800 min-w-[140px]"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap"
                                >
                                    {{ branch.name }}
                                </div>
                            </th>
                            <th
                                class="sticky z-30 py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800 min-w-[120px] max-w-[120px] w-[120px]"
                                style="right: 80px"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Total Persediaan
                                </div>
                            </th>
                            <th 
                                class="sticky right-0 z-30 py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800 min-w-[80px] max-w-[80px] w-[80px]"
                            >
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">
                                    Aksi
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="!stockIns?.data || stockIns.data.length === 0"
                        >
                            <td
                                :colspan="6 + branches.length"
                                class="py-8 text-center text-gray-500"
                            >
                                Tidak ada data
                            </td>
                        </tr>
                        <template v-else-if="isGrouped">
                            <template v-for="(rows, gkey) in groupedData" :key="gkey">
                                <tr>
                                    <td :colspan="6 + branches.length" class="px-4 py-2 font-semibold text-gray-600 bg-gray-50 dark:bg-gray-800/40 dark:text-gray-300">
                                        <div class="sticky left-4 inline-block">
                                            <button class="inline-flex items-center gap-2" @click="toggleGroupCollapse(gkey)">
                                                <span class="text-sm">{{ isCollapsed(gkey) ? "▶" : "▼" }}</span>
                                                <span>{{ groupLabel(gkey) }}</span>
                                                <span class="ml-1 text-sm font-medium text-gray-500">({{ rows.length }})</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <template v-if="!isCollapsed(gkey)">
                                    <tr
                                        v-for="row in rows"
                                        :key="row.id"
                                        class="border-b border-gray-200 dark:border-gray-700"
                                    >
                                        <td class="sticky left-0 z-10 px-3 py-3 bg-white dark:bg-gray-800 min-w-[50px] max-w-[50px] w-[50px]">
                                            {{ serialMap[row.id] ?? "-" }}
                                        </td>
                                        <td 
                                            class="sticky z-10 px-3 py-3 bg-white dark:bg-gray-800 min-w-[220px] max-w-[220px] w-[220px]"
                                            style="left: 50px"
                                        >
                                            <div
                                                class="font-medium text-gray-800 dark:text-gray-200"
                                            >
                                                {{ row.item?.name }}
                                            </div>
                                        </td>
                                        <td 
                                            class="sticky z-10 px-3 py-3 text-center bg-white dark:bg-gray-800 min-w-[160px] max-w-[160px] w-[160px]"
                                            style="left: 270px"
                                        >
                                            <span class="inline-flex px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ row.item?.category || '-' }}
                                            </span>
                                        </td>
                                        <td 
                                            class="sticky z-10 px-3 py-3 text-center bg-white dark:bg-gray-800 min-w-[100px] max-w-[100px] w-[100px]"
                                            style="left: 430px"
                                        >
                                            {{ row.item?.unit || '-' }}
                                        </td>
                                        <td v-for="branch in branches" :key="branch.id" class="px-3 py-3 text-center min-w-[140px]">
                                            <button
                                                v-if="row.stocks[branch.id]"
                                                @click="openTotalPersediaanModal({ item: { name: row.item.name, id: row.stocks[branch.id].item_id }, branch_name: branch.name })"
                                                class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer"
                                            >
                                                {{ row.stocks[branch.id].qty }}
                                            </button>
                                            <span v-else class="text-gray-400">0</span>
                                        </td>
                                        <td 
                                            class="sticky z-10 px-3 py-3 text-center font-semibold text-purple-600 bg-white dark:bg-gray-800 min-w-[120px] max-w-[120px] w-[120px]"
                                            style="right: 80px"
                                        >
                                            {{ row.total_persediaan }}
                                        </td>
                                        <td 
                                            class="sticky right-0 z-10 px-3 py-3 text-center bg-white dark:bg-gray-800 min-w-[80px] max-w-[80px] w-[80px]"
                                        >
                                            <button v-if="can.adjust" @click="openAdjustModal(row)" class="text-blue-500 hover:text-blue-700" title="Sesuaikan Stok">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </template>
                        <template v-else>
                            <tr
                                v-for="row in stockIns.data"
                                :key="row.id"
                                class="border-b border-gray-200 dark:border-gray-700"
                            >
                                <td class="sticky left-0 z-10 px-3 py-3 bg-white dark:bg-gray-800 min-w-[50px] max-w-[50px] w-[50px]">
                                    {{ serialMap[row.id] ?? "-" }}
                                </td>
                                <td 
                                    class="sticky z-10 px-3 py-3 bg-white dark:bg-gray-800 min-w-[220px] max-w-[220px] w-[220px]"
                                    style="left: 50px"
                                >
                                    <div
                                        class="font-medium text-gray-800 dark:text-gray-200"
                                    >
                                        {{ row.item?.name }}
                                    </div>
                                </td>
                                <td 
                                    class="sticky z-10 px-3 py-3 text-center bg-white dark:bg-gray-800 min-w-[160px] max-w-[160px] w-[160px]"
                                    style="left: 270px"
                                >
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ row.item?.category || '-' }}
                                    </span>
                                </td>
                                <td 
                                    class="sticky z-10 px-3 py-3 text-center bg-white dark:bg-gray-800 min-w-[100px] max-w-[100px] w-[100px]"
                                    style="left: 430px"
                                >
                                    {{ row.item?.unit || '-' }}
                                </td>
                                <td v-for="branch in branches" :key="branch.id" class="px-3 py-3 text-center min-w-[140px]">
                                    <button
                                        v-if="row.stocks[branch.id]"
                                        @click="openTotalPersediaanModal({ item: { name: row.item.name, id: row.stocks[branch.id].item_id }, branch_name: branch.name })"
                                        class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer"
                                    >
                                        {{ row.stocks[branch.id].qty }}
                                    </button>
                                    <span v-else class="text-gray-400">0</span>
                                </td>
                                <td 
                                    class="sticky z-10 px-3 py-3 text-center font-semibold text-purple-600 bg-white dark:bg-gray-800 min-w-[120px] max-w-[120px] w-[120px]"
                                    style="right: 80px"
                                >
                                    {{ row.total_persediaan }}
                                </td>
                                <td 
                                    class="sticky right-0 z-10 px-3 py-3 text-center bg-white dark:bg-gray-800 min-w-[80px] max-w-[80px] w-[80px]"
                                >
                                    <button v-if="can.adjust" @click="openAdjustModal(row)" class="text-blue-500 hover:text-blue-700" title="Sesuaikan Stok">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <Pagination :pagination="stockIns" />
            </div>

        </div>

        <!-- Modal Saldo Awal -->
        <div
            v-if="showSaldoAwalModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showSaldoAwalModal = false"
        >
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-auto"
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                >
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Saldo Awal - {{ selectedRow?.item?.name }}
                    </h3>
                    <button
                        @click="showSaldoAwalModal = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>
                <div class="p-6">
                    <div v-if="loadingDetail" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Memuat data...</p>
                    </div>
                    <div v-else-if="detailTransactions.length === 0" class="text-center py-8">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tidak ada transaksi</p>
                    </div>
                    <table v-else class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-2 text-left">No</th>
                                <th class="px-4 py-2 text-left">Waktu</th>
                                <th class="px-4 py-2 text-left">Sumber</th>
                                <th class="px-4 py-2 text-left">Referensi</th>
                                <th class="px-4 py-2 text-center">Masuk</th>
                                <th class="px-4 py-2 text-center">Keluar</th>
                                <th class="px-4 py-2 text-center">Total Persediaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tx, idx) in paginatedDetailTransactions" :key="tx.id || idx" class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-4 py-3">{{ detailFrom + idx }}</td>
                                <td class="px-4 py-3">{{ tx.created_at }}</td>
                                <td class="px-4 py-3">{{ tx.source_type || '-' }}</td>
                                <td class="px-4 py-3">{{ tx.reference || '-' }}</td>
                                <!-- <td class="px-4 py-3 text-right">Rp. {{ Number(tx.transaction_value || 0).toLocaleString('id-ID') }}</td> -->
                                <td class="px-4 py-3 text-center">{{ tx.type === 'In' ? tx.amount : '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ tx.type === 'Out' ? tx.amount : '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ tx.last_stock }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Pagination for Detail Transactions -->
                    <div v-if="detailTransactions.length > 0" class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <label for="detail-per-page" class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">Tampilkan</label>
                                <select
                                    id="detail-per-page"
                                    :value="detailPerPage"
                                    @change="changeDetailPerPage(Number($event.target.value))"
                                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                                >
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">per halaman</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Menampilkan
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailFrom }}</span>
                                ke
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailTo }}</span>
                                dari
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailTransactions.length }}</span>
                                hasil
                            </p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button
                                @click="goToDetailPage(detailCurrentPage - 1)"
                                :disabled="detailCurrentPage === 1"
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700"
                            >
                                Sebelumnya
                            </button>
                            <template v-for="page in Math.min(5, detailTotalPages)" :key="page">
                                <button
                                    v-if="page <= detailTotalPages"
                                    @click="goToDetailPage(page)"
                                    :class="[
                                        'px-3 py-2 text-sm font-semibold rounded-md',
                                        detailCurrentPage === page
                                            ? 'bg-blue-500 text-white'
                                            : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700'
                                    ]"
                                >
                                    {{ page }}
                                </button>
                            </template>
                            <button
                                @click="goToDetailPage(detailCurrentPage + 1)"
                                :disabled="detailCurrentPage >= detailTotalPages"
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700"
                            >
                                Berikutnya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Stok Masuk -->
        <div
            v-if="showStokMasukModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showStokMasukModal = false"
        >
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-auto"
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                >
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Stok Masuk - {{ selectedRow?.item?.name }}
                    </h3>
                    <button
                        @click="showStokMasukModal = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>
                <div class="p-6">
                    <div v-if="loadingDetail" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Memuat data...</p>
                    </div>
                    <div v-else-if="detailTransactions.length === 0" class="text-center py-8">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tidak ada transaksi</p>
                    </div>
                    <table v-else class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-2 text-left">No</th>
                                <th class="px-4 py-2 text-left">Waktu</th>
                                <th class="px-4 py-2 text-left">Sumber</th>
                                <th class="px-4 py-2 text-left">Referensi</th>
                                <!-- <th class="px-4 py-2 text-right">Transaksi (Rp)</th> -->
                                <th class="px-4 py-2 text-center">Masuk</th>
                                <!-- <th class="px-4 py-2 text-center">Keluar</th> -->
                                <th class="px-4 py-2 text-center">Total Persediaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tx, idx) in paginatedDetailTransactions" :key="tx.id || idx" class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-4 py-3">{{ detailFrom + idx }}</td>
                                <td class="px-4 py-3">{{ tx.created_at }}</td>
                                <td class="px-4 py-3">{{ tx.source_type || '-' }}</td>
                                <td class="px-4 py-3">{{ tx.reference || '-' }}</td>
                                <!-- <td class="px-4 py-3 text-right">Rp. {{ Number(tx.transaction_value || 0).toLocaleString('id-ID') }}</td> -->
                                <td class="px-4 py-3 text-center">{{ tx.type === 'In' ? tx.amount : '-' }}</td>
                                <!-- <td class="px-4 py-3 text-center">{{ tx.type === 'Out' ? tx.amount : '-' }}</td> -->
                                <td class="px-4 py-3 text-center">{{ tx.last_stock }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Pagination for Detail Transactions -->
                    <div v-if="detailTransactions.length > 0" class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <label for="detail-per-page-2" class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">Tampilkan</label>
                                <select
                                    id="detail-per-page-2"
                                    :value="detailPerPage"
                                    @change="changeDetailPerPage(Number($event.target.value))"
                                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                                >
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">per halaman</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Menampilkan
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailFrom }}</span>
                                ke
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailTo }}</span>
                                dari
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailTransactions.length }}</span>
                                hasil
                            </p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button
                                @click="goToDetailPage(detailCurrentPage - 1)"
                                :disabled="detailCurrentPage === 1"
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700"
                            >
                                Sebelumnya
                            </button>
                            <template v-for="page in Math.min(5, detailTotalPages)" :key="page">
                                <button
                                    v-if="page <= detailTotalPages"
                                    @click="goToDetailPage(page)"
                                    :class="[
                                        'px-3 py-2 text-sm font-semibold rounded-md',
                                        detailCurrentPage === page
                                            ? 'bg-blue-500 text-white'
                                            : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700'
                                    ]"
                                >
                                    {{ page }}
                                </button>
                            </template>
                            <button
                                @click="goToDetailPage(detailCurrentPage + 1)"
                                :disabled="detailCurrentPage >= detailTotalPages"
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700"
                            >
                                Berikutnya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Stok Keluar -->
        <div
            v-if="showStokKeluarModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showStokKeluarModal = false"
        >
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-auto"
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                >
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Stok Keluar - {{ selectedRow?.item?.name }}
                    </h3>
                    <button
                        @click="showStokKeluarModal = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>
                <div class="p-6">
                    <div v-if="loadingDetail" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Memuat data...</p>
                    </div>
                    <div v-else-if="detailTransactions.length === 0" class="text-center py-8">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tidak ada transaksi</p>
                    </div>
                    <table v-else class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="px-4 py-2 text-left">No</th>
                                <th class="px-4 py-2 text-left">Waktu</th>
                                <th class="px-4 py-2 text-left">Sumber</th>
                                <th class="px-4 py-2 text-left">Referensi</th>
                                <!-- <th class="px-4 py-2 text-right">Transaksi (Rp)</th> -->
                                <!-- <th class="px-4 py-2 text-center">Masuk</th> -->
                                <th class="px-4 py-2 text-center">Keluar</th>
                                <th class="px-4 py-2 text-center">Total Persediaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tx, idx) in paginatedDetailTransactions" :key="tx.id || idx" class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-4 py-3">{{ detailFrom + idx }}</td>
                                <td class="px-4 py-3">{{ tx.created_at }}</td>
                                <td class="px-4 py-3">{{ tx.source_type || '-' }}</td>
                                <td class="px-4 py-3">{{ tx.reference || '-' }}</td>
                                <!-- <td class="px-4 py-3 text-right">Rp. {{ Number(tx.transaction_value || 0).toLocaleString('id-ID') }}</td> -->
                                <!-- <td class="px-4 py-3 text-center">{{ tx.type === 'In' ? tx.amount : '-' }}</td> -->
                                <td class="px-4 py-3 text-center">{{ tx.type === 'Out' ? tx.amount : '-' }}</td>
                                <td class="px-4 py-3 text-center">{{ tx.last_stock }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Pagination for Detail Transactions -->
                    <div v-if="detailTransactions.length > 0" class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <label for="detail-per-page-3" class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">Tampilkan</label>
                                <select
                                    id="detail-per-page-3"
                                    :value="detailPerPage"
                                    @change="changeDetailPerPage(Number($event.target.value))"
                                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                                >
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">per halaman</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Menampilkan
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailFrom }}</span>
                                ke
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailTo }}</span>
                                dari
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailTransactions.length }}</span>
                                hasil
                            </p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button
                                @click="goToDetailPage(detailCurrentPage - 1)"
                                :disabled="detailCurrentPage === 1"
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700"
                            >
                                Sebelumnya
                            </button>
                            <template v-for="page in Math.min(5, detailTotalPages)" :key="page">
                                <button
                                    v-if="page <= detailTotalPages"
                                    @click="goToDetailPage(page)"
                                    :class="[
                                        'px-3 py-2 text-sm font-semibold rounded-md',
                                        detailCurrentPage === page
                                            ? 'bg-blue-500 text-white'
                                            : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700'
                                    ]"
                                >
                                    {{ page }}
                                </button>
                            </template>
                            <button
                                @click="goToDetailPage(detailCurrentPage + 1)"
                                :disabled="detailCurrentPage >= detailTotalPages"
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700"
                            >
                                Berikutnya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Total Persediaan -->
        <div
            v-if="showTotalPersediaanModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showTotalPersediaanModal = false"
        >
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col"
            >
                <div
                    class="flex items-center justify-between px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50"
                >
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            Detail Transaksi: {{ selectedRow?.item?.name }}
                        </h3>
                        <p v-if="selectedRow?.branch_name" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Gudang Cabang {{ selectedRow.branch_name }} • Periode Aktif
                        </p>
                    </div>
                    <button
                        @click="showTotalPersediaanModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        ✕
                    </button>
                </div>
                <div class="p-6 overflow-y-auto flex-1">
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <!-- TOTAL MASUK -->
                        <div class="flex items-center gap-4 p-4 rounded-xl border border-emerald-100 bg-emerald-50/50 dark:border-emerald-950/20 dark:bg-emerald-950/10">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Total Masuk</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ totalMasukDetail }} <span class="text-sm font-normal text-gray-500">{{ selectedRow?.item?.unit || 'pcs' }}</span></div>
                            </div>
                        </div>

                        <!-- TOTAL KELUAR -->
                        <div class="flex items-center gap-4 p-4 rounded-xl border border-amber-100 bg-amber-50/50 dark:border-amber-950/20 dark:bg-amber-950/10">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500 text-white shadow-lg shadow-amber-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Total Keluar</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ totalKeluarDetail }} <span class="text-sm font-normal text-gray-500">{{ selectedRow?.item?.unit || 'pcs' }}</span></div>
                            </div>
                        </div>

                        <!-- SISA STOK -->
                        <div class="flex items-center gap-4 p-4 rounded-xl border border-blue-100 bg-blue-50/50 dark:border-blue-950/20 dark:bg-blue-950/10">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Sisa Stok</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ sisaStokDetail }} <span class="text-sm font-normal text-gray-500">{{ selectedRow?.item?.unit || 'pcs' }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div v-if="loadingDetail" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Memuat data...</p>
                    </div>
                    <div v-else-if="detailTransactions.length === 0" class="text-center py-8">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tidak ada transaksi</p>
                    </div>
                    <table v-else class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400 w-16">No</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Waktu Transaksi</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-400">Sumber / Referensi</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Jumlah Mutasi</th>
                                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-400">Stok Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(tx, idx) in paginatedDetailTransactions" :key="tx.id || idx" class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50/30 dark:hover:bg-gray-800/10 transition-colors">
                                <td class="px-4 py-3 align-middle">{{ detailFrom + idx }}</td>
                                <td class="px-4 py-3 align-middle">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(tx.created_at) }}</span>
                                        <span class="text-xs text-gray-500 mt-0.5">{{ formatTime(tx.created_at) }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 align-middle">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ tx.source_type || '-' }}</span>
                                        <span class="text-xs text-gray-500 font-mono mt-0.5">{{ tx.reference || '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center align-middle font-semibold" :class="tx.type === 'In' ? 'text-emerald-600' : tx.type === 'Out' ? 'text-rose-600' : 'text-gray-500'">
                                    {{ tx.type === 'In' ? '+' + tx.amount : tx.type === 'Out' ? '-' + tx.amount : '0' }}
                                </td>
                                <td class="px-4 py-3 text-center align-middle font-bold text-gray-900 dark:text-white">{{ tx.last_stock }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Pagination for Detail Transactions -->
                    <div v-if="detailTransactions.length > 0" class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <label for="detail-per-page-4" class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">Tampilkan</label>
                                <select
                                    id="detail-per-page-4"
                                    :value="detailPerPage"
                                    @change="changeDetailPerPage(Number($event.target.value))"
                                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                                >
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="text-sm text-gray-700 dark:text-gray-300 whitespace-nowrap">per halaman</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Menampilkan
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailFrom }}</span>
                                ke
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailTo }}</span>
                                dari
                                <span class="font-medium text-gray-900 dark:text-white">{{ detailTransactions.length }}</span>
                                hasil
                            </p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button
                                @click="goToDetailPage(detailCurrentPage - 1)"
                                :disabled="detailCurrentPage === 1"
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700"
                            >
                                Sebelumnya
                            </button>
                            <template v-for="page in Math.min(5, detailTotalPages)" :key="page">
                                <button
                                    v-if="page <= detailTotalPages"
                                    @click="goToDetailPage(page)"
                                    :class="[
                                        'px-3 py-2 text-sm font-semibold rounded-md',
                                        detailCurrentPage === page
                                            ? 'bg-blue-500 text-white'
                                            : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700'
                                    ]"
                                >
                                    {{ page }}
                                </button>
                            </template>
                            <button
                                @click="goToDetailPage(detailCurrentPage + 1)"
                                :disabled="detailCurrentPage >= detailTotalPages"
                                class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700"
                            >
                                Berikutnya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Penyesuaian Stok -->
        <div
            v-if="showAdjustModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            @click.self="showAdjustModal = false"
        >
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4"
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Penyesuaian Stok (Opname)
                    </h3>
                    <button @click="showAdjustModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <form @submit.prevent="submitAdjustment">
                    <div class="p-6 space-y-4">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Barang</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ selectedRow?.item?.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Cabang <span class="text-red-500">*</span>
                            </label>
                            <SearchableSelect
                                v-model="adjustForm.item_id"
                                :options="selectedRow?.branches_data || []"
                                label-key="branch_name"
                                track-by-key="item_id"
                                :allow-empty="false"
                                placeholder="Cari Cabang"
                            />
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 mb-1">Stok Saat Ini (Sistem)</p>
                            <p class="font-medium text-gray-900 dark:text-white">
                                {{ selectedRow && adjustForm.item_id ? (selectedRow.stocks[selectedRow.branches_data.find(b => b.item_id === adjustForm.item_id)?.branch_id]?.qty ?? 0) : 0 }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Stok Aktual (Fisik) <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="adjustForm.actual_stock"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Masukkan jumlah stok aktual di gudang"
                            />
                            <p v-if="adjustForm.errors.actual_stock" class="mt-1 text-sm text-red-500">{{ adjustForm.errors.actual_stock }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan</label>
                            <textarea
                                v-model="adjustForm.note"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Cth: Hasil opname bulanan"
                            ></textarea>
                            <p v-if="adjustForm.errors.note" class="mt-1 text-sm text-red-500">{{ adjustForm.errors.note }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-b-lg">
                        <button
                            type="button"
                            @click="showAdjustModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="adjustForm.processing"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ adjustForm.processing ? 'Menyimpan...' : 'Simpan Penyesuaian' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>

<script setup>
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted, onUnmounted } from "vue";
import Pagination from "@/Components/common/Pagination.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import SearchableSelect from "@/Components/common/SearchableSelect.vue";

const props = defineProps({
    recaps: { type: Object, required: true },
    items: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    sortBy: { type: String, default: "date" },
    sortDirection: { type: String, default: "desc" },
    groupBy: { type: String, default: null },
    can: { type: Object, default: () => ({}) },
});

// Temporary alias to reuse table/pagination variables without breaking
const stockIns = computed(
    () => props.recaps ?? { data: [], current_page: 1, per_page: 15, total: 0 }
);

const breadcrumbs = [
    { label: "Dashboard", href: route("dashboard") },
    { label: "Rekap Stok" },
];

defineOptions({
    layout: AppLayout,
});

const filtersLocal = ref({
    q: props.filters.q ?? "",
    branch_id: props.filters.branch_id ?? null,
    category_id: props.filters.category_id ?? "",
    unit_id: props.filters.unit_id ?? "",
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
    hide_empty: props.filters.hide_empty === '1' || props.filters.hide_empty === true,
});

// Date filter dropdown state
const showDateFilter = ref(false);
const tempDateFrom = ref(props.filters.date_from ?? "");
const tempDateTo = ref(props.filters.date_to ?? "");

// Toggle date filter dropdown and sync values
function toggleDateFilter() {
    if (!showDateFilter.value) {
        // When opening, sync temp values with current filter values
        tempDateFrom.value = filtersLocal.value.date_from;
        tempDateTo.value = filtersLocal.value.date_to;
    }
    showDateFilter.value = !showDateFilter.value;
}

// Close date filter dropdown when clicking outside
function closeDateFilterOnClickOutside(event) {
    const dateFilterContainer = event.target.closest('[data-date-filter]');
    if (showDateFilter.value && !dateFilterContainer) {
        showDateFilter.value = false;
    }
}

// Apply date filter
function applyDateFilter() {
    filtersLocal.value.date_from = tempDateFrom.value;
    filtersLocal.value.date_to = tempDateTo.value;
    showDateFilter.value = false;
    fetchRequests({});
}

// Clear date filter
function clearDateFilter() {
    tempDateFrom.value = "";
    tempDateTo.value = "";
    filtersLocal.value.date_from = "";
    filtersLocal.value.date_to = "";
    showDateFilter.value = false;
    fetchRequests({});
}

// Add click outside listener on mount
onMounted(() => {
    document.addEventListener('click', closeDateFilterOnClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDateFilterOnClickOutside);
});

const showFiltersDropdown = ref(false);
const activeFiltersCount = computed(() => {
    let count = 0;
    if (filtersLocal.value.category_id) count++;
    if (filtersLocal.value.unit_id) count++;
    if (filtersLocal.value.date_from || filtersLocal.value.date_to) count++;
    if (filtersLocal.value.hide_empty) count++;
    if (groupByLocal.value) count++;
    return count;
});

function clickOutsideFilter(event) {
    const el = document.querySelector('[data-filters-dropdown]');
    if (el && !el.contains(event.target)) {
        showFiltersDropdown.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', clickOutsideFilter);
});

onUnmounted(() => {
    document.removeEventListener('click', clickOutsideFilter);
});

// Modal states
const showSaldoAwalModal = ref(false);
const showStokMasukModal = ref(false);
const showStokKeluarModal = ref(false);
const showTotalPersediaanModal = ref(false);
const selectedRow = ref(null);
const detailTransactions = ref([]);
const loadingDetail = ref(false);

// Pagination for detail transactions
const detailCurrentPage = ref(1);
const detailPerPage = ref(10);

// Computed paginated detail transactions
const paginatedDetailTransactions = computed(() => {
    const start = (detailCurrentPage.value - 1) * detailPerPage.value;
    const end = start + detailPerPage.value;
    return detailTransactions.value.slice(start, end);
});

const detailTotalPages = computed(() => {
    return Math.ceil(detailTransactions.value.length / detailPerPage.value);
});

const detailFrom = computed(() => {
    if (detailTransactions.value.length === 0) return 0;
    return (detailCurrentPage.value - 1) * detailPerPage.value + 1;
});

const detailTo = computed(() => {
    return Math.min(detailCurrentPage.value * detailPerPage.value, detailTransactions.value.length);
});

const totalMasukDetail = computed(() => {
    return detailTransactions.value
        .filter(tx => tx.type === 'In')
        .reduce((sum, tx) => sum + Number(tx.amount || 0), 0);
});

const totalKeluarDetail = computed(() => {
    return detailTransactions.value
        .filter(tx => tx.type === 'Out')
        .reduce((sum, tx) => sum + Number(tx.amount || 0), 0);
});

const sisaStokDetail = computed(() => {
    if (detailTransactions.value.length > 0) {
        return detailTransactions.value[0].last_stock;
    }
    return 0;
});

function formatDate(dateTimeStr) {
    if (!dateTimeStr) return '-';
    const date = new Date(dateTimeStr);
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
}

function formatTime(dateTimeStr) {
    if (!dateTimeStr) return '';
    const date = new Date(dateTimeStr);
    return date.toTimeString().split(' ')[0];
}

function goToDetailPage(page) {
    if (page >= 1 && page <= detailTotalPages.value) {
        detailCurrentPage.value = page;
    }
}

function changeDetailPerPage(newPerPage) {
    detailPerPage.value = newPerPage;
    detailCurrentPage.value = 1;
}

async function fetchTransactionDetail(itemId, type) {
    loadingDetail.value = true;
    detailCurrentPage.value = 1; // Reset to first page when fetching new data
    try {
        const params = new URLSearchParams({
            item_id: itemId,
            type: type,
            date_from: filtersLocal.value.date_from || '',
            date_to: filtersLocal.value.date_to || '',
        });

        const response = await fetch(`/api/stock-recap/detail?${params}`);
        const data = await response.json();
        detailTransactions.value = data.transactions || [];
    } catch (error) {
        console.error('Error fetching transaction detail:', error);
        detailTransactions.value = [];
    } finally {
        loadingDetail.value = false;
    }
}

function openSaldoAwalModal(row) {
    selectedRow.value = row;
    showSaldoAwalModal.value = true;
    fetchTransactionDetail(row.item.id, 'saldo_awal');
}

function openStokMasukModal(row) {
    selectedRow.value = row;
    showStokMasukModal.value = true;
    fetchTransactionDetail(row.item.id, 'stok_masuk');
}

function openStokKeluarModal(row) {
    selectedRow.value = row;
    showStokKeluarModal.value = true;
    fetchTransactionDetail(row.item.id, 'stok_keluar');
}

function openTotalPersediaanModal(row) {
    selectedRow.value = row;
    showTotalPersediaanModal.value = true;
    fetchTransactionDetail(row.item.id, 'total_persediaan');
}

const showAdjustModal = ref(false);
const adjustForm = useForm({
    item_id: null,
    actual_stock: null,
    note: ''
});

watch(() => adjustForm.item_id, (newVal) => {
    if (selectedRow.value && newVal) {
        const branchData = selectedRow.value.branches_data.find(b => b.item_id === newVal);
        if (branchData) {
            adjustForm.actual_stock = selectedRow.value.stocks[branchData.branch_id]?.qty || 0;
        }
    }
});

function openAdjustModal(row) {
    selectedRow.value = row;
    adjustForm.item_id = row.branches_data[0]?.item_id || null;
    adjustForm.actual_stock = row.branches_data[0] ? (row.stocks[row.branches_data[0].branch_id]?.qty || 0) : 0;
    adjustForm.note = 'Penyesuaian stok (Opname)';
    showAdjustModal.value = true;
}

function submitAdjustment() {
    adjustForm.post(route('stock-recap.adjust'), {
        preserveScroll: true,
        onSuccess: () => {
            showAdjustModal.value = false;
            fetchRequests({});
        }
    });
}

const groupByLocal = ref(props.groupBy ?? "");
const effectiveGroup = computed(
    () => groupByLocal.value || (props.groupBy ?? "")
);
const isGrouped = computed(() => !!effectiveGroup.value);

// Serial number map to support grouped view
const serialMap = computed(() => {
    const map = {};
    const base =
        ((stockIns.value?.current_page || 1) - 1) *
        (stockIns.value?.per_page || 20);
    const arr = stockIns.value?.data || [];
    arr.forEach((row, idx) => {
        map[row.id] = base + idx + 1;
    });
    return map;
});

// Grouping helpers
const collapsedGroups = ref(new Set());
const groupedData = computed(() => {
    if (!isGrouped.value) return {};
    const data = stockIns.value?.data || [];
    const groups = {};
    for (const row of data) {
        const k = keyOf(row);
        if (!groups[k]) groups[k] = [];
        groups[k].push(row);
    }
    return groups;
});

// Utilities
function formatDateTimeID(dt) {
    if (!dt) return "";
    try {
        const d = new Date(dt);
        const date = new Intl.DateTimeFormat("id-ID", {
            day: "numeric",
            month: "long",
            year: "numeric",
        }).format(d);
        const time = new Intl.DateTimeFormat("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
        })
            .format(d)
            .replace(".", ":");
        return `${date} ${time}`;
    } catch {
        return dt;
    }
}

function totalQty(rows) {
    if (!rows || !rows.length) return 0;
    return rows.reduce((acc, r) => acc + (Number(r.qty) || 0), 0);
}

function keyOf(row) {
    switch (effectiveGroup.value) {
        case "warehouse":
            return row.branch ?? "Tanpa Cabang";
        case "source":
            return row.item?.category ?? "Tanpa Kategori";
        case "date":
            return "Semua Tanggal"; // Date grouping not available at recap level
        case "item":
            return `${row.item?.code ?? ""} - ${row.item?.name ?? ""}`;
        default:
            return "ungrouped";
    }
}

function groupLabel(key) {
    switch (effectiveGroup.value) {
        case "warehouse":
            return `Cabang: ${key}`;
        case "source":
            return `Kategori: ${key}`;
        case "date":
            return `Tanggal: ${key}`;
        case "item":
            return `Barang: ${key}`;
        default:
            return key;
    }
}

function isCollapsed(key) {
    return collapsedGroups.value.has(key);
}

function toggleGroupCollapse(key) {
    const s = new Set(collapsedGroups.value);
    if (s.has(key)) s.delete(key);
    else s.add(key);
    collapsedGroups.value = s;
}

function fetchRequests({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    const params = {
        q: filtersLocal.value.q?.trim() || null,
        branch_id: filtersLocal.value.branch_id || null,
        category_id: filtersLocal.value.category_id ? Number(filtersLocal.value.category_id) : null,
        unit_id: filtersLocal.value.unit_id ? Number(filtersLocal.value.unit_id) : null,
        date_from: filtersLocal.value.date_from || null,
        date_to: filtersLocal.value.date_to || null,
        hide_empty: filtersLocal.value.hide_empty ? '1' : null,
        groupBy: effectiveGroup.value || null,
        sortBy,
        sortDirection,
    };

    // Remove null and empty values
    Object.keys(params).forEach(key => {
        if (params[key] === null || params[key] === '' || params[key] === undefined) {
            delete params[key];
        }
    });

    router.get(
        route("stock-recap.index"),
        params,
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onStart: () => {
                if (typeof window !== "undefined")
                    window.__suppressProgress = true;
            },
            onFinish: () => {
                if (typeof window !== "undefined")
                    window.__suppressProgress = false;
            },
        }
    );
}

let debounceTimer;
watch(
    () => filtersLocal.value.q,
    () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchRequests({}), 400);
    }
);
watch(
    () => [
        filtersLocal.value.branch_id,
        filtersLocal.value.category_id,
        filtersLocal.value.unit_id,
        filtersLocal.value.date_from,
        filtersLocal.value.date_to,
    ],
    () => {
        fetchRequests({});
    }
);

watch(groupByLocal, () => {
    fetchRequests({});
});
watch(
    () => props.groupBy,
    (val) => {
        // Sync local state if navigation updates prop (e.g., back/forward)
        if (!groupByLocal.value && val) groupByLocal.value = val;
    }
);

function changeSort(column) {
    const nextDir =
        props.sortBy === column && props.sortDirection === "asc"
            ? "desc"
            : "asc";
    fetchRequests({ sortBy: column, sortDirection: nextDir });
}

function sourceLabel(v) {
    const map = Object.fromEntries(
        (props.sources || []).map((s) => [s.value, s.label])
    );
    return map[v] ?? v;
}

// No badge for stock in rows

function onDelete(row) {
    if (!row?.id) return;
    if (!confirm(`Hapus permintaan ${row.number}?`)) return;
    router.delete(route("item-receives.destroy", row.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh current list
            fetchRequests({});
        },
    });
}
</script>

<script>
export default {
    components: {
        SortIcon: {
            props: { active: Boolean, direction: String },
            template: `
                <span class="inline-block ml-2 text-xs">
                    <template v-if="active">
                        {{ direction === 'asc' ? '▲' : '▼' }}
                    </template>
                    <template v-else>
                        ⇅
                    </template>
                </span>
            `,
        },
    },
};
</script>
