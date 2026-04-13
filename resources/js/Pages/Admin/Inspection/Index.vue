<template>
    <Head title="Inspeksi" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div class="flex gap-2"></div>
        </div>

        <div
            class="flex overflow-hidden flex-col bg-white rounded-lg border border-gray-200"
        >
            <div
                class="flex flex-col gap-2 px-6 py-3 border-b sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="text-xl font-semibold text-gray-800">
                    Daftar Inspeksi
                </div>
                <div class="flex gap-2">
                    <!-- Export Button -->
                    <button
                        type="button"
                        @click="exportToExcel"
                        class="flex gap-2 items-center px-3 h-10 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors"
                        title="Export ke Excel"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                        </svg>
                        Export Excel
                    </button>
                    <!-- Group By -->
                    <div class="relative" ref="groupMenuRef">
                        <button
                            type="button"
                            @click.stop="toggleGroupMenu"
                            class="px-3 h-10 text-sm rounded-lg border border-gray-200 hover:bg-gray-50"
                        >
                            Group By: {{ groupByLabel }}
                        </button>
                    <div
                        v-if="showGroupMenu"
                        class="absolute right-0 z-10 mt-2 w-52 bg-white rounded-lg border shadow-lg"
                    >
                        <ul class="py-1 text-sm">
                            <li>
                                <button
                                    @click.stop="setGroupBy(null)"
                                    class="px-3 py-2 w-full text-left hover:bg-gray-50"
                                    :class="{ 'bg-blue-50 text-blue-700 font-medium': !localGroupBy }"
                                >
                                    Tidak digrup
                                </button>
                            </li>
                            <li>
                                <button
                                    @click.stop="setGroupBy('checklist')"
                                    class="px-3 py-2 w-full text-left hover:bg-gray-50"
                                    :class="{ 'bg-blue-50 text-blue-700 font-medium': localGroupBy === 'checklist' }"
                                >
                                    Checklist
                                </button>
                            </li>
                            <li>
                                <button
                                    @click.stop="setGroupBy('status')"
                                    class="px-3 py-2 w-full text-left hover:bg-gray-50"
                                    :class="{ 'bg-blue-50 text-blue-700 font-medium': localGroupBy === 'status' }"
                                >
                                    Status
                                </button>
                            </li>
                            <li>
                                <button
                                    @click.stop="setGroupBy('location')"
                                    class="px-3 py-2 w-full text-left hover:bg-gray-50"
                                    :class="{ 'bg-blue-50 text-blue-700 font-medium': localGroupBy === 'location' }"
                                >
                                    Cabang
                                </button>
                            </li>
                            <li>
                                <button
                                    @click.stop="setGroupBy('target')"
                                    class="px-3 py-2 w-full text-left hover:bg-gray-50"
                                    :class="{ 'bg-blue-50 text-blue-700 font-medium': localGroupBy === 'target' }"
                                >
                                    Target Pengecekan
                                </button>
                            </li>
                        </ul>
                    </div>
                    </div>
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-xs">
                    <thead>
                        <tr>
                            <th
                                class="p-2 bg-gray-100 border-gray-200 border-y"
                                style="width: 100px; max-width: 100px"
                            >
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    Aksi
                                </div>
                            </th>
                            <th
                                class="p-2 bg-gray-100 border border-gray-200"
                                style="width: 140px; max-width: 140px"
                            >
                                <div class="font-medium text-gray-600">
                                    Nomor Inspeksi
                                </div>
                            </th>
                            <th
                                class="p-2 bg-gray-100 border border-gray-200"
                                style="width: 200px; max-width: 200px"
                            >
                                <div class="font-medium text-gray-600">
                                    Checklist
                                </div>
                            </th>
                            <th
                                class="p-2 bg-gray-100 border border-gray-200"
                                style="width: 130px; max-width: 130px"
                            >
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Tanggal Inspeksi
                                </div>
                            </th>
                            <th
                                class="p-2 bg-gray-100 border border-gray-200"
                                style="width: 100px; max-width: 100px"
                            >
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Status
                                </div>
                            </th>
                            <th
                                class="p-2 bg-gray-100 border border-gray-200"
                                style="width: 100px; max-width: 100px"
                            >
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Cabang
                                </div>
                            </th>
                            <th
                                class="p-2 bg-gray-100 border border-gray-200"
                                style="width: 110px; max-width: 110px"
                            >
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    Kondisi
                                </div>
                            </th>

                            <th
                                class="p-2 bg-gray-100 border border-gray-200"
                                style="width: 140px; max-width: 140px"
                            >
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Dibuat Oleh
                                </div>
                            </th>
                        </tr>
                        <!-- Inline column filters -->
                        <tr>
                            <th
                                class="px-1 py-1 bg-gray-50 border-gray-200 border-y"
                            ></th>
                            <th
                                class="px-1 py-1 bg-gray-50 border border-gray-200"
                            >
                                <input
                                    v-model="filtersLocal.q"
                                    type="text"
                                    placeholder="Cari..."
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300"
                                />
                            </th>
                            <th
                                class="px-1 py-1 bg-gray-50 border border-gray-200"
                            >
                                <select
                                    v-model="filtersLocal.checklist_id"
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300"
                                >
                                    <option :value="null">Semua</option>
                                    <option
                                        v-for="c in checklists"
                                        :key="c.id"
                                        :value="c.id"
                                    >
                                        {{ c.name }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-1 py-1 bg-gray-50 border border-gray-200"
                            >
                                <div class="flex flex-col gap-0.5">
                                    <input
                                        v-model="filtersLocal.date_from"
                                        type="date"
                                        class="px-2 w-full h-7 text-xs rounded border-gray-300"
                                    />
                                    <input
                                        v-model="filtersLocal.date_to"
                                        type="date"
                                        class="px-2 w-full h-7 text-xs rounded border-gray-300"
                                    />
                                </div>
                            </th>
                            <th
                                class="px-1 py-1 bg-gray-50 border border-gray-200"
                            >
                                <select
                                    v-model="filtersLocal.status"
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300"
                                >
                                    <option :value="null">Semua</option>
                                    <option value="draft">Belum Selesai</option>
                                    <option value="on_progress">Dalam Proses</option>
                                    <option value="submitted">Selesai</option>
                                </select>
                            </th>
                            <th
                                class="px-1 py-1 bg-gray-50 border border-gray-200"
                            >
                                <select
                                    v-model="filtersLocal.location_id"
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300"
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
                                class="px-1 py-1 bg-gray-50 border border-gray-200"
                            ></th>
                            <th
                                class="px-1 py-1 bg-gray-50 border border-gray-200"
                            >
                                <select
                                    v-model="filtersLocal.submitted_by"
                                    class="px-2 w-full h-8 text-xs rounded border-gray-300"
                                >
                                    <option :value="null">Semua</option>
                                    <option
                                        v-for="u in users"
                                        :key="u.id"
                                        :value="u.id"
                                    >
                                        {{ u.name }}
                                    </option>
                                </select>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Grouped View -->
                        <template v-if="isGrouped && groupedRows.length > 0">
                            <template v-for="group in groupedRows" :key="group.key">
                                <!-- Group Header -->
                                <tr class="bg-gray-50 border-b-2 border-gray-300">
                                    <td :colspan="8" class="p-3">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <button
                                                    @click="toggleGroupCollapse(group.key)"
                                                    class="text-gray-600 hover:text-gray-900"
                                                >
                                                    <svg
                                                        v-if="isCollapsed(group.key)"
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 5l7 7-7 7"
                                                        />
                                                    </svg>
                                                    <svg
                                                        v-else
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 9l-7 7-7-7"
                                                        />
                                                    </svg>
                                                </button>
                                                <span class="font-semibold text-gray-800">
                                                    {{ group.label }} ({{ group.items.length }})
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Group Items -->
                                <template v-if="!isCollapsed(group.key)">
                                    <tr
                                        v-for="row in group.items"
                                        :key="row.id"
                                        class="border-b border-gray-200 cursor-pointer hover:bg-gray-50"
                                        @click="openQuickLook(row)"
                                    >
                                        <td class="p-2" @click.stop>
                                            <div class="flex gap-2 justify-center">
                                                <button
                                                    @click="goDetail(row.id)"
                                                    class="px-2 py-1 text-xs text-white bg-sky-600 rounded transition-colors hover:bg-sky-700"
                                                    title="Detail"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                        />
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                        />
                                                    </svg>
                                                </button>
                                                <button
                                                    @click="deleteInspection(row.id)"
                                                    class="px-2 py-1 text-xs text-white bg-red-600 rounded transition-colors hover:bg-red-700"
                                                    title="Hapus"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="w-4 h-4"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="p-2">{{ row.inspection_number }}</td>
                                        <td class="p-2">
                                            <div class="flex flex-col gap-1">
                                                <div class="font-medium">
                                                    {{ row.checklist?.name || "-" }}
                                                </div>
                                                <div class="flex flex-wrap gap-1">
                                                    <span
                                                        v-if="row.checklist?.category?.name"
                                                        class="px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded w-fit"
                                                    >
                                                        {{ row.checklist?.category?.name }}
                                                    </span>
                                                    <span
                                                        v-if="row.checklist?.type"
                                                        :class="getTypeClass(row.checklist?.type)"
                                                        class="px-2 py-0.5 text-xs font-medium rounded w-fit"
                                                    >
                                                        {{ getTypeLabel(row.checklist?.type) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-2">
                                            {{ formatDateTime(row.submit_date) }}
                                        </td>
                                        <td class="p-2">
                                            <span
                                                :class="statusClass(row.status)"
                                                class="px-2 py-0.5 text-xs font-medium rounded-full"
                                            >
                                                {{ row.status }}
                                            </span>
                                        </td>
                                        <td class="p-2">
                                            <div
                                                class="truncate"
                                                :title="row.location?.name"
                                            >
                                                {{ row.location?.name || "-" }}
                                            </div>
                                        </td>
                                        <td class="p-2 text-center">
                                            <div
                                                class="flex gap-2 justify-center items-center"
                                            >
                                                <div
                                                    class="text-sm font-semibold"
                                                    :class="getConditionColor(row.condition_percentage)"
                                                >
                                                    {{ row.condition_percentage }}%
                                                </div>
                                                <div
                                                    class="w-20 h-2 bg-gray-200 rounded-full"
                                                >
                                                    <div
                                                        class="h-2 rounded-full transition-all"
                                                        :class="getConditionBgColor(row.condition_percentage)"
                                                        :style="{
                                                            width: row.condition_percentage + '%',
                                                        }"
                                                    ></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-2">
                                            <div class="flex flex-col gap-0.5">
                                                <div
                                                    class="text-xs font-medium text-gray-800"
                                                >
                                                    {{
                                                        row.submitted_by?.name ||
                                                        row.submitted_by ||
                                                        "-"
                                                    }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ formatDateTime(row.created_at) }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </template>
                        <!-- Ungrouped View -->
                        <tr
                            v-else-if="inspections.data && inspections.data.length"
                            v-for="row in inspections.data"
                            :key="row.id"
                            class="border-b border-gray-200 cursor-pointer hover:bg-gray-50"
                            @click="openQuickLook(row)"
                        >
                            <td class="p-2" @click.stop>
                                <div class="flex gap-2 justify-center">
                                    <button
                                        @click="goDetail(row.id)"
                                        class="px-2 py-1 text-xs text-white bg-sky-600 rounded transition-colors hover:bg-sky-700"
                                        title="Detail"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="w-4 h-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteInspection(row.id)"
                                        class="px-2 py-1 text-xs text-white bg-red-600 rounded transition-colors hover:bg-red-700"
                                        title="Hapus"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="w-4 h-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="p-2">{{ row.inspection_number }}</td>
                            <td class="p-2">
                                <div class="flex flex-col gap-1">
                                    <div class="font-medium">
                                        {{ row.checklist?.name || "-" }}
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-if="row.checklist?.category?.name"
                                            class="px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded w-fit"
                                        >
                                            {{ row.checklist?.category?.name }}
                                        </span>
                                        <span
                                            v-if="row.checklist?.type"
                                            :class="
                                                getTypeClass(
                                                    row.checklist?.type
                                                )
                                            "
                                            class="px-2 py-0.5 text-xs font-medium rounded w-fit"
                                        >
                                            {{
                                                getTypeLabel(
                                                    row.checklist?.type
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-2">
                                {{ formatDateTime(row.submit_date) }}
                            </td>
                            <td class="p-2">
                                <span
                                    :class="statusClass(row.status)"
                                    class="px-2 py-0.5 text-xs font-medium rounded-full"
                                    >{{ row.status }}</span
                                >
                            </td>
                            <td class="p-2">
                                <div
                                    class="truncate"
                                    :title="row.location?.name"
                                >
                                    {{ row.location?.name || "-" }}
                                </div>
                            </td>
                            <td class="p-2 text-center">
                                <div
                                    class="flex gap-2 justify-center items-center"
                                >
                                    <div
                                        class="text-sm font-semibold"
                                        :class="
                                            getConditionColor(
                                                row.condition_percentage
                                            )
                                        "
                                    >
                                        {{ row.condition_percentage }}%
                                    </div>
                                    <div
                                        class="w-20 h-2 bg-gray-200 rounded-full"
                                    >
                                        <div
                                            class="h-2 rounded-full transition-all"
                                            :class="
                                                getConditionBgColor(
                                                    row.condition_percentage
                                                )
                                            "
                                            :style="{
                                                width:
                                                    row.condition_percentage +
                                                    '%',
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </td>

                            <td class="p-2">
                                <div class="flex flex-col gap-0.5">
                                    <div
                                        class="text-xs font-medium text-gray-800"
                                    >
                                        {{
                                            row.submitted_by?.name ||
                                            row.submitted_by ||
                                            "-"
                                        }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ formatDateTime(row.created_at) }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <!-- Empty State -->
                        <tr v-if="(!inspections.data || !inspections.data.length) && (!isGrouped || groupedRows.length === 0)">
                            <td
                                colspan="8"
                                class="py-6 text-center text-gray-500"
                            >
                                Tidak ada data
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="inspections.data && inspections.data.length"
                :pagination="inspections"
                class="border-t"
            />
        </div>

        <!-- Quick Look Modal -->
        <div
            v-if="showQuickLook"
            @click="closeQuickLook"
            class="flex fixed inset-0 z-50 justify-center items-center bg-black bg-opacity-50"
        >
            <div
                @click.stop
                class="bg-white rounded-lg shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col"
            >
                <!-- Modal Header -->
                <div
                    class="flex justify-between items-center px-6 py-4 border-b"
                >
                    <h3 class="text-xl font-semibold text-gray-800">
                        Quick Look - {{ selectedInspection?.inspection_number }}
                    </h3>
                    <button
                        @click="closeQuickLook"
                        class="text-2xl text-gray-400 hover:text-gray-600"
                    >
                        ×
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="overflow-auto p-6">
                    <!-- Info Section -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <div class="text-sm text-gray-600">Checklist</div>
                            <div class="font-semibold">
                                {{ selectedInspection?.checklist?.name || "-" }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Status</div>
                            <span
                                :class="statusClass(selectedInspection?.status)"
                                class="px-2 py-0.5 text-xs font-medium rounded-full"
                            >
                                {{ selectedInspection?.status }}
                            </span>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">
                                Tanggal Submit
                            </div>
                            <div class="font-semibold">
                                {{
                                    formatDateTime(
                                        selectedInspection?.submit_date
                                    )
                                }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">
                                Diajukan Oleh
                            </div>
                            <div class="font-semibold">
                                {{
                                    selectedInspection?.submitted_by?.name ||
                                    selectedInspection?.submitted_by ||
                                    "-"
                                }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Cabang</div>
                            <div class="font-semibold">
                                {{ selectedInspection?.location?.name || "-" }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600">Kondisi</div>
                            <div class="flex gap-2 items-center">
                                <div
                                    class="text-sm font-semibold"
                                    :class="
                                        getConditionColor(
                                            selectedInspection?.condition_percentage
                                        )
                                    "
                                >
                                    {{
                                        selectedInspection?.condition_percentage
                                    }}%
                                </div>
                                <div class="w-32 h-2 bg-gray-200 rounded-full">
                                    <div
                                        class="h-2 rounded-full transition-all"
                                        :class="
                                            getConditionBgColor(
                                                selectedInspection?.condition_percentage
                                            )
                                        "
                                        :style="{
                                            width:
                                                selectedInspection?.condition_percentage +
                                                '%',
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Answers Table -->
                    <div class="overflow-hidden rounded-lg border">
                        <div class="px-4 py-2 bg-gray-50 border-b">
                            <h4 class="font-semibold text-gray-700">
                                Daftar Jawaban
                            </h4>
                        </div>
                        <div class="overflow-auto max-h-[250px]">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 bg-gray-100">
                                    <tr>
                                        <th
                                            class="p-3 font-medium text-left text-gray-600"
                                        >
                                            No
                                        </th>
                                        <th
                                            class="p-3 font-medium text-left text-gray-600"
                                        >
                                            Pertanyaan
                                        </th>
                                        <th
                                            class="p-3 font-medium text-left text-gray-600"
                                        >
                                            Jawaban
                                        </th>
                                        <th
                                            class="p-3 font-medium text-left text-gray-600"
                                        >
                                            Catatan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Loading State -->
                                    <tr v-if="isLoadingQuickLook">
                                        <td colspan="4" class="p-8 text-center">
                                            <div
                                                class="flex flex-col gap-3 justify-center items-center"
                                            >
                                                <svg
                                                    class="w-8 h-8 text-blue-600 animate-spin"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <circle
                                                        class="opacity-25"
                                                        cx="12"
                                                        cy="12"
                                                        r="10"
                                                        stroke="currentColor"
                                                        stroke-width="4"
                                                    ></circle>
                                                    <path
                                                        class="opacity-75"
                                                        fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                    ></path>
                                                </svg>
                                                <p
                                                    class="text-sm text-gray-600"
                                                >
                                                    Memuat data...
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Data Rows -->
                                    <tr
                                        v-else
                                        v-for="(
                                            answer, idx
                                        ) in quickLookAnswers"
                                        :key="idx"
                                        class="border-b hover:bg-gray-50"
                                    >
                                        <td class="p-3">{{ idx + 1 }}</td>
                                        <td class="p-3">
                                            {{ answer.question || "-" }}
                                        </td>
                                        <td class="p-3">
                                            <span
                                                :class="
                                                    getAnswerClass(
                                                        answer.answer
                                                    )
                                                "
                                                class="px-2 py-0.5 text-xs font-medium rounded"
                                            >
                                                {{ answer.answer || "-" }}
                                            </span>
                                        </td>
                                        <td class="p-3">
                                            {{ answer.note || "-" }}
                                        </td>
                                    </tr>
                                    <!-- Empty State -->
                                    <tr
                                        v-if="
                                            !isLoadingQuickLook &&
                                            (!quickLookAnswers ||
                                                quickLookAnswers.length === 0)
                                        "
                                    >
                                        <td
                                            colspan="4"
                                            class="p-6 text-center text-gray-500"
                                        >
                                            Tidak ada data jawaban
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="flex gap-2 justify-end px-6 py-4 bg-gray-50 border-t"
                >
                    <button
                        @click="closeQuickLook"
                        class="px-4 py-2 text-sm text-gray-700 bg-white rounded border hover:bg-gray-50"
                    >
                        Tutup
                    </button>
                    <button
                        @click="goDetail(selectedInspection?.id)"
                        class="px-4 py-2 text-sm text-white bg-sky-600 rounded hover:bg-sky-700"
                    >
                        Lihat Detail Lengkap
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref, watch, computed, onMounted, onBeforeUnmount } from "vue";
import * as XLSX from "xlsx";
import axios from "axios";

defineOptions({ layout: AppLayout });

const breadcrumbs = [{ label: "Menu Utama" }, { label: "Inspeksi" }];

const props = defineProps({
    inspections: Object,
    q: String,
    filters: { type: Object, default: () => ({}) },
    group_by: { type: String, default: null },
    branches: { type: Array, default: () => [] },
    statuses: { type: Array, default: () => [] },
    checklists: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});

// Initialize with safe defaults to avoid undefined during first render
const filtersLocal = ref({
    q: props.filters?.q || "",
    checklist_id: props.filters?.checklist_id ?? null,
    location_id: props.filters?.location_id ?? null,
    submitted_by: props.filters?.submitted_by ?? null,
    status: props.filters?.status ?? null,
    date_from: props.filters?.date_from || "",
    date_to: props.filters?.date_to || "",
});

const localQ = ref(props.q || "");
const showGroupMenu = ref(false);
const showQuickLook = ref(false);
const selectedInspection = ref(null);
const quickLookAnswers = ref([]);
const isLoadingQuickLook = ref(false);
const localGroupBy = ref(props.group_by || null);
const groupMenuRef = ref(null);
const collapsedGroups = ref(new Set());

const isGrouped = computed(() => !!localGroupBy.value);

const groupByLabel = computed(() => {
    const labels = {
        checklist: "Checklist",
        status: "Status",
        location: "Cabang",
        target: "Target Pengecekan",
    };
    return localGroupBy.value ? labels[localGroupBy.value] : "Tidak digrup";
});

// Group rows based on group_by
const groupedRows = computed(() => {
    if (!isGrouped.value) return [];

    const data = props.inspections?.data || [];
    const groups = [];
    const groupIndex = new Map();

    for (const row of data) {
        const key = getGroupKey(row);
        const label = getGroupLabel(row);

        if (!groupIndex.has(key)) {
            groupIndex.set(key, groups.length);
            groups.push({ key, label, items: [] });
        }
        groups[groupIndex.get(key)].items.push(row);
    }

    return groups;
});

function getGroupKey(row) {
    switch (localGroupBy.value) {
        case 'checklist':
            return row.checklist?.id?.toString() || 'null';
        case 'status':
            return row.status || 'null';
        case 'location':
            return row.location?.id?.toString() || 'null';
        case 'target':
            return getInspectionTargetKey(row);
        default:
            return 'ungrouped';
    }
}

function getGroupLabel(row) {
    switch (localGroupBy.value) {
        case 'checklist':
            return `Checklist: ${row.checklist?.name || 'Tanpa Checklist'}`;
        case 'status':
            return `Status: ${row.status || 'Tanpa Status'}`;
        case 'location':
            return `Cabang: ${row.location?.name || 'Tanpa Cabang'}`;
        case 'target':
            return `Target: ${getInspectionTargetLabel(row)}`;
        default:
            return 'Ungrouped';
    }
}

function getInspectionTargetKey(row) {
    if (!row.model_type) return 'null';
    return row.model_type + '_' + (row.model?.id?.toString() || 'null');
}

function getInspectionTargetLabel(row) {
    if (!row.model_type) return 'Tanpa Target';
    if (row.model_type === "App\\Models\\Vehicle") {
        return row.model?.license_plate || row.model?.name || 'Kendaraan';
    } else if (row.model_type === "App\\Models\\Employee") {
        return row.model?.name || 'Karyawan';
    }
    return 'Tanpa Target';
}

function isCollapsed(key) {
    return collapsedGroups.value.has(key);
}

function toggleGroupCollapse(key) {
    const s = new Set(collapsedGroups.value);
    if (s.has(key)) {
        s.delete(key);
    } else {
        s.add(key);
    }
    collapsedGroups.value = s;
}

function toggleGroupMenu() {
    showGroupMenu.value = !showGroupMenu.value;
}

function closeGroupMenu() {
    showGroupMenu.value = false;
}

// Handle click outside to close menu
function handleClickOutside(event) {
    if (groupMenuRef.value && !groupMenuRef.value.contains(event.target)) {
        closeGroupMenu();
    }
}

function getInspectionTarget(row) {
    if (!row.model_type) return "-";

    if (row.model_type === "App\\Models\\Vehicle") {
        return "Kendaraan";
    } else if (row.model_type === "App\\Models\\Employee") {
        return "Karyawan";
    }

    return "-";
}

function openQuickLook(row) {
    selectedInspection.value = row;
    showQuickLook.value = true;

    // Fetch answers for this inspection using axios
    fetchInspectionAnswers(row.id);
}

function closeQuickLook() {
    showQuickLook.value = false;
    selectedInspection.value = null;
    quickLookAnswers.value = [];
}

async function fetchInspectionAnswers(inspectionId) {
    if (!inspectionId) {
        console.error("No inspection ID provided");
        return;
    }

    console.log("Fetching answers for inspection:", inspectionId);
    isLoadingQuickLook.value = true;

    try {
        const response = await axios.get(
            `/inspections/${inspectionId}/answers`
        );

        console.log("Quick Look Response:", response.data);

        if (response.data && response.data.questions) {
            quickLookAnswers.value = response.data.questions.map((q) => ({
                question: q.pertanyaan || q.title,
                answer: q.jawaban || q.answer,
                note: q.catatan || q.note,
            }));
        } else {
            quickLookAnswers.value = [];
        }
    } catch (error) {
        console.error("Error fetching inspection answers:", error);
        console.error("Error details:", error.response?.data);
        quickLookAnswers.value = [];
    } finally {
        isLoadingQuickLook.value = false;
    }
}

function getAnswerClass(answer) {
    if (!answer) return "bg-gray-100 text-gray-700";
    const ans = answer.toLowerCase().trim();
    if (["baik", "true", "ya", "yes", "1", "ok"].includes(ans)) {
        return "bg-green-100 text-green-700";
    }
    if (["tidak", "false", "no", "0", "tidak baik", "buruk"].includes(ans)) {
        return "bg-red-100 text-red-700";
    }
    return "bg-gray-100 text-gray-700";
}
let t = null;
watch(localQ, () => {
    clearTimeout(t);
    t = setTimeout(() => {
        fetchList();
    }, 350);
});

// keep filtersLocal synced after navigation
watch(
    () => props.filters,
    (f) => {
        if (!f) return;
        filtersLocal.value.q = f.q || "";
        filtersLocal.value.checklist_id = f.checklist_id ?? null;
        filtersLocal.value.location_id = f.location_id ?? null;
        filtersLocal.value.submitted_by = f.submitted_by ?? null;
        filtersLocal.value.status = f.status ?? null;
        filtersLocal.value.date_from = f.date_from ?? "";
        filtersLocal.value.date_to = f.date_to ?? "";
    }
);

function getTypeClass(type) {
    if (!type) return "bg-gray-100 text-gray-700";
    const typeStr = type.toLowerCase().trim();

    // Berkelompok = Hijau
    if (typeStr === "berkelompok" || typeStr === "group") {
        return "bg-green-100 text-green-700";
    }
    // Perorangan = Merah
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

    if (typeStr === "multiple" || typeStr === "group") {
        return "Berkelompok";
    }
    if (
        typeStr === "perorangan" ||
        typeStr === "single" ||
        typeStr === "personal"
    ) {
        return "Perorangan";
    }

    return type;
}

function statusClass(st) {
    const map = {
        "Belum Selesai": "bg-yellow-100 text-yellow-700",
        "Dalam Proses": "bg-blue-100 text-blue-700",
        Selesai: "bg-green-100 text-green-700",
        Disetujui: "bg-blue-100 text-blue-700",
        Ditolak: "bg-red-100 text-red-700",
    };
    return map[st] || "bg-gray-100 text-gray-700";
}

function setGroupBy(v) {
    localGroupBy.value = v || null;
    closeGroupMenu();
    // Trigger fetch with new group_by parameter
    fetchList();
}

function fetchList() {
    router.get(
        route("inspections.index"),
        {
            q: filtersLocal.value.q,
            checklist_id: filtersLocal.value.checklist_id,
            location_id: filtersLocal.value.location_id,
            submitted_by: filtersLocal.value.submitted_by,
            status: filtersLocal.value.status,
            date_from: filtersLocal.value.date_from,
            date_to: filtersLocal.value.date_to,
            group_by: localGroupBy.value,
        },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

// Debounced filter syncing
watch(
    () => ({ ...filtersLocal.value }),
    () => {
        clearTimeout(t);
        t = setTimeout(() => fetchList(), 300);
    },
    { deep: true }
);

function formatLocalTime(time) {
    if (!time) return "-";
    const date = new Date(`1970-01-01T${time}+08:00`);
    return date
        .toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
        })
        .replace(":", ".");
}

function formatDateTime(val) {
    if (!val) return "-";
    // Accepts preformatted string "YYYY-MM-DD HH:mm:ss" or ISO date
    const date = new Date(val);
    if (isNaN(date.getTime())) return String(val);
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const dd = String(date.getDate()).padStart(2, "0");
    const hh = String(date.getHours()).padStart(2, "0");
    const mi = String(date.getMinutes()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd} ${hh}.${mi}`;
}

function goDetail(id) {
    router.get(route("inspections.show", id));
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

function deleteInspection(id) {
    if (confirm("Apakah Anda yakin ingin menghapus inspeksi ini?")) {
        router.delete(route("inspections.destroy", id), {
            onSuccess: () => {
                alert("Inspeksi berhasil dihapus");
            },
            onError: () => {
                alert("Gagal menghapus inspeksi");
            },
        });
    }
}

// Sync localGroupBy with props
watch(
    () => props.group_by,
    (newVal) => {
        localGroupBy.value = newVal || null;
    }
);

async function exportToExcel() {
    // Show loading indicator
    const loadingMessage = "Mengambil semua data untuk diekspor...";
    alert(loadingMessage);

    try {
        // Build query parameters with current filters but with very large per_page
        const exportParams = {
            q: filtersLocal.value.q || "",
            checklist_id: filtersLocal.value.checklist_id || "",
            location_id: filtersLocal.value.location_id || "",
            submitted_by: filtersLocal.value.submitted_by || "",
            status: filtersLocal.value.status || "",
            date_from: filtersLocal.value.date_from || "",
            date_to: filtersLocal.value.date_to || "",
            group_by: localGroupBy.value || "",
            per_page: 99999, // Very large number to get all data
        };

        // Use router.get to fetch all data with large per_page
        // This will temporarily update the props but we'll extract data immediately
        return new Promise((resolve) => {
            router.get(route('inspections.index'), exportParams, {
                preserveScroll: true,
                preserveState: true,
                only: ['inspections'],
                onSuccess: (page) => {
                    // Extract all data from the updated props
                    let allData = [];
                    if (page.props.inspections && page.props.inspections.data) {
                        allData = page.props.inspections.data;
                    }

                    if (allData.length === 0) {
                        alert("Tidak ada data untuk diekspor");
                        // Restore normal pagination
                        restorePagination();
                        resolve();
                        return;
                    }

                    // Process export with all data
                    processExport(allData);

                    // Restore normal pagination after export
                    restorePagination();
                    resolve();
                },
                onError: () => {
                    alert("Terjadi kesalahan saat mengambil data");
                    // Restore normal pagination
                    restorePagination();
                    resolve();
                }
            });
        });
    } catch (error) {
        console.error("Error exporting data:", error);
        alert("Terjadi kesalahan saat mengekspor data. Silakan coba lagi.");
    }
}

function restorePagination() {
    // Restore normal pagination by reloading with default per_page
    const restoreParams = {
        q: filtersLocal.value.q || "",
        checklist_id: filtersLocal.value.checklist_id || "",
        location_id: filtersLocal.value.location_id || "",
        submitted_by: filtersLocal.value.submitted_by || "",
        status: filtersLocal.value.status || "",
        date_from: filtersLocal.value.date_from || "",
        date_to: filtersLocal.value.date_to || "",
        group_by: localGroupBy.value || "",
        per_page: 10, // Restore to default pagination
    };

    router.get(route('inspections.index'), restoreParams, {
        preserveScroll: true,
        preserveState: true,
        only: ['inspections'],
    });
}

function processExport(dataToExport) {
    // Prepare Excel data
    const wsData = [];

    // Header row
    const headers = [
        "No",
        "Nomor Inspeksi",
        "Checklist",
        "Kategori",
        "Tipe",
        "Tanggal Inspeksi",
        "Status",
        "Cabang",
        "Kondisi (%)",
        "Target Pengecekan",
        "Nama Target",
        "Dibuat Oleh",
        "Tanggal Dibuat",
    ];
    wsData.push(headers);

    // Data rows
    dataToExport.forEach((row, idx) => {
        const targetType = getInspectionTarget(row);
        const targetName = getInspectionTargetLabel(row);

        wsData.push([
            idx + 1,
            row.inspection_number || "-",
            row.checklist?.name || "-",
            row.checklist?.category?.name || "-",
            getTypeLabel(row.checklist?.type) || "-",
            row.submit_date ? formatDateTime(row.submit_date) : "-",
            row.status || "-",
            row.location?.name || "-",
            row.condition_percentage || 0,
            targetType,
            targetName,
            row.submitted_by?.name || row.submitted_by || "-",
            row.created_at ? formatDateTime(row.created_at) : "-",
        ]);
    });

    // Create workbook and worksheet
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(wsData);

    // Set column widths
    ws["!cols"] = [
        { wch: 5 },   // No
        { wch: 18 },  // Nomor Inspeksi
        { wch: 25 },  // Checklist
        { wch: 15 },  // Kategori
        { wch: 12 },  // Tipe
        { wch: 18 },  // Tanggal Inspeksi
        { wch: 15 },  // Status
        { wch: 20 },  // Cabang
        { wch: 12 },  // Kondisi (%)
        { wch: 18 },  // Target Pengecekan
        { wch: 20 },  // Nama Target
        { wch: 20 },  // Dibuat Oleh
        { wch: 18 },  // Tanggal Dibuat
    ];

    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, "Data Inspeksi");

    // Generate filename with current date
    const currentDate = new Date()
        .toLocaleDateString("id-ID")
        .replace(/\//g, "-");
    const fileName = `Data_Inspeksi_${currentDate}.xlsx`;

    // Save file
    XLSX.writeFile(wb, fileName);
}

// Setup click outside listener
onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>
