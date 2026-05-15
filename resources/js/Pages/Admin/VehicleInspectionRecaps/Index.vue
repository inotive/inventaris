<template>
    <Head title="Rekap Inspeksi Kendaraan" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <!-- Tabs -->
            <div
                class="px-6 pt-4 border-b border-gray-200 dark:border-gray-700"
            >
                <div class="flex gap-4 items-center">
                    <button
                        v-for="t in tabs"
                        :key="t.value"
                        :class="[
                            'px-4 py-2 text-sm font-medium rounded-t transition-colors',
                            activeTab === t.value
                                ? 'text-blue-600 border-b-2 border-blue-600 dark:text-blue-400'
                                : 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200',
                        ]"
                        @click="switchTab(t.value)"
                    >
                        {{ t.label }}
                    </button>
                </div>
            </div>

            <div
                class="flex flex-col gap-2 px-8 py-1 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    {{
                        activeTab === "inspection"
                            ? "Rekap Inspeksi"
                            : activeTab === "kilometer"
                            ? "Rekap Kilo Meter"
                            : "Rekap Servis"
                    }}
                    <span v-if="activeTab !== 'service'">{{
                        months[props.month - 1]
                    }}</span>
                    {{ props.year }}
                </div>

                <div class="flex gap-3 items-center">
                    <div class="relative py-2">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari inspeksi"
                            class="dark:bg-gray-800 h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-800 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[200px]"
                        />
                    </div>

                    <button
                        @click="openAddModal"
                        type="button"
                        class="h-10 inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                    >
                        <SettingIcon />
                        Filter
                    </button>
                    <Modal
                        :show="isModalOpen"
                        title="
                            Filter
                        "
                        confirmText="Terapkan"
                        maxWidth="lg"
                        @close="closeModal"
                        @confirm="fetchChecklist"
                    >
                        <div class="space-y-3">
                            <div class="flex gap-2 justify-between">
                                <div class="space-y-1 text-sm w-[50%]">
                                    <label
                                        for="vehicle_id"
                                        class="text-gray-900 dark:text-white"
                                        >Pilih Bulan</label
                                    >
                                    <select
                                        v-model="month"
                                        class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                    >
                                        <option
                                            v-for="(m, index) in months"
                                            :key="index"
                                            :value="index + 1"
                                        >
                                            {{ m }}
                                        </option>
                                    </select>
                                </div>
                                <div class="space-y-1 text-sm w-[50%]">
                                    <label
                                        for="vehicle_id"
                                        class="text-gray-900 dark:text-white"
                                        >Pilih Tahun<span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <select
                                        v-model="year"
                                        class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                    >
                                        <option
                                            v-for="y in years"
                                            :key="y"
                                            :value="y"
                                        >
                                            {{ y }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </Modal>
                </div>
            </div>

            <!-- Rekap Inspeksi Tab -->
            <div
                v-if="activeTab === 'inspection'"
                class="overflow-auto"
                data-simplebar
            >
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="sticky left-0 z-20 p-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                                style="min-width: 75px"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        No.
                                    </p>
                                </div>
                            </th>
                            <th
                                @click="changeSort('type.name')"
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800 sticky left-[60px] z-20"
                                style="min-width: 250px"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3 cursor-pointer"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Kendaraan
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
                                v-for="d in dates"
                                :key="d"
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex flex-col justify-center items-center w-8"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        {{ getDayName(d) }}
                                    </p>
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        {{ new Date(d).getDate() }}
                                    </p>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="vehicles.data && vehicles.data.length > 0"
                            v-for="(v, index) in vehicles.data"
                            :key="v.id"
                            class="hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <td
                                class="sticky left-0 z-10 p-2.5 bg-white border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <div
                                    class="flex justify-center items-center whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{
                                            (vehicles.current_page - 1) *
                                                vehicles.per_page +
                                            index +
                                            1
                                        }}.
                                    </p>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600 sticky left-[60px] z-10 bg-white dark:bg-gray-900"
                            >
                                <Link
                                    :href="route('vehicles.show', v.id)"
                                    class="flex gap-3 items-center px-3 w-56 whitespace-nowrap"
                                >
                                    <img
                                        class="object-cover w-10 h-10 rounded-full"
                                        :src="
                                            v.photo
                                                ? `/storage/${v.photo}`
                                                : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                      v.type?.name || v.license_code || 'Kendaraan'
                                                  )}&background=3b82f6&color=fff`
                                        "
                                        alt="Vehicle photo"
                                        loading="lazy"
                                    />
                                    <div class="flex flex-col leading-tight">
                                        <p
                                            class="font-medium text-gray-800 dark:text-white/90"
                                        >
                                            {{ v.license_code }}
                                        </p>
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            {{ v.type?.name || 'Kendaraan' }}
                                        </span>
                                    </div>
                                </Link>
                            </td>
                            <td
                                v-for="d in dates"
                                :key="d"
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center items-center w-8 whitespace-nowrap"
                                >
                                    <template
                                        v-if="
                                            getInspectionCountOnDate(v, d) > 0
                                        "
                                    >
                                        <Link
                                            v-if="
                                                getInspectionCountOnDate(
                                                    v,
                                                    d
                                                ) === 1
                                            "
                                            :href="
                                                route(
                                                    'inspections.show',
                                                    getInspectionId(v, d)
                                                )
                                            "
                                        >
                                            <CheckIcon
                                                class="mx-auto w-6 h-6 text-green-500"
                                            />
                                        </Link>
                                        <button
                                            v-else
                                            @click="openInspectionModal(v, d)"
                                            class="inline-flex justify-center items-center w-6 h-6 text-xs font-bold text-white bg-blue-500 rounded-full transition-colors cursor-pointer hover:bg-blue-600"
                                            :title="`${getInspectionCountOnDate(
                                                v,
                                                d
                                            )} inspeksi pada tanggal ini. Klik untuk melihat detail`"
                                        >
                                            {{ getInspectionCountOnDate(v, d) }}
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>

                        <tr v-else>
                            <td
                                colspan="10"
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Rekap Kilo Meter Tab -->
            <div
                v-else-if="activeTab === 'kilometer'"
                class="overflow-auto"
                data-simplebar
            >
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="sticky left-0 z-20 p-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                                style="min-width: 75px"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        No.
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800 sticky left-[60px] z-20"
                                style="min-width: 250px"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Kendaraan
                                    </p>
                                </div>
                            </th>
                            <th
                                v-for="d in dates"
                                :key="d"
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex flex-col justify-center items-center w-16"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        {{ getDayName(d) }}
                                    </p>
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        {{ new Date(d).getDate() }}
                                    </p>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="vehicles.data && vehicles.data.length > 0"
                            v-for="(v, index) in vehicles.data"
                            :key="v.id"
                            class="hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <td
                                class="sticky left-0 z-10 p-2.5 bg-white border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <div
                                    class="flex justify-center items-center whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{
                                            (vehicles.current_page - 1) *
                                                vehicles.per_page +
                                            index +
                                            1
                                        }}.
                                    </p>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600 sticky left-[60px] z-10 bg-white dark:bg-gray-900"
                            >
                                <Link
                                    :href="route('vehicles.show', v.id)"
                                    class="flex gap-3 items-center px-3 w-56 whitespace-nowrap"
                                >
                                    <img
                                        class="object-cover w-10 h-10 rounded-full"
                                        :src="
                                            v.photo
                                                ? `/storage/${v.photo}`
                                                : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                      v.type.name
                                                  )}&background=3b82f6&color=fff`
                                        "
                                        alt="Vehicle photo"
                                        loading="lazy"
                                    />
                                    <div class="flex flex-col leading-tight">
                                        <p
                                            class="font-medium text-gray-800 dark:text-white/90"
                                        >
                                            {{ v.license_code }}
                                        </p>
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            {{ v.type.name }}
                                        </span>
                                    </div>
                                </Link>
                            </td>
                            <td
                                v-for="d in dates"
                                :key="d"
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                                :class="{
                                    'bg-blue-50 dark:bg-blue-900/20':
                                        isKmUpdated(v, d),
                                }"
                            >
                                <div
                                    class="flex justify-center items-center w-16 whitespace-nowrap"
                                >
                                    <template v-if="getDailyKm(v, d)">
                                        <button
                                            @click="openKilometerModal(v, d)"
                                            class="text-sm font-medium text-blue-600 cursor-pointer hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-300"
                                            :title="`Klik untuk melihat detail kilometer pada tanggal ini`"
                                        >
                                            {{ formatNumber(getDailyKm(v, d)) }}
                                            km
                                        </button>
                                    </template>
                                    <template v-else>
                                        <span
                                            class="text-gray-400 dark:text-gray-600"
                                            >-</span
                                        >
                                    </template>
                                </div>
                            </td>
                        </tr>

                        <tr v-else>
                            <td
                                colspan="10"
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Rekap Servis Tab -->
            <div
                v-else-if="activeTab === 'service'"
                class="overflow-auto"
                data-simplebar
            >
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="p-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                                style="min-width: 75px"
                            >
                                <div
                                    class="font-medium text-center text-gray-500 dark:text-gray-400"
                                >
                                    No.
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                                style="min-width: 250px"
                            >
                                <div
                                    class="font-medium text-left text-gray-500 dark:text-gray-400"
                                >
                                    Kendaraan
                                </div>
                            </th>
                            <th
                                class="p-3 bg-blue-50 border border-gray-200 dark:border-gray-600 dark:bg-blue-900"
                            >
                                <div
                                    class="font-medium text-center text-blue-700 dark:text-blue-300"
                                >
                                    Q1<br /><span class="text-xs font-normal"
                                        >(Jan-Mar)</span
                                    >
                                </div>
                            </th>
                            <th
                                class="p-3 bg-green-50 border border-gray-200 dark:border-gray-600 dark:bg-green-900"
                            >
                                <div
                                    class="font-medium text-center text-green-700 dark:text-green-300"
                                >
                                    Q2<br /><span class="text-xs font-normal"
                                        >(Apr-Jun)</span
                                    >
                                </div>
                            </th>
                            <th
                                class="p-3 bg-amber-50 border border-gray-200 dark:border-gray-600 dark:bg-amber-900"
                            >
                                <div
                                    class="font-medium text-center text-amber-700 dark:text-amber-300"
                                >
                                    Q3<br /><span class="text-xs font-normal"
                                        >(Jul-Sep)</span
                                    >
                                </div>
                            </th>
                            <th
                                class="p-3 bg-rose-50 border border-gray-200 dark:border-gray-600 dark:bg-rose-900"
                            >
                                <div
                                    class="font-medium text-center text-rose-700 dark:text-rose-300"
                                >
                                    Q4<br /><span class="text-xs font-normal"
                                        >(Oct-Dec)</span
                                    >
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="font-medium text-center text-gray-500 dark:text-gray-400"
                                >
                                    Total
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="vehicles.data && vehicles.data.length > 0"
                            v-for="(v, index) in vehicles.data"
                            :key="v.id"
                            class="border-b border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <td
                                class="p-3 text-center text-gray-500 dark:text-gray-400"
                            >
                                {{
                                    (vehicles.current_page - 1) *
                                        vehicles.per_page +
                                    index +
                                    1
                                }}.
                            </td>
                            <td class="p-3">
                                <Link
                                    :href="route('vehicles.show', v.id)"
                                    class="flex gap-3 items-center"
                                >
                                    <img
                                        class="object-cover w-10 h-10 rounded-full"
                                        :src="
                                            v.photo
                                                ? `/storage/${v.photo}`
                                                : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                      v.type.name
                                                  )}&background=3b82f6&color=fff`
                                        "
                                        alt="Vehicle photo"
                                        loading="lazy"
                                    />
                                    <div class="flex flex-col">
                                        <p
                                            class="font-medium text-gray-800 dark:text-white"
                                        >
                                            {{ v.license_code }}
                                        </p>
                                        <span
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ v.type.name }}
                                        </span>
                                    </div>
                                </Link>
                            </td>
                            <td
                                class="p-3 text-center bg-blue-50/30 dark:bg-blue-900/10"
                            >
                                <span
                                    class="text-lg font-semibold text-blue-700 dark:text-blue-400"
                                >
                                    {{ getQuarterlyServiceCount(v, 1) }}
                                </span>
                            </td>
                            <td
                                class="p-3 text-center bg-green-50/30 dark:bg-green-900/10"
                            >
                                <span
                                    class="text-lg font-semibold text-green-700 dark:text-green-400"
                                >
                                    {{ getQuarterlyServiceCount(v, 2) }}
                                </span>
                            </td>
                            <td
                                class="p-3 text-center bg-amber-50/30 dark:bg-amber-900/10"
                            >
                                <span
                                    class="text-lg font-semibold text-amber-700 dark:text-amber-400"
                                >
                                    {{ getQuarterlyServiceCount(v, 3) }}
                                </span>
                            </td>
                            <td
                                class="p-3 text-center bg-rose-50/30 dark:bg-rose-900/10"
                            >
                                <span
                                    class="text-lg font-semibold text-rose-700 dark:text-rose-400"
                                >
                                    {{ getQuarterlyServiceCount(v, 4) }}
                                </span>
                            </td>
                            <td
                                class="p-3 text-center bg-gray-50 dark:bg-gray-800"
                            >
                                <span
                                    class="text-lg font-bold text-gray-900 dark:text-white"
                                >
                                    {{ getTotalServiceCount(v) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-else>
                            <td
                                colspan="7"
                                class="py-6 text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex gap-10 justify-between px-5 pt-4 mt-4 w-full border-t border-gray-200 dark:border-gray-700"
            >
                <!-- <div class="flex items-center gap-2 px-5 text-gray-500 w-[5%]">
                    <select
                        v-model="perPage"
                        @change="fetchChecklist"
                        class="text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                    >
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div> -->

                <Pagination
                    v-if="vehicles.data && vehicles.data.length > 0"
                    :pagination="vehicles"
                    @page-changed="changePage"
                    @per-page-changed="perPageChanged"
                    class="w-[100%]"
                />
            </div>
        </div>
    </div>

    <!-- Modal List Inspeksi -->
    <Modal
        :show="showInspectionModal"
        :close-text="'Tutup'"
        @close="closeInspectionModal"
        max-width="5xl"
    >
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3
                        class="text-xl font-semibold text-gray-900 dark:text-white"
                    >
                        Daftar Inspeksi - {{ modalData.vehicleName }}
                    </h3>
                    <div class="flex gap-3 items-center mt-2">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Tanggal:
                            {{
                                modalData.date
                                    ? formatDate(modalData.date)
                                    : "-"
                            }}
                        </p>
                        <span
                            class="px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300"
                        >
                            {{ modalData.inspections.length }} Inspeksi
                        </span>
                    </div>
                </div>

            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                            >
                                No. Inspeksi
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                            >
                                Checklist
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                            >
                                Tanggal Inspeksi
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                            >
                                Status
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                            >
                                Lokasi
                            </th>
                            <th
                                class="px-4 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase"
                            >
                                % Kondisi
                            </th>
                        </tr>
                    </thead>
                    <tbody
                        class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700"
                    >
                        <template
                            v-if="
                                modalData.inspections &&
                                modalData.inspections.length > 0
                            "
                        >
                            <tr
                                v-for="inspection in modalData.inspections"
                                :key="inspection.id"
                                class="transition-colors cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                                @click="goToInspection(inspection.id)"
                            >
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white"
                                    >
                                        {{
                                            inspection.inspection_number || "-"
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{
                                                inspection.checklist?.name ||
                                                "-"
                                            }}
                                        </span>
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-if="
                                                    inspection.checklist
                                                        ?.category?.name
                                                "
                                                class="px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded dark:bg-blue-900 dark:text-blue-300"
                                            >
                                                {{
                                                    inspection.checklist
                                                        .category.name
                                                }}
                                            </span>
                                            <span
                                                v-if="
                                                    inspection.checklist?.type
                                                "
                                                class="px-2 py-0.5 text-xs font-medium rounded"
                                                :class="
                                                    inspection.checklist
                                                        .type === 'multiple'
                                                        ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                                        : 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300'
                                                "
                                            >
                                                {{
                                                    inspection.checklist
                                                        .type === "multiple"
                                                        ? "Berkelompok"
                                                        : "Perorangan"
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    {{
                                        inspection.submit_date
                                            ? formatDateTime(
                                                  inspection.submit_date
                                              )
                                            : "-"
                                    }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full"
                                        :class="
                                            getStatusClass(inspection.status)
                                        "
                                    >
                                        {{ getStatusLabel(inspection.status) }}
                                    </span>
                                </td>
                                <td
                                    class="px-4 py-3 text-sm text-gray-900 dark:text-white"
                                >
                                    {{
                                        inspection.location?.name ||
                                        inspection.location ||
                                        "-"
                                    }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div
                                        v-if="
                                            inspection.condition_percentage !==
                                                null &&
                                            inspection.condition_percentage !==
                                                undefined
                                        "
                                        class="flex gap-2 justify-center items-center"
                                    >
                                        <div
                                            class="w-16 h-2 bg-gray-200 rounded-full dark:bg-gray-700"
                                        >
                                            <div
                                                :class="
                                                    getConditionBgColor(
                                                        inspection.condition_percentage
                                                    )
                                                "
                                                class="h-2 rounded-full transition-all"
                                                :style="{
                                                    width:
                                                        inspection.condition_percentage +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                        <span
                                            :class="
                                                getConditionColor(
                                                    inspection.condition_percentage
                                                )
                                            "
                                            class="text-sm font-semibold"
                                        >
                                            {{
                                                inspection.condition_percentage
                                            }}%
                                        </span>
                                    </div>
                                    <span
                                        v-else
                                        class="text-gray-400 dark:text-gray-500"
                                        >0%</span
                                    >
                                </td>
                            </tr>
                        </template>
                        <tr v-else>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <div
                                    class="flex flex-col justify-center items-center text-gray-500 dark:text-gray-400"
                                >
                                    <svg
                                        class="mb-2 w-12 h-12"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                    <p class="text-sm font-medium">
                                        Tidak ada data inspeksi
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-6">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Klik pada baris untuk melihat detail inspeksi
                </p>

            </div>
        </div>
    </Modal>

    <!-- Modal Detail Kilometer -->
    <Modal
        :show="showKilometerModal"
        @close="closeKilometerModal"
        max-width="2xl"
    >
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Detail Kilometer - {{ kilometerModalData.vehicleName }}
                </h3>
                <button
                    @click="closeKilometerModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                >
                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                Tanggal:
                {{
                    kilometerModalData.date
                        ? formatDate(kilometerModalData.date)
                        : "-"
                }}
            </p>

            <div class="space-y-4">
                <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-800">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Kilometer Terakhir
                            </p>
                            <p
                                class="text-2xl font-bold text-gray-900 dark:text-white"
                            >
                                {{ formatNumber(kilometerModalData.lastKm) }} km
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Kilometer Saat Ini
                            </p>
                            <p
                                class="text-2xl font-bold text-blue-600 dark:text-blue-400"
                            >
                                {{ formatNumber(kilometerModalData.currentKm) }}
                                km
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="kilometerModalData.odometerData"
                    class="p-4 rounded-lg border border-gray-200 dark:border-gray-700"
                >
                    <h4
                        class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300"
                    >
                        Informasi Odometer
                    </h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span
                                class="text-sm text-gray-600 dark:text-gray-400"
                                >Tanggal Update:</span
                            >
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{
                                    kilometerModalData.odometerData.tanggal
                                        ? formatDate(
                                              kilometerModalData.odometerData
                                                  .tanggal
                                          )
                                        : "-"
                                }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span
                                class="text-sm text-gray-600 dark:text-gray-400"
                                >Dibuat oleh:</span
                            >
                            <span
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                {{
                                    kilometerModalData.odometerData.creator
                                        ?.name || "-"
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import SearchIcon from "@/Components/icons/SearchIcon.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import CheckIcon from "@/Components/icons/CheckIcon.vue";
import SettingIcon from "@/Components/icons/SettingIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

const breadcrumbs = [{ label: "Kendaraan" }, { label: "Riwayat Inspeksi" }];

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    vehicles: Object,
    dates: Array,
    month: Number,
    year: Number,
    search: String,
    sortBy: String,
    sortDirection: String,
    perPage: Number,
    tab: String,
});

// Tabs
const tabs = [
    { value: "inspection", label: "Rekap Inspeksi" },
    { value: "kilometer", label: "Rekap Kilo Meter" },
    { value: "service", label: "Rekap Servis" },
];

const activeTab = ref(props.tab || "inspection");

// Modal state
const showInspectionModal = ref(false);
const modalData = ref({
    vehicleName: "",
    date: "",
    inspections: [],
});

const showKilometerModal = ref(false);
const kilometerModalData = ref({
    vehicleName: "",
    date: "",
    currentKm: 0,
    lastKm: 0,
    odometerData: null,
});

function switchTab(tab) {
    activeTab.value = tab;
    router.get(
        route("vehicle-inspection-recaps.index"),
        {
            tab: tab,
            search: search.value,
            perPage: perPage.value,
            month: month.value,
            year: year.value,
        },
        {
            preserveScroll: false,
            preserveState: false, // Force reload data saat switch tab
            replace: true,
            onSuccess: () => {
                // Debug: cek data yang diterima
                console.log("Active tab:", activeTab.value);
                console.log("Vehicles data:", props.vehicles.data);
                if (props.vehicles.data && props.vehicles.data.length > 0) {
                    const firstVehicle = props.vehicles.data[0];
                    console.log(
                        "First vehicle inspections:",
                        firstVehicle.inspections
                    );
                    console.log(
                        "First vehicle odometers:",
                        firstVehicle.odometers
                    );
                    console.log(
                        "First vehicle daily_km:",
                        firstVehicle.daily_km
                    );
                }
            },
        }
    );
}

function getDayName(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("id-ID", { weekday: "short" }).format(date);
}

function getAllInspections(vehicle) {
    // Use direct inspections relation (polymorphic)
    return vehicle.inspections || [];
}

function hasInspectionOnDate(vehicle, date) {
    return getAllInspections(vehicle).some((i) => {
        const inspectionDate = i.submit_date || i.created_at;
        return inspectionDate && inspectionDate.substring(0, 10) === date;
    });
}

function getInspectionCountOnDate(vehicle, date) {
    const inspections = getAllInspections(vehicle).filter((i) => {
        const inspectionDate = i.submit_date || i.created_at;
        return inspectionDate && inspectionDate.substring(0, 10) === date;
    });
    return inspections.length;
}

function getInspectionId(vehicle, date) {
    const inspection = getAllInspections(vehicle).find((i) => {
        const inspectionDate = i.submit_date || i.created_at;
        return inspectionDate && inspectionDate.substring(0, 10) === date;
    });
    return inspection ? inspection.id : null;
}

function getInspectionIdsOnDate(vehicle, date) {
    const inspections = getAllInspections(vehicle).filter((i) => {
        const inspectionDate = i.submit_date || i.created_at;
        return inspectionDate && inspectionDate.substring(0, 10) === date;
    });
    return inspections.map((i) => i.id);
}

function getKilometerOnDate(vehicle, date) {
    // Check odometer histories first (prioritas utama)
    if (vehicle.odometers && vehicle.odometers.length > 0) {
        // Filter odometers by date
        const odometerOnDate = vehicle.odometers.filter((o) => {
            const odometerDate = o.tanggal ? o.tanggal.substring(0, 10) : null;
            return odometerDate === date;
        });

        // Jika ada multiple odometer di tanggal yang sama, ambil yang terbaru (id terbesar)
        if (odometerOnDate.length > 0) {
            const latestOdometer = odometerOnDate.reduce((latest, current) => {
                return current.id > latest.id ? current : latest;
            });
            return latestOdometer.current_km;
        }
    }

    // Fallback: cari di semua odometer untuk tanggal <= date, ambil yang terdekat
    if (vehicle.odometers && vehicle.odometers.length > 0) {
        const odometerBeforeDate = vehicle.odometers.filter((o) => {
            const odometerDate = o.tanggal ? o.tanggal.substring(0, 10) : null;
            return odometerDate && odometerDate <= date;
        });

        if (odometerBeforeDate.length > 0) {
            // Ambil yang paling baru (tanggal terdekat dengan date)
            const latestOdometer = odometerBeforeDate.reduce(
                (latest, current) => {
                    const latestDate = latest.tanggal
                        ? latest.tanggal.substring(0, 10)
                        : "";
                    const currentDate = current.tanggal
                        ? current.tanggal.substring(0, 10)
                        : "";
                    // Jika tanggal sama, pilih yang ID lebih besar
                    if (currentDate === latestDate) {
                        return current.id > latest.id ? current : latest;
                    }
                    return currentDate > latestDate ? current : latest;
                }
            );
            return latestOdometer.current_km;
        }
    }

    // Fallback terakhir: inspection kilometer
    const inspection = getAllInspections(vehicle).find((i) => {
        const inspectionDate = i.submit_date || i.created_at;
        return inspectionDate && inspectionDate.substring(0, 10) === date;
    });
    return inspection ? inspection.kilometer : null;
}

// Prefer nilai yang sudah diprecompute dari controller
function getDailyKm(vehicle, date) {
    if (
        vehicle &&
        vehicle.daily_km &&
        Object.prototype.hasOwnProperty.call(vehicle.daily_km, date)
    ) {
        const v = vehicle.daily_km[date];
        if (v !== null && v !== undefined) return v;
    }
    // Fallback: coba cari di odometer
    return getKilometerOnDate(vehicle, date);
}

function isKmUpdated(vehicle, date) {
    if (
        vehicle &&
        vehicle.daily_km_updated &&
        Object.prototype.hasOwnProperty.call(vehicle.daily_km_updated, date)
    ) {
        return vehicle.daily_km_updated[date] === true;
    }
    return false;
}

function formatNumber(num) {
    if (!num) return "0";
    return new Intl.NumberFormat("id-ID").format(num);
}

// Service count functions for Rekap Servis tab
function getQuarterlyServiceCount(vehicle, quarter) {
    if (!vehicle.services || !Array.isArray(vehicle.services)) return 0;

    // Define month ranges for each quarter
    const quarterMonths = {
        1: [1, 2, 3], // Q1: Jan-Mar
        2: [4, 5, 6], // Q2: Apr-Jun
        3: [7, 8, 9], // Q3: Jul-Sep
        4: [10, 11, 12], // Q4: Oct-Dec
    };

    const months = quarterMonths[quarter];
    if (!months) return 0;

    return vehicle.services.filter((service) => {
        if (!service.date) return false;
        const serviceDate = new Date(service.date);
        const serviceMonth = serviceDate.getMonth() + 1; // getMonth() returns 0-11
        const serviceYear = serviceDate.getFullYear();

        // Check if service is in the selected year and quarter
        return serviceYear === props.year && months.includes(serviceMonth);
    }).length;
}

function getTotalServiceCount(vehicle) {
    if (!vehicle.services || !Array.isArray(vehicle.services)) return 0;

    // Count all services in the selected year
    return vehicle.services.filter((service) => {
        if (!service.date) return false;
        const serviceDate = new Date(service.date);
        return serviceDate.getFullYear() === props.year;
    }).length;
}

function fetchChecklist({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
    monthFilter = month.value,
    yearFilter = year.value,
} = {}) {
    router.get(
        route("vehicle-inspection-recaps.index"),
        {
            search: search.value,
            perPage: perPage.value,
            sortBy,
            sortDirection,
            month: monthFilter,
            year: yearFilter,
        },
        {
            onSuccess: () => {
                closeModal();
            },
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
}

const search = ref(props.search || "");
const perPage = ref(props.perPage || 10);
const month = ref(props.month || new Date().getMonth() + 1);
const year = ref(props.year || new Date().getFullYear());

const currentYear = new Date().getFullYear();
const years = Array.from(
    { length: currentYear - 2020 + 1 },
    (_, i) => 2020 + i
);

const months = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
];

let timeout = null;
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        fetchChecklist();
    }, 400);
});

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchChecklist({ sortBy: column, sortDirection: direction });
}

const isModalOpen = ref(false);

// Buka modal untuk tambah
function openAddModal() {
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
}

// Modal functions
function openInspectionModal(vehicle, date) {
    const inspections = getAllInspections(vehicle).filter((i) => {
        const inspectionDate = i.submit_date || i.created_at;
        return inspectionDate && inspectionDate.substring(0, 10) === date;
    });

    modalData.value = {
        vehicleName: vehicle.license_code || vehicle.type?.name || "Kendaraan",
        date: date,
        inspections: inspections,
    };

    showInspectionModal.value = true;
}

function closeInspectionModal() {
    showInspectionModal.value = false;
    modalData.value = {
        vehicleName: "",
        date: "",
        inspections: [],
    };
}

function goToInspection(inspectionId) {
    router.get(route("inspections.show", inspectionId));
}

function formatDate(dateString) {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("id-ID", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(date);
}

function formatDateTime(dateString) {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("id-ID", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(date);
}

function getStatusClass(status) {
    if (!status) return "bg-gray-100 text-gray-700";
    const s = status.toLowerCase();

    const map = {
        draft: "bg-amber-100 text-amber-700",
        submitted: "bg-green-100 text-green-700",
        approved: "bg-green-100 text-green-700",
        rejected: "bg-red-100 text-red-700",
    };
    return map[s] || "bg-gray-100 text-gray-700";
}

function getStatusLabel(status) {
    if (!status) return "-";
    const s = status.toLowerCase();

    const map = {
        draft: "Belum Selesai",
        submitted: "Selesai",
        approved: "Selesai",
        rejected: "Ditolak",
    };
    return map[s] || status;
}

function getConditionColor(percentage) {
    if (percentage >= 80) return "text-green-600";
    if (percentage >= 60) return "text-yellow-600";
    return "text-red-600";
}

function getConditionBgColor(percentage) {
    if (percentage >= 80) return "bg-green-500";
    if (percentage >= 60) return "bg-yellow-500";
    return "bg-red-500";
}

// Kilometer modal functions
function openKilometerModal(vehicle, date) {
    // Find odometer data for this date
    let odometerData = null;
    let currentKm = 0;
    let lastKm = 0;

    if (vehicle.odometers && vehicle.odometers.length > 0) {
        // Find odometer for this exact date
        odometerData = vehicle.odometers.find((o) => {
            const oDate = o.tanggal ? o.tanggal.substring(0, 10) : null;
            return oDate === date;
        });

        if (odometerData) {
            currentKm = odometerData.current_km || 0;
            lastKm = odometerData.last_km || 0;
        } else {
            // If no exact match, use the daily_km value (which is the last known km up to this date)
            currentKm = getDailyKm(vehicle, date) || 0;

            // Find the last km before this date
            const odometersBefore = vehicle.odometers
                .filter((o) => {
                    const oDate = o.tanggal ? o.tanggal.substring(0, 10) : null;
                    return oDate && oDate < date;
                })
                .sort((a, b) => {
                    const dateA = a.tanggal ? a.tanggal.substring(0, 10) : "";
                    const dateB = b.tanggal ? b.tanggal.substring(0, 10) : "";
                    if (dateA === dateB) return b.id - a.id;
                    return dateB.localeCompare(dateA);
                });

            if (odometersBefore.length > 0) {
                lastKm = odometersBefore[0].current_km || 0;
            }
        }
    }

    // If still no data, try to get from inspection
    if (!odometerData && currentKm === 0) {
        const inspection = getAllInspections(vehicle).find((i) => {
            const inspectionDate = i.submit_date || i.created_at;
            return inspectionDate && inspectionDate.substring(0, 10) === date;
        });
        if (inspection && inspection.kilometer) {
            currentKm = inspection.kilometer;
        }
    }

    kilometerModalData.value = {
        vehicleName: vehicle.license_code || vehicle.type?.name || "Kendaraan",
        date: date,
        currentKm: currentKm,
        lastKm: lastKm,
        odometerData: odometerData,
    };

    showKilometerModal.value = true;
}

function closeKilometerModal() {
    showKilometerModal.value = false;
    kilometerModalData.value = {
        vehicleName: "",
        date: "",
        currentKm: 0,
        lastKm: 0,
        odometerData: null,
    };
}
</script>
