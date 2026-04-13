<template>
    <Head title="Daftar Barang" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <button
                @click="openAddItemModal"
                type="button"
                class="flex gap-2 items-center px-3 py-2 text-white bg-blue-500 rounded"
            >
                <PlusSquareIcon />
                <span class="hidden text-sm md:block">Tambah Barang</span>
            </button>
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
                    Daftar Barang
                </div>

                <div class="flex gap-3 justify-between items-center">
                    <div class="flex gap-2 items-center">
                        <select
                            v-model="groupByLocal"
                            class="px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                        >
                            <option value="">Kelompokan Berdasarkan</option>
                            <option value="warehouse">Group: Cabang</option>
                            <option value="item">Group: Kategori</option>
                            <option value="item">Group: Satuan</option>
                        </select>
                    </div>

                    <div class="relative py-2">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari barang"
                            class="h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-400 dark:focus:border-blue-800 xl:w-[200px]"
                        />
                    </div>
                </div>
            </div>

            <!-- Simple Items Table -->
            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 56px" />
                        <col style="width: 200px" />
                        <col style="width: 120px" />
                        <col style="width: 160px" />
                        <col style="width: 100px" />
                        <col style="width: 120px" />
                        <col style="width: 120px" />
                        <col style="width: 120px" />
                        <col style="width: 100px" />
                        <col style="width: 120px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y"
                            >
                                <div
                                    class="px-3 font-medium text-center text-gray-600"
                                >
                                    No
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border border-gray-200"
                            >
                                <button
                                    class="px-3 w-full font-medium text-left text-gray-600"
                                    @click="changeSortItems('name')"
                                >
                                    Nama Barang
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border border-gray-200"
                            >
                                <button
                                    class="px-3 w-full font-medium text-left text-gray-600"
                                    @click="changeSortItems('code')"
                                >
                                    Kode
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border border-gray-200"
                            >
                                <div
                                    class="px-3 font-medium text-left text-gray-600"
                                >
                                    Kategori
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border border-gray-200"
                            >
                                <button
                                    class="px-3 w-full font-medium text-left text-gray-600"
                                    @click="changeSortItems('branch')"
                                >
                                    Cabang
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border border-gray-200"
                            >
                                <button
                                    class="px-3 w-full font-medium text-left text-gray-600"
                                    @click="changeSortItems('unit')"
                                >
                                    Satuan
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border border-gray-200"
                            >
                                <button
                                    class="px-3 w-full font-medium text-right text-gray-600"
                                    @click="changeSortItems('min_stock')"
                                >
                                    Minimal Stok
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border border-gray-200"
                            >
                                <button
                                    class="px-3 w-full font-medium text-right text-gray-600"
                                    @click="changeSortItems('stock')"
                                >
                                    Stok Saat Ini
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border border-gray-200"
                            >
                                <button
                                    class="px-3 w-full font-medium text-right text-gray-600"
                                    @click="changeSortItems('price')"
                                >
                                    Harga
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border border-gray-200"
                            >
                                <button
                                    class="px-3 w-full font-medium text-left text-gray-600"
                                    @click="changeSortItems('status')"
                                >
                                    Status
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y"
                            >
                                <div
                                    class="px-3 font-medium text-center text-gray-600"
                                >
                                    Aksi
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y"
                            ></th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200"
                            >
                                <input
                                    v-model="filtersItems.name"
                                    type="text"
                                    placeholder="Cari Nama"
                                    class="px-3 w-full h-9 text-sm rounded border-gray-300"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200"
                            >
                                <input
                                    v-model="filtersItems.code"
                                    type="text"
                                    placeholder="Kode"
                                    class="px-3 w-full h-9 text-sm rounded border-gray-300"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200"
                            >
                                <select
                                    v-model.number="filtersItems.department_id"
                                    class="px-3 w-full h-9 text-sm rounded border-gray-300"
                                >
                                    <option :value="null">Semua</option>
                                    <option
                                        v-for="d in departments || []"
                                        :key="d.id"
                                        :value="d.id"
                                    >
                                        {{ d.name }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200"
                            >
                                <input
                                    v-model="filtersItems.branch"
                                    type="text"
                                    placeholder="Cabang"
                                    class="px-3 w-full h-9 text-sm rounded border-gray-300"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200"
                            >
                                <input
                                    v-model="filtersItems.unit"
                                    type="text"
                                    placeholder="Satuan"
                                    class="px-3 w-full h-9 text-sm rounded border-gray-300"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200"
                            >
                                <input
                                    v-model.number="filtersItems.min_from"
                                    type="number"
                                    placeholder="Min ≥"
                                    class="px-3 w-full h-9 text-sm text-right rounded border-gray-300"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200"
                            >
                                <input
                                    v-model.number="filtersItems.stock_from"
                                    type="number"
                                    placeholder="Stok ≥"
                                    class="px-3 w-full h-9 text-sm text-right rounded border-gray-300"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200"
                            >
                                <input
                                    v-model.number="filtersItems.price_from"
                                    type="number"
                                    placeholder="Harga ≥"
                                    class="px-3 w-full h-9 text-sm text-right rounded border-gray-300"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200"
                            >
                                <select
                                    v-model="filtersItems.status"
                                    class="px-3 w-full h-9 text-sm rounded border-gray-300"
                                >
                                    <option :value="null">Semua</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y"
                            ></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!itemsList.length">
                            <td
                                colspan="10"
                                class="py-6 text-center text-gray-500"
                            >
                                Tidak ada data
                            </td>
                        </tr>
                        <tr
                            v-for="(row, idx) in itemsList"
                            :key="row.id"
                            class="border-b border-gray-200"
                        >
                            <td class="px-3 py-3 text-center">
                                {{ serialBase + idx + 1 }}
                            </td>
                            <td class="px-3 py-3">{{ row.name }}</td>
                            <td class="px-3 py-3">{{ row.code }}</td>
                            <td class="px-3 py-3">
                                {{ row.department?.name ?? "-" }}
                            </td>
                            <td class="px-3 py-3">
                                {{ row.branch?.name ?? "-" }}
                            </td>
                            <td class="px-3 py-3">{{ row.unit || "-" }}</td>
                            <td class="px-3 py-3 text-right">
                                {{ row.min_stock ?? "-" }}
                            </td>
                            <td class="px-3 py-3 text-right">
                                {{ row.stock ?? 0 }}
                            </td>
                            <td class="px-3 py-3 text-right">
                                Rp{{
                                    new Intl.NumberFormat("id-ID").format(
                                        row.price || 0
                                    )
                                }}
                            </td>
                            <td class="px-3 py-3">{{ row.status || "-" }}</td>
                            <td class="px-3 py-3">
                                <div class="flex gap-2 justify-center">
                                    <!-- <Link :href="route('items.show', row.id)" class="text-blue-600">Lihat</Link> -->
                                    <button
                                        type="button"
                                        class="text-yellow-600"
                                        @click="openEditItem(row)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        class="text-red-600"
                                        @click="destroyRow(row)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Item Modal -->
            <Modal
                :show="isItemModalOpen"
                :title="itemDialogTitle"
                confirmText="Simpan"
                maxWidth="lg"
                @close="closeItemModal"
                @confirm="saveItem"
            >
                <form @submit.prevent="saveItem" class="space-y-3">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm text-gray-600"
                                >Nama Barang</label
                            >
                            <input
                                v-model="itemForm.name"
                                type="text"
                                class="w-full rounded border-gray-300"
                                placeholder="Nama"
                            />
                            <small
                                v-if="itemForm.errors.name"
                                class="text-red-500"
                                >{{ itemForm.errors.name }}</small
                            >
                        </div>
                        <div>
                            <label class="block mb-1 text-sm text-gray-600"
                                >Kode</label
                            >
                            <input
                                v-model="itemForm.code"
                                type="text"
                                class="w-full rounded border-gray-300"
                                placeholder="Kode"
                            />
                            <small
                                v-if="itemForm.errors.code"
                                class="text-red-500"
                                >{{ itemForm.errors.code }}</small
                            >
                        </div>
                        <div>
                            <label class="block mb-1 text-sm text-gray-600"
                                >Departemen</label
                            >
                            <select
                                v-model.number="itemForm.department_id"
                                class="w-full rounded border-gray-300"
                            >
                                <option :value="null" disabled>
                                    Pilih departemen
                                </option>
                                <option
                                    v-for="d in departments || []"
                                    :key="d.id"
                                    :value="d.id"
                                >
                                    {{ d.name }}
                                </option>
                            </select>
                            <small
                                v-if="itemForm.errors.department_id"
                                class="text-red-500"
                                >{{ itemForm.errors.department_id }}</small
                            >
                        </div>
                        <div>
                            <label class="block mb-1 text-sm text-gray-600"
                                >Satuan</label
                            >
                            <input
                                v-model="itemForm.unit"
                                type="text"
                                class="w-full rounded border-gray-300"
                                placeholder="Satuan (pcs, sak)"
                            />
                        </div>
                        <div>
                            <label class="block mb-1 text-sm text-gray-600"
                                >Harga</label
                            >
                            <input
                                v-model.number="itemForm.price"
                                type="number"
                                class="w-full rounded border-gray-300"
                                placeholder="Harga"
                            />
                        </div>
                        <div>
                            <label class="block mb-1 text-sm text-gray-600"
                                >Stok</label
                            >
                            <input
                                v-model.number="itemForm.stock"
                                type="number"
                                class="w-full rounded border-gray-300"
                                placeholder="Stok"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-1 text-sm text-gray-600"
                                >Keterangan</label
                            >
                            <textarea
                                v-model="itemForm.description"
                                rows="3"
                                class="w-full rounded border-gray-300"
                                placeholder="Keterangan"
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end pt-2">
                        <button
                            type="button"
                            class="px-3 py-2 rounded border"
                            @click="closeItemModal"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-3 py-2 text-white bg-blue-600 rounded"
                            :disabled="itemForm.processing"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </Modal>

            <!-- Existing content below will be kept for now (disabled) -->
            <div v-if="false" class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 56px" />
                        <col style="width: 200px" />
                        <col style="width: 120px" />
                        <col style="width: 160px" />
                        <col style="width: 100px" />
                        <col style="width: 120px" />
                        <col style="width: 120px" />
                        <col style="width: 120px" />
                        <col style="width: 100px" />
                        <col style="width: 120px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex justify-center items-center px-3"
                                >
                                    <p
                                        class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        No.
                                    </p>
                                </div>
                            </th>
                            <th
                                @click="changeSort('name')"
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3 cursor-pointer"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Nama Barang
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'name' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'name' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('code')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Kode
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'code' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'code' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('branch')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Cabang
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'branch' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'branch' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('unit')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Satuan
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'unit' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'unit' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('min_stock')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Min Stok
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'min_stock' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'min_stock' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('stock')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Stok Saat Ini
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'stock' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'stock' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('price')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Harga
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'price' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'price' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('status')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Status
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'status' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'status' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                class="bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Aksi
                                    </p>
                                </div>
                            </th>
                        </tr>
                        <!-- Inline column filters -->
                        <tr>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filters.vehicle_type_id"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option :value="null">Semua</option>
                                    <option
                                        v-for="t in vehicleTypes"
                                        :key="t.id"
                                        :value="t.id"
                                    >
                                        {{ t.name }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <input
                                    v-model="filters.license_code"
                                    type="text"
                                    placeholder="Cari plat"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filters.branch_id"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option :value="null">Semua</option>
                                    <option
                                        v-for="b in branches"
                                        :key="b.id"
                                        :value="b.id"
                                    >
                                        {{ b.name }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <input
                                    v-model="filters.track"
                                    type="text"
                                    placeholder="Cari rute"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filters.employee_id"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option :value="null">Semua</option>
                                    <option
                                        v-for="e in employees"
                                        :key="e.id"
                                        :value="e.id"
                                    >
                                        {{ e.name }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filters.status"
                                    class="px-3 w-full h-10 text-xs rounded border-gray-300 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option :value="null">Semua</option>
                                    <option :value="1">Aktif</option>
                                    <option :value="0">Tidak Aktif</option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template
                            v-if="vehicles.data && vehicles.data.length > 0"
                        >
                            <!-- Grouped rows rendering -->
                            <template v-if="groupBy">
                                <template
                                    v-for="(row, rIndex) in groupedRows"
                                    :key="
                                        row.__group
                                            ? `g-${rIndex}`
                                            : row.data.id
                                    "
                                >
                                    <!-- Group header row -->
                                    <tr
                                        v-if="row.__group"
                                        class="cursor-pointer select-none"
                                        @click="toggleGroupCollapse(row.key)"
                                    >
                                        <td
                                            :colspan="8"
                                            class="px-4 py-2 font-semibold text-left text-gray-600 bg-gray-50 dark:bg-gray-900/40 dark:text-gray-300"
                                        >
                                            <span
                                                class="inline-flex gap-2 items-center"
                                            >
                                                <span
                                                    class="inline-block"
                                                    :class="
                                                        collapsedGroups.has(
                                                            row.key
                                                        )
                                                            ? 'rotate-[-90deg] transition-transform'
                                                            : 'rotate-0 transition-transform'
                                                    "
                                                    ><DownIcon
                                                /></span>
                                                <span>{{ row.label }}</span>
                                                <span
                                                    class="ml-2 text-xs font-normal text-gray-500"
                                                    >({{ row.count }})</span
                                                >
                                            </span>
                                        </td>
                                    </tr>
                                    <!-- Vehicle row -->
                                    <tr
                                        v-else
                                        class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                                    >
                                        <td
                                            class="py-2.5 border-gray-200 border-y dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center whitespace-nowrap"
                                            >
                                                <p
                                                    class="px-3 text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        (vehicles.current_page -
                                                            1) *
                                                            vehicles.per_page +
                                                        rIndex +
                                                        1
                                                    }}.
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-2.5 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex gap-3 items-center whitespace-nowrap ps-5"
                                            >
                                                <img
                                                    class="object-cover w-10 h-10 rounded-full"
                                                    :src="
                                                        row.data.photo
                                                            ? `/storage/${row.data.photo}`
                                                            : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                                  row.data.type
                                                                      .name
                                                              )}&background=3b82f6&color=fff`
                                                    "
                                                    alt="Vehicle photo"
                                                    loading="lazy"
                                                />
                                                <div
                                                    class="flex flex-col leading-tight"
                                                >
                                                    <p
                                                        class="font-medium text-gray-800 dark:text-white/90"
                                                    >
                                                        {{ row.data.type.name }}
                                                    </p>
                                                    <span
                                                        class="text-gray-500 dark:text-gray-400"
                                                    >
                                                        {{
                                                            row.data.type
                                                                .category
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="py-2.5 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ row.data.license_code }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-2.5 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ row.data.branch?.name }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-2.5 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ row.data.track }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-2.5 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ row.data.driver?.name }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-2.5 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 text-xs whitespace-nowrap"
                                            >
                                                <span
                                                    v-if="row.data.status"
                                                    class="px-3 py-1 font-normal text-green-600 bg-green-100 rounded-lg border-2 border-green-300"
                                                >
                                                    Aktif
                                                </span>
                                                <span
                                                    v-else
                                                    class="px-2 py-1 font-normal text-red-600 bg-red-100 rounded-lg border-2 border-red-300"
                                                >
                                                    Non Aktif
                                                </span>
                                            </div>
                                        </td>
                                        <td
                                            class="py-2.5 border-gray-200 border-y dark:border-gray-600"
                                        >
                                            <div
                                                class="flex gap-3 justify-center px-4 whitespace-nowrap"
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'vehicles.show',
                                                            row.data.id
                                                        )
                                                    "
                                                >
                                                    <ShowIcon
                                                        class="text-blue-400"
                                                    />
                                                </Link>
                                                <button
                                                    @click="
                                                        openEditModal(row.data)
                                                    "
                                                >
                                                    <EditIcon
                                                        class="text-yellow-500"
                                                    />
                                                </button>
                                                <button
                                                    @click="
                                                        openConfirmModal(
                                                            row.data
                                                        )
                                                    "
                                                >
                                                    <TrashIcon
                                                        class="text-red-500"
                                                    />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>

                            <!-- Non-grouped rendering -->
                            <template v-else>
                                <tr
                                    v-for="(vehicle, index) in vehicles.data"
                                    :key="vehicle.id"
                                    class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    <!-- original vehicle row content unchanged -->

                                    <td
                                        class="py-2.5 border-gray-200 border-y dark:border-gray-600"
                                    >
                                        <div
                                            class="flex justify-center items-center whitespace-nowrap"
                                        >
                                            <p
                                                class="px-3 text-gray-500 dark:text-gray-400"
                                            >
                                                {{
                                                    (vehicles.current_page -
                                                        1) *
                                                        vehicles.per_page +
                                                    index +
                                                    1
                                                }}.
                                            </p>
                                        </div>
                                    </td>
                                    <td
                                        class="py-2.5 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div
                                            class="flex gap-3 items-center whitespace-nowrap ps-5"
                                        >
                                            <img
                                                class="object-cover w-10 h-10 rounded-full"
                                                :src="
                                                    vehicle.photo
                                                        ? `/storage/${vehicle.photo}`
                                                        : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                              vehicle.type.name
                                                          )}&background=3b82f6&color=fff`
                                                "
                                                alt="Vehicle photo"
                                                loading="lazy"
                                            />
                                            <div
                                                class="flex flex-col leading-tight"
                                            >
                                                <p
                                                    class="font-medium text-gray-800 dark:text-white/90"
                                                >
                                                    {{ vehicle.type.name }}
                                                </p>
                                                <span
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ vehicle.type.category }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="py-2.5 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div
                                            class="flex justify-center items-center px-3 whitespace-nowrap"
                                        >
                                            <p
                                                class="text-gray-500 dark:text-gray-400"
                                            >
                                                {{ vehicle.license_code }}
                                            </p>
                                        </div>
                                    </td>
                                    <td
                                        class="py-2.5 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div
                                            class="flex justify-center items-center px-3 whitespace-nowrap"
                                        >
                                            <p
                                                class="text-gray-500 dark:text-gray-400"
                                            >
                                                {{ vehicle.branch?.name }}
                                            </p>
                                        </div>
                                    </td>
                                    <td
                                        class="py-2.5 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div
                                            class="flex justify-center items-center px-3 whitespace-nowrap"
                                        >
                                            <p
                                                class="text-gray-500 dark:text-gray-400"
                                            >
                                                {{ vehicle.track }}
                                            </p>
                                        </div>
                                    </td>
                                    <td
                                        class="py-2.5 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div
                                            class="flex justify-center items-center px-3 whitespace-nowrap"
                                        >
                                            <p
                                                class="text-gray-500 dark:text-gray-400"
                                            >
                                                {{ vehicle.driver?.name }}
                                            </p>
                                        </div>
                                    </td>
                                    <td
                                        class="py-2.5 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div
                                            class="flex justify-center items-center px-3 text-xs whitespace-nowrap"
                                        >
                                            <span
                                                v-if="vehicle.status"
                                                class="px-3 py-1 font-normal text-green-600 bg-green-100 rounded-lg border-2 border-green-300"
                                            >
                                                Aktif
                                            </span>
                                            <span
                                                v-else
                                                class="px-2 py-1 font-normal text-red-600 bg-red-100 rounded-lg border-2 border-red-300"
                                            >
                                                Non Aktif
                                            </span>
                                        </div>
                                    </td>
                                    <td
                                        class="py-2.5 border-gray-200 border-y dark:border-gray-600"
                                    >
                                        <div
                                            class="flex gap-3 justify-center px-4 whitespace-nowrap"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'vehicles.show',
                                                        vehicle.id
                                                    )
                                                "
                                            >
                                                <ShowIcon
                                                    class="text-blue-400"
                                                />
                                            </Link>
                                            <button
                                                @click="openEditModal(vehicle)"
                                            >
                                                <EditIcon
                                                    class="text-yellow-500"
                                                />
                                            </button>
                                            <button
                                                @click="
                                                    openConfirmModal(vehicle)
                                                "
                                            >
                                                <TrashIcon
                                                    class="text-red-500"
                                                />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <tr v-else>
                            <td
                                colspan="8"
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada kendaraan ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>

                <Modal
                    :show="isModalOpen"
                    :title="
                        isEditMode
                            ? `Edit ${selectedItem?.name}`
                            : 'Tambah Kendaraan'
                    "
                    confirmText="Simpan"
                    maxWidth="lg"
                    @close="closeModal"
                    @confirm="saveVehicle"
                >
                    <div class="space-y-3">
                        <div class="space-y-1 text-sm">
                            <div
                                v-bind="getRootProps()"
                                class="p-6 text-center text-blue-500 bg-sky-400 bg-opacity-5 rounded-lg border-2 border-blue-300 cursor-pointer border-1"
                            >
                                <input v-bind="getInputProps()" />
                                <div
                                    class="flex gap-8 items-center justify-stretch"
                                >
                                    <UploadIcon
                                        class="w-12 h-12 text-blue-300"
                                    />
                                    <div class="flex flex-col text-start">
                                        <p v-if="isDragActive">
                                            Lepaskan file di sini ...
                                        </p>
                                        <p v-else>
                                            Drag & drop file di sini, atau klik
                                            untuk pilih
                                        </p>
                                        <ul class="mt-2 text-sm text-gray-600">
                                            <li
                                                v-for="file in form.files"
                                                :key="file.name"
                                            >
                                                {{ file.name }} ({{
                                                    Math.round(file.size / 1024)
                                                }}
                                                KB)
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="vehicle_type_id"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Tipe Kendaraan<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <Select
                                v-model="form.vehicle_type_id"
                                label="Pilih tipe kendaraan"
                                :items="vehicleTypes"
                                :taggable="true"
                            />
                            <div
                                v-if="form.errors.vehicle_type_id"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.vehicle_type_id[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="license_code"
                                class="font-semibold text-gray-900 dark:text-white"
                                >No. Plat<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <input
                                id="license_code"
                                class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.license_code"
                                required
                                placeholder="Masukkan nomor plat kendaraan"
                            />
                            <div
                                v-if="form.errors.license_code"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.license_code[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="chassis_code"
                                class="font-semibold text-gray-900 dark:text-white"
                                >No. Rangka<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <input
                                id="chassis_code"
                                class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.chassis_code"
                                required
                                placeholder="Masukkan nomor rangka kendaraan"
                            />
                            <div
                                v-if="form.errors.chassis_code"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.chassis_code[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="machine_code"
                                class="font-semibold text-gray-900 dark:text-white"
                                >No. Mesin<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <input
                                id="machine_code"
                                class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.machine_code"
                                required
                                placeholder="Masukkan nomor mesin kendaraan"
                            />
                            <div
                                v-if="form.errors.machine_code"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.machine_code[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="branch_id"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Cabang<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <Select
                                v-model="form.branch_id"
                                label="Pilih kantor cabang"
                                :items="branches"
                                :taggable="true"
                            />
                            <div
                                v-if="form.errors.branch_id"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.branch_id[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="employee_id"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Driver<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <Select
                                v-model="form.employee_id"
                                label="Pilih driver penanggungjawab"
                                :items="employees"
                                :taggable="true"
                            />
                            <div
                                v-if="form.errors.employee_id"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.employee_id[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="track"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Rute<span class="text-red-500">*</span></label
                            >
                            <input
                                id="track"
                                class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.track"
                                required
                                placeholder="Masukkan rute penugasan"
                            />
                            <div
                                v-if="form.errors.track"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.track[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="status"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Status<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <select
                                id="type"
                                v-model="form.status"
                                class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            >
                                <option :value="1">Aktif</option>
                                <option :value="0">Tidak Aktif</option>
                            </select>
                            <div
                                v-if="form.errors.status"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.status[0] }}
                            </div>
                        </div>
                    </div>
                </Modal>
                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus Tipe Kendaraan"
                    confirmText="Ya, Hapus!"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>

            <Pagination
                v-if="itemsPag && itemsPag.data && itemsPag.data.length > 0"
                :pagination="itemsPag"
            />
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import SearchIcon from "@/Components/icons/SearchIcon.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import UploadIcon from "@/Components/icons/UploadIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { ref, watch, computed } from "vue";
import { useForm, router, Link, Head } from "@inertiajs/vue3";

defineOptions({ layout: AppLayout });

// Props coming from ItemController@index
const props = defineProps({
    items: Object,
    departments: Array,
    filters: Object,
});

// Breadcrumbs
const breadcrumbs = [{ label: "Persediaan" }, { label: "Daftar Barang" }];

// Pagination + listing
const itemsPag = computed(() => props.items ?? {});
const itemsList = computed(() => itemsPag.value?.data ?? []);
const serialBase = computed(
    () =>
        ((itemsPag.value?.current_page ?? 1) - 1) *
        (itemsPag.value?.per_page ?? 10)
);

// Sorting and filters (match controller contract)
const sortField = ref(props.filters?.sort_field ?? "id");
const sortOrder = ref(props.filters?.sort_order ?? 1);
const filtersItems = ref({
    q: props.filters?.q ?? "",
    name: props.filters?.name ?? "",
    code: props.filters?.code ?? "",
    branch: props.filters?.branch ?? "",
    unit: props.filters?.unit ?? "",
    min_from: props.filters?.min_from ?? null,
    stock_from: props.filters?.stock_from ?? null,
    price_from: props.filters?.price_from ?? null,
    status: props.filters?.status ?? null,
});

function fetchItems(extra = {}) {
    router.get(
        route("items.index"),
        {
            ...filtersItems.value,
            sort_field: sortField.value,
            sort_order: sortOrder.value,
            per_page: itemsPag.value?.per_page ?? 10,
            page: itemsPag.value?.current_page ?? 1,
            ...extra,
        },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

function changeSortItems(column) {
    if (sortField.value === column) {
        sortOrder.value = sortOrder.value === 1 ? -1 : 1;
    } else {
        sortField.value = column;
        sortOrder.value = 1;
    }
    fetchItems();
}

let debounceT;
watch(
    () => ({ ...filtersItems.value }),
    () => {
        clearTimeout(debounceT);
        debounceT = setTimeout(() => fetchItems(), 400);
    },
    { deep: true }
);

function destroyRow(row) {
    if (!row?.id) return;
    if (!confirm("Yakin ingin menghapus barang ini?")) return;
    router.delete(route("items.destroy", row.id), {
        preserveScroll: true,
        onSuccess: () => fetchItems(),
    });
}

// Item modal (create/update)
const isItemModalOpen = ref(false);
const isEditItem = ref(false);
const itemForm = useForm({
    id: null,
    name: "",
    code: "",
    department_id: null,
    unit: "",
    price: null,
    stock: null,
    description: "",
});

const itemDialogTitle = computed(() =>
    isEditItem.value ? "Ubah Barang" : "Tambah Barang"
);

function openAddItemModal() {
    isEditItem.value = false;
    itemForm.reset();
    itemForm.department_id = props.departments?.[0]?.id ?? null;
    isItemModalOpen.value = true;
}

function openEditItem(row) {
    if (!row) return;
    isEditItem.value = true;
    itemForm.id = row.id;
    itemForm.name = row.name ?? "";
    itemForm.code = row.code ?? "";
    itemForm.department_id = row.department?.id ?? null;
    itemForm.unit = row.unit ?? "";
    itemForm.price = row.price ?? null;
    itemForm.stock = row.stock ?? null;
    itemForm.description = row.description ?? "";
    isItemModalOpen.value = true;
}

function closeItemModal() {
    isItemModalOpen.value = false;
    itemForm.clearErrors();
}

function saveItem() {
    if (isEditItem.value && itemForm.id) {
        itemForm.put(route("items.update", itemForm.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeItemModal();
                fetchItems();
            },
        });
    } else {
        itemForm.post(route("items.store"), {
            preserveScroll: true,
            onSuccess: () => {
                closeItemModal();
                fetchItems();
            },
        });
    }
}
</script>
