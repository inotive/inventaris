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
                    <div>
                        <select
                            v-model="groupByLocal"
                            class="px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                        >
                            <option value="">Kelompokan Berdasarkan</option>
                            <option value="warehouse">Group: Cabang</option>
                            <option value="source">
                                Group: Kategori
                            </option>
                        </select>
                    </div>
                    <div class="relative" data-date-filter>
                        <button
                            @click="toggleDateFilter"
                            type="button"
                            class="flex items-center gap-2 px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <span>Filter Tanggal</span>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                class="size-4"
                            >
                                <path
                                    d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z"
                                />
                            </svg>
                        </button>

                        <!-- Date Filter Dropdown -->
                        <div
                            v-if="showDateFilter"
                            class="absolute right-0 z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                            @click.stop
                        >
                            <div class="p-4 space-y-4">
                                <!-- Tanggal Awal -->
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Awal
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="tempDateFrom"
                                            type="date"
                                            class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        />
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                                class="size-4"
                                            >
                                                <path
                                                    d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tanggal Akhir -->
                                <div>
                                    <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Akhir
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model="tempDateTo"
                                            type="date"
                                            class="w-full px-3 py-2 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        />
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 24 24"
                                                fill="currentColor"
                                                class="size-4"
                                            >
                                                <path
                                                    d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75ZM7.5 12a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM18.894 6.166a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061l1.591-1.59ZM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75ZM17.834 18.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06l1.59 1.591ZM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18ZM7.758 17.303a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061l1.591-1.59ZM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12ZM6.697 7.757a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06l1.59 1.591Z"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-2 pt-2">
                                    <button
                                        @click="applyDateFilter"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                    >
                                        Terapkan
                                    </button>
                                    <button
                                        @click="clearDateFilter"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
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
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 50px" />
                        <col style="width: 250px" />
                        <col style="width: 120px" />
                        <!-- <col /> -->
                        <col style="width: 70px" />
                        <!-- <col style="width: 200px" /> -->
                        <col style="width: 140px" />
                        <col style="width: 100px" />
                        <!-- <col style="width: 140px" /> -->
                        <col style="width: 100px" />
                        <!-- <col style="width: 140px" />
                        <col style="width: 140px" /> -->
                        <col style="width: 100px" />
                        <col style="width: 120px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    No.
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Nama Barang
                                </div>
                            </th>
                            <th
                                class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Kategori
                                </div>
                            </th>
                            <th
                                class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Satuan
                                </div>
                            </th>
                            <th
                                class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Cabang
                                </div>
                            </th>
                            <th
                                class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Saldo Awal
                                </div>
                            </th>
                            <!-- <th colspan="2" class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">Saldo Awal</div>
                            </th> -->
                            <th
                                class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Stok Masuk
                                </div>
                            </th>
                            <!-- <th colspan="2" class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">Stok Masuk</div>
                            </th> -->
                            <th
                                class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Stok Keluar
                                </div>
                            </th>
                            <!-- <th colspan="2" class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">Stok Keluar</div>
                            </th> -->
                            <th
                                class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Total Persediaan
                                </div>
                            </th>
                            <!-- <th colspan="2" class="py-2.5 text-center bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="px-3 font-medium text-gray-600 dark:text-gray-300">Total Persediaan</div>
                            </th> -->
                        </tr>
                        <tr>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <input
                                    v-model="filtersLocal.q"
                                    type="text"
                                    placeholder="Cari Barang"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </th>
                            <th
                                class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filtersLocal.category_id"
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua Kategori</option>
                                    <option
                                        v-for="category in categories"
                                        :key="category.id"
                                        :value="category.id"
                                    >
                                        {{ category.name }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filtersLocal.unit_id"
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua Satuan</option>
                                    <option
                                        v-for="unit in units"
                                        :key="unit.id"
                                        :value="unit.id"
                                    >
                                        {{ unit.name }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filtersLocal.branch_id"
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua Cabang</option>
                                    <option
                                        v-for="branch in branches"
                                        :key="branch.id"
                                        :value="branch.id"
                                    >
                                        {{ branch.name }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                            <!-- <th class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900">
                                <div class="px-3 text-xs font-medium text-gray-600 dark:text-gray-300">Transaksi (Rp)</div>
                            </th> -->
                            <th
                                class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                            <!-- <th class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900">
                                <div class="px-3 text-xs font-medium text-gray-600 dark:text-gray-300">Transaksi (Rp)</div>
                            </th> -->
                            <th
                                class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                            <!-- <th class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900">
                                <div class="px-3 text-xs font-medium text-gray-600 dark:text-gray-300">Transaksi (Rp)</div>
                            </th> -->
                            <th
                                class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                            <!-- <th class="px-2 py-2 text-center bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900">
                                <div class="px-3 text-xs font-medium text-gray-600 dark:text-gray-300">Transaksi (Rp)</div>
                            </th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="!stockIns?.data || stockIns.data.length === 0"
                        >
                            <td
                                colspan="9"
                                class="py-8 text-center text-gray-500"
                            >
                                Tidak ada data
                            </td>
                        </tr>
                        <template v-else-if="isGrouped">
                            <template v-for="(rows, gkey) in groupedData" :key="gkey">
                                <tr>
                                    <td :colspan="9" class="px-4 py-2 font-semibold text-gray-600 bg-gray-50 dark:bg-gray-800/40 dark:text-gray-300">
                                        <button class="inline-flex items-center gap-2" @click="toggleGroupCollapse(gkey)">
                                            <span class="text-sm">{{ isCollapsed(gkey) ? "▶" : "▼" }}</span>
                                            <span>{{ groupLabel(gkey) }}</span>
                                            <span class="ml-1 text-sm font-medium text-gray-500">({{ rows.length }})</span>
                                        </button>
                                    </td>
                                </tr>
                                <template v-if="!isCollapsed(gkey)">
                                    <tr
                                        v-for="row in rows"
                                        :key="row.id"
                                        class="border-b border-gray-200 dark:border-gray-700"
                                    >
                                        <td class="px-3 py-3">
                                            {{ serialMap[row.id] ?? "-" }}
                                        </td>
                                        <td class="px-3 py-3">
                                            <div
                                                class="font-medium text-gray-800 dark:text-gray-200"
                                            >
                                                {{ row.item?.name }}
                                            </div>
                                            <div
                                                class="mt-1 flex flex-wrap gap-1 text-[10px] text-gray-500"
                                            >
                                                <span
                                                    v-for="b in row.item?.brands || []"
                                                    :key="'b-' + b"
                                                    class="px-2 py-0.5 bg-gray-100 rounded-full dark:bg-white/10"
                                                    >{{ b }}</span
                                                >
                                                <span
                                                    v-for="t in row.item?.types || []"
                                                    :key="'t-' + t"
                                                    class="px-2 py-0.5 bg-gray-100 rounded-full dark:bg.white/10"
                                                    >{{ t }}</span
                                                >
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="inline-flex px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded dark:bg-blue-900/30 dark:text-blue-300">
                                                {{ row.item?.category || '-' }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            {{ row.item?.unit || '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            {{ row.branch || '-' }}
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <button
                                                @click="openSaldoAwalModal(row)"
                                                class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer"
                                            >
                                                {{ row.saldo_awal.qty }}
                                            </button>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <button
                                                @click="openStokMasukModal(row)"
                                                class="text-green-600 hover:text-green-800 hover:underline cursor-pointer"
                                            >
                                                {{ row.in.qty }}
                                            </button>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <button
                                                @click="openStokKeluarModal(row)"
                                                class="text-red-600 hover:text-red-800 hover:underline cursor-pointer"
                                            >
                                                {{ row.out.qty }}
                                            </button>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <button
                                                @click="openTotalPersediaanModal(row)"
                                                class="text-purple-600 hover:text-purple-800 hover:underline cursor-pointer"
                                            >
                                                {{ row.ending.qty }}
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
                                <td class="px-3 py-3">
                                    {{ serialMap[row.id] ?? "-" }}
                                </td>
                                <td class="px-3 py-3">
                                    <div
                                        class="font-medium text-gray-800 dark:text-gray-200"
                                    >
                                        {{ row.item?.name }} <span v-if="!isSuperadmin">({{ row.branch }})</span>

                                    </div>
                                    <div
                                        class="mt-1 flex flex-wrap gap-1 text-[10px] text-gray-500"
                                    >
                                        <span
                                            v-for="b in row.item?.brands || []"
                                            :key="'b-' + b"
                                            class="px-2 py-0.5 bg-gray-100 rounded-full dark:bg-white/10"
                                            >{{ b }}</span
                                        >
                                        <span
                                            v-for="t in row.item?.types || []"
                                            :key="'t-' + t"
                                            class="px-2 py-0.5 bg-gray-100 rounded-full dark:bg.white/10"
                                            >{{ t }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ row.item?.category || '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    {{ row.item?.unit || '-' }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    {{ row.branch || '-' }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <button
                                        @click="openSaldoAwalModal(row)"
                                        class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer"
                                    >
                                        {{ row.saldo_awal.qty }}
                                    </button>
                                </td>
                                <!-- <td class="px-3 py-3 text-right">{{ formatCurrencyIDR(row.saldo_awal.value) }}</td> -->
                                <td class="px-3 py-3 text-center">
                                    <button
                                        @click="openStokMasukModal(row)"
                                        class="text-green-600 hover:text-green-800 hover:underline cursor-pointer"
                                    >
                                        {{ row.in.qty }}
                                    </button>
                                </td>
                                <!-- <td class="px-3 py-3 text-right">{{ formatCurrencyIDR(row.in.value) }}</td> -->
                                <td class="px-3 py-3 text-center">
                                    <button
                                        @click="openStokKeluarModal(row)"
                                        class="text-red-600 hover:text-red-800 hover:underline cursor-pointer"
                                    >
                                        {{ row.out.qty }}
                                    </button>
                                </td>
                                <!-- <td class="px-3 py-3 text-right">{{ formatCurrencyIDR(row.out.value) }}</td> -->
                                <td class="px-3 py-3 text-center">
                                    <button
                                        @click="openTotalPersediaanModal(row)"
                                        class="text-purple-600 hover:text-purple-800 hover:underline cursor-pointer"
                                    >
                                        {{ row.ending.qty }}
                                    </button>
                                </td>
                                <!-- <td class="px-3 py-3 text-right">{{ formatCurrencyIDR(row.ending.value) }}</td> -->
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
                class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-auto"
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700"
                >
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Total Persediaan - {{ selectedRow?.item?.name }}
                    </h3>
                    <button
                        @click="showTotalPersediaanModal = false"
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
    </div>
</template>

<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted, onUnmounted } from "vue";
import Pagination from "@/Components/common/Pagination.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

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
