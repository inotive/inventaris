<template>
    <Head title="Inspeksi Kendaraan" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
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
                    Daftar Inspeksi Kendaraan
                </div>
                <div class="flex gap-3 items-center">

                    <select
                        v-model="groupByLocal"
                        class="px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                    >
                        <option value="">Kelompokan Berdasarkan</option>
                        <option value="vehicle">Group: Kendaraan</option>
                        <option value="inspection">Group: Inspeksi</option>
                        <option value="checklist">Group: Checklist</option>
                        <option value="created_at">Group: Tanggal Masuk</option>
                    </select>
                </div>
            </div>

            <div class="overflow-auto overflow-x-auto" data-simplebar>
                <table class="min-w-full text-xs table-fixed">
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
                                <button
                                    @click="
                                        changeSort(
                                            'checklist.vehicle.license_code'
                                        )
                                    "
                                    class="px-3 w-full font-medium text-center text-gray-600 dark:text-gray-300"
                                >
                                    Kendaraan
                                    <SortIcon
                                        :active="
                                            sortBy ===
                                            'checklist.vehicle.license_code'
                                        "
                                        :direction="sortDirection"
                                    />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button
                                    @click="changeSort('inspection_number')"
                                    class="px-3 w-full font-medium text-center text-gray-600 dark:text-gray-300"
                                >
                                    No. Inspeksi
                                    <SortIcon
                                        :active="sortBy === 'inspection_number'"
                                        :direction="sortDirection"
                                    />
                                </button>
                            </th>

                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button
                                    @click="changeSort('checklist_name')"
                                    class="px-3 w-full font-medium text-center text-gray-600 dark:text-gray-300"
                                >
                                    Checklist
                                    <SortIcon
                                        :active="sortBy === 'checklist_name'"
                                        :direction="sortDirection"
                                    />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                                style="min-width: 120px"
                            >
                                <div
                                    class="px-3 font-medium text-center text-gray-600 dark:text-gray-300"
                                >
                                    <div
                                        class="flex justify-center items-center h-full"
                                    >
                                        <span>Tanggal Inspeksi</span>
                                    </div>
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-center text-gray-600 dark:text-gray-300"
                                >
                                    Status
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-center text-gray-600 dark:text-gray-300"
                                >
                                    Lokasi
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                                style="min-width: 180px"
                            >
                                <div
                                    class="px-3 font-medium text-center text-gray-600 dark:text-gray-300"
                                >
                                    % Kondisi
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-center text-gray-600 dark:text-gray-300"
                                >
                                    Update Kilometer
                                </div>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <button
                                    @click="changeSort('submitted_by')"
                                    class="px-3 w-full font-medium text-center text-gray-600 dark:text-gray-300"
                                >
                                    Waktu Dibuat
                                    <SortIcon
                                        :active="sortBy === 'submitted_by'"
                                        :direction="sortDirection"
                                    />
                                </button>
                            </th>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="px-3 font-medium text-gray-600 dark:text-gray-300"
                                >
                                    Hasil Akhir
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th
                                class="p-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                            <th
                                class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <input
                                    v-model="filtersLocal.vehicle"
                                    type="text"
                                    placeholder="Nama kendaraan"
                                    class="px-2 py-2 w-full text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </th>
                            <th
                                class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <input
                                    v-model="filtersLocal.inspection_number"
                                    type="text"
                                    placeholder="No. Inspeksi"
                                    class="px-2 py-2 w-full text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </th>
                            <th
                                class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <input
                                    v-model="filtersLocal.checklist"
                                    type="text"
                                    placeholder="Checklist"
                                    class="px-2 py-2 w-full text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </th>
                            <th
                                class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <div class="flex flex-col gap-1">
                                    <input
                                        v-model="filtersLocal.date_from"
                                        type="date"
                                        placeholder="Dari"
                                        class="w-full h-9 text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                    <input
                                        v-model="filtersLocal.date_to"
                                        type="date"
                                        placeholder="Sampai"
                                        class="w-full h-9 text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                    />
                                </div>
                            </th>
                            <th
                                class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <select
                                    v-model="filtersLocal.status"
                                    class="px-2 py-2 w-full text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="draft">Belum Selesai</option>
                                    <option value="on_progress">Dalam Proses</option>
                                    <option value="submitted">Selesai</option>
                                </select>
                            </th>
                            <th
                                class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <input
                                    v-model="filtersLocal.location"
                                    type="text"
                                    placeholder="Lokasi"
                                    class="px-2 py-2 w-full text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </th>
                            <th
                                class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                            <th
                                class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            ></th>
                            <th
                                class="p-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900"
                            >
                                <input
                                    v-model="filtersLocal.submitted_by"
                                    type="text"
                                    placeholder="Created by"
                                    class="px-2 py-2 w-full text-xs rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"
                                />
                            </th>
                            <th
                                class="px-2 py-2 bg-gray-50 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-900"
                            >
                                <button
                                    @click="clearFilters"
                                    class="px-3 py-2 text-xs text-white bg-red-500 rounded hover:bg-red-600"
                                >
                                    Clear
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-if="
                                !inspections.data ||
                                inspections.data.length === 0
                            "
                        >
                            <td
                                colspan="11"
                                class="py-8 text-center text-gray-500"
                            >
                                Tidak inspeksi kendaraan ditemukan.
                            </td>
                        </tr>
                        <template v-else-if="isGrouped">
                            <template
                                v-for="(rows, gkey) in groupedData"
                                :key="gkey"
                            >
                                <tr>
                                    <td
                                        :colspan="11"
                                        class="px-4 py-2 font-semibold text-gray-600 bg-gray-50 dark:bg-gray-900/40 dark:text-gray-300"
                                    >
                                        <button
                                            class="inline-flex gap-2 items-center"
                                            @click="toggleGroupCollapse(gkey)"
                                        >
                                            <span class="text-xs">{{
                                                isCollapsed(gkey) ? "▶" : "▼"
                                            }}</span>
                                            <span>
                                                {{ groupLabel(gkey) }}
                                            </span>
                                            <span
                                                class="ml-1 text-xs font-medium text-gray-500"
                                                >({{ rows.length }})</span
                                            >
                                        </button>
                                    </td>
                                </tr>
                                <template v-if="!isCollapsed(gkey)">
                                    <tr
                                        v-for="(row, idx) in rows"
                                        :key="row.id"
                                        class="border-b border-gray-200 cursor-pointer dark:border-gray-700 hover:bg-gray-100"
                                        @click="goDetail(row.id)"
                                    >
                                        <td class="p-3 text-center">
                                            {{ idx + 1 }}.
                                        </td>
                                        <td class="p-3 text-center">
                                            <div
                                                class="flex gap-3 items-center whitespace-nowrap ps-5"
                                            >
                                                <img
                                                    class="object-cover w-10 h-10 rounded-full"
                                                    :src="
                                                        row.photo
                                                            ? `/storage/${row.photo}`
                                                            : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                                  row.vehicle
                                                              )}&background=3b82f6&color=fff`
                                                    "
                                                    alt="Vehicle photo"
                                                    loading="lazy"
                                                />
                                                <div
                                                    class="flex flex-col gap-1 leading-tight"
                                                >
                                                    <p
                                                        class="font-medium text-gray-800 dark:text-white/90"
                                                    >
                                                        {{ row.vehicle }}
                                                    </p>
                                                    <div
                                                        class="flex flex-wrap gap-1"
                                                    >
                                                        <span
                                                            v-if="row.category"
                                                            class="px-2 py-0.5 text-xs font-medium text-green-700 bg-green-100 rounded w-fit"
                                                        >
                                                            {{ row.category }}
                                                        </span>
                                                        <span
                                                            v-if="row.route"
                                                            class="px-2 py-0.5 text-xs font-medium text-purple-700 bg-purple-100 rounded w-fit"
                                                        >
                                                            🚗 {{ row.route }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3 text-center">
                                            {{ row.inspection_number }}
                                        </td>
                                        <td class="p-3">
                                            <div class="flex flex-col gap-1">
                                                <div class="font-medium">
                                                    {{
                                                        row.checklist?.name ||
                                                        "-"
                                                    }}
                                                </div>
                                                <div
                                                    class="flex flex-wrap gap-1"
                                                >
                                                    <span
                                                        v-if="
                                                            row.checklist
                                                                ?.category
                                                        "
                                                        class="px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded w-fit"
                                                    >
                                                        {{
                                                            row.checklist
                                                                .category
                                                        }}
                                                    </span>
                                                    <span
                                                        v-if="
                                                            row.checklist?.type
                                                        "
                                                        :class="
                                                            getTypeClass(
                                                                row.checklist
                                                                    .type
                                                            )
                                                        "
                                                        class="px-2 py-0.5 text-xs font-medium rounded w-fit"
                                                    >
                                                        {{
                                                            getTypeLabel(
                                                                row.checklist
                                                                    .type
                                                            )
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="p-3 text-center whitespace-nowrap"
                                        >
                                            {{ row.submit_date || "-" }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <span
                                                :class="statusClass(row.status)"
                                                class="px-2 py-0.5 text-xs font-medium rounded-full"
                                            >
                                                {{ statusLabel(row.status) }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-center">
                                            {{ row.location || "-" }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <div
                                                v-if="
                                                    row.condition_percentage !==
                                                        null &&
                                                    row.condition_percentage !==
                                                        undefined
                                                "
                                                class="flex gap-2 justify-center items-center"
                                            >
                                                <div
                                                    class="w-full h-2 bg-gray-200 rounded-full"
                                                >
                                                    <div
                                                        :class="
                                                            getConditionBgColor(
                                                                row.condition_percentage
                                                            )
                                                        "
                                                        class="h-2 rounded-full"
                                                        :style="{
                                                            width:
                                                                row.condition_percentage +
                                                                '%',
                                                        }"
                                                    ></div>
                                                </div>
                                                <span
                                                    :class="
                                                        getConditionColor(
                                                            row.condition_percentage
                                                        )
                                                    "
                                                    class="text-sm font-semibold"
                                                    >{{
                                                        row.condition_percentage
                                                    }}%</span
                                                >
                                            </div>
                                            <span v-else class="text-gray-400"
                                                >0%</span
                                            >
                                        </td>
                                        <td class="p-3 text-center">
                                            <div v-if="row.odometer_data" class="flex flex-col gap-1 text-xs">
                                                <div class="flex gap-1 items-center justify-center">
                                                    <span class="text-gray-500">Sebelum:</span>
                                                    <span class="font-medium text-gray-800">{{ formatNumber(row.odometer_data.last_km) }} km</span>
                                                </div>
                                                <div class="flex gap-1 items-center justify-center">
                                                    <span class="text-gray-500">Saat ini:</span>
                                                    <span class="font-medium text-blue-600">{{ formatNumber(row.odometer_data.current_km) }} km</span>
                                                </div>
                                            </div>
                                            <div v-else class="text-xs text-center text-gray-400">-</div>
                                        </td>
                                        <td class="p-3 text-center">
                                            <div class="flex flex-col gap-1">
                                                <span class="font-medium">{{
                                                    row.submitted_by || "-"
                                                }}</span>
                                                <span
                                                    class="text-xs text-gray-500"
                                                    >{{ row.created_at }}</span
                                                >
                                            </div>
                                        </td>
                                        <td
                                            class="p-3 text-center whitespace-nowrap"
                                        >
                                            <button
                                                @click.stop="openQuickLook(row)"
                                                class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700"
                                            >
                                                Lihat Detail
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </template>

                        <template v-else>
                            <tr
                                v-for="(row, idx) in inspections.data"
                                :key="row.id"
                                class="border-b border-gray-200 cursor-pointer dark:border-gray-700 hover:bg-gray-100"
                                @click="goDetail(row.id)"
                            >
                                <td class="p-3 text-center">{{ idx + 1 }}.</td>
                                <td class="p-3 text-center">
                                    <div
                                        class="flex gap-3 items-center whitespace-nowrap ps-5"
                                    >
                                        <img
                                            class="object-cover w-10 h-10 rounded-full"
                                            :src="
                                                row.photo
                                                    ? `/storage/${row.photo}`
                                                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                          row.vehicle
                                                      )}&background=3b82f6&color=fff`
                                            "
                                            alt="Vehicle photo"
                                            loading="lazy"
                                        />
                                        <div
                                            class="flex flex-col gap-1 leading-tight"
                                        >
                                            <p
                                                class="font-medium text-gray-800 dark:text-white/90"
                                            >
                                                {{ row.vehicle }}
                                            </p>
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-if="row.category"
                                                    class="px-2 py-0.5 text-xs font-medium text-green-700 bg-green-100 rounded w-fit"
                                                >
                                                    {{ row.category }}
                                                </span>
                                                <span
                                                    v-if="row.route"
                                                    class="px-2 py-0.5 text-xs font-medium text-purple-700 bg-purple-100 rounded w-fit"
                                                >
                                                    🚗 {{ row.route }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    {{ row.inspection_number }}
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-col gap-1">
                                        <div class="font-medium">
                                            {{ row.checklist?.name || "-" }}
                                        </div>
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-if="row.checklist?.category"
                                                class="px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded w-fit"
                                            >
                                                {{ row.checklist.category }}
                                            </span>
                                            <span
                                                v-if="row.checklist?.type"
                                                :class="
                                                    getTypeClass(
                                                        row.checklist.type
                                                    )
                                                "
                                                class="px-2 py-0.5 text-xs font-medium rounded w-fit"
                                            >
                                                {{
                                                    getTypeLabel(
                                                        row.checklist.type
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    {{ row.submit_date || "-" }}
                                </td>
                                <td class="p-3 text-center">
                                    <span
                                        :class="statusClass(row.status)"
                                        class="px-2 py-0.5 text-xs font-medium rounded-full"
                                    >
                                        {{ statusLabel(row.status) }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    {{ row.location || "-" }}
                                </td>
                                <td class="p-3 text-center">
                                    <div
                                        v-if="
                                            row.condition_percentage !== null &&
                                            row.condition_percentage !==
                                                undefined
                                        "
                                        class="flex gap-2 justify-center items-center"
                                    >
                                        <div
                                            class="w-full h-2 bg-gray-200 rounded-full"
                                        >
                                            <div
                                                :class="
                                                    getConditionBgColor(
                                                        row.condition_percentage
                                                    )
                                                "
                                                class="h-2 rounded-full"
                                                :style="{
                                                    width:
                                                        row.condition_percentage +
                                                        '%',
                                                }"
                                            ></div>
                                        </div>
                                        <span
                                            :class="
                                                getConditionColor(
                                                    row.condition_percentage
                                                )
                                            "
                                            class="text-sm font-semibold"
                                            >{{
                                                row.condition_percentage
                                            }}%</span
                                        >
                                    </div>
                                    <span v-else class="text-gray-400">0%</span>
                                </td>
                                <td class="p-3 text-center">
                                    <div v-if="row.odometer_data" class="flex flex-col gap-1 text-xs">
                                        <div class="flex gap-1 items-center justify-center">
                                            <span class="text-gray-500">Sebelum:</span>
                                            <span class="font-medium text-gray-800">{{ formatNumber(row.odometer_data.last_km) }} km</span>
                                        </div>
                                        <div class="flex gap-1 items-center justify-center">
                                            <span class="text-gray-500">Saat ini:</span>
                                            <span class="font-medium text-blue-600">{{ formatNumber(row.odometer_data.current_km) }} km</span>
                                        </div>
                                    </div>
                                    <div v-else class="text-xs text-center text-gray-400">-</div>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex flex-col gap-1">
                                        <span class="font-medium">{{
                                            row.submitted_by || "-"
                                        }}</span>
                                        <span class="text-xs text-gray-500">{{
                                            row.created_at || "-"
                                        }}</span>
                                    </div>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <button
                                        @click.stop="openQuickLook(row)"
                                        class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded hover:bg-blue-700"
                                    >
                                        Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <Pagination :pagination="inspections" />
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Select from "@/Components/form/Select.vue";
import Pagination from "@/Components/common/Pagination.vue";
import SortIcon from "@/Components/common/SortIcon.vue";
import { useAuth } from "@/Composables/useAuth";
import { Head, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

const { user, is, can } = useAuth();

const isSuperadmin = computed(() => is("Superadmin"));

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [{ label: "Kendaraan" }, { label: "Daftar Inspeksi" }];

const props = defineProps({
    inspections: { type: Object, required: true },
    branches: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    sortBy: { type: String, default: "created_at" },
    sortDirection: { type: String, default: "desc" },
    groupBy: { type: String, default: null },
});

// Initialize branch_id: if not Superadmin, use user's branch_id
const getInitialBranchId = () => {
    if (props.filters.branch_id) {
        return props.filters.branch_id;
    }
    if (!isSuperadmin.value && user.value?.employee?.branch_id) {
        return user.value.employee.branch_id;
    }
    return null;
};

const filtersLocal = ref({
    q: props.filters.q ?? "",
    branch_id: getInitialBranchId(),
    vehicle: props.filters.vehicle ?? "",
    inspection_number: props.filters.inspection_number ?? "",
    checklist: props.filters.checklist ?? "",
    status: props.filters.status ?? "",
    location: props.filters.location ?? "",
    submitted_by: props.filters.submitted_by ?? "",
    date_from: props.filters.date_from ?? "",
    date_to: props.filters.date_to ?? "",
});

const groupByLocal = ref(props.groupBy ?? "");
const effectiveGroup = computed(
    () => groupByLocal.value || (props.groupBy ?? "")
);
const isGrouped = computed(() => !!effectiveGroup.value);

const collapsedGroups = ref(new Set());
const groupedData = computed(() => {
    if (!isGrouped.value) return {};
    const data = props.inspections.data || [];
    const groups = {};
    for (const row of data) {
        const k = keyOf(row);
        if (!groups[k]) groups[k] = [];
        groups[k].push(row);
    }
    return groups;
});

function keyOf(row) {
    switch (effectiveGroup.value) {
        case "vehicle":
            return row.vehicle ?? "Kendaraan";
        case "inspection":
            return row.inspection_number ?? "Inspeksi";
        case "checklist":
            return row.checklist ?? "Checklist";
        case "created_at":
            return row.created_at ?? "Tanggal Pengajuan";
        default:
            return "ungrouped";
    }
}

function groupLabel(key) {
    switch (effectiveGroup.value) {
        case "vehicle":
            return `Kendaran: ${key}`;
        case "inspection":
            return `Inspeksi: ${key}`;
        case "checklist":
            return `Checklist: ${key}`;
        case "created_at":
            return `Tanggal Pengajuan: ${key}`;
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

function fetchInspection({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    const params = {
        ...filtersLocal.value,
        groupBy: effectiveGroup.value || null,
        sortBy,
        sortDirection,
    };

    // Remove empty values to clean up URL
    Object.keys(params).forEach(key => {
        if (params[key] === '' || params[key] === null) {
            delete params[key];
        }
    });

    router.get(
        route("vehicle-inspections.index"),
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
        debounceTimer = setTimeout(() => fetchInspection({}), 400);
    }
);
// Watch branch_id to prevent changes if user is not Superadmin
watch(
    () => filtersLocal.value.branch_id,
    (newVal) => {
        if (!isSuperadmin.value && user.value?.employee?.branch_id) {
            // Force branch_id to user's branch_id if not Superadmin
            if (newVal !== user.value.employee.branch_id) {
                filtersLocal.value.branch_id = user.value.employee.branch_id;
            }
        }
    }
);

watch(
    () => [
        filtersLocal.value.branch_id,
        filtersLocal.value.vehicle,
        filtersLocal.value.inspection_number,
        filtersLocal.value.checklist,
        filtersLocal.value.status,
        filtersLocal.value.location,
        filtersLocal.value.submitted_by,
        filtersLocal.value.date_from,
        filtersLocal.value.date_to,
    ],
    () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchInspection({}), 400);
    }
);

watch(groupByLocal, () => {
    fetchInspection({});
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
    fetchInspection({ sortBy: column, sortDirection: nextDir });
}

function clearFilters() {
    // Preserve branch_id if user is not Superadmin
    const preservedBranchId = !isSuperadmin.value && user.value?.employee?.branch_id
        ? user.value.employee.branch_id
        : null;

    filtersLocal.value = {
        q: "",
        branch_id: preservedBranchId,
        vehicle: "",
        inspection_number: "",
        checklist: "",
        status: "",
        location: "",
        submitted_by: "",
        date_from: "",
        date_to: "",
    };
    groupByLocal.value = "";
    fetchInspection({});
}

function goDetail(id) {
    router.get(route("inspections.show", id));
}

function getTypeClass(type) {
    if (!type) return "bg-gray-100 text-gray-700";
    const typeStr = type.toLowerCase().trim();

    if (typeStr === "berkelompok" || typeStr === "group") {
        return "bg-green-100 text-green-700";
    }
    if (
        typeStr === "perorangan" ||
        typeStr === "individual" ||
        typeStr === "personal"
    ) {
        return "bg-red-100 text-red-700";
    }

    return "bg-gray-100 text-gray-700";
}

function getTypeLabel(type) {
    if (!type) return "-";
    const typeStr = type.toLowerCase().trim();

    if (typeStr === "berkelompok" || typeStr === "group") {
        return "Berkelompok";
    }
    if (
        typeStr === "perorangan" ||
        typeStr === "individual" ||
        typeStr === "personal"
    ) {
        return "Perorangan";
    }

    return type;
}

function statusClass(st) {
    if (!st) return "bg-gray-100 text-gray-700";
    const status = st.toLowerCase();

    const map = {
        draft: "bg-amber-100 text-amber-700",
        on_progress: "bg-blue-100 text-blue-700",
        submitted: "bg-green-100 text-green-700",
        approved: "bg-green-100 text-green-700",
        rejected: "bg-red-100 text-red-700",
    };
    return map[status] || "bg-gray-100 text-gray-700";
}

function statusLabel(st) {
    if (!st) return "-";
    const status = st.toLowerCase();

    const map = {
        draft: "Belum Selesai",
        on_progress: "Dalam Proses",
        submitted: "Selesai",
        approved: "Selesai",
        rejected: "Ditolak",
    };
    return map[status] || st;
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

function openUpdateKilometerModal(row) {
    // Navigate to vehicle inspection detail page where kilometer can be updated
    router.get(route("vehicle-inspections.show", row.id));
}

function openQuickLook(row) {
    // Navigate to inspection detail page (existing inspections.show route)
    router.get(route("inspections.show", row.id));
}

function formatNumber(num) {
    if (!num) return "0";
    return new Intl.NumberFormat("id-ID").format(num);
}
</script>

<style scoped>
.writing-mode-vertical-rl {
    writing-mode: vertical-rl;
    text-orientation: mixed;
}
</style>
