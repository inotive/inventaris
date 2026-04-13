<template>
    <Head title="Detail Kendaraan" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="bg-white rounded-lg lg:col-span-2">
                <div class="px-5 py-3 border-b border-gray-200">
                    <h1 class="text-lg font-semibold text-gray-800">
                        Detail Informasi
                    </h1>
                </div>
                <div class="flex flex-col gap-5 p-5 md:flex-row">
                    <!-- Images -->
                    <div
                        v-if="images && images.length > 0"
                        class="relative rounded-lg"
                    >
                        <!-- Active Image -->
                        <transition name="fade" mode="out-in">
                            <img
                                v-if="images.length > 0"
                                :key="images[activeIndex].id ?? activeIndex"
                                :src="`/storage/${images[activeIndex].file_path}`"
                                :alt="`Foto Barang ${activeIndex + 1}`"
                                class="h-40 w-full max-w-[220px] rounded-xl object-contain cursor-pointer sm:h-[180px]"
                                @click="openCarousel(images, activeIndex)"
                            />
                        </transition>

                        <!-- Controls -->
                        <button
                            v-if="activeIndex > 0"
                            @click="prev"
                            class="absolute left-2 top-1/2 p-1 text-white rounded-full -translate-y-1/2 bg-black/50"
                        >
                            ‹
                        </button>
                        <button
                            v-if="activeIndex < carouselImages.length - 1"
                            @click="next"
                            class="absolute right-2 top-1/2 p-1 text-white rounded-full -translate-y-1/2 bg-black/50"
                        >
                            ›
                        </button>

                        <!-- Indicators -->
                        <div
                            class="flex absolute bottom-2 left-1/2 gap-1 -translate-x-1/2"
                        >
                            <span
                                v-for="(image, idx) in images"
                                :key="image.id ?? idx"
                                class="w-2 h-2 rounded-full cursor-pointer"
                                :class="
                                    idx === activeIndex
                                        ? 'bg-white'
                                        : 'bg-gray-400'
                                "
                                @click="activeIndex = idx"
                            ></span>
                        </div>
                    </div>
                    <div
                        v-else
                        class="flex justify-center items-center h-40 w-full max-w-[220px] sm:h-[180px] bg-gray-50 rounded-xl border border-dashed border-gray-300"
                    >
                        <span
                            class="flex gap-2 justify-center items-center text-sm text-gray-400"
                        >
                            <svg
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M21 19V7a2 2 0 0 0-2-2H5C3.9 5 3 5.9 3 7v12a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2ZM8.5 11A2.5 2.5 0 1 0 8.5 6a2.5 2.5 0 0 0 0 5Zm12.5 6-6-8-5 6-3-4-5 6h19Z"
                                    fill="currentColor"
                                />
                            </svg>
                            Tidak ada foto kendaraan
                        </span>
                    </div>
                    <!-- End Images -->
                    <div
                        class="flex flex-col gap-3 justify-between px-3 w-full text-gray-800"
                    >
                        <div class="flex justify-between">
                            <div class="space-y-1 w-3/5">
                                <div class="text-lg font-bold">
                                    {{ vehicle.type?.name }} -
                                    {{ vehicle.license_code }}
                                </div>
                                <div class="flex justify-between">
                                    <div>
                                        {{ vehicle.branch?.name }}
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <div>
                                        {{ vehicle.track }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-4 items-end">
                                <div v-if="vehicle.progress">
                                    <div class="flex justify-center mb-1">
                                        <span
                                            class="text-blue-700 font- dark:text-white"
                                            >{{ vehicle.progress }}%</span
                                        >
                                    </div>
                                    <div
                                        class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700"
                                    >
                                        <div
                                            class="h-2 bg-blue-600 rounded-full"
                                            :style="{
                                                width: vehicle.progress + '%',
                                            }"
                                        ></div>
                                    </div>
                                </div>
                                <div>
                                    <span
                                        v-if="vehicle.status"
                                        class="inline-flex items-center px-3 h-7 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full border border-emerald-200"
                                        >Aktif</span
                                    >
                                    <span
                                        v-else
                                        class="inline-flex items-center px-3 h-7 text-xs font-medium text-rose-700 bg-rose-50 rounded-full border border-rose-200"
                                        >Non Aktif</span
                                    >
                                </div>
                            </div>
                        </div>
                        <div
                            class="grid grid-cols-1 gap-6 text-sm font-semibold sm:grid-cols-3"
                        >
                            <div class="space-y-1">
                                <div class="text-gray-500">Nama Driver</div>
                                <div class="text-gray-800">
                                    {{ vehicle.driver?.name ?? "-" }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-gray-500">Nomor Rangka</div>
                                <div class="text-gray-800">
                                    {{ vehicle.chassis_code ?? "-" }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="text-gray-500">Nomor Mesin</div>
                                <div class="text-gray-800">
                                    {{ vehicle.machine_code ?? "-" }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Informasi Rute Aktif -->
            <div class="mb-6 bg-white rounded-lg lg:col-span-1">
                <div class="px-5 py-3 border-b border-gray-200">
                    <h1 class="text-lg font-semibold text-gray-800">
                        Rute Perjalanan
                    </h1>
                </div>
                <div class="p-4">
                    <div class="flex flex-col gap-4">
                        <div class="flex justify-between items-center">
                            <span class="text-base text-gray-500"
                                >Rute Penugasan</span
                            >
                            <span class="text-base font-medium text-gray-800">
                                {{ vehicle.track ?? "-" }}
                            </span>
                        </div>
                        <!-- <div class="flex justify-between items-center">
                                <span class="text-base text-gray-500"
                                    >Status Operasional</span
                                >
                                <span
                                    v-if="vehicle.operational_status"
                                    class="inline-flex items-center px-3 h-7 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full border border-emerald-200"
                                >Beroperasi</span>
                                <span
                                    v-else
                                    class="inline-flex items-center px-3 h-7 text-xs font-medium text-gray-700 bg-gray-100 rounded-full border border-gray-300"
                                >Tidak Beroperasi</span>
                            </div> -->
                        <div class="flex justify-between items-center">
                            <span class="text-base text-gray-500">Cabang</span>
                            <span class="text-base font-medium text-gray-800">
                                {{ vehicle.branch?.name ?? "-" }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 mb-6 bg-white rounded-lg lg:col-span-3">
                <div class="px-5 py-3 border-b border-gray-200">
                    <h1 class="text-lg font-semibold text-gray-800">
                        Riwayat Terakhir
                    </h1>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div
                            class="flex justify-between items-center md:flex-col md:items-start"
                        >
                            <span class="text-base text-gray-500"
                                >Jarak Tempuh</span
                            >
                            <div class="text-right md:text-left">
                                <div class="text-lg font-bold">
                                    {{
                                        vehicle.latest_odometer?.current_km
                                            ? vehicle.latest_odometer.current_km.toLocaleString("id-ID")
                                            : 0
                                    }}
                                    KM
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex justify-between items-center md:flex-col md:items-start"
                        >
                            <span class="text-base text-gray-500"
                                >Inspeksi Terakhir</span
                            >
                            <div class="text-right md:text-left">
                                <div class="text-lg font-bold">
                                    {{
                                        vehicle.inspection
                                            ? formatTime(
                                                  vehicle.inspection.created_at
                                              )
                                            : "Tidak ditemukan"
                                    }}
                                </div>
                                <div
                                    v-if="vehicle.inspection"
                                    class="text-xs text-gray-400"
                                >
                                    {{
                                        Math.floor(
                                            (new Date() -
                                                new Date(
                                                    vehicle.inspection.created_at
                                                )) /
                                                (1000 * 60 * 60 * 24)
                                        )
                                    }}
                                    hari lalu
                                </div>
                            </div>
                        </div>
                        <div
                            class="flex justify-between items-center md:flex-col md:items-start"
                        >
                            <span class="text-base text-gray-500"
                                >Servis Terakhir</span
                            >
                            <div class="text-right md:text-left">
                                <div class="text-lg font-bold">
                                    {{
                                        vehicle.service
                                            ? formatTime(vehicle.service.date)
                                            : "Tidak ditemukan"
                                    }}
                                </div>
                                <div
                                    v-if="vehicle.service"
                                    class="text-xs text-gray-400"
                                >
                                    {{
                                        Math.floor(
                                            (new Date() -
                                                new Date(
                                                    vehicle.service.date
                                                )) /
                                                (1000 * 60 * 60 * 24)
                                        )
                                    }}
                                    hari lalu
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed -->
        <div class="bg-white rounded-lg">
            <div class="flex justify-between items-center pr-10">
                <ul
                    class="flex flex-wrap -mb-px text-sm font-medium text-center"
                    role="tablist"
                >
                    <li role="presentation">
                        <button
                            @click="setActive('documents')"
                            :class="[
                                'inline-block p-4 border-b-2 rounded-t-lg transition-colors',
                                activeTab === 'documents'
                                    ? 'border-blue-600 text-blue-700'
                                    : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300',
                            ]"
                            type="button"
                            role="tab"
                        >
                            Dokumen Kendaraan ({{
                                vehicleDocuments?.length || 0
                            }})
                        </button>
                    </li>
                    <li role="presentation">
                        <button
                            @click="setActive('inspections')"
                            :class="[
                                'inline-block p-4 border-b-2 rounded-t-lg transition-colors',
                                activeTab === 'inspections'
                                    ? 'border-blue-600 text-blue-700'
                                    : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300',
                            ]"
                            type="button"
                            role="tab"
                        >
                            Riwayat Inspeksi ({{
                                filteredInspections?.length || 0
                            }})
                        </button>
                    </li>
                    <li role="presentation">
                        <button
                            @click="setActive('services')"
                            :class="[
                                'inline-block p-4 border-b-2 rounded-t-lg transition-colors',
                                activeTab === 'services'
                                    ? 'border-blue-600 text-blue-700'
                                    : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300',
                            ]"
                            type="button"
                            role="tab"
                        >
                            Riwayat Service ({{
                                filteredServices?.length || 0
                            }})
                        </button>
                    </li>
                    <li role="presentation">
                        <button
                            @click="setActive('odometer')"
                            :class="[
                                'inline-block p-4 border-b-2 rounded-t-lg transition-colors',
                                activeTab === 'odometer'
                                    ? 'border-blue-600 text-blue-700'
                                    : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300',
                            ]"
                            type="button"
                            role="tab"
                        >
                            Riwayat Kilometer ({{
                                filteredOdometerHistory?.length || 0
                            }})
                        </button>
                    </li>
                </ul>
                <button
                    v-if="activeTab === 'documents'"
                    @click="openAddModal"
                    type="button"
                    class="flex gap-2 items-center px-3 py-2 text-white bg-blue-500 rounded transition-colors hover:bg-blue-600"
                >
                    <PlusSquareIcon />
                    <span class="hidden text-sm md:block">Tambah Dokumen</span>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="border-t border-gray-200">
                <!-- Documents Tab -->
                <div
                    v-show="activeTab === 'documents'"
                    class="p-5 rounded-b-lg dark:bg-gray-800"
                    role="tabpanel"
                >
                    <div class="overflow-x-auto bg-white shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex gap-2 items-center">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari dokumen..."
                                    class="px-3 py-2 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>

                        <table class="w-full text-sm border border-1">
                            <thead>
                                <tr>
                                    <th
                                        class="py-3 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
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
                                        class="py-3 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                Nama Dokumen
                                            </p>
                                        </div>
                                    </th>
                                    <th
                                        class="py-3 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                Lampiran
                                            </p>
                                        </div>
                                    </th>
                                    <th
                                        class="py-3 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                Tanggal Perpanjangan
                                            </p>
                                        </div>
                                    </th>
                                    <th
                                        class="py-3 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                Tanggal Kadaluarsa
                                            </p>
                                        </div>
                                    </th>
                                    <th
                                        class="py-3 border-gray-200 cursor-pointer border-y dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                Aksi
                                            </p>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template
                                    v-if="
                                        vehicleDocuments &&
                                        vehicleDocuments.length > 0
                                    "
                                >
                                    <tr
                                        v-for="(
                                            document, index
                                        ) in vehicleDocuments"
                                        :key="document.id"
                                        class="cursor-pointer hover: dark:hover:bg-gray-800"
                                    >
                                        <td
                                            class="py-3 border-gray-200 border-y dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center whitespace-nowrap"
                                            >
                                                <p
                                                    class="px-3 text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ index + 1 }}.
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ document.name }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <img
                                                    v-for="(
                                                        file, fIndex
                                                    ) in document.files"
                                                    :key="file.id"
                                                    :src="`/storage/${file.file_path}`"
                                                    @click="
                                                        openCarousel(
                                                            document.files,
                                                            fIndex
                                                        )
                                                    "
                                                    class="object-contain w-10 h-10 transition-opacity duration-500 cursor-pointer"
                                                />
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        formatTime(
                                                            document.renewal_date
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        formatTime(
                                                            document.expired_date
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border-gray-200 border-y dark:border-gray-600"
                                        >
                                            <div
                                                class="flex gap-3 justify-center whitespace-nowrap"
                                            >
                                                <button
                                                    @click="
                                                        openEditModal(document)
                                                    "
                                                >
                                                    <EditIcon
                                                        class="text-yellow-500"
                                                    />
                                                </button>
                                                <button
                                                    @click="
                                                        openConfirmModal(
                                                            document
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
                                <tr v-else>
                                    <td
                                        colspan="6"
                                        class="py-8 font-medium text-center text-gray-500 dark:text-gray-400"
                                    >
                                        <div
                                            class="flex flex-col gap-2 items-center"
                                        >
                                            <svg
                                                width="28"
                                                height="28"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm4 18H6V4h7v5h5v11Z"
                                                    fill="currentColor"
                                                />
                                            </svg>
                                            <div>
                                                Tidak ada dokumen ditemukan.
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div
                            class="flex justify-between items-center px-4 py-3 bg-gray-50 border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600"
                        >
                            <div class="flex gap-4 items-center">
                                <select
                                    v-model="perPage"
                                    class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="10">10 per halaman</option>
                                    <option value="25">25 per halaman</option>
                                    <option value="50">50 per halaman</option>
                                </select>
                                <div
                                    class="text-sm text-gray-700 dark:text-gray-300"
                                >
                                    Menampilkan
                                    <span class="font-medium">{{
                                        paginatedDocuments?.length || 0
                                    }}</span>
                                    dari
                                    <span class="font-medium">{{
                                        filteredDocuments?.length || 0
                                    }}</span>
                                    dokumen
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button
                                    class="px-3 py-1 text-sm text-gray-500 bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                    disabled
                                >
                                    ‹ Sebelumnya
                                </button>
                                <button
                                    class="px-3 py-1 text-sm text-white bg-blue-500 rounded-md border border-blue-500"
                                >
                                    1
                                </button>
                                <button
                                    class="px-3 py-1 text-sm text-gray-500 bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                    disabled
                                >
                                    Selanjutnya ›
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inspections Tab -->
                <div
                    v-show="activeTab === 'inspections'"
                    class="py-3 rounded-b-lg dark:bg-gray-800"
                    role="tabpanel"
                >
                    <div class="overflow-auto" data-simplebar>
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr>
                                    <th
                                        class="py-3 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
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
                                        class="py-3 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                Tanggal Inspeksi
                                            </p>
                                        </div>
                                    </th>
                                    <th
                                        class="py-3 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                No. Inspeksi
                                            </p>
                                        </div>
                                    </th>
                                    <th
                                        class="py-3 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                Checklist
                                            </p>
                                        </div>
                                    </th>
                                    <!-- Inspektor -->
                                    <th
                                        class="py-3 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                Inspektor
                                            </p>
                                        </div>
                                    </th>
                                    <th
                                        class="py-3 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                % Kondisi
                                            </p>
                                        </div>
                                    </th>
                                    <th
                                        class="py-3 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                Status
                                            </p>
                                        </div>
                                    </th>
                                    <th
                                        class="py-3 border-gray-200 cursor-pointer border-y dark:border-gray-600 dark:bg-gray-800"
                                    >
                                        <div
                                            class="flex gap-2 justify-center items-center px-3"
                                        >
                                            <p
                                                class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                            >
                                                Aksi
                                            </p>
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <!-- Filter Row -->
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    ></th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div class="flex flex-col gap-1">
                                            <input
                                                v-model="filters.date_from"
                                                type="date"
                                                placeholder="Dari"
                                                class="px-2 w-full h-8 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            />
                                            <input
                                                v-model="filters.date_to"
                                                type="date"
                                                placeholder="Sampai"
                                                class="px-2 w-full h-8 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            />
                                        </div>
                                    </th>
                                    <th class="px-2 py-2 border border-gray-200 dark:border-gray-600"></th>
                                    <th class="px-2 py-2 border border-gray-200 dark:border-gray-600"></th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    >
                                        <select
                                            v-model="filters.inspector"
                                            class="px-2 py-1 w-full text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="">
                                                Semua Inspektor
                                            </option>
                                            <option
                                                v-for="inspector in uniqueInspectors"
                                                :key="inspector.id"
                                                :value="inspector.id"
                                            >
                                                {{ inspector.name }}
                                            </option>
                                        </select>
                                    </th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    ></th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    >
                                        <select
                                            v-model="filters.status"
                                            class="px-2 py-1 w-full text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value="">
                                                Semua Status
                                            </option>
                                            <option value="draft">
                                                Belum Selesai
                                            </option>
                                            <option value="on_progress">
                                                Sedang Berjalan
                                            </option>
                                            <option value="submitted">
                                                Selesai
                                            </option>
                                        </select>
                                    </th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    >
                                        <button
                                            @click="clearFilters"
                                            class="px-2 py-1 text-xs text-gray-600 bg-white rounded border border-gray-300 hover:bg-gray-50"
                                        >
                                            Clear
                                        </button>
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <template
                                    v-if="
                                        paginatedInspections &&
                                        paginatedInspections.length > 0
                                    "
                                >
                                    <tr
                                        v-for="(
                                            inspection, index
                                        ) in paginatedInspections"
                                        :key="inspection.id"
                                        class="border-b border-gray-200 hover:bg-gray-50"
                                    >
                                        <td
                                            class="py-3 border-gray-200 border-y dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center whitespace-nowrap"
                                            >
                                                <p
                                                    class="px-3 text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ (currentPageInspections - 1) * perPageInspections + index + 1 }}.
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        formatTime(
                                                            inspection.created_at
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        inspection.inspection_number ||
                                                        "-"
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        inspection.checklist
                                                            ?.name || "-"
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                        <!-- Inspektor -->
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        inspection.submitted_by
                                                            ?.name ?? "-"
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="px-14 whitespace-nowrap"
                                            >
                                                <div
                                                    class="flex justify-center mb-1"
                                                >
                                                    <span
                                                        class="text-blue-700 dark:text-white"
                                                        >{{
                                                            inspection.progress
                                                        }}%</span
                                                    >
                                                </div>
                                                <div
                                                    class="w-full h-2 bg-gray-200 rounded-full dark:bg-gray-700"
                                                >
                                                    <div
                                                        class="h-2 bg-blue-600 rounded-full"
                                                        :style="{
                                                            width:
                                                                inspection.progress +
                                                                '%',
                                                        }"
                                                    ></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <span
                                                    :class="
                                                        getStatusBadge(
                                                            inspection.status
                                                        ).class
                                                    "
                                                    class="px-2 py-1 font-normal rounded-lg border-2"
                                                >
                                                    {{
                                                        getStatusBadge(
                                                            inspection.status
                                                        ).label
                                                    }}
                                                </span>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border-gray-200 border-y dark:border-gray-600"
                                        >
                                            <div
                                                class="flex gap-2 justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <button
                                                    @click="
                                                        viewInspectionDetail(
                                                            inspection.id
                                                        )
                                                    "
                                                    class="px-3 py-1 text-xs text-blue-600 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100"
                                                >
                                                    Lihat Detail
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-else>
                                    <td
                                        colspan="6"
                                        class="py-8 font-medium text-center text-gray-500"
                                    >
                                        <div
                                            class="flex flex-col gap-2 items-center"
                                        >
                                            <svg
                                                width="28"
                                                height="28"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path
                                                    d="M9 11H7v2h2v-2Zm4 0h-2v2h2v-2Zm4 0h-2v2h2v-2Zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2Zm0 16H5V9h14v11Z"
                                                    fill="currentColor"
                                                />
                                            </svg>
                                            <div>
                                                Belum ada riwayat inspeksi
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination for Inspections -->
                        <div
                            v-if="filteredInspections && filteredInspections.length > 0"
                            class="flex justify-between items-center px-4 py-3 bg-gray-50 border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600"
                        >
                            <div class="flex gap-4 items-center">
                                <select
                                    v-model.number="perPageInspections"
                                    @change="currentPageInspections = 1"
                                    class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                >
                                    <option :value="10">10 per halaman</option>
                                    <option :value="25">25 per halaman</option>
                                    <option :value="50">50 per halaman</option>
                                </select>
                                <div
                                    class="text-sm text-gray-700 dark:text-gray-300"
                                >
                                    Menampilkan
                                    <span class="font-medium">{{ paginatedInspections?.length || 0 }}</span>
                                    dari
                                    <span class="font-medium">{{ filteredInspections?.length || 0 }}</span>
                                    inspeksi
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button
                                    @click="prevPageInspections"
                                    :disabled="currentPageInspections === 1"
                                    class="px-3 py-1 text-sm text-gray-500 bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                >
                                    ‹ Sebelumnya
                                </button>
                                <template v-for="page in totalPagesInspections" :key="page">
                                    <button
                                        v-if="page === 1 || page === totalPagesInspections || (page >= currentPageInspections - 1 && page <= currentPageInspections + 1)"
                                        @click="goToPageInspections(page)"
                                        :class="[
                                            'px-3 py-1 text-sm rounded-md border',
                                            currentPageInspections === page
                                                ? 'text-white bg-blue-500 border-blue-500'
                                                : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                    <span
                                        v-else-if="page === currentPageInspections - 2 || page === currentPageInspections + 2"
                                        class="px-2 text-gray-500"
                                    >
                                        ...
                                    </span>
                                </template>
                                <button
                                    @click="nextPageInspections"
                                    :disabled="currentPageInspections === totalPagesInspections || totalPagesInspections === 0"
                                    class="px-3 py-1 text-sm text-gray-500 bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                >
                                    Selanjutnya ›
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services Tab -->
                <div
                    v-show="activeTab === 'services'"
                    class="py-3 rounded-b-lg dark:bg-gray-800"
                    role="tabpanel"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr>
                                    <th
                                        class="p-3 bg-gray-100 border-gray-200 border-y"
                                    >
                                        <div
                                            class="font-medium text-center text-gray-600"
                                        >
                                            No
                                        </div>
                                    </th>
                                    <th
                                        class="p-3 bg-gray-100 border border-gray-200"
                                    >
                                        <div
                                            class="font-medium text-center text-gray-600"
                                        >
                                            Gambar Kendaraan
                                        </div>
                                    </th>
                                    <th
                                        class="p-3 bg-gray-100 border border-gray-200"
                                    >
                                        <div
                                            class="font-medium text-left text-gray-600"
                                        >
                                            Tanggal Service
                                        </div>
                                    </th>
                                    <th
                                        class="p-3 bg-gray-100 border border-gray-200"
                                    >
                                        <div
                                            class="font-medium text-center text-gray-600"
                                        >
                                            Jarak Tempuh
                                        </div>
                                    </th>
                                    <th
                                        class="p-3 bg-gray-100 border border-gray-200"
                                    >
                                        <div
                                            class="font-medium text-center text-gray-600"
                                        >
                                            Biaya Service
                                        </div>
                                    </th>
                                    <th
                                        class="p-3 bg-gray-100 border-gray-200 border-y"
                                    >
                                        <div
                                            class="font-medium text-center text-gray-600"
                                        >
                                            Aksi
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <!-- Filter Row -->
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    ></th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    ></th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div class="flex flex-col gap-1">
                                            <input
                                                v-model="
                                                    serviceFilters.date_from
                                                "
                                                type="date"
                                                placeholder="Dari"
                                                class="px-2 w-full h-8 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            />
                                            <input
                                                v-model="serviceFilters.date_to"
                                                type="date"
                                                placeholder="Sampai"
                                                class="px-2 w-full h-8 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div class="flex flex-col gap-1">
                                            <input
                                                v-model.number="
                                                    serviceFilters.distance_from
                                                "
                                                type="number"
                                                min="0"
                                                step="1"
                                                placeholder="Min (≥)"
                                                class="px-2 w-full h-8 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                @input="
                                                    serviceFilters.distance_from =
                                                        serviceFilters.distance_from ===
                                                        ''
                                                            ? null
                                                            : serviceFilters.distance_from
                                                "
                                            />
                                            <input
                                                v-model.number="
                                                    serviceFilters.distance_to
                                                "
                                                type="number"
                                                min="0"
                                                step="1"
                                                placeholder="Max (≤)"
                                                class="px-2 w-full h-8 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                @input="
                                                    serviceFilters.distance_to =
                                                        serviceFilters.distance_to ===
                                                        ''
                                                            ? null
                                                            : serviceFilters.distance_to
                                                "
                                            />
                                        </div>
                                    </th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div class="flex flex-col gap-1">
                                            <input
                                                v-model.number="
                                                    serviceFilters.cost_from
                                                "
                                                type="number"
                                                min="0"
                                                step="1"
                                                placeholder="Min (≥)"
                                                class="px-2 w-full h-8 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                @input="
                                                    serviceFilters.cost_from =
                                                        serviceFilters.cost_from ===
                                                        ''
                                                            ? null
                                                            : serviceFilters.cost_from
                                                "
                                            />
                                            <input
                                                v-model.number="
                                                    serviceFilters.cost_to
                                                "
                                                type="number"
                                                min="0"
                                                step="1"
                                                placeholder="Max (≤)"
                                                class="px-2 w-full h-8 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                @input="
                                                    serviceFilters.cost_to =
                                                        serviceFilters.cost_to ===
                                                        ''
                                                            ? null
                                                            : serviceFilters.cost_to
                                                "
                                            />
                                        </div>
                                    </th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    >
                                        <button
                                            @click="clearServiceFilters"
                                            class="px-2 py-1 text-xs text-gray-600 bg-white rounded border border-gray-300 hover:bg-gray-50"
                                        >
                                            Clear
                                        </button>
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <template
                                    v-if="
                                        paginatedServices &&
                                        paginatedServices.length > 0
                                    "
                                >
                                    <tr
                                        v-for="(
                                            service, index
                                        ) in paginatedServices"
                                        :key="service.id"
                                        class="border-b border-gray-200 hover:bg-gray-50"
                                    >
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="px-3 text-gray-500 dark:text-gray-400"
                                                >
                                                    {{ (currentPageServices - 1) * perPageServices + index + 1 }}.
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3"
                                            >
                                                <img
                                                    v-if="
                                                        getVehiclePhoto(service)
                                                    "
                                                    :src="`/storage/${getVehiclePhoto(
                                                        service
                                                    )}`"
                                                    @click="
                                                        openCarousel(
                                                            getVehicleImages(
                                                                service
                                                            ),
                                                            0
                                                        )
                                                    "
                                                    class="object-cover w-12 h-12 rounded-full transition-opacity cursor-pointer hover:opacity-80"
                                                    alt="Vehicle photo"
                                                    loading="lazy"
                                                />
                                                <div
                                                    v-else
                                                    class="flex justify-center items-center w-12 h-12 bg-gray-200 rounded-full"
                                                >
                                                    <svg
                                                        width="20"
                                                        height="20"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                    >
                                                        <path
                                                            d="M21 19V7a2 2 0 0 0-2-2H5C3.9 5 3 5.9 3 7v12a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2ZM8.5 11A2.5 2.5 0 1 0 8.5 6a2.5 2.5 0 0 0 0 5Zm12.5 6-6-8-5 6-3-4-5 6h19Z"
                                                            fill="currentColor"
                                                            class="text-gray-400"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-800 dark:text-gray-300"
                                                >
                                                    {{
                                                        formatTime(service.date)
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-800 dark:text-gray-300"
                                                >
                                                    {{
                                                        service.distance?.toLocaleString(
                                                            "id-ID"
                                                        ) || 0
                                                    }}
                                                    km
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-800 dark:text-gray-300"
                                                >
                                                    Rp
                                                    {{
                                                        service.cost?.toLocaleString(
                                                            "id-ID"
                                                        ) || 0
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border-gray-200 border-y dark:border-gray-600"
                                        >
                                            <div
                                                class="flex gap-2 justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <button
                                                    @click="
                                                        viewServiceDetail(
                                                            service.id
                                                        )
                                                    "
                                                    class="px-3 py-1 text-xs text-blue-600 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100"
                                                >
                                                    Lihat Detail
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr>
                                        <td
                                            colspan="6"
                                            class="py-8 font-medium text-center text-gray-500"
                                        >
                                            <div
                                                class="flex flex-col gap-2 items-center"
                                            >
                                                <svg
                                                    width="28"
                                                    height="28"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <path
                                                        d="M22.7 19-9.1 9.6c.1-.3.2-.6.2-1 0-2.2-1.8-4-4-4S5 6.4 5 8.6c0 2.2 1.8 4 4 4 .4 0 .7-.1 1-.2l9.4 9.4c.4.4 1 .4 1.4 0l1.9-1.9c.4-.4.4-1 0-1.4ZM7 8.6c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2Zm3.85 5.15c-.3.3-.7.5-1.1.6l-1.9-1.9c.1-.4.3-.8.6-1.1l1.4 1.4 1 1Z"
                                                        fill="currentColor"
                                                    />
                                                    <path
                                                        d="M17.5 10c1.4 0 2.5-1.1 2.5-2.5V2H4v5.5C4 8.9 5.1 10 6.5 10h1.6c.2.6.6 1.1 1 1.5L7.7 13H4v9h16v-9h-3.7l-1.4-1.5c.4-.4.8-.9 1-1.5h1.6Z"
                                                        fill="currentColor"
                                                    />
                                                </svg>
                                                <div>
                                                    Belum ada riwayat service
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <!-- Pagination for Services -->
                        <div
                            v-if="totalPagesServices > 1"
                            class="flex justify-between items-center px-4 py-3 bg-gray-50 border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600"
                        >
                            <div class="flex gap-4 items-center">
                                <select
                                    v-model.number="perPageServices"
                                    @change="currentPageServices = 1"
                                    class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                >
                                    <option :value="10">10 per halaman</option>
                                    <option :value="25">25 per halaman</option>
                                    <option :value="50">50 per halaman</option>
                                </select>
                                <div
                                    class="text-sm text-gray-700 dark:text-gray-300"
                                >
                                    Menampilkan
                                    <span class="font-medium">{{ paginatedServices?.length || 0 }}</span>
                                    dari
                                    <span class="font-medium">{{ filteredServices?.length || 0 }}</span>
                                    service
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button
                                    @click="prevPageServices"
                                    :disabled="currentPageServices === 1"
                                    class="px-3 py-1 text-sm text-gray-500 bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                >
                                    ‹ Sebelumnya
                                </button>
                                <template v-for="page in totalPagesServices" :key="page">
                                    <button
                                        v-if="page === 1 || page === totalPagesServices || (page >= currentPageServices - 1 && page <= currentPageServices + 1)"
                                        @click="goToPageServices(page)"
                                        :class="[
                                            'px-3 py-1 text-sm rounded-md border',
                                            currentPageServices === page
                                                ? 'text-white bg-blue-500 border-blue-500'
                                                : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                    <span
                                        v-else-if="page === currentPageServices - 2 || page === currentPageServices + 2"
                                        class="px-2 text-gray-500"
                                    >
                                        ...
                                    </span>
                                </template>
                                <button
                                    @click="nextPageServices"
                                    :disabled="currentPageServices === totalPagesServices || totalPagesServices === 0"
                                    class="px-3 py-1 text-sm text-gray-500 bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                >
                                    Selanjutnya ›
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Odometer History Tab -->
                <div
                    v-show="activeTab === 'odometer'"
                    class="py-3 rounded-b-lg dark:bg-gray-800"
                    role="tabpanel"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr>
                                    <th
                                        class="p-3 bg-gray-100 border-gray-200 border-y"
                                    >
                                        <div
                                            class="font-medium text-center text-gray-600"
                                        >
                                            No
                                        </div>
                                    </th>
                                    <th
                                        class="p-3 bg-gray-100 border border-gray-200"
                                    >
                                        <div
                                            class="font-medium text-left text-gray-600"
                                        >
                                            Tanggal
                                        </div>
                                    </th>
                                    <th
                                        class="p-3 bg-gray-100 border border-gray-200"
                                    >
                                        <div
                                            class="font-medium text-center text-gray-600"
                                        >
                                            Kilometer Terakhir
                                        </div>
                                    </th>
                                    <th
                                        class="p-3 bg-gray-100 border-gray-200 border-y"
                                    >
                                        <div
                                            class="font-medium text-center text-gray-600"
                                        >
                                            Kilometer Terbaru
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <!-- Filter Row -->
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    ></th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    >
                                        <div class="flex flex-col gap-1">
                                            <input
                                                v-model="odometerFilters.date_from"
                                                type="date"
                                                placeholder="Dari"
                                                class="px-2 w-full h-8 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            />
                                            <input
                                                v-model="odometerFilters.date_to"
                                                type="date"
                                                placeholder="Sampai"
                                                class="px-2 w-full h-8 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    ></th>
                                    <th
                                        class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                                    >
                                        <button
                                            @click="clearOdometerFilters"
                                            class="px-2 py-1 text-xs text-gray-600 bg-white rounded border border-gray-300 hover:bg-gray-50"
                                        >
                                            Clear
                                        </button>
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <template
                                    v-if="
                                        paginatedOdometerHistory &&
                                        paginatedOdometerHistory.length > 0
                                    "
                                >
                                    <tr
                                        v-for="(
                                            odometer, index
                                        ) in paginatedOdometerHistory"
                                        :key="odometer.id"
                                        class="border-b border-gray-200 hover:bg-gray-50"
                                    >
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="px-3 text-gray-500 dark:text-gray-400"
                                                >
                                                    {{
                                                        (currentPageOdometer -
                                                            1) *
                                                            perPageOdometer +
                                                        index +
                                                        1
                                                    }}.
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-800 dark:text-gray-300"
                                                >
                                                    {{
                                                        formatTime(
                                                            odometer.tanggal
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border border-gray-200 dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-800 dark:text-gray-300"
                                                >
                                                    {{
                                                        odometer.last_km?.toLocaleString(
                                                            "id-ID"
                                                        ) || 0
                                                    }}
                                                    km
                                                </p>
                                            </div>
                                        </td>
                                        <td
                                            class="py-3 border-gray-200 border-y dark:border-gray-600"
                                        >
                                            <div
                                                class="flex justify-center items-center px-3 whitespace-nowrap"
                                            >
                                                <p
                                                    class="text-gray-800 dark:text-gray-300"
                                                >
                                                    {{
                                                        odometer.current_km?.toLocaleString(
                                                            "id-ID"
                                                        ) || 0
                                                    }}
                                                    km
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <template v-else>
                                    <tr>
                                        <td
                                            colspan="4"
                                            class="py-8 font-medium text-center text-gray-500"
                                        >
                                            <div
                                                class="flex flex-col gap-2 items-center"
                                            >
                                                <svg
                                                    width="28"
                                                    height="28"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                >
                                                    <path
                                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                                                        fill="currentColor"
                                                    />
                                                </svg>
                                                <div>
                                                    Belum ada riwayat kilometer
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <!-- Pagination for Odometer History -->
                        <div
                            v-if="
                                filteredOdometerHistory &&
                                filteredOdometerHistory.length > 0
                            "
                            class="flex justify-between items-center px-4 py-3 bg-gray-50 border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600"
                        >
                            <div class="flex gap-4 items-center">
                                <select
                                    v-model.number="perPageOdometer"
                                    @change="currentPageOdometer = 1"
                                    class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                >
                                    <option :value="10">10 per halaman</option>
                                    <option :value="25">25 per halaman</option>
                                    <option :value="50">50 per halaman</option>
                                </select>
                                <div
                                    class="text-sm text-gray-700 dark:text-gray-300"
                                >
                                    Menampilkan
                                    <span class="font-medium">{{
                                        paginatedOdometerHistory?.length || 0
                                    }}</span>
                                    dari
                                    <span class="font-medium">{{
                                        filteredOdometerHistory?.length || 0
                                    }}</span>
                                    riwayat kilometer
                                </div>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button
                                    @click="prevPageOdometer"
                                    :disabled="currentPageOdometer === 1"
                                    class="px-3 py-1 text-sm text-gray-500 bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                >
                                    ‹ Sebelumnya
                                </button>
                                <template
                                    v-for="page in totalPagesOdometer"
                                    :key="page"
                                >
                                    <button
                                        v-if="
                                            page === 1 ||
                                            page === totalPagesOdometer ||
                                            (page >=
                                                currentPageOdometer - 1 &&
                                                page <=
                                                    currentPageOdometer + 1)
                                        "
                                        @click="goToPageOdometer(page)"
                                        :class="[
                                            'px-3 py-1 text-sm rounded-md border',
                                            currentPageOdometer === page
                                                ? 'text-white bg-blue-500 border-blue-500'
                                                : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700',
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                    <span
                                        v-else-if="
                                            page ===
                                                currentPageOdometer - 2 ||
                                            page ===
                                                currentPageOdometer + 2
                                        "
                                        class="px-2 text-gray-500"
                                    >
                                        ...
                                    </span>
                                </template>
                                <button
                                    @click="nextPageOdometer"
                                    :disabled="
                                        currentPageOdometer ===
                                            totalPagesOdometer ||
                                        totalPagesOdometer === 0
                                    "
                                    class="px-3 py-1 text-sm text-gray-500 bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                                >
                                    Selanjutnya ›
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal
            :show="isModalOpen"
            :title="
                isEditMode
                    ? `Edit ${selectedItem?.name}`
                    : 'Tambah Dokumen Kendaraan'
            "
            confirmText="Simpan"
            maxWidth="lg"
            @close="closeModal"
            @confirm="saveDocument"
        >
            <div class="space-y-3">
                <!-- Tampilkan file yang sudah ada saat edit mode -->
                <div
                    v-if="isEditMode && selectedItem?.files?.length > 0"
                    class="space-y-1 text-sm"
                >
                    <label class="font-semibold text-gray-900 dark:text-white">
                        File Saat Ini
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <div
                            v-for="file in selectedItem.files"
                            :key="file.id"
                            class="relative"
                        >
                            <img
                                :src="`/storage/${file.file_path}`"
                                class="object-cover w-20 h-20 rounded border border-gray-300"
                                :alt="selectedItem.name"
                            />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">
                        Upload file baru untuk mengganti file yang ada
                    </p>
                </div>

                <div class="space-y-1 text-sm">
                    <label class="font-semibold text-gray-900 dark:text-white">
                        File Dokumen<span
                            v-if="!isEditMode"
                            class="text-red-500"
                            >*</span
                        >
                        <span
                            v-if="isEditMode"
                            class="font-normal text-gray-500"
                            >(Opsional)</span
                        >
                    </label>

                    <!-- Preview Existing Files (Edit Mode) -->
                    <div
                        v-if="
                            isEditMode &&
                            selectedItem &&
                            selectedItem.files &&
                            selectedItem.files.length > 0
                        "
                        class="mb-3"
                    >
                        <p class="mb-2 text-xs text-gray-600">File Saat Ini:</p>
                        <div class="grid grid-cols-3 gap-2">
                            <div
                                v-for="(file, idx) in selectedItem.files"
                                :key="file.id || idx"
                                class="relative group"
                                :class="{
                                    'opacity-50 border-red-500': isFileDeleted(
                                        file.id
                                    ),
                                }"
                            >
                                <img
                                    :src="`/storage/${file.file_path}`"
                                    :alt="`Document file ${idx + 1}`"
                                    class="object-cover w-full h-24 rounded-lg border border-gray-300 cursor-pointer"
                                    @click="
                                        openCarousel(selectedItem.files, idx)
                                    "
                                />
                                <!-- Delete Button -->
                                <div
                                    v-if="!isFileDeleted(file.id)"
                                    class="flex absolute top-1 right-1 z-10 justify-center items-center w-6 h-6 text-xs text-white bg-red-500 rounded-full cursor-pointer hover:bg-red-600"
                                    @click="deleteExistingFile(file.id)"
                                >
                                    ×
                                </div>
                                <!-- Undo Button -->
                                <div
                                    v-else
                                    class="flex absolute top-1 right-1 z-10 justify-center items-center w-6 h-6 text-xs text-white bg-green-500 rounded-full cursor-pointer hover:bg-green-600"
                                    @click="undoDeleteFile(file.id)"
                                >
                                    ↺
                                </div>
                                <!-- Delete overlay message -->
                                <div
                                    v-if="isFileDeleted(file.id)"
                                    class="flex absolute inset-0 justify-center items-center rounded-lg bg-red-500/20"
                                >
                                    <span
                                        class="text-xs font-semibold text-red-600"
                                        >Akan dihapus</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview New Uploads -->
                    <div v-if="imagePreviews.length > 0" class="mb-3">
                        <p class="mb-2 text-xs text-gray-600">
                            Preview File Baru:
                        </p>
                        <div class="grid grid-cols-3 gap-2">
                            <div
                                v-for="(preview, index) in imagePreviews"
                                :key="index"
                                class="relative group"
                            >
                                <img
                                    :src="preview"
                                    :alt="`Preview ${index + 1}`"
                                    class="object-cover w-full h-24 rounded-lg border border-gray-300"
                                />
                                <div
                                    class="flex absolute top-1 right-1 justify-center items-center w-6 h-6 text-xs text-white bg-red-500 rounded-full cursor-pointer hover:bg-red-600"
                                    @click="removeFile(index)"
                                >
                                    ×
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-bind="getRootProps()"
                        :class="[
                            'p-6 text-center rounded-lg border-2 cursor-pointer border-1',
                            form.errors.files
                                ? 'text-red-500 bg-red-50 border-red-300 dark:bg-red-900/20 dark:border-red-500'
                                : 'text-blue-500 bg-sky-400 bg-opacity-5 border-blue-300',
                        ]"
                    >
                        <input v-bind="getInputProps()" />
                        <div class="flex gap-8 items-center justify-stretch">
                            <UploadIcon
                                :class="
                                    form.errors.files
                                        ? 'w-12 h-12 text-red-300'
                                        : 'w-12 h-12 text-blue-300'
                                "
                            />
                            <div class="flex flex-col text-start">
                                <p v-if="isDragActive">
                                    Lepaskan file di sini ...
                                </p>
                                <p v-else>
                                    {{
                                        isEditMode
                                            ? "Ganti atau tambah file dengan drag & drop"
                                            : "Drag & drop file di sini, atau klik untuk pilih"
                                    }}
                                </p>
                                <ul
                                    v-if="form.files.length > 0"
                                    class="mt-2 text-sm text-gray-600"
                                >
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
                    <div v-if="form.errors.files" class="text-sm text-red-500">
                        {{ form.errors.files }}
                    </div>
                </div>
                <div class="space-y-1 text-sm">
                    <label
                        for="name"
                        class="font-semibold text-gray-900 dark:text-white"
                        >Nama Dokumen<span class="text-red-500">*</span></label
                    >
                    <input
                        v-model="form.name"
                        id="name"
                        class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        type="text"
                        required
                        placeholder="Masukkan nama dokumen"
                    />
                    <div v-if="form.errors.name" class="text-sm text-red-500">
                        {{ form.errors.name[0] }}
                    </div>
                </div>

                <div class="flex gap-x-5 justify-between text-sm">
                    <label
                        for="renewal_date"
                        class="space-y-1 w-1/2 font-semibold text-gray-900 dark:text-white"
                        >Tanggal Perpanjangan<span class="text-red-500">*</span>
                        <div class="relative">
                            <input
                                v-model="form.renewal_date"
                                id="renewal_date"
                                type="date"
                                class="w-full text-sm font-medium leading-none placeholder-gray-500 text-gray-600 rounded-lg border border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 ps-2.5"
                            />
                        </div>
                        <div
                            v-if="form.errors.renewal_date"
                            class="text-sm text-red-500"
                        >
                            {{ form.errors.renewal_date[0] }}
                        </div>
                    </label>
                    <label
                        for="expired_date"
                        class="space-y-1 w-1/2 font-semibold text-gray-900 dark:text-white"
                        >Tanggal Kadaluarsa<span class="text-red-500">*</span>
                        <div class="relative">
                            <input
                                v-model="form.expired_date"
                                id="expired_date"
                                type="date"
                                class="w-full text-sm font-medium leading-none placeholder-gray-500 text-gray-600 rounded-lg border border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 ps-2.5"
                            />
                        </div>
                        <div
                            v-if="form.errors.expired_date"
                            class="text-sm text-red-500"
                        >
                            {{ form.errors.expired_date[0] }}
                        </div>
                    </label>
                </div>
            </div>
        </Modal>
        <ConfirmModal
            :show="isConfirmModalOpen"
            :question="`Yakin ingin menghapus`"
            :selected="`${selectedItem?.name}`"
            title="Hapus Dokumen Kendaraan"
            confirmText="Ya, Hapus!"
            maxWidth="md"
            @close="closeConfirmModal"
            @confirm="destroyData"
        />

        <!-- Carousel -->
        <div
            v-if="isOpen"
            @click.self="closeCarousel"
            class="flex fixed inset-0 z-50 justify-center items-center bg-black/60"
        >
            <div class="w-full max-w-5xl bg-white rounded-lg shadow-lg">
                <!-- Header -->
                <div
                    class="flex justify-between items-center px-5 py-2.5 border-b"
                >
                    <h5 class="font-semibold">
                        Preview {{ vehicle.type.name }}
                    </h5>
                    <button
                        @click="closeCarousel"
                        class="font-bold text-gray-500 hover:text-gray-700"
                    >
                        ✕
                    </button>
                </div>

                <!-- Body (Carousel) -->
                <div class="relative bg-gray-100 rounded-lg">
                    <!-- Images -->
                    <div
                        class="overflow-hidden h-[90vh] flex items-center justify-center"
                    >
                        <img
                            v-for="(image, idx) in carouselImages"
                            :key="idx"
                            :src="`/storage/${image.file_path}`"
                            :alt="`Foto ${idx + 1}`"
                            class="object-contain absolute max-w-full max-h-full transition-opacity duration-500"
                            :class="
                                idx === activeIndex
                                    ? 'opacity-100 relative'
                                    : 'opacity-0'
                            "
                        />
                    </div>

                    <!-- Prev Button -->
                    <button
                        v-if="activeIndex > 0"
                        @click="prev"
                        class="flex absolute left-4 top-1/2 justify-center items-center w-10 h-10 text-white rounded-full -translate-y-1/2 bg-black/50"
                    >
                        ‹
                    </button>

                    <!-- Indicators -->
                    <div
                        class="flex absolute bottom-3 left-1/2 gap-2 -translate-x-1/2"
                    >
                        <span
                            v-for="(image, idx) in carouselImages"
                            :key="image.id ?? idx"
                            class="w-2 h-2 rounded-full cursor-pointer"
                            :class="idx === activeIndex ? 'bg-white' : '0'"
                            @click="activeIndex = idx"
                        />
                    </div>

                    <!-- Next Button -->
                    <button
                        v-if="activeIndex < carouselImages.length - 1"
                        @click="next"
                        class="flex absolute right-4 top-1/2 justify-center items-center w-10 h-10 text-white rounded-full -translate-y-1/2 bg-black/50"
                    >
                        ›
                    </button>
                </div>
            </div>
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
import CalendarIcon from "@/Components/icons/CalendarIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import Select from "@/Components/form/Select.vue";
import SearchBar from "@/Components/layout/header/SearchBar.vue";
import { ref, watch, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { useDropzone } from "vue3-dropzone";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [{ label: "Kendaraan" }, { label: "Detail Kendaraan" }];

const props = defineProps({
    vehicle: Object,
    vehicleDocuments: Object,
    vehicleInspections: Object,
    vehicleServices: Object,
    vehicleOdometerHistory: {
        type: Array,
        default: () => [],
    },
    images: Array,
});

console.log(props.vehicleInspections);

const activeTab = ref("documents");

// Filter state for inspections
const filters = ref({
    date_from: "",
    date_to: "",
    inspector: "",
    progress_from: null,
    progress_to: null,
    status: "",
});

// Computed property for unique inspectors
const uniqueInspectors = computed(() => {
    if (!props.vehicleInspections || !Array.isArray(props.vehicleInspections)) {
        return [];
    }

    const inspectorMap = new Map();

    props.vehicleInspections.forEach((inspection) => {
        if (inspection.submitted_by?.id && inspection.submitted_by?.name) {
            if (!inspectorMap.has(inspection.submitted_by.id)) {
                inspectorMap.set(inspection.submitted_by.id, {
                    id: inspection.submitted_by.id,
                    name: inspection.submitted_by.name,
                });
            }
        }
    });

    return Array.from(inspectorMap.values()).sort((a, b) =>
        a.name.localeCompare(b.name)
    );
});

// Computed property for filtered inspections
const filteredInspections = computed(() => {
    if (!props.vehicleInspections || !Array.isArray(props.vehicleInspections)) {
        return [];
    }

    let filtered = [...props.vehicleInspections];

    // Filter by date range
    if (filters.value.date_from || filters.value.date_to) {
        filtered = filtered.filter((inspection) => {
            if (!inspection.submit_date) return false;

            const inspectionDate = new Date(inspection.submit_date);
            inspectionDate.setHours(0, 0, 0, 0);

            if (filters.value.date_from) {
                const fromDate = new Date(filters.value.date_from);
                fromDate.setHours(0, 0, 0, 0);
                if (inspectionDate < fromDate) return false;
            }

            if (filters.value.date_to) {
                const toDate = new Date(filters.value.date_to);
                toDate.setHours(23, 59, 59, 999);
                if (inspectionDate > toDate) return false;
            }

            return true;
        });
    }

    // Filter by inspector
    if (filters.value.inspector) {
        filtered = filtered.filter((inspection) => {
            return (
                inspection.submitted_by?.id ===
                parseInt(filters.value.inspector)
            );
        });
    }

    // Filter by progress/score range
    if (
        filters.value.progress_from !== null &&
        filters.value.progress_from !== "" &&
        filters.value.progress_from !== undefined
    ) {
        const progressFromValue = Number(filters.value.progress_from);
        if (!isNaN(progressFromValue)) {
            filtered = filtered.filter((inspection) => {
                const inspectionProgress = Number(inspection.progress) || 0;
                return inspectionProgress >= progressFromValue;
            });
        }
    }

    if (
        filters.value.progress_to !== null &&
        filters.value.progress_to !== "" &&
        filters.value.progress_to !== undefined
    ) {
        const progressToValue = Number(filters.value.progress_to);
        if (!isNaN(progressToValue)) {
            filtered = filtered.filter((inspection) => {
                const inspectionProgress = Number(inspection.progress) || 0;
                return inspectionProgress <= progressToValue;
            });
        }
    }

    // Filter by status
    if (filters.value.status) {
        filtered = filtered.filter((inspection) => {
            return (
                inspection.status?.toLowerCase() ===
                filters.value.status.toLowerCase()
            );
        });
    }

    // Sort by inspection date (submit_date or created_at) descending: newest first
    filtered.sort((a, b) => {
        const aDateRaw = a.submit_date || a.created_at;
        const bDateRaw = b.submit_date || b.created_at;

        if (!aDateRaw && !bDateRaw) return 0;
        if (!aDateRaw) return 1;
        if (!bDateRaw) return -1;

        const aTime = new Date(aDateRaw).getTime();
        const bTime = new Date(bDateRaw).getTime();

        return bTime - aTime;
    });

    return filtered;
});

// Clear all filters
function clearFilters() {
    filters.value = {
        date_from: "",
        date_to: "",
        inspector: "",
        progress_from: null,
        progress_to: null,
        status: "",
    };
    currentPageInspections.value = 1;
}

// Pagination state for inspections
const currentPageInspections = ref(1);
const perPageInspections = ref(10);

// Computed property for paginated inspections
const paginatedInspections = computed(() => {
    const start = (currentPageInspections.value - 1) * perPageInspections.value;
    const end = start + perPageInspections.value;
    return filteredInspections.value.slice(start, end);
});

// Total pages for inspections
const totalPagesInspections = computed(() => {
    return Math.ceil(filteredInspections.value.length / perPageInspections.value);
});

// Pagination functions for inspections
function goToPageInspections(page) {
    if (page >= 1 && page <= totalPagesInspections.value) {
        currentPageInspections.value = page;
    }
}

function prevPageInspections() {
    if (currentPageInspections.value > 1) {
        currentPageInspections.value--;
    }
}

function nextPageInspections() {
    if (currentPageInspections.value < totalPagesInspections.value) {
        currentPageInspections.value++;
    }
}

// Filter state for services
const serviceFilters = ref({
    date_from: "",
    date_to: "",
    distance_from: null,
    distance_to: null,
    cost_from: null,
    cost_to: null,
});

// Computed property for filtered services
const filteredServices = computed(() => {
    if (!props.vehicleServices || !Array.isArray(props.vehicleServices)) {
        return [];
    }

    let filtered = [...props.vehicleServices];

    // Filter by date range
    if (serviceFilters.value.date_from || serviceFilters.value.date_to) {
        filtered = filtered.filter((service) => {
            if (!service.date) return false;

            const serviceDate = new Date(service.date);
            serviceDate.setHours(0, 0, 0, 0);

            if (serviceFilters.value.date_from) {
                const fromDate = new Date(serviceFilters.value.date_from);
                fromDate.setHours(0, 0, 0, 0);
                if (serviceDate < fromDate) return false;
            }

            if (serviceFilters.value.date_to) {
                const toDate = new Date(serviceFilters.value.date_to);
                toDate.setHours(23, 59, 59, 999);
                if (serviceDate > toDate) return false;
            }

            return true;
        });
    }

    // Filter by distance range
    if (
        serviceFilters.value.distance_from !== null &&
        serviceFilters.value.distance_from !== "" &&
        serviceFilters.value.distance_from !== undefined
    ) {
        const distanceFromValue = Number(serviceFilters.value.distance_from);
        if (!isNaN(distanceFromValue)) {
            filtered = filtered.filter((service) => {
                const serviceDistance = Number(service.distance) || 0;
                return serviceDistance >= distanceFromValue;
            });
        }
    }

    if (
        serviceFilters.value.distance_to !== null &&
        serviceFilters.value.distance_to !== "" &&
        serviceFilters.value.distance_to !== undefined
    ) {
        const distanceToValue = Number(serviceFilters.value.distance_to);
        if (!isNaN(distanceToValue)) {
            filtered = filtered.filter((service) => {
                const serviceDistance = Number(service.distance) || 0;
                return serviceDistance <= distanceToValue;
            });
        }
    }

    // Filter by cost range
    if (
        serviceFilters.value.cost_from !== null &&
        serviceFilters.value.cost_from !== "" &&
        serviceFilters.value.cost_from !== undefined
    ) {
        const costFromValue = Number(serviceFilters.value.cost_from);
        if (!isNaN(costFromValue)) {
            filtered = filtered.filter((service) => {
                const serviceCost = Number(service.cost) || 0;
                return serviceCost >= costFromValue;
            });
        }
    }

    if (
        serviceFilters.value.cost_to !== null &&
        serviceFilters.value.cost_to !== "" &&
        serviceFilters.value.cost_to !== undefined
    ) {
        const costToValue = Number(serviceFilters.value.cost_to);
        if (!isNaN(costToValue)) {
            filtered = filtered.filter((service) => {
                const serviceCost = Number(service.cost) || 0;
                return serviceCost <= costToValue;
            });
        }
    }

    // Sort by date (terbaru ke terlama) - DESC
    filtered.sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        return dateB - dateA; // Descending order (newest first)
    });

    return filtered;
});

// Clear all service filters
function clearServiceFilters() {
    serviceFilters.value = {
        date_from: "",
        date_to: "",
        distance_from: null,
        distance_to: null,
        cost_from: null,
        cost_to: null,
    };
    currentPageServices.value = 1;
}

// Pagination state for services
const currentPageServices = ref(1);
const perPageServices = ref(10);

// Computed property for paginated services
const paginatedServices = computed(() => {
    const start = (currentPageServices.value - 1) * perPageServices.value;
    const end = start + perPageServices.value;
    return filteredServices.value.slice(start, end);
});

// Total pages for services
const totalPagesServices = computed(() => {
    return Math.ceil(filteredServices.value.length / perPageServices.value);
});

// Pagination functions for services
function goToPageServices(page) {
    if (page >= 1 && page <= totalPagesServices.value) {
        currentPageServices.value = page;
    }
}

function prevPageServices() {
    if (currentPageServices.value > 1) {
        currentPageServices.value--;
    }
}

function nextPageServices() {
    if (currentPageServices.value < totalPagesServices.value) {
        currentPageServices.value++;
    }
}

// Filter state for odometer history
const odometerFilters = ref({
    date_from: "",
    date_to: "",
});

// Pagination state for odometer history
const currentPageOdometer = ref(1);
const perPageOdometer = ref(10);

function setActive(tab) {
    activeTab.value = tab;
    // Reset pagination when switching tabs
    currentPageInspections.value = 1;
    currentPageServices.value = 1;
    currentPageOdometer.value = 1;
    // Reset odometer filters when switching away from odometer tab
    if (tab !== 'odometer') {
        odometerFilters.value = {
            date_from: "",
            date_to: "",
        };
    }
}

// Watch filters to reset pagination
watch(() => filters.value, () => {
    currentPageInspections.value = 1;
}, { deep: true });

watch(() => serviceFilters.value, () => {
    currentPageServices.value = 1;
}, { deep: true });

watch(() => odometerFilters.value, () => {
    currentPageOdometer.value = 1;
}, { deep: true });

// Computed property for filtered odometer history
const filteredOdometerHistory = computed(() => {
    if (!props.vehicleOdometerHistory || !Array.isArray(props.vehicleOdometerHistory)) {
        return [];
    }

    let filtered = [...props.vehicleOdometerHistory];

    // Filter by date range
    if (odometerFilters.value.date_from || odometerFilters.value.date_to) {
        filtered = filtered.filter((odometer) => {
            if (!odometer.tanggal) return false;

            const odometerDate = new Date(odometer.tanggal);
            odometerDate.setHours(0, 0, 0, 0);

            if (odometerFilters.value.date_from) {
                const fromDate = new Date(odometerFilters.value.date_from);
                fromDate.setHours(0, 0, 0, 0);
                if (odometerDate < fromDate) return false;
            }

            if (odometerFilters.value.date_to) {
                const toDate = new Date(odometerFilters.value.date_to);
                toDate.setHours(23, 59, 59, 999);
                if (odometerDate > toDate) return false;
            }

            return true;
        });
    }

    return filtered;
});

// Clear all odometer filters
function clearOdometerFilters() {
    odometerFilters.value = {
        date_from: "",
        date_to: "",
    };
    currentPageOdometer.value = 1;
}

// Computed property for paginated odometer history
const paginatedOdometerHistory = computed(() => {
    const start = (currentPageOdometer.value - 1) * perPageOdometer.value;
    const end = start + perPageOdometer.value;
    return filteredOdometerHistory.value.slice(start, end);
});

// Total pages for odometer history
const totalPagesOdometer = computed(() => {
    return Math.ceil(filteredOdometerHistory.value.length / perPageOdometer.value);
});

// Pagination functions for odometer history
function goToPageOdometer(page) {
    if (page >= 1 && page <= totalPagesOdometer.value) {
        currentPageOdometer.value = page;
    }
}

function prevPageOdometer() {
    if (currentPageOdometer.value > 1) {
        currentPageOdometer.value--;
    }
}

function nextPageOdometer() {
    if (currentPageOdometer.value < totalPagesOdometer.value) {
        currentPageOdometer.value++;
    }
}

function formatTime(time) {
    const date = new Date(time);
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function formatRupiah(value) {
    if (!value) return "Rp0,-";
    return "Rp" + Number(value).toLocaleString("id-ID") + ",-";
}

function getStatusBadge(status) {
    const statusMap = {
        submitted: {
            label: "Selesai",
            class: "text-green-600 bg-green-100 border-green-300",
        },
        approved: {
            label: "Selesai",
            class: "text-green-600 bg-green-100 border-green-300",
        },
        draft: {
            label: "Belum Selesai",
            class: "text-yellow-600 bg-yellow-100 border-yellow-300",
        },
        on_progress: {
            label: "Sedang Berjalan",
            class: "text-blue-600 bg-blue-100 border-blue-300",
        },
        rejected: {
            label: "Ditolak",
            class: "text-red-600 bg-red-100 border-red-300",
        },
    };

    const normalizedStatus = status?.toLowerCase() || "";
    return (
        statusMap[normalizedStatus] || {
            label: status || "-",
            class: "text-gray-600 bg-gray-100 border-gray-300",
        }
    );
}
const isModalOpen = ref(false);
const isEditMode = ref(false);
const deletedFiles = ref([]); // Track files to be deleted

const form = useForm({
    id: null,
    vehicle_id: props.vehicle.id,
    name: "",
    renewal_date: "",
    expired_date: "",
    files: [],
});

// Get image preview URLs for newly uploaded files
const imagePreviews = computed(() => {
    if (!form.files || form.files.length === 0) return [];

    return form.files
        .map((file) => {
            if (file instanceof File) {
                // Newly uploaded file
                return URL.createObjectURL(file);
            }
            return null;
        })
        .filter(Boolean);
});

function onDrop(acceptedFiles, fileRejections) {
    form.files = acceptedFiles;

    fileRejections.forEach((reject) => {
        reject.errors.forEach((err) => {
            if (err.code === "file-too-large") {
                flash.error(`File ${reject.file.name} terlalu besar!`);
            } else if (err.code === "file-invalid-type") {
                flash.error(
                    `Tipe file ${reject.file.name} tidak diperbolehkan!`
                );
            } else if (err.code === "too-many-files") {
                flash.error(`Maksimal hanya 5 file yang diperbolehkan!`);
            } else {
                console.warn(
                    "Unhandled dropzone error:",
                    err.code,
                    reject.file
                );
            }
        });
    });
}

function removeFile(index) {
    // Clean up the object URL before removing
    const file = form.files[index];
    if (file instanceof File) {
        const url = URL.createObjectURL(file);
        URL.revokeObjectURL(url);
    }
    form.files.splice(index, 1);
}

// Delete existing file (mark for deletion)
function deleteExistingFile(fileId) {
    deletedFiles.value.push(fileId);
}

// Undo deletion of existing file
function undoDeleteFile(fileId) {
    deletedFiles.value = deletedFiles.value.filter((id) => id !== fileId);
}

// Check if file is marked for deletion
function isFileDeleted(fileId) {
    return deletedFiles.value.includes(fileId);
}

const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    multiple: true,
    accept: "image/jpeg, image/png",
    maxFiles: 5,
    maxSize: 5 * 1024 * 1024, // maksimal 5 MB
});

// Buka modal untuk tambah
function openAddModal() {
    form.reset();
    isEditMode.value = false;
    deletedFiles.value = [];
    isModalOpen.value = true;
}

// Buka modal untuk edit
function openEditModal(document) {
    form.id = document.id;
    form.vehicle_id = props.vehicle.id;
    form.name = document.name;
    form.renewal_date = document.renewal_date;
    form.expired_date = document.expired_date;
    // Don't set files for edit mode - user can optionally upload new files
    form.files = [];
    selectedItem.value = document;
    isEditMode.value = true;
    isModalOpen.value = true;
}

function closeModal() {
    // Clean up object URLs to prevent memory leaks
    if (imagePreviews.value.length > 0) {
        imagePreviews.value.forEach((url) => {
            URL.revokeObjectURL(url);
        });
    }

    isModalOpen.value = false;
    selectedItem.value = null;
    deletedFiles.value = [];
    form.reset();
    form.clearErrors();
}

// Simpan (otomatis create/update)
function saveDocument() {
    if (isEditMode.value) {
        const hasNewFiles = form.files && form.files.length > 0;
        const hasExistingFiles = selectedItem.value?.files && selectedItem.value.files.length > 0;
        const allFilesDeleted = hasExistingFiles && deletedFiles.value.length === selectedItem.value.files.length;

        if (!hasNewFiles && allFilesDeleted) {
            form.setError('files', 'Minimal harus ada satu file dokumen');
            return;
        }
    }
    if (isEditMode.value) {
        // Use POST with _method=PUT for file uploads (Laravel method spoofing)
        form.transform((data) => ({
            ...data,
            _method: "PUT",
        })).post(route("vehicle-documents.update", form.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                // Reload page to show updated data
                router.reload({ only: ["vehicle", "vehicleDocuments"] });
            },
        });
    } else {
        form.post(route("vehicle-documents.store"), {
            onSuccess: () => {
                closeModal();
                router.reload({ only: ["vehicle", "vehicleDocuments"] });
            },
        });
    }
}

// destroy
const selectedItem = ref(null);

const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    selectedItem.value = item;
    isConfirmModalOpen.value = true;
};
const closeConfirmModal = () => {
    selectedItem.value = null;
    isConfirmModalOpen.value = false;
};
const destroyData = () => {
    router.delete(route("vehicle-documents.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        preserveScroll: true,
    });
};

const isOpen = ref(false);
const activeIndex = ref(0);
const carouselImages = ref([]);

function openCarousel(images, idx = 0) {
    carouselImages.value = images;
    activeIndex.value = idx;
    isOpen.value = true;
}

function closeCarousel() {
    isOpen.value = false;
}

function prev() {
    activeIndex.value =
        activeIndex.value === 0
            ? carouselImages.value.length - 1
            : activeIndex.value - 1;
}

function next() {
    activeIndex.value =
        activeIndex.value === carouselImages.value.length - 1
            ? 0
            : activeIndex.value + 1;
}

function viewInspectionDetail(id) {
    router.visit(route("inspections.show", id));
}

function viewServiceDetail(id) {
    router.visit(route("vehicle-services.show", id));
}

// Get vehicle photo from service
function getVehiclePhoto(service) {
    // Use images from props (VehicleFilePath) or vehicle.files
    if (props.images && props.images.length > 0) {
        return props.images[0]?.file_path;
    }
    if (props.vehicle?.files && props.vehicle.files.length > 0) {
        const photo =
            props.vehicle.files.find((file) => file.type === "photo") ||
            props.vehicle.files[0];
        return photo?.file_path;
    }
    return null;
}

// Get vehicle images for carousel
function getVehicleImages(service) {
    // Use images from props (VehicleFilePath) or vehicle.files
    if (props.images && props.images.length > 0) {
        return props.images.map((file) => ({
            id: file.id,
            file_path: file.file_path,
        }));
    }
    if (props.vehicle?.files && props.vehicle.files.length > 0) {
        return props.vehicle.files.map((file) => ({
            id: file.id,
            file_path: file.file_path,
        }));
    }
    return [];
}
</script>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
