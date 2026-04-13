<template>
    <Head title="Laporan Karyawan" />

    <div
        class="flex overflow-hidden flex-col gap-3 px-3 h-full"
        key="reports-page"
    >
        <!-- Header -->
        <div class="flex z-10 justify-between items-center h-10">
            <Breadcrumb
                :items="[{ label: 'Laporan' }, { label: 'Data Laporan' }]"
            />
            <!-- (opsional) tombol tambah bisa diaktifkan jika diperlukan
            <Link :href="route('reports.create')" class="flex gap-2 items-center px-3 py-2 text-white bg-blue-500 rounded">
                <PlusSquareIcon />
                <span class="hidden text-sm md:block">Tambah Laporan</span>
            </Link>
            -->
        </div>

        <!-- Card -->
        <div
            class="flex overflow-hidden flex-col bg-white rounded-lg border border-gray-200"
        >
            <!-- Card header -->
            <div
                class="flex flex-col gap-2 px-8 py-4 border-b border-gray-200 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="font-bold text-gray-700 md:text-xl">
                    Laporan Karyawan
                </div>

                <div class="flex gap-3 items-center">
                    <!-- Live Search -->
                    <div class="relative">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari laporan..."
                            class="h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 xl:w-[260px]"
                        />
                    </div>

                    <!-- Per page
                    <select
                        v-model.number="perPage"
                        class="px-3 h-10 text-sm bg-white rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                    >
                        <option :value="10">10 / halaman</option>
                        <option :value="25">25 / halaman</option>
                        <option :value="50">50 / halaman</option>
                        <option :value="100">100 / halaman</option>
                    </select> -->
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width: 64px" />
                        <col style="width: 120px" />
                        <col style="width: 260px" />
                        <col />
                        <col style="width: 200px" />
                        <col style="width: 160px" />
                        <col style="width: 140px" />
                    </colgroup>

                    <thead>
                        <!-- Header Row -->
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y"
                            >
                                <div
                                    class="flex justify-center items-center px-3"
                                >
                                    <p
                                        class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap"
                                    >
                                        No.
                                    </p>
                                </div>
                            </th>

                            <th class="py-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="flex justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap"
                                    >
                                        Gambar
                                    </p>
                                </div>
                            </th>

                            <!-- Sort by title -->
                            <th
                                @click="changeSort('title')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap"
                                    >
                                        Judul
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'title' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900'
                                                    : 'text-gray-400',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'title' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900'
                                                    : 'text-gray-400',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>

                            <th class="py-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="flex justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap"
                                    >
                                        Deskripsi
                                    </p>
                                </div>
                            </th>

                            <th class="py-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="flex justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap"
                                    >
                                        Dibuat Oleh
                                    </p>
                                </div>
                            </th>

                            <!-- Sort by created_at -->
                            <th
                                @click="changeSort('created_at')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap"
                                    >
                                        Tanggal
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'created_at' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900'
                                                    : 'text-gray-400',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'created_at' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900'
                                                    : 'text-gray-400',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>

                            <th
                                class="py-3 bg-gray-100 border-gray-200 border-y"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap"
                                    >
                                        Aksi
                                    </p>
                                </div>
                            </th>
                        </tr>

                        <!-- Filter Row -->
                        <tr class="bg-white border-b border-gray-200">
                            <!-- No. - Empty -->
                            <th class="py-2 px-2 border-x border-gray-200">
                                <button
                                    v-if="
                                        filters.employee_id ||
                                        filters.department_id ||
                                        filters.branch_id ||
                                        filters.date_from ||
                                        filters.date_to
                                    "
                                    @click="clearFilters"
                                    class="px-2 py-1 text-xs text-white bg-red-500 rounded hover:bg-red-600 transition-colors"
                                    title="Clear Filter"
                                >
                                    Clear
                                </button>
                            </th>

                            <!-- Gambar - Empty -->
                            <th class="py-2 px-2 border-x border-gray-200"></th>

                            <!-- Judul - Search -->
                            <th class="py-2 px-2 border-x border-gray-200">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari judul..."
                                    class="w-full px-2 py-1.5 text-xs bg-white rounded border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-400"
                                />
                            </th>

                            <!-- Deskripsi - Empty -->
                            <th class="py-2 px-2 border-x border-gray-200"></th>

                            <!-- Dibuat Oleh - Filter Karyawan -->
                            <th class="py-2 px-2 border-x border-gray-200">
                                <SearchableSelect
                                    v-model="filters.employee_id"
                                    :options="employees"
                                    placeholder="Semua Karyawan"
                                    label-key="name"
                                    track-by-key="id"
                                    :allow-empty="true"
                                    custom-class="filter-select"
                                />
                            </th>

                            <!-- Tanggal - Filter Date Range -->
                            <th class="py-2 px-2 border-x border-gray-200">
                                <div class="flex gap-1">
                                    <input
                                        v-model="filters.date_from"
                                        type="date"
                                        placeholder="Dari"
                                        class="w-full px-1 py-1.5 text-xs bg-white rounded border border-gray-300 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-400"
                                    />
                                </div>
                            </th>

                            <!-- Aksi - Empty -->
                            <th class="py-2 px-2 border-x border-gray-200"></th>
                        </tr>
                    </thead>

                    <tbody>
                        <template v-if="reports.data && reports.data.length">
                            <tr
                                v-for="(report, index) in reports.data"
                                :key="report.id"
                                class="cursor-pointer hover:bg-gray-50 transition-colors"
                                @click="openDetailModal(report)"
                            >
                                <td class="py-2.5 border-gray-200 border-y">
                                    <div
                                        class="flex justify-center items-center whitespace-nowrap"
                                    >
                                        <p class="px-3 text-gray-500">
                                            {{
                                                (reports.current_page - 1) *
                                                    reports.per_page +
                                                index +
                                                1
                                            }}.
                                        </p>
                                    </div>
                                </td>

                                <td class="py-2.5 border border-gray-200">
                                    <div
                                        class="flex justify-center items-center px-3"
                                    >
                                        <img
                                            v-if="report.image"
                                            :src="`/storage/${report.image}`"
                                            alt="Report Image"
                                            class="object-cover w-12 h-12 rounded"
                                        />
                                        <div
                                            v-else
                                            class="flex justify-center items-center w-12 h-12 text-xs text-gray-500 bg-gray-200 rounded"
                                        >
                                            N/A
                                        </div>
                                    </div>
                                </td>

                                <td class="py-2.5 border border-gray-200">
                                    <div
                                        class="flex items-center px-3 whitespace-nowrap"
                                    >
                                        <p class="font-medium text-gray-800">
                                            {{ report.title }}
                                        </p>
                                    </div>
                                </td>

                                <td class="py-2.5 border border-gray-200">
                                    <div
                                        class="px-3 text-gray-600 truncate max-w-[420px]"
                                    >
                                        {{ report.description || "-" }}
                                    </div>
                                </td>

                                <td class="py-2.5 border border-gray-200">
                                    <div
                                        class="flex justify-center items-center px-3 whitespace-nowrap"
                                    >
                                        <p class="text-gray-500">
                                            {{ report.user?.name || "-" }}
                                        </p>
                                    </div>
                                </td>

                                <td class="py-2.5 border border-gray-200">
                                    <div
                                        class="flex justify-center items-center px-3 whitespace-nowrap"
                                    >
                                        <p class="text-gray-500">
                                            {{ formatDate(report.created_at) }}
                                        </p>
                                    </div>
                                </td>

                                <td class="py-2.5 border-gray-200 border-y">
                                    <div
                                        class="flex gap-3 justify-center px-4 whitespace-nowrap"
                                    >
                                        <!-- Tombol buka modal detail -->
                                        <button
                                            v-if="can('reports.view')"
                                            @click.stop="
                                                openDetailModal(report)
                                            "
                                            aria-label="Lihat detail"
                                        >
                                            <ShowIcon
                                                class="text-blue-400 hover:text-blue-600 transition-colors"
                                            />
                                        </button>
                                        <!-- Tombol buka modal edit -->
                                        <button
                                            v-if="can('reports.edit')"
                                            @click.stop="openEditModal(report)"
                                            aria-label="Edit cepat"
                                        >
                                            <EditIcon
                                                class="text-yellow-500 hover:text-yellow-600 transition-colors"
                                            />
                                        </button>
                                        <button
                                            v-if="can('reports.delete')"
                                            @click.stop="
                                                openConfirmModal(report)
                                            "
                                            aria-label="Hapus"
                                        >
                                            <TrashIcon class="text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-else>
                            <td
                                colspan="7"
                                class="py-6 font-medium text-center text-gray-500"
                            >
                                Tidak ada laporan ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer: info + pagination -->
            <div
                v-if="reports && reports.data && reports.data.length"
                class="flex justify-between items-center px-4 py-3 bg-gray-50 border-t border-gray-200"
            >
                <div class="text-sm text-gray-700">
                    Menampilkan
                    <span class="font-medium">{{ reports.from }}</span>
                    -
                    <span class="font-medium">{{ reports.to }}</span>
                    dari
                    <span class="font-medium">{{ reports.total }}</span>
                    data
                </div>

                <Pagination :pagination="reports" />
            </div>
        </div>

        <!-- Confirm Delete -->
        <ConfirmModal
            :show="isConfirmModalOpen"
            :question="`Yakin ingin menghapus laporan`"
            :selected="`${selectedReport?.title || ''}`"
            title="Hapus Laporan"
            confirmText="Ya, Hapus!"
            maxWidth="md"
            @close="closeConfirmModal"
            @confirm="destroyReport"
        />

        <!-- Detail Modal -->
        <Teleport to="body">
            <transition name="fade">
                <div
                    v-if="isDetailModalOpen"
                    class="fixed inset-0 z-[1000] flex items-center justify-center"
                    @keydown.esc="closeDetailModal"
                >
                    <!-- Overlay -->
                    <div
                        class="absolute inset-0 bg-black/40"
                        @click="closeDetailModal"
                    ></div>

                    <!-- Dialog -->
                    <transition name="pop">
                        <div
                            class="relative w-[900px] max-w-[95vw] bg-white border border-gray-200 rounded-xl shadow-2xl"
                            role="dialog"
                            aria-modal="true"
                            @click.stop
                        >
                            <!-- Header -->
                            <div
                                class="flex justify-between items-center px-6 py-4 border-b"
                            >
                                <h3 class="text-xl font-semibold text-gray-800">
                                    Detail Laporan
                                </h3>
                                <button
                                    class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                                    @click="closeDetailModal"
                                    aria-label="Tutup"
                                >
                                    <svg
                                        class="w-5 h-5"
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

                            <!-- Body -->
                            <div
                                class="p-6 space-y-6 max-h-[75vh] overflow-y-auto"
                            >
                                <!-- Image -->
                                <div
                                    v-if="detailReport.image"
                                    class="flex justify-center"
                                >
                                    <img
                                        :src="`/storage/${detailReport.image}`"
                                        :alt="detailReport.title"
                                        class="max-w-full max-h-96 object-contain rounded-lg border border-gray-200"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="flex justify-center items-center h-48 bg-gray-100 rounded-lg border border-gray-200"
                                >
                                    <div class="text-center text-gray-500">
                                        <svg
                                            class="w-16 h-16 mx-auto mb-2"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                        <p>Tidak ada gambar</p>
                                    </div>
                                </div>

                                <!-- Title -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-600 mb-2"
                                        >Judul</label
                                    >
                                    <p
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ detailReport.title }}
                                    </p>
                                </div>

                                <!-- Description -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-600 mb-2"
                                        >Deskripsi</label
                                    >
                                    <p
                                        class="text-gray-700 whitespace-pre-wrap"
                                    >
                                        {{ detailReport.description || "-" }}
                                    </p>
                                </div>

                                <!-- Meta Info -->
                                <div
                                    class="grid grid-cols-2 gap-4 pt-4 border-t"
                                >
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-600 mb-1"
                                            >Dibuat Oleh</label
                                        >
                                        <p class="text-gray-900">
                                            {{ detailReport.user?.name || "-" }}
                                        </p>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-600 mb-1"
                                            >Tanggal</label
                                        >
                                        <p class="text-gray-900">
                                            {{
                                                formatDate(
                                                    detailReport.created_at,
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Comments Section -->
                                <div class="pt-6 border-t">
                                    <ReportComments
                                        :reportId="detailReport.id"
                                    />
                                </div>
                            </div>

                            <!-- Footer -->
                            <div
                                class="flex justify-end gap-3 px-6 py-4 border-t bg-gray-50 rounded-b-xl"
                            >
                                <button
                                    @click="closeDetailModal"
                                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    Tutup
                                </button>
                                <button
                                    @click="openEditFromDetail"
                                    class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    Edit Laporan
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>
            </transition>
        </Teleport>

        <!-- Edit Modal (Teleport ke body agar tidak bertabrakan dengan header/overflow) -->
        <Teleport to="body">
            <transition name="fade">
                <div
                    v-if="isEditModalOpen"
                    class="fixed inset-0 z-[1000] flex items-center justify-center"
                    @keydown.esc="closeEditModal"
                >
                    <!-- Overlay -->
                    <div
                        class="absolute inset-0 bg-black/40"
                        @click="closeEditModal"
                    ></div>

                    <!-- Dialog -->
                    <transition name="pop">
                        <div
                            class="relative w-[720px] max-w-[92vw] bg-white border border-gray-200 rounded-xl shadow-2xl"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="editModalTitle"
                            aria-describedby="editModalDesc"
                            @click.stop
                        >
                            <!-- Header -->
                            <div
                                class="flex justify-between items-center px-5 py-3 border-b"
                            >
                                <h3
                                    id="editModalTitle"
                                    class="text-lg font-semibold text-gray-800"
                                >
                                    Edit Laporan
                                </h3>
                                <button
                                    class="p-2 rounded hover:bg-gray-100"
                                    @click="closeEditModal"
                                    aria-label="Tutup"
                                >
                                    ✕
                                </button>
                            </div>

                            <!-- Body -->
                            <div
                                id="editModalDesc"
                                class="p-5 space-y-4 max-h-[75vh] overflow-y-auto"
                            >
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Judul
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <input
                                        ref="editTitleRef"
                                        v-model.trim="editForm.title"
                                        type="text"
                                        class="px-3 py-2 mt-1 w-full text-gray-800 rounded-lg border border-gray-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/40 focus:border-blue-400"
                                        placeholder="Masukkan judul laporan"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Deskripsi</label
                                    >
                                    <textarea
                                        v-model.trim="editForm.description"
                                        rows="4"
                                        class="px-3 py-2 mt-1 w-full text-gray-800 rounded-lg border border-gray-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/40 focus:border-blue-400"
                                        placeholder="Tulis deskripsi singkat"
                                    ></textarea>
                                </div>

                                <div
                                    class="grid grid-cols-1 gap-4 sm:grid-cols-2"
                                >
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700"
                                            >Gambar (opsional)</label
                                        >
                                        <input
                                            type="file"
                                            accept="image/*"
                                            @change="handleFileChange"
                                            class="block mt-1 w-full text-sm"
                                        />
                                        <p class="mt-1 text-xs text-gray-500">
                                            Format: JPG/PNG. Ukuran maksimal
                                            mengikuti validasi server.
                                        </p>
                                    </div>
                                    <div
                                        class="flex gap-3 justify-start items-end"
                                    >
                                        <div class="text-sm text-gray-500">
                                            Preview:
                                        </div>
                                        <img
                                            v-if="editForm.previewUrl"
                                            :src="editForm.previewUrl"
                                            alt="Preview"
                                            class="object-cover w-28 h-28 rounded border"
                                        />
                                        <div
                                            v-else
                                            class="flex justify-center items-center w-28 h-28 text-xs text-gray-500 bg-gray-50 rounded border"
                                        >
                                            Tidak ada
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div
                                class="flex gap-3 justify-end px-5 py-4 bg-gray-50 rounded-b-xl border-t"
                            >
                                <button
                                    @click="closeEditModal"
                                    class="px-4 h-10 text-gray-700 rounded-lg border border-gray-300 hover:bg-gray-100"
                                >
                                    Batal
                                </button>
                                <button
                                    :disabled="isUpdating || !editForm.title"
                                    @click="updateReport"
                                    class="px-4 h-10 text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed"
                                >
                                    <span v-if="isUpdating">Menyimpan...</span>
                                    <span v-else>Simpan Perubahan</span>
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>
            </transition>
        </Teleport>
        <!-- /Edit Modal -->
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/Pagination.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import SearchableSelect from "@/Components/common/SearchableSelect.vue";
import ReportComments from "@/Components/Reports/ReportComments.vue";
import SearchIcon from "@/Components/icons/SearchIcon.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import { useAuth } from "@/Composables/useAuth";
// import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue"; // jika tombol tambah dipakai

import { ref, watch, reactive, nextTick, onBeforeUnmount, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";

const { can } = useAuth();

defineOptions({ layout: AppLayout });

const props = defineProps({
    reports: Object,
    sort_by: String,
    sort_direction: String,
    search: String,
    filters: Object,
    employees: Array,
    departments: Array,
    branches: Array,
});

// Use computed to always get fresh props data
const reports = computed(() => props.reports);
const searchQuery = ref(props.search || "");
const sortBy = ref(props.sort_by || "created_at");
const sortDirection = ref(props.sort_direction || "desc");
const perPage = ref(props.reports?.per_page ?? 10);

// Filter states
const filters = reactive({
    employee_id: props.filters?.employee_id || "",
    department_id: props.filters?.department_id || "",
    branch_id: props.filters?.branch_id || "",
    date_from: props.filters?.date_from || "",
    date_to: props.filters?.date_to || "",
});

// live search (debounce)
let t = null;
watch(searchQuery, () => {
    clearTimeout(t);
    t = setTimeout(() => {
        fetchReports({ page: 1 });
    }, 400);
});

// Watch filters
watch(
    filters,
    () => {
        fetchReports({ page: 1 });
    },
    { deep: true },
);

// per page change
watch(perPage, () => {
    fetchReports({ page: 1 });
});

// util: format tanggal
function formatDate(dt) {
    if (!dt) return "-";
    return new Date(dt).toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

// fetch with current state
function fetchReports({ page = reports.value?.current_page || 1 } = {}) {
    router.get(
        route("reports.index"),
        {
            page,
            per_page: perPage.value,
            search: searchQuery.value,
            sort_by: sortBy.value,
            sort_direction: sortDirection.value,
            employee_id: filters.employee_id,
            department_id: filters.department_id,
            branch_id: filters.branch_id,
            date_from: filters.date_from,
            date_to: filters.date_to,
        },
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
        },
    );
}

// sorting (hanya field aman: title & created_at)
function changeSort(column) {
    if (!["title", "created_at"].includes(column)) return;
    if (sortBy.value === column) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortBy.value = column;
        sortDirection.value = "asc";
    }
    fetchReports({ page: 1 });
}

// Filter functions
function clearFilters() {
    filters.employee_id = "";
    filters.department_id = "";
    filters.branch_id = "";
    filters.date_from = "";
    filters.date_to = "";
    searchQuery.value = "";
    fetchReports({ page: 1 });
}

// delete flow
const isConfirmModalOpen = ref(false);
const selectedReport = ref(null);

function openConfirmModal(report) {
    selectedReport.value = report;
    isConfirmModalOpen.value = true;
}
function closeConfirmModal() {
    selectedReport.value = null;
    isConfirmModalOpen.value = false;
}
function destroyReport() {
    if (!selectedReport.value) return;
    router.delete(route("reports.destroy", selectedReport.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        preserveScroll: true,
    });
}

// =========================
// DETAIL MODAL (View only)
// =========================
const isDetailModalOpen = ref(false);
const detailReport = reactive({
    id: null,
    title: "",
    description: "",
    image: "",
    user: null,
    created_at: "",
});

function openDetailModal(report) {
    detailReport.id = report.id;
    detailReport.title = report.title || "";
    detailReport.description = report.description || "";
    detailReport.image = report.image || "";
    detailReport.user = report.user || null;
    detailReport.created_at = report.created_at || "";

    isDetailModalOpen.value = true;
    lockBodyScroll();
}

function closeDetailModal() {
    isDetailModalOpen.value = false;
    unlockBodyScroll();

    // Reset data
    detailReport.id = null;
    detailReport.title = "";
    detailReport.description = "";
    detailReport.image = "";
    detailReport.user = null;
    detailReport.created_at = "";
}

function openEditFromDetail() {
    // Copy data from detail to edit form
    const reportData = {
        id: detailReport.id,
        title: detailReport.title,
        description: detailReport.description,
        image: detailReport.image,
        user: detailReport.user,
        created_at: detailReport.created_at,
    };

    // Close detail modal
    closeDetailModal();

    // Open edit modal with data
    nextTick(() => {
        openEditModal(reportData);
    });
}

// =========================
// EDIT DETAIL (Modal + fungsi) - rapi & aman dari header
// =========================
const isEditModalOpen = ref(false);
const isUpdating = ref(false);
const editTitleRef = ref(null);
const previousBodyOverflow = ref("");

const editForm = reactive({
    id: null,
    title: "",
    description: "",
    image: null, // File | null
    previewUrl: "",
    existingImage: "", // string path lama (jika ada)
});

function lockBodyScroll() {
    previousBodyOverflow.value = document.body.style.overflow;
    document.body.style.overflow = "hidden";
}
function unlockBodyScroll() {
    document.body.style.overflow = previousBodyOverflow.value || "";
}

function openEditModal(report) {
    editForm.id = report.id;
    editForm.title = report.title || "";
    editForm.description = report.description || "";
    editForm.image = null;
    editForm.existingImage = report.image ? `/storage/${report.image}` : "";
    // reset preview
    if (editForm.previewUrl && editForm.previewUrl.startsWith("blob:")) {
        URL.revokeObjectURL(editForm.previewUrl);
    }
    editForm.previewUrl = editForm.existingImage;

    isEditModalOpen.value = true;
    lockBodyScroll();

    nextTick(() => {
        editTitleRef.value?.focus();
    });
}

function closeEditModal() {
    isEditModalOpen.value = false;
    unlockBodyScroll();

    if (editForm.previewUrl && editForm.previewUrl.startsWith("blob:")) {
        URL.revokeObjectURL(editForm.previewUrl);
    }
    editForm.id = null;
    editForm.title = "";
    editForm.description = "";
    editForm.image = null;
    editForm.previewUrl = "";
    editForm.existingImage = "";
}

onBeforeUnmount(() => {
    // cleanup blob URL + pastikan scroll unlock
    if (editForm.previewUrl && editForm.previewUrl.startsWith("blob:")) {
        URL.revokeObjectURL(editForm.previewUrl);
    }
    unlockBodyScroll();
});

function handleFileChange(e) {
    const file = e.target.files?.[0];
    editForm.image = file || null;
    if (editForm.previewUrl && editForm.previewUrl.startsWith("blob:")) {
        URL.revokeObjectURL(editForm.previewUrl);
    }
    editForm.previewUrl = file
        ? URL.createObjectURL(file)
        : editForm.existingImage;
}

function updateReport() {
    if (!editForm.id) return;
    isUpdating.value = true;

    const fd = new FormData();
    fd.append("_method", "PUT");
    fd.append("title", editForm.title);
    fd.append("description", editForm.description || "");
    if (editForm.image) {
        fd.append("image", editForm.image);
    }

    router.post(route("reports.update", editForm.id), fd, {
        preserveScroll: true,
        onSuccess: () => {
            isUpdating.value = false;
            closeEditModal();
            // refresh daftar tanpa mengganti halaman
            fetchReports({});
        },
        onError: () => {
            isUpdating.value = false;
        },
    });
}
</script>

<style scoped>
/* Tabel rapi */
:deep(table thead th) {
    padding: 1rem !important;
    background-color: #f9fafb !important;
    color: #6b7280 !important;
    text-transform: uppercase;
    font-size: 13px !important;
    font-weight: 600 !important;
    letter-spacing: 0.02em !important;
}
:deep(table tbody td) {
    padding: 0.9rem 0.8rem !important;
    font-size: 14px !important;
    color: #1f2937 !important;
    font-weight: 500 !important;
    letter-spacing: 0.02em !important;
    line-height: 1.25rem !important;
}
:deep(tbody tr) {
    transition: background-color 0.15s ease;
}
:deep(tbody tr:hover) {
    background-color: #f3f4f6 !important;
}

/* Filter row styling */
:deep(thead tr:nth-child(2) th) {
    padding: 0.5rem !important;
    background-color: #ffffff !important;
    font-weight: normal !important;
    text-transform: none !important;
}

/* Compact styling for filter select */
:deep(.filter-select .multiselect) {
    min-height: 32px;
    font-size: 0.75rem;
}

:deep(.filter-select .multiselect__tags) {
    min-height: 32px;
    padding: 4px 32px 0 6px;
    font-size: 0.75rem;
}

:deep(.filter-select .multiselect__input) {
    font-size: 0.75rem;
    padding: 0;
    margin-bottom: 4px;
}

:deep(.filter-select .multiselect__single) {
    font-size: 0.75rem;
    margin-bottom: 4px;
}

:deep(.filter-select .multiselect__placeholder) {
    font-size: 0.75rem;
    margin-bottom: 4px;
}

:deep(.filter-select .multiselect__select) {
    height: 32px;
    padding: 2px 6px;
}

:deep(.filter-select .multiselect__option) {
    font-size: 0.75rem;
    padding: 8px 10px;
    min-height: 32px;
}

/* Animasi modal */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
.pop-enter-active,
.pop-leave-active {
    transition:
        transform 0.18s ease,
        opacity 0.18s ease;
}
.pop-enter-from,
.pop-leave-to {
    transform: translateY(6px) scale(0.98);
    opacity: 0;
}
</style>
