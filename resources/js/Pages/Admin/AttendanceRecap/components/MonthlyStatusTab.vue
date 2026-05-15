<template>
    <div class="overflow-auto" data-simplebar>
        <table class="min-w-full text-sm">
            <thead>
                <!-- Row 1: Main Headers with Rowspan & Colspan -->
                <tr>
                    <th
                        rowspan="2"
                        class="p-3 bg-gray-100 border-gray-200 border-y"
                    >
                        <div class="font-medium text-center text-gray-600">
                            No
                        </div>
                    </th>
                    <th
                        rowspan="2"
                        class="p-3 bg-gray-100 border border-gray-200"
                    >
                        <div class="font-medium text-left text-gray-600">
                            Karyawan
                        </div>
                    </th>
                    <th
                        rowspan="2"
                        class="p-3 bg-gray-100 border border-gray-200"
                    >
                        <div class="font-medium text-center text-gray-600">
                            Hari<br />Terjadwal
                        </div>
                    </th>
                    <th
                        colspan="5"
                        class="p-3 bg-emerald-50 border border-gray-200"
                    >
                        <div class="font-medium text-center text-emerald-700">
                            Hadir
                        </div>
                    </th>
                    <th
                        colspan="2"
                        class="p-3 bg-rose-50 border border-gray-200"
                    >
                        <div class="font-medium text-center text-rose-700">
                            Tidak Hadir
                        </div>
                    </th>
                    <th
                        rowspan="2"
                        class="p-3 bg-blue-50 border border-gray-200"
                    >
                        <div class="font-medium text-center text-blue-700">
                            % Kehadiran
                        </div>
                    </th>
                    <th
                        rowspan="2"
                        class="p-3 bg-green-50 border border-gray-200"
                    >
                        <div class="font-medium text-center text-green-700">
                            % Tepat Waktu
                        </div>
                    </th>
                    <th
                        rowspan="2"
                        class="p-3 bg-gray-100 border border-gray-200"
                        v-for="d in totalDays"
                        :key="'d' + d"
                    >
                        <div class="font-medium text-center text-gray-600">
                            {{ String(d).padStart(2, "0") }}
                        </div>
                    </th>
                </tr>
                <!-- Row 2: Sub Headers -->
                <tr>
                    <!-- Hadir Sub-columns -->
                    <th class="p-2 bg-emerald-50 border border-gray-200">
                        <div
                            class="text-xs font-medium text-center text-emerald-600"
                        >
                            On Time
                        </div>
                    </th>
                    <th class="p-2 bg-emerald-50 border border-gray-200">
                        <div
                            class="text-xs font-medium text-center text-emerald-600"
                        >
                            Berjalan
                        </div>
                    </th>
                    <th class="p-2 bg-emerald-50 border border-gray-200">
                        <div
                            class="text-xs font-medium text-center text-amber-600"
                        >
                            Terlambat
                        </div>
                    </th>
                    <th class="p-2 bg-emerald-50 border border-gray-200">
                        <div
                            class="text-xs font-medium text-center text-sky-600"
                        >
                            Cuti
                        </div>
                    </th>
                    <th class="p-2 bg-emerald-50 border border-gray-200">
                        <div
                            class="text-xs font-medium text-center text-fuchsia-600"
                        >
                            Sakit
                        </div>
                    </th>
                    <!-- Tidak Hadir Sub-columns -->
                    <th class="p-2 bg-rose-50 border border-gray-200">
                        <div
                            class="text-xs font-medium text-center text-rose-600"
                        >
                            Alpha
                        </div>
                    </th>
                    <th class="p-2 bg-rose-50 border border-gray-200">
                        <div
                            class="text-xs font-medium text-center text-gray-600"
                        >
                            Lainnya
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                <template v-if="rows?.data && rows.data.length">
                    <tr
                        v-for="(row, idx) in rows.data"
                        :key="row.id"
                        class="border-b border-gray-200"
                    >
                        <td class="p-3 text-center">
                            {{
                                (rows.current_page - 1) * rows.per_page +
                                idx +
                                1
                            }}
                        </td>
                        <td class="p-3">
                            <div class="flex gap-3 items-center">
                                <template v-if="row.employee.photo_url">
                                    <img
                                        :src="
                                            row.employee.photo_url
                                                ? `/storage/${row.employee.photo_url}`
                                                : ''
                                        "
                                        alt="avatar"
                                        class="object-cover w-8 h-8 rounded-full cursor-zoom-in"
                                        @click="openAvatarPreview(row.employee)"
                                    />
                                </template>
                                <template v-else>
                                    <button
                                        type="button"
                                        @click="openAvatarPreview(row.employee)"
                                        class="inline-flex"
                                    >
                                        <AvatarInitials
                                            :name="row.employee.name"
                                            :gender="row.employee.gender || ''"
                                            :size="32"
                                        />
                                    </button>
                                </template>
                                <div class="flex flex-col">
                                    <span class="font-medium text-gray-800">{{
                                        row.employee.name
                                    }}</span>
                                    <span class="text-xs text-gray-500">{{
                                        row.employee.role || "Staff"
                                    }}</span>
                                </div>
                            </div>
                        </td>
                        <!-- Jumlah Hari Kerja -->
                        <td
                            class="p-3 font-semibold text-center text-gray-700 bg-gray-50"
                        >
                            {{ row.work_days || 0 }}
                        </td>
                        <!-- Hadir Group -->
                        <td
                            class="p-3 text-center text-emerald-600 transition-colors cursor-pointer bg-emerald-50/30 hover:bg-emerald-100/50"
                            @click="openRecapModal(row, 'H', 'On Time')"
                        >
                            {{ row.recap?.H || 0 }}
                        </td>
                        <td
                            class="p-3 text-center text-amber-600 transition-colors cursor-pointer bg-emerald-50/30 hover:bg-amber-100/50"
                            @click="openRecapModal(row, 'B', 'RUNNING')"
                        >
                            {{ row.recap?.B || 0 }}
                        </td>
                        <td
                            class="p-3 text-center text-amber-600 transition-colors cursor-pointer bg-emerald-50/30 hover:bg-amber-100/50"
                            @click="openRecapModal(row, 'T', 'Terlambat')"
                        >
                            {{ row.recap?.T || 0 }}
                        </td>
                        <td
                            class="p-3 text-center text-sky-600 transition-colors cursor-pointer bg-emerald-50/30 hover:bg-sky-100/50"
                            @click="openRecapModal(row, 'C', 'Cuti')"
                        >
                            {{ row.recap?.C || 0 }}
                        </td>
                        <td
                            class="p-3 text-center text-fuchsia-600 transition-colors cursor-pointer bg-emerald-50/30 hover:bg-fuchsia-100/50"
                            @click="openRecapModal(row, 'S', 'Sakit')"
                        >
                            {{ row.recap?.S || 0 }}
                        </td>
                        <!-- Tidak Hadir Group -->
                        <td
                            class="p-3 text-center text-rose-600 transition-colors cursor-pointer bg-rose-50/30 hover:bg-rose-100/50"
                            @click="openRecapModal(row, 'A', 'Alpha')"
                        >
                            {{ row.recap?.A || 0 }}
                        </td>
                        <td
                            class="p-3 text-center transition-colors cursor-pointer bg-rose-50/30 hover:bg-gray-100/50"
                            @click="openRecapModal(row, 'I', 'Izin')"
                        >
                            {{ row.recap?.I || 0 }}
                        </td>
                        <!-- % Kehadiran -->
                        <td
                            class="p-3 font-semibold text-center bg-blue-50/30"
                            :class="
                                getAttendancePercentageClass(
                                    calculateAttendancePercentage(row),
                                )
                            "
                        >
                            {{ calculateAttendancePercentage(row) }}%
                        </td>
                        <!-- % Tepat Waktu -->
                        <td
                            class="p-3 font-semibold text-center bg-green-50/30"
                            :class="
                                getOnTimePercentageClass(
                                    calculateOnTimePercentage(row),
                                )
                            "
                        >
                            {{ calculateOnTimePercentage(row) }}%
                        </td>
                        <td
                            class="p-3 text-center"
                            v-for="d in totalDays"
                            :key="'bd' + row.id + '-' + d"
                        >
                            <span
                                :class="
                                    badgeClass(getDisplayStatus(row.days?.[d]))
                                "
                                @click="openModal(row, d)"
                                class="transition-opacity cursor-pointer hover:opacity-80"
                                >{{ getDisplayStatus(row.days?.[d]) }}</span
                            >
                        </td>
                    </tr>
                </template>
                <tr v-else>
                    <td
                        :colspan="2 + 6 + 1 + totalDays"
                        class="py-6 text-center text-gray-500"
                    >
                        Tidak ada data
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <Pagination
        v-if="rows?.data && rows.data.length"
        :pagination="rows"
        class="border-t"
    />

    <!-- Modal Detail Kehadiran -->
    <Dialog
        v-model:visible="showModal"
        modal
        dismissableMask
        :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
        :style="{ width: '600px', padding: '6px' }"
        :pt="dialogPt"
    >
        <!-- Header -->
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h3
                    class="text-xl font-semibold text-gray-900 dark:text-gray-100"
                >
                    Detail Kehadiran
                </h3>
            </div>
        </template>
        <!-- Content -->
        <div class="flex flex-col gap-6">
            <!-- Employee Info -->
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="font-normal text-dark">Nama</label>
                    <p class="font-normal text-gray-500">
                        {{ modalData.employee?.name || "-" }}
                    </p>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-normal text-dark">Departemen</label>
                    <p class="font-normal text-gray-500">
                        {{ modalData.department || "-" }}
                    </p>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-normal text-dark">Tanggal</label>
                    <p class="font-normal text-gray-500">
                        {{ formatModalDate(modalData.date) }}
                    </p>
                </div>
            </div>

            <!-- Status Info -->
            <div class="flex flex-col gap-2">
                <label class="font-normal text-dark">Status Kehadiran</label>
                <div class="flex gap-3 items-center">
                    <span
                        :class="badgeClass(modalData.status)"
                        class="text-sm"
                        >{{ modalData.status || "-" }}</span
                    >
                    <span class="text-sm font-medium text-gray-600">
                        {{ getStatusDescription(modalData.status) }}
                    </span>
                </div>
            </div>

            <!-- Time Info -->
            <div
                v-if="modalData.checkIn || modalData.checkOut"
                class="flex flex-col gap-4"
            >
                <label class="font-semibold text-dark">Waktu Kehadiran</label>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700"
                            >Jam Masuk</label
                        >
                        <p class="font-medium text-gray-900">
                            {{ modalData.checkIn || "-" }}
                        </p>
                        <span
                            v-if="modalData.status_masuk"
                            :class="badgeClassMasuk(modalData.status_masuk)"
                            class="text-xs"
                            >{{ modalData.status_masuk || "-" }}</span
                        >
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700"
                            >Jam Keluar</label
                        >
                        <p class="font-medium text-gray-900">
                            {{ modalData.checkOut || "-" }}
                        </p>
                        <span
                            v-if="modalData.status_keluar"
                            :class="badgeClassKeluar(modalData.status_keluar)"
                            class="text-xs"
                            >{{ modalData.status_keluar || "-" }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Jadwal Shift -->
            <div v-if="modalData.shift" class="flex flex-col gap-2">
                <label class="font-normal text-dark">Jadwal Shift</label>
                <div class="flex flex-col gap-2">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm text-gray-500">Nama Shift</span>
                        <p class="font-normal text-gray-700">
                            {{ modalData.shift.name || "-" }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-sm text-gray-500">Jam Kerja</span>
                        <p class="font-normal text-gray-700">
                            {{ formatShiftTime(modalData.shift.start_time) }}
                            -
                            {{ formatShiftTime(modalData.shift.end_time) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="modalData.notes" class="flex flex-col gap-2">
                <label class="font-normal text-dark">Catatan</label>
                <p class="font-normal text-gray-500">
                    {{ modalData.notes }}
                </p>
            </div>
        </div>
    </Dialog>

    <!-- Modal Recap List -->
    <Dialog
        v-model:visible="showRecapModal"
        modal
        dismissableMask
        :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
        :style="{ width: '700px', padding: '6px' }"
        :pt="dialogPt"
    >
        <!-- Header -->
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h3
                    class="text-xl font-semibold text-gray-900 dark:text-gray-100"
                >
                    Daftar {{ recapModalData.statusLabel }}
                </h3>
            </div>
        </template>
        <!-- Content -->
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-2">
                <div class="flex flex-col gap-1">
                    <label class="text-sm text-gray-500">Karyawan</label>
                    <p class="font-medium text-gray-900">
                        {{ recapModalData.employeeName }}
                    </p>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-sm text-gray-500">Total</label>
                    <p class="font-medium text-gray-900">
                        {{ recapModalData.dates.length }} hari
                    </p>
                </div>
            </div>

            <div class="overflow-y-auto max-h-96">
                <div v-if="recapModalData.dates.length > 0" class="space-y-2">
                    <div
                        v-for="(item, idx) in recapModalData.dates"
                        :key="idx"
                        class="p-3 bg-gray-50 rounded-lg border border-gray-200 transition-colors hover:bg-gray-100"
                    >
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">
                                    {{ formatRecapDate(item.date) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ item.dayName }}
                                </p>
                                <div
                                    v-if="item.jam_masuk || item.jam_keluar"
                                    class="flex gap-3 mt-2 text-xs"
                                >
                                    <span
                                        v-if="item.jam_masuk"
                                        class="text-gray-600"
                                    >
                                        <span class="font-medium">Masuk:</span>
                                        {{ item.jam_masuk }}
                                    </span>
                                    <span
                                        v-if="item.jam_keluar"
                                        class="text-gray-600"
                                    >
                                        <span class="font-medium">Keluar:</span>
                                        {{ item.jam_keluar }}
                                    </span>
                                </div>
                                <div
                                    v-if="
                                        item.status_masuk &&
                                        (item.status === 'H' ||
                                            item.status === 'T')
                                    "
                                    class="mt-1"
                                >
                                    <span
                                        class="px-2 py-0.5 text-xs rounded"
                                        :class="
                                            item.status_masuk === 'TERLAMBAT'
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-emerald-100 text-emerald-700'
                                        "
                                    >
                                        {{ item.status_masuk }}
                                    </span>
                                </div>
                            </div>
                            <span
                                :class="badgeClass(item.status)"
                                class="ml-2 text-xs"
                            >
                                {{ item.status }}
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else class="py-8 text-center text-gray-500">
                    Tidak ada data
                </div>
            </div>
        </div>
    </Dialog>

    <!-- Avatar Preview Modal -->
    <Dialog
        v-model:visible="showAvatarModal"
        modal
        dismissableMask
        :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
        :style="{ width: '500px', padding: '6px' }"
        :pt="dialogPt"
    >
        <!-- Header -->
        <template #header>
            <div class="flex justify-between items-center w-full">
                <h3
                    class="text-xl font-semibold text-gray-900 dark:text-gray-100"
                >
                    Foto Karyawan
                </h3>
            </div>
        </template>
        <!-- Content -->
        <div class="flex flex-col items-center justify-center py-4">
            <template v-if="avatarEmployee?.photo_url">
                <img
                    :src="
                        avatarEmployee?.photo_url
                            ? `/storage/${avatarEmployee.photo_url}`
                            : ''
                    "
                    alt="avatar"
                    class="object-cover w-64 h-64 rounded-xl shadow-md"
                />
            </template>
            <template v-else>
                <AvatarInitials
                    :name="avatarEmployee?.name"
                    :gender="avatarEmployee?.gender || ''"
                    :size="160"
                />
            </template>
            <div class="mt-3 text-sm text-gray-600">
                {{ avatarEmployee?.name }}
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { computed, ref } from "vue";
import Pagination from "@/Components/common/Pagination.vue";
import AvatarInitials from "@/Components/common/AvatarInitials.vue";
import { Dialog } from "primevue";
const props = defineProps({
    rows: { type: Object, required: true },
    year: { type: Number, default: () => new Date().getFullYear() },
    month: { type: Number, default: () => new Date().getMonth() + 1 },
});

console.log("All rows:", props.rows);

// Debug employee 4
if (props.rows?.data) {
    const employee4 = props.rows.data.find((row) => row.id === 4);
    if (employee4) {
        console.log("========== EMPLOYEE 4 DATA (Frontend) ==========");
        console.log("Employee:", employee4.employee);
        console.log("Work Days:", employee4.work_days);
        console.log("Recap:", employee4.recap);
        console.log("Days:", employee4.days);
        console.log("================================================");
    }
}
// Modal state
const showModal = ref(false);
const modalData = ref({
    employee: null,
    department: null,
    date: null,
    status: null,
    checkIn: null,
    checkOut: null,
    status_masuk: null,
    status_keluar: null,
    notes: null,
    shift: null,
});

// Recap Modal state
const showRecapModal = ref(false);
const recapModalData = ref({
    employeeName: "",
    statusLabel: "",
    statusCode: "",
    dates: [],
});

// Recap Modal functions
function openRecapModal(row, statusCode, statusLabel) {
    const dates = [];

    // Loop through all days to find matching status
    if (row.days) {
        Object.keys(row.days).forEach((day) => {
            const dayData = row.days[day];
            if (!dayData) return;

            let isMatch = false;
            console.log("dayData:", dayData);

            // Logic untuk mencocokkan status
            if (statusCode === "H") {
                // On Time: status hadir DAN status_masuk === "ON TIME"
                isMatch =
                    dayData.status === "H" &&
                    dayData.status_masuk === "ON TIME";
            } else if (statusCode === "T") {
                // Terlambat: status hadir DAN status_masuk === "TERLAMBAT"
                isMatch =
                    dayData.status === "H" &&
                    dayData.status_masuk === "TERLAMBAT";
            } else if (statusCode === "B") {
                // Berjalan: status hadir DAN status_masuk === "RUNNING"
                isMatch =
                    dayData.status === "B" &&
                    dayData.jam_keluar === "sedang berjalan";
            } else if (statusCode === "A") {
                // Alpha
                isMatch = dayData.status === "A";
            } else if (statusCode === "C") {
                // Cuti
                isMatch = dayData.status === "C";
            } else if (statusCode === "S") {
                // Sakit
                isMatch = dayData.status === "S";
            } else if (statusCode === "I") {
                // Izin
                isMatch = dayData.status === "I";
            }

            if (isMatch) {
                const dateStr = formatDateFromDay(parseInt(day));
                dates.push({
                    date: dateStr,
                    dayName: getDayName(dateStr),
                    status: statusCode,
                    status_masuk: dayData.status_masuk || null,
                    jam_masuk: dayData.jam_masuk || null,
                    jam_keluar: dayData.jam_keluar || null,
                });
            }
        });
    }

    recapModalData.value = {
        employeeName: row.employee?.name || "-",
        statusLabel: statusLabel,
        statusCode: statusCode,
        dates: dates,
    };

    // Debug log
    console.log(
        "openRecapModal - statusCode:",
        statusCode,
        "statusLabel:",
        statusLabel,
    );
    console.log("openRecapModal - dates found:", dates.length);
    console.log("openRecapModal - dates:", dates);

    showRecapModal.value = true;
}

function closeRecapModal() {
    showRecapModal.value = false;
    recapModalData.value = {
        employeeName: "",
        statusLabel: "",
        statusCode: "",
        dates: [],
    };
}

function formatRecapDate(dateStr) {
    const date = new Date(dateStr);
    const options = { day: "numeric", month: "long", year: "numeric" };
    return date.toLocaleDateString("id-ID", options);
}

function getDayName(dateStr) {
    const date = new Date(dateStr);
    const days = [
        "Minggu",
        "Senin",
        "Selasa",
        "Rabu",
        "Kamis",
        "Jumat",
        "Sabtu",
    ];
    return days[date.getDay()];
}

// Modal functions
function openModal(row, day) {
    const dayData = row.days?.[day];
    if (!dayData) return;

    modalData.value = {
        employee: row.employee,
        department: row.department,
        date: formatDateFromDay(day),
        status: dayData.status,
        checkIn: dayData.jam_masuk,
        checkOut: dayData.jam_keluar,
        status_masuk: dayData.status_masuk,
        status_keluar: dayData.status_keluar,
        notes: dayData.keterangan,
        shift: dayData.shift || null,
    };
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    modalData.value = {
        employee: null,
        department: null,
        date: null,
        status: null,
        checkIn: null,
        checkOut: null,
        status_masuk: null,
        status_keluar: null,
        notes: null,
        shift: null,
    };
}

function formatModalDate(date) {
    if (!date) return "-";
    try {
        return new Date(date).toLocaleDateString("id-ID", {
            weekday: "long",
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    } catch (e) {
        return date;
    }
}

function formatDateFromDay(day) {
    return `${props.year}-${String(props.month).padStart(2, "0")}-${String(
        day,
    ).padStart(2, "0")}`;
}

function formatShiftTime(time) {
    if (!time) return "-";

    // Jika format ISO 8601 (2025-10-15T23:00:00.000000Z)
    if (time.includes("T")) {
        try {
            const date = new Date(time);
            return date.toLocaleTimeString("id-ID", {
                hour: "2-digit",
                minute: "2-digit",
                hour12: false,
                timeZone: "Asia/Makassar",
            });
        } catch (e) {
            return "-";
        }
    }

    // Jika format HH:MM:SS, ambil HH:MM saja
    if (time.includes(":")) {
        return time.substring(0, 5);
    }

    return time;
}

function getDisplayStatus(dayData) {
    if (!dayData) return "-";

    // Jika dayData adalah string, kembalikan langsung
    if (typeof dayData === "string") {
        return dayData;
    }

    // Jika dayData adalah object
    if (typeof dayData === "object") {
        const status = dayData.status;

        // Jika status adalah 'H' (Hadir), cek status_masuk
        if (status === "H") {
            // Cek status_masuk untuk menentukan OT atau T
            if (dayData.status_masuk === "TERLAMBAT") {
                return "T";
            } else if (dayData.status_masuk === "ON TIME") {
                return "OT";
            } else {
                // Jika status_masuk tidak ada, default ke OT
                return "OT";
            }
        }

        // Untuk status lainnya, kembalikan status asli
        return status || "-";
    }

    return "-";
}

function getStatusDescription(status) {
    const descriptions = {
        H: "Hadir",
        OT: "On Time",
        B: "Berjalan",
        T: "Terlambat",
        A: "Belum ada rekam jam",
        C: "Cuti",
        S: "Sakit",
        I: "Izin",
        SH: "Shift (Belum Terjadi)",
        "-": "Tidak ada data",
    };
    return descriptions[status] || "Status tidak diketahui";
}

function getStatusMasukDescription(status) {
    const descriptions = {
        "ON TIME": "On Time",
        TERLAMBAT: "Terlambat",
    };
    return descriptions[status] || "-";
}

//fetch data
//total hari
const totalDays = computed(() => {
    return props.rows?.data &&
        props.rows.data.length > 0 &&
        props.rows.data[0]?.days
        ? Object.keys(props.rows.data[0].days).length
        : 0;
});

// Function to count summary for each status type
function getSummaryCount(row, status) {
    if (!row.days) return 0;
    return Object.values(row.days).filter((day) => day === status).length;
}

function badgeClass(code) {
    const base =
        "inline-flex items-center justify-center w-6 h-6 rounded text-xs font-semibold";
    switch (code) {
        case "H":
            return base + " bg-emerald-100 text-emerald-600"; // hadir
        case "OT":
            return base + " bg-emerald-100 text-emerald-600"; // on time
        case "B":
            return base + " bg-emerald-100 text-emerald-600"; // berjalan
        case "T":
            return base + " bg-amber-100 text-amber-600"; // terlambat
        case "C":
            return base + " bg-sky-100 text-sky-600"; // cuti
        case "I":
            return base + " bg-sky-100 text-sky-600"; // izin
        case "S":
            return base + " bg-fuchsia-100 text-fuchsia-600"; // sakit
        case "A":
            return base + " bg-rose-100 text-rose-600"; // alpa
        case "SH":
            return base + " bg-blue-100 text-blue-600"; // shift (belum terjadi)
        default:
            return base + " text-gray-400";
    }
}

function badgeClassMasuk(code) {
    const base =
        "inline-flex items-center justify-center rounded text-xs font-semibold px-2 py-0.5";
    // px-2 agar width mengikuti content, tidak fixed
    switch (code) {
        case "ON TIME":
            return base + " bg-emerald-100 text-emerald-600";
        case "TERLAMBAT":
            return base + " bg-amber-100 text-amber-600";
        default:
            return base + " text-gray-400";
    }
}

function badgeClassKeluar(code) {
    const base =
        "inline-flex items-center justify-center rounded text-xs font-semibold px-2 py-0.5";
    switch (code) {
        case "PULANG CEPAT":
            return base + " bg-amber-100 text-amber-600";
        case "SESUAI":
            return base + " bg-emerald-100 text-emerald-600";
        default:
            return base + " text-gray-400";
    }
}

function countCode(days, code) {
    if (!days) return 0;
    try {
        return Object.values(days).filter((v) => v === code).length;
    } catch (e) {
        return 0;
    }
}

function countOthers(days) {
    if (!days) return 0;
    const known = new Set(["H", "T", "C", "S", "A", "SH", "-"]);
    try {
        return Object.values(days).filter((v) => !known.has(v || "-")).length;
    } catch (e) {
        return 0;
    }
}

// Calculate attendance percentage
function calculateAttendancePercentage(row) {
    if (!row.recap || !row.work_days || row.work_days === 0) return 0;

    // Total hadir = H (On Time) + B (Berjalan) + T (Terlambat) + S (Sakit) + I (Izin)
    // Cuti TIDAK dihitung dalam presentase kehadiran
    const totalHadir =
        (row.recap.H || 0) +
        (row.recap.B || 0) +
        (row.recap.T || 0) +
        (row.recap.S || 0) +
        (row.recap.I || 0);

    // Persentase kehadiran = (Total Hadir / Hari Terjadwal) * 100
    const percentage = (totalHadir / row.work_days) * 100;

    return Math.round(percentage);
}

// Get color class based on attendance percentage
function getAttendancePercentageClass(percentage) {
    if (percentage >= 90) {
        return "text-emerald-700"; // Excellent
    } else if (percentage >= 75) {
        return "text-blue-700"; // Good
    } else if (percentage >= 60) {
        return "text-amber-700"; // Fair
    } else {
        return "text-rose-700"; // Poor
    }
}

// Calculate on-time percentage
function calculateOnTimePercentage(row) {
    if (!row.recap) return 0;

    // Total hadir (H + T)
    const totalHadir = (row.recap.H || 0) + (row.recap.T || 0);

    if (totalHadir === 0) return 0;

    // Total on time (H)
    const totalOnTime = row.recap.H || 0;

    // Persentase tepat waktu = (Total On Time / Total Hadir) * 100
    const percentage = (totalOnTime / totalHadir) * 100;

    return Math.round(percentage);
}

// Get color class based on on-time percentage
function getOnTimePercentageClass(percentage) {
    if (percentage >= 90) {
        return "text-emerald-700"; // Excellent
    } else if (percentage >= 75) {
        return "text-green-700"; // Good
    } else if (percentage >= 60) {
        return "text-amber-700"; // Fair
    } else {
        return "text-rose-700"; // Poor
    }
}

// Avatar preview modal
const showAvatarModal = ref(false);
const avatarEmployee = ref(null);
function openAvatarPreview(employee) {
    avatarEmployee.value = employee || null;
    showAvatarModal.value = true;
}
function closeAvatarPreview() {
    showAvatarModal.value = false;
    avatarEmployee.value = null;
}

const dialogPt = {
    root: { class: "rounded-2xl" },
    header: { class: "px-6 pt-6 pb-2" },
    content: { class: "px-6 pb-2" },
    footer: { class: "px-6 pb-6" },
};
</script>
