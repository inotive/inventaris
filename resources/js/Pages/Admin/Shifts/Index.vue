<template>
    <Head title="Shift Kerja" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
            <button
                v-if="can('shifts.create')"
                @click="openAddModal"
                type="button"
                class="flex items-center gap-2 px-3 py-2 text-white bg-blue-500 rounded"
            >
                <PlusSquareIcon />
                <span class="hidden text-sm md:block">Tambah Shift</span>
            </button>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col gap-2 px-8 py-1 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Daftar Shift Kerja
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative py-2">
                        <div class="absolute -translate-y-1/2 left-4 top-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari shift kerja"
                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[200px]"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="p-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
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
                                class="p-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Kode
                                    </p>
                                </div>
                            </th>
                            <th
                                @click="changeSort('name')"
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Kategori
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
                                @click="changeSort('start_time')"
                                class="p-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Waktu Masuk
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'start_time' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'start_time' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('end_time')"
                                class="p-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Waktu Selesai
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'end_time' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'end_time' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('late_tolerance')"
                                class="p-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Batas Waktu Keterlambatan
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'late_tolerance' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'late_tolerance' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Mulai Lembur
                                    </p>
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Pola/Hari
                                    </p>
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Durasi Kerja
                                    </p>
                                </div>
                            </th>
                            <th
                                v-if="can('shifts.edit') || can('shifts.delete')"
                                class="p-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
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
                        <tr
                            v-if="shifts.data && shifts.data.length > 0"
                            v-for="(shift, index) in shifts.data"
                            :key="shift.id"
                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <td
                                class="p-2.5 border-y border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center justify-center whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{
                                            (shifts.current_page - 1) *
                                                shifts.per_page +
                                            index +
                                            1
                                        }}.
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border-y border-gray-200 dark:border-gray-600"
                            >
                                <div class="flex items-center justify-center whitespace-nowrap">
                                    <p class="font-medium text-gray-700 dark:text-gray-300">
                                        {{ shift.code || ('S' + ((shifts.current_page - 1) * shifts.per_page + index + 1)) }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 px-8 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap"
                                >
                                    <div class="flex flex-col leading-tight">
                                        <p
                                            class="font-medium text-gray-800 dark:text-white/90"
                                        >
                                            {{ shift.name }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center justify-center px-3 whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ formatLocalTime(shift.start_time) }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center justify-center px-3 whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ formatLocalTime(shift.end_time) }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center justify-center px-3 whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{
                                            formatLocalTime(
                                                shift.late_tolerance
                                            )
                                        }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center justify-center px-3 whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ formatLocalTime(shift.overtime_start) }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center justify-center px-3 whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ summarizeWeekly(shift.weekly_pattern) }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center justify-center whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ shift.duration }}
                                    </p>
                                </div>
                            </td>
                            <td
                                v-if="can('shifts.edit') || can('shifts.delete')"
                                class="py-2.5 px-8 border-y border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center gap-3 whitespace-nowrap"
                                >
                                    <button
                                        v-if="can('shifts.edit')"
                                        @click="openEditModal(shift)"
                                        title="Edit"
                                    >
                                        <EditIcon class="text-yellow-500" />
                                    </button>
                                    <button
                                        v-if="can('shifts.delete')"
                                        @click="openConfirmModal(shift)"
                                        title="Hapus"
                                    >
                                        <TrashIcon class="text-red-500" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-else>
                            <td
                                :colspan="(can('shifts.edit') || can('shifts.delete')) ? 10 : 9"
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>

                <Modal
                    :show="isModalOpen"
                    :title="isEditMode ? `Edit Shift` : 'Tambah Shift'"
                    confirmText="Simpan"
                    maxWidth="lg"
                    @close="closeModal"
                    @confirm="saveShift"
                >
                    <div class="space-y-5">
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Nama Kategori<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <input
                                id="name"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="Masukkan kategori shift kerja"
                            />
                            <div
                                v-if="form.errors.name"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.name[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="code" class="font-semibold text-gray-900 dark:text-white">
                                Kode Shift (opsional)
                            </label>
                            <input
                                id="code"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.code"
                                placeholder="Contoh: S1"
                                @input="form.code = (form.code || '').toUpperCase()"
                            />
                            <p class="text-xs text-gray-500">Kosongkan untuk generate otomatis (S1, S2, ...)</p>
                            <div v-if="form.errors.code" class="text-sm text-red-500">
                                {{ form.errors.code[0] }}
                            </div>
                        </div>
                        <div class="flex justify-between text-sm gap-x-5">
                            <label
                                for="start_time"
                                class="text-center text-gray-900 dark:text-white"
                            >
                                <span class="font-semibold">Waktu Masuk</span>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 end-0 top-0 flex items-center pe-2.5 pointer-events-none"
                                    >
                                        <ClockIcon class="text-blue-500" />
                                    </div>
                                    <input
                                        v-model="form.start_time"
                                        id="start_time"
                                        type="time"
                                        class="w-full border leading-none text-sm rounded-lg placeholder-gray-500 text-gray-600 placeholder:font-normal font-medium border-gray-400 dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 ps-2.5"
                                    />
                                </div>
                                <div
                                    v-if="form.errors.start_time"
                                    class="text-sm text-red-500"
                                >
                                    {{ form.errors.start_time[0] }}
                                </div>
                            </label>
                            <label
                                for="end_time"
                                class="text-center text-gray-900 dark:text-white"
                            >
                                <span class="font-semibold">Waktu Selesai</span>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 end-0 top-0 flex items-center pe-2.5 pointer-events-none"
                                    >
                                        <ClockIcon class="text-blue-500" />
                                    </div>
                                    <input
                                        v-model="form.end_time"
                                        id="end_time"
                                        type="time"
                                        class="w-full border leading-none text-sm rounded-lg placeholder-gray-500 text-gray-600 placeholder:font-normal font-medium border-gray-400 dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 ps-2.5"
                                    />
                                </div>
                                <div
                                    v-if="form.errors.end_time"
                                    class="text-sm text-red-500"
                                >
                                    {{ form.errors.end_time[0] }}
                                </div>
                            </label>
                            <label
                                for="late_tolerance"
                                class="text-center text-gray-900 dark:text-white"
                            >
                                <span class="font-semibold">Batas Waktu Keterlambatan</span>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 end-0 top-0 flex items-center pe-2.5 pointer-events-none"
                                    >
                                        <ClockIcon class="text-blue-500" />
                                    </div>
                                    <input
                                        v-model="form.late_tolerance"
                                        id="late_tolerance"
                                        type="time"
                                        class="w-full border leading-none text-sm rounded-lg placeholder-gray-500 text-gray-600 placeholder:font-normal font-medium border-gray-400 dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 ps-2.5"
                                    />
                                </div>
                                <div
                                    v-if="form.errors.late_tolerance"
                                    class="text-sm text-red-500"
                                >
                                    {{ form.errors.late_tolerance[0] }}
                                </div>
                            </label>
                        </div>
                        <div class="flex justify-between text-sm gap-x-5">
                            <label
                                for="overtime_start"
                                class="text-center text-gray-900 dark:text-white"
                            >
                                <span class="font-semibold">Mulai Lembur</span>
                                <div class="relative">
                                    <div
                                        class="absolute inset-y-0 end-0 top-0 flex items-center pe-2.5 pointer-events-none"
                                    >
                                        <ClockIcon class="text-blue-500" />
                                    </div>
                                    <input
                                        v-model="form.overtime_start"
                                        id="overtime_start"
                                        type="time"
                                        class="w-full border leading-none text-sm rounded-lg placeholder-gray-500 text-gray-600 placeholder:font-normal font-medium border-gray-400 dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 ps-2.5"
                                    />
                                </div>
                                <div v-if="form.errors.overtime_start" class="text-sm text-red-500">
                                    {{ form.errors.overtime_start[0] }}
                                </div>
                            </label>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="font-semibold text-gray-900 dark:text-white">Pola/Hari Kerja</div>
                            <div class="flex items-center gap-2 mb-1">
                                <button type="button" class="px-2 py-1 text-xs border rounded bg-slate-100" @click="applyWeekdayPreset">Preset Senin–Jumat</button>
                                <button type="button" class="px-2 py-1 text-xs border rounded bg-slate-100" @click="selectAllDays(true)">Pilih Semua</button>
                                <button type="button" class="px-2 py-1 text-xs border rounded bg-slate-100" @click="selectAllDays(false)">Kosongkan</button>
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.weekly_pattern.mon"/> Senin</label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.weekly_pattern.tue"/> Selasa</label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.weekly_pattern.wed"/> Rabu</label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.weekly_pattern.thu"/> Kamis</label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.weekly_pattern.fri"/> Jumat</label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.weekly_pattern.sat"/> Sabtu</label>
                                <label class="inline-flex items-center gap-2"><input type="checkbox" v-model="form.weekly_pattern.sun"/> Minggu</label>
                            </div>
                            <div v-if="form.errors.weekly_pattern" class="text-sm text-red-500">
                                {{ form.errors.weekly_pattern[0] }}
                            </div>
                        </div>
                    </div>
                </Modal>
                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus Shift Kerja"
                    confirmText="Ya, Hapus!"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>

            <Pagination
                v-if="shifts.data && shifts.data.length > 0"
                :pagination="shifts"
            />
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import SearchIcon from "@/Components/icons/SearchIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import ClockIcon from "@/Components/icons/ClockIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { ref, watch } from "vue";
import { useForm, router, Head } from "@inertiajs/vue3";
import { useAuth } from "@/Composables/useAuth";

const { can } = useAuth();

const breadcrumbs = [
    { label: "Konfigurasi" },
    { label: "Manajemen Shift Kerja" },
];

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    shifts: Object,
    search: String,
    sortBy: String,
    sortDirection: String,
});

function formatLocalTime(time) {
    if (!time) return "";

    // Tambahkan tanggal dummy + zona waktu Makassar
    const date = new Date(`1970-01-01T${time}+08:00`);

    return date
        .toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
            hour12: false,
        })
        .replace(":", ".");
}

function fetchShift({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    router.get(
        route("shifts.index"),
        {
            search: search.value,
            sortBy,
            sortDirection,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
}

const search = ref(props.search || "");

let timeout = null;
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        fetchShift();
    }, 400);
});

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchShift({ sortBy: column, sortDirection: direction });
}

const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    code: "",
    name: "",
    start_time: "",
    end_time: "",
    late_tolerance: "",
    overtime_start: "",
    weekly_pattern: { mon: true, tue: true, wed: true, thu: true, fri: true, sat: false, sun: false },
});

// Buka modal untuk tambah
function openAddModal() {
    if (!can('shifts.create')) return;
    form.reset();
    form.overtime_start = "";
    form.weekly_pattern = { mon: true, tue: true, wed: true, thu: true, fri: true, sat: false, sun: false };
    isEditMode.value = false;
    isModalOpen.value = true;
}

// Buka modal untuk edit
function openEditModal(shift) {
    if (!can('shifts.edit')) return;
    form.id = shift.id;
    form.code = shift.code;
    form.name = shift.name;
    form.start_time = shift.start_time;
    form.end_time = shift.end_time;
    form.late_tolerance = shift.late_tolerance;
    form.overtime_start = shift.overtime_start;
    form.weekly_pattern = Object.assign({ mon: false, tue: false, wed: false, thu: false, fri: false, sat: false, sun: false }, shift.weekly_pattern || {});
    selectedItem.value = shift;
    isEditMode.value = true;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    selectedItem.value = null;
    form.reset();
    form.clearErrors();
}

// Simpan (otomatis create/update)
function saveShift() {
    if (isEditMode.value) {
        if (!can('shifts.edit')) return;
        form.put(route("shifts.update", form.id), {
            onSuccess: closeModal,
        });
    } else {
        if (!can('shifts.create')) return;
        form.post(route("shifts.store"), {
            onSuccess: closeModal,
        });
    }
}

// destroy
const selectedItem = ref(null);

const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    if (!can('shifts.delete')) return;
    selectedItem.value = item;
    isConfirmModalOpen.value = true;
};
const closeConfirmModal = () => {
    selectedItem.value = null;
    isConfirmModalOpen.value = false;
};
const destroyData = () => {
    if (!can('shifts.delete')) return;
    router.delete(route("shifts.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        preserveScroll: true,
    });
};

// Helpers for weekly pattern presets
function applyWeekdayPreset() {
    form.weekly_pattern = { mon: true, tue: true, wed: true, thu: true, fri: true, sat: false, sun: false };
}
function selectAllDays(val) {
    form.weekly_pattern = { mon: val, tue: val, wed: val, thu: val, fri: val, sat: val, sun: val };
}

// Ringkas pola mingguan menjadi label singkat untuk tampilan tabel
function summarizeWeekly(pattern) {
    const p = pattern || {};
    const order = ["mon", "tue", "wed", "thu", "fri", "sat", "sun"];
    const labels = {
        mon: "Sen",
        tue: "Sel",
        wed: "Rab",
        thu: "Kam",
        fri: "Jum",
        sat: "Sab",
        sun: "Min",
    };

    const selected = order.filter((k) => !!p[k]);

    if (selected.length === 0) return "—";
    if (selected.length === 7) return "Semua Hari";

    // Weekdays only preset
    const isWeekdays = order.slice(0, 5).every((k) => !!p[k]) && !p.sat && !p.sun;
    if (isWeekdays) return "Sen–Jum";

    return selected.map((k) => labels[k]).join(", ");
}
</script>
