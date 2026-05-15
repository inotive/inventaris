<template>
    <Head title="Detail Checklist" />
    <Toast />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div class="flex gap-2">
                <!-- Dev/Local Only: Manual Reminder Trigger -->
                <button
                    v-if="isDevEnvironment"
                    @click="sendReminderManually"
                    :disabled="isSendingReminder"
                    class="px-3 py-2 text-sm text-white bg-green-600 rounded border hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <svg
                        v-if="isSendingReminder"
                        class="w-4 h-4 animate-spin"
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
                    {{
                        isSendingReminder
                            ? "Mengirim..."
                            : "🔔 Kirim Reminder (Dev)"
                    }}
                </button>
                <Link
                    :href="route('checklists.index')"
                    class="px-3 py-2 text-sm text-gray-700 rounded border"
                    >Kembali
                </Link>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b">
                <h2 class="text-xl font-semibold text-gray-800">
                    Detail Checklist
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Checklist</label
                        >
                        <h3 class="text-gray-800">
                            {{ general.checklist_name ?? "-" }}
                        </h3>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Nomor SOP</label
                        >
                        <h3 class="text-gray-800">
                            {{
                                general.sop_code ??
                                general.inspection_number ??
                                "-"
                            }}
                        </h3>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Kategori Checklist</label
                        >
                        <h3 class="text-gray-800">
                            {{ general.category ?? "-" }}
                        </h3>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Departemen</label
                        >
                        <h3 class="text-gray-800">
                            {{ general.departments ?? "-" }}
                        </h3>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Status</label
                        >
                        <h3 class="text-gray-800">
                            {{ general.status ?? "-" }}
                        </h3>
                    </div>

                    <!-- Pengaturan Reminder Otomatis -->
                    <div
                        class="md:col-span-2 mt-4 pt-4 border-t border-gray-200"
                    >
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Pengaturan Reminder Otomatis
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1 text-sm text-gray-600"
                                    >Status Reminder</label
                                >
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full',
                                        general.reminder_enabled
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-gray-100 text-gray-800',
                                    ]"
                                >
                                    {{
                                        general.reminder_enabled
                                            ? "✓ Aktif"
                                            : "✗ Nonaktif"
                                    }}
                                </span>
                            </div>
                            <div v-if="general.reminder_enabled">
                                <label class="block mb-1 text-sm text-gray-600"
                                    >Jam Pengiriman (WITA)</label
                                >
                                <h3 class="text-gray-800">
                                    {{ general.reminder_time ?? "-" }}
                                </h3>
                            </div>
                            <div v-if="general.reminder_enabled">
                                <label class="block mb-1 text-sm text-gray-600"
                                    >Frekuensi</label
                                >
                                <h3 class="text-gray-800">
                                    {{
                                        formatFrequency(
                                            general.reminder_frequency,
                                        )
                                    }}
                                </h3>
                            </div>
                            <div
                                v-if="
                                    general.reminder_enabled &&
                                    general.reminder_frequency === 'weekly'
                                "
                                class="md:col-span-2"
                            >
                                <label class="block mb-1 text-sm text-gray-600"
                                    >Hari Pengiriman</label
                                >
                                <div class="flex flex-wrap gap-2 mt-1">
                                    <span
                                        v-for="day in formatReminderDays(
                                            general.reminder_days,
                                        )"
                                        :key="day"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800"
                                    >
                                        {{ day }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1 text-sm text-gray-600"
                            >Deskripsi</label
                        >
                        <h3 class="text-gray-800">
                            {{ general.remarks || "-" }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200">
            <!-- Tab Navigation -->
            <div class="px-6 py-4 border-b">
                <ul class="flex gap-4 border-b border-gray-200">
                    <li v-for="tab in tabs" :key="tab.value">
                        <button
                            @click="activeTab = tab.value"
                            :class="[
                                'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                                activeTab === tab.value
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            ]"
                        >
                            {{ tab.label }}
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab: Pertanyaan -->
            <div v-show="activeTab === 'questions'" class="p-4 space-y-3">
                <!-- Controls -->
                <div
                    class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex gap-2 items-center">
                        <input
                            v-model="qSearch"
                            type="text"
                            placeholder="Cari pertanyaan..."
                            class="px-3 w-64 h-9 text-sm rounded border border-gray-300 focus:border-sky-400 focus:ring-sky-200"
                        />
                        <select
                            v-model="qCategory"
                            class="px-2 h-9 text-sm rounded border border-gray-300 focus:border-sky-400 focus:ring-sky-200"
                        >
                            <option value="">Semua Kategori</option>
                            <option
                                v-for="c in questionCategories"
                                :key="c"
                                :value="c"
                            >
                                {{ c }}
                            </option>
                        </select>
                    </div>
                    <div class="flex gap-2 items-center">
                        <button
                            type="button"
                            @click="downloadQuestionsCsv"
                            class="px-3 py-2 text-sm bg-white rounded border hover:bg-gray-50"
                        >
                            Unduh CSV
                        </button>
                    </div>
                </div>

                <div class="overflow-auto" data-simplebar>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr>
                                <th
                                    class="sticky top-0 p-3 bg-gray-100 border-gray-200 border-y"
                                >
                                    <div
                                        class="font-medium text-center text-gray-600"
                                    >
                                        No
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 p-3 bg-gray-100 border border-gray-200"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Pertanyaan
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 p-3 bg-gray-100 border border-gray-200"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Kategori
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 p-3 bg-gray-100 border border-gray-200"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Jawaban
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="filteredQuestions.length">
                                <tr
                                    v-for="(q, idx) in filteredQuestions"
                                    :key="idx"
                                    class="border-b border-gray-200"
                                >
                                    <td class="p-3 text-center">
                                        {{ idx + 1 }}
                                    </td>
                                    <td class="p-3">{{ q.title }}</td>
                                    <td class="p-3">{{ q.category || "-" }}</td>
                                    <td class="p-3">{{ q.answer ?? "-" }}</td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td
                                    colspan="4"
                                    class="py-6 text-center text-gray-500"
                                >
                                    Tidak ada pertanyaan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Responden -->
            <div v-show="activeTab === 'respondents'" class="p-4 space-y-4">
                <!-- Header dengan Search dan Button -->
                <div
                    class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex-1 max-w-md">
                        <input
                            v-model="respondentSearch"
                            @input="debouncedSearchRespondents"
                            type="text"
                            placeholder="Cari responden..."
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                    </div>
                    <button
                        @click="openAddRespondentModal"
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 whitespace-nowrap"
                    >
                        + Tambah Responden
                    </button>
                </div>

                <!-- Daftar Responden -->
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Daftar Responden
                        </h3>
                        <button
                            v-if="selectedRespondents.length > 0"
                            @click="removeMultipleRespondents"
                            :disabled="isProcessing"
                            class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Hapus {{ selectedRespondents.length }} Responden
                        </button>
                    </div>
                    <div class="overflow-auto" data-simplebar>
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr>
                                    <th
                                        class="sticky top-0 p-3 bg-gray-100 border-gray-200 border-y"
                                    >
                                        <div
                                            class="flex items-center justify-center"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="isAllSelected"
                                                @change="toggleSelectAll"
                                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                            />
                                        </div>
                                    </th>
                                    <th
                                        class="sticky top-0 p-3 bg-gray-100 border-gray-200 border-y"
                                    >
                                        <div
                                            class="font-medium text-center text-gray-600"
                                        >
                                            No
                                        </div>
                                    </th>
                                    <th
                                        class="sticky top-0 p-3 bg-gray-100 border border-gray-200"
                                    >
                                        <div
                                            class="font-medium text-left text-gray-600"
                                        >
                                            Nama
                                        </div>
                                    </th>
                                    <th
                                        class="sticky top-0 p-3 bg-gray-100 border border-gray-200"
                                    >
                                        <div
                                            class="font-medium text-left text-gray-600"
                                        >
                                            Departemen
                                        </div>
                                    </th>
                                    <th
                                        class="sticky top-0 p-3 bg-gray-100 border border-gray-200"
                                    >
                                        <div
                                            class="font-medium text-left text-gray-600"
                                        >
                                            Cabang
                                        </div>
                                    </th>
                                    <th
                                        class="sticky top-0 p-3 bg-gray-100 border border-gray-200"
                                    >
                                        <div
                                            class="font-medium text-center text-gray-600"
                                        >
                                            Aksi
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <template v-if="respondents.length">
                                    <tr
                                        v-for="(respondent, idx) in respondents"
                                        :key="respondent.id"
                                        class="border-b border-gray-200 hover:bg-gray-50"
                                        :class="{
                                            'bg-blue-50': isSelected(
                                                respondent.id,
                                            ),
                                        }"
                                    >
                                        <td class="p-3 text-center">
                                            <input
                                                type="checkbox"
                                                :checked="
                                                    isSelected(respondent.id)
                                                "
                                                @change="
                                                    toggleSelect(respondent.id)
                                                "
                                                class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                            />
                                        </td>
                                        <td class="p-3 text-center">
                                            {{
                                                (respondentPage - 1) * 10 +
                                                idx +
                                                1
                                            }}
                                        </td>
                                        <td class="p-3">
                                            {{ respondent.name }}
                                        </td>
                                        <td class="p-3">
                                            {{
                                                respondent.department?.name ||
                                                "-"
                                            }}
                                        </td>
                                        <td class="p-3">
                                            {{ respondent.branch?.name || "-" }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <button
                                                @click="
                                                    removeRespondent(
                                                        respondent.id,
                                                    )
                                                "
                                                :disabled="isProcessing"
                                                class="px-3 py-1 text-xs text-red-600 rounded border border-red-300 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr v-else>
                                    <td
                                        colspan="6"
                                        class="py-6 text-center text-gray-500"
                                    >
                                        <template v-if="isLoadingRespondents"
                                            >Memuat data...</template
                                        >
                                        <template v-else
                                            >Tidak ada responden</template
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="
                            respondents.length > 0 ||
                            respondentPagination.next_cursor ||
                            respondentPagination.prev_cursor
                        "
                        class="flex items-center justify-between mt-4 px-2"
                    >
                        <div class="text-sm text-gray-600">
                            Menampilkan {{ respondents.length }} responden
                            <span v-if="respondentPage > 1">
                                - Halaman {{ respondentPage }}</span
                            >
                        </div>
                        <div class="flex gap-2 items-center">
                            <button
                                @click="loadPreviousRespondents"
                                :disabled="
                                    !respondentPagination.prev_cursor ||
                                    isLoadingRespondents
                                "
                                class="px-3 py-1 text-sm text-gray-700 bg-white rounded-lg border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Sebelumnya
                            </button>
                            <button
                                @click="loadNextRespondents"
                                :disabled="
                                    !respondentPagination.has_more ||
                                    isLoadingRespondents
                                "
                                class="px-3 py-1 text-sm text-gray-700 bg-white rounded-lg border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Selanjutnya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Tambah Responden -->
        <div
            v-if="showAddRespondentModal"
            @click="closeAddRespondentModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        >
            <div
                @click.stop
                class="w-full max-w-2xl bg-white rounded-lg shadow-xl max-h-[90vh] overflow-hidden flex flex-col"
            >
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Tambah Responden
                        </h3>
                        <button
                            @click="closeAddRespondentModal"
                            class="text-gray-400 hover:text-gray-600"
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
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <!-- Cabang -->
                    <div>
                        <label
                            class="block mb-1 text-sm font-medium text-gray-600"
                            >Cabang</label
                        >
                        <Select
                            v-model="respondentForm.branch_id"
                            label="Pilih Cabang"
                            :items="branches"
                            :class="{
                                'border-red-500':
                                    respondentFormErrors.branch_id,
                            }"
                        />
                        <p
                            v-if="respondentFormErrors.branch_id"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ respondentFormErrors.branch_id }}
                        </p>
                    </div>

                    <!-- Departemen -->
                    <div>
                        <label
                            class="block mb-1 text-sm font-medium text-gray-600"
                            >Departemen</label
                        >
                        <Select
                            v-model="respondentForm.department_id"
                            label="Pilih Departemen"
                            :items="filteredDepartments"
                            :class="{
                                'border-red-500':
                                    respondentFormErrors.department_id,
                            }"
                        />
                        <p
                            v-if="respondentFormErrors.department_id"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ respondentFormErrors.department_id }}
                        </p>
                    </div>

                    <!-- Karyawan -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label
                                class="block text-sm font-medium text-gray-600"
                                >Karyawan</label
                            >
                            <button
                                v-if="filteredEmployees.length > 0"
                                @click="selectAllEmployees"
                                type="button"
                                class="text-xs text-blue-600 hover:text-blue-800"
                            >
                                Pilih Semua
                            </button>
                        </div>
                        <SelectMultiple
                            v-model="respondentForm.employee_ids"
                            :items="filteredEmployees"
                            label="Pilih Karyawan"
                            :class="{
                                'border-red-500':
                                    respondentFormErrors.employee_ids,
                            }"
                        />
                        <p
                            v-if="respondentFormErrors.employee_ids"
                            class="mt-1 text-xs text-red-500"
                        >
                            {{ respondentFormErrors.employee_ids }}
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3"
                >
                    <button
                        @click="closeAddRespondentModal"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
                    >
                        Batal
                    </button>
                    <button
                        @click="addRespondents"
                        :disabled="
                            respondentForm.employee_ids.length === 0 ||
                            isProcessing
                        "
                        class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ isProcessing ? "Menambahkan..." : "Tambah" }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import SelectMultiple from "@/Components/form/SelectMultiple.vue";
import Select from "@/Components/form/SelectKategoriCheklist.vue";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
    checklist: Object,
    general: Object,
    questions: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    approvals: { type: Array, default: () => [] },
    employees: { type: Array, default: () => [] },
    respondents: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
});

const breadcrumbs = [
    { label: "Menu Utama" },
    { label: `${props.general?.inspection_number || props.inspection?.id}` },
];

const grouped = computed(() => {
    const map = {};
    (props.questions || []).forEach((q) => {
        const k = q.category || "Umum";
        if (!map[k]) map[k] = [];
        map[k].push(q);
    });
    return Object.entries(map).map(([category, items]) => ({
        category,
        items,
    }));
});

// flat list for tabular design
const flatQuestions = computed(() => {
    return (props.questions || []).map((q) => ({
        title: q.title,
        category: q.category,
        answer: q.answer,
    }));
});

// Filters & helpers for Questions/Activities tabs
const qSearch = ref("");
const qCategory = ref("");
const questionCategories = computed(() => {
    const set = new Set(
        (flatQuestions.value || []).map((q) => q.category).filter(Boolean),
    );
    return Array.from(set).sort();
});
const filteredQuestions = computed(() => {
    let list = flatQuestions.value || [];
    if (qCategory.value)
        list = list.filter((q) => (q.category || "") === qCategory.value);
    if (qSearch.value) {
        const s = qSearch.value.toLowerCase();
        list = list.filter(
            (q) =>
                String(q.title).toLowerCase().includes(s) ||
                String(q.answer ?? "")
                    .toLowerCase()
                    .includes(s),
        );
    }
    return list;
});

const aSearch = ref("");
const filteredActivities = computed(() => {
    let list = (props.activities || []).slice();
    if (aSearch.value) {
        const s = aSearch.value.toLowerCase();
        list = list.filter(
            (a) =>
                String(a.action || "")
                    .toLowerCase()
                    .includes(s) ||
                String(a.by || "")
                    .toLowerCase()
                    .includes(s) ||
                String(a.notes || "")
                    .toLowerCase()
                    .includes(s),
        );
    }
    return list;
});

function downloadQuestionsCsv() {
    const rows = [["No", "Pertanyaan", "Kategori", "Jawaban"]];
    (filteredQuestions.value || []).forEach((q, i) => {
        rows.push([
            String(i + 1),
            String(q.title || ""),
            String(q.category || ""),
            String(q.answer ?? ""),
        ]);
    });
    const csv = rows
        .map((r) =>
            r.map((x) => '"' + String(x).replace(/"/g, '""') + '"').join(","),
        )
        .join("\n");
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `inspection_${props.general?.inspection_number || props.inspection?.id}_questions.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

const toast = useToast();

// Check if current environment is dev/local
const isDevEnvironment = computed(() => {
    const env = import.meta.env.VITE_APP_DEPLOYMENT || "production";
    return ["local", "dev", "development"].includes(env.toLowerCase());
});

const isSendingReminder = ref(false);

// Function to manually trigger reminder (dev/local only)
async function sendReminderManually() {
    if (!isDevEnvironment.value) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Fitur ini hanya tersedia di environment development",
            life: 3000,
        });
        return;
    }

    isSendingReminder.value = true;

    try {
        const response = await fetch(route("checklists.send-reminder"), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                Accept: "application/json",
            },
            credentials: "include",
            body: JSON.stringify({
                checklist_id: props.general?.id || props.checklist?.id,
            }),
        });

        const data = await response.json();

        if (response.ok) {
            toast.add({
                severity: "success",
                summary: "Success",
                detail: data.message || "Reminder berhasil dikirim!",
                life: 5000,
            });
        } else {
            throw new Error(data.message || "Gagal mengirim reminder");
        }
    } catch (error) {
        console.error("Error sending reminder:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: error.message || "Gagal mengirim reminder",
            life: 5000,
        });
    } finally {
        isSendingReminder.value = false;
    }
}

// Helper functions for displaying reminder settings
function formatFrequency(freq) {
    const map = {
        daily: "Setiap Hari",
        weekly: "Setiap Minggu",
        monthly: "Setiap Bulan",
    };
    return map[freq] || freq || "-";
}

function formatReminderDays(days) {
    if (!days || !Array.isArray(days)) return [];

    const dayNames = {
        0: "Minggu",
        1: "Senin",
        2: "Selasa",
        3: "Rabu",
        4: "Kamis",
        5: "Jumat",
        6: "Sabtu",
    };

    return days.map((d) => dayNames[d] || d);
}

const tabs = [
    { value: "questions", label: "Daftar Pertanyaan" },
    { value: "respondents", label: "Responden" },
];

const activeTab = ref("questions");

// Respondents management
const respondents = ref([]);
const allRespondentIds = ref([]); // Store all respondent IDs to filter employees
const isProcessing = ref(false);
const showAddRespondentModal = ref(false);
const isLoadingRespondents = ref(false);
const respondentSearch = ref("");
const respondentPage = ref(1);
const respondentPagination = ref({
    has_more: false,
    next_cursor: null,
    prev_cursor: null,
});
const respondentCursor = ref(null);
const cursorHistory = ref([]); // Track cursor history for back navigation
const selectedRespondents = ref([]); // Store selected respondent IDs for bulk delete
let searchTimeout = null;

// Modal form state
const respondentForm = ref({
    branch_id: null,
    department_id: null,
    employee_ids: [],
});

const respondentFormErrors = ref({});

// Filtered data based on selections
const filteredDepartments = ref([]);
const filteredEmployees = ref([]);

// Watch branch_id changes
watch(
    () => respondentForm.value.branch_id,
    async (newBranchId, oldBranchId) => {
        if (newBranchId === oldBranchId) return;

        // Reset department and employee when branch changes
        respondentForm.value.department_id = null;
        respondentForm.value.employee_ids = [];

        // Fetch departments
        if (newBranchId) {
            // Fetch departments for selected branch
            try {
                const url = route("attendance-recap.departments", {
                    branch_id: newBranchId,
                });
                const response = await fetch(url, {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    credentials: "include",
                });

                if (!response.ok) {
                    console.error(
                        "Failed to load departments for branch",
                        newBranchId,
                    );
                    return;
                }

                const data = await response.json();
                filteredDepartments.value = Array.isArray(data.departments)
                    ? data.departments.map((dept) => ({
                          id: dept.id,
                          name: dept.name,
                      }))
                    : [];

                // Fetch all employees for this branch
                await fetchEmployeesByBranch(newBranchId);
            } catch (e) {
                console.error(
                    "Error fetching departments for branch",
                    newBranchId,
                    e,
                );
            }
        } else {
            // If no branch selected, fetch all departments and employees
            await fetchAllDepartments();
            await fetchAllEmployees();
        }
    },
    { immediate: true },
);

// Watch department_id changes
watch(
    () => respondentForm.value.department_id,
    async (newDepartmentId, oldDepartmentId) => {
        if (newDepartmentId === oldDepartmentId) return;

        // Reset employee selection when department changes
        respondentForm.value.employee_ids = [];

        if (!newDepartmentId) {
            // If no department selected, show employees based on branch or all
            if (respondentForm.value.branch_id) {
                await fetchEmployeesByBranch(respondentForm.value.branch_id);
            } else {
                await fetchAllEmployees();
            }
            return;
        }

        // Fetch employees for selected department
        await fetchEmployeesByDepartment(newDepartmentId);
    },
);

// Fetch all departments
async function fetchAllDepartments() {
    try {
        const response = await fetch(
            route("attendance-recap.departments", {}),
            {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "include",
            },
        );

        if (!response.ok) {
            console.error("Failed to load all departments");
            return;
        }

        const data = await response.json();
        filteredDepartments.value = Array.isArray(data.departments)
            ? data.departments.map((dept) => ({
                  id: dept.id,
                  name: dept.name,
              }))
            : [];
    } catch (e) {
        console.error("Error fetching all departments", e);
    }
}

// Load all respondent IDs (for filtering employees)
async function loadAllRespondentIds() {
    try {
        const allIds = [];
        let cursor = null;
        let hasMore = true;

        // Fetch all respondents using cursor pagination
        while (hasMore) {
            const params = new URLSearchParams({
                per_page: "100", // Fetch 100 at a time
            });

            if (cursor) {
                params.append("cursor", cursor);
            }

            const response = await fetch(
                `/api/v1/checklists/${props.checklist.id}/respondents?${params.toString()}`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    credentials: "include",
                },
            );

            if (!response.ok) {
                break;
            }

            const result = await response.json();
            if (result.success && result.data) {
                const paginationData = result.data || {};
                const respondents = paginationData.data || [];
                allIds.push(...respondents.map((r) => r.id));

                cursor = paginationData.next_cursor || null;
                hasMore = !!cursor;
            } else {
                break;
            }
        }

        allRespondentIds.value = allIds;
    } catch (e) {
        console.error("Error loading all respondent IDs", e);
    }
}

// Fetch all employees
async function fetchAllEmployees() {
    try {
        const response = await fetch(
            `/api/v1/checklists/${props.checklist.id}/employees`,
            {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "include",
            },
        );

        if (!response.ok) {
            console.error("Failed to load all employees");
            return;
        }

        const data = await response.json();
        if (data.success && data.data) {
            filteredEmployees.value = data.data
                .filter((emp) => !allRespondentIds.value.includes(emp.id))
                .map((emp) => ({
                    id: emp.id,
                    name: emp.name,
                    department: emp.department || null,
                    branch: emp.branch || null,
                }));
        }
    } catch (e) {
        console.error("Error fetching all employees", e);
    }
}

// Fetch employees by branch
async function fetchEmployeesByBranch(branchId) {
    try {
        const response = await fetch(
            `/api/v1/checklists/${props.checklist.id}/employees?branch_id=${branchId}`,
            {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "include",
            },
        );

        if (!response.ok) {
            console.error("Failed to load employees for branch", branchId);
            return;
        }

        const data = await response.json();
        if (data.success && data.data) {
            filteredEmployees.value = data.data
                .filter((emp) => !allRespondentIds.value.includes(emp.id))
                .map((emp) => ({
                    id: emp.id,
                    name: emp.name,
                    department: emp.department || null,
                    branch: emp.branch || null,
                }));
        }
    } catch (e) {
        console.error("Error fetching employees for branch", branchId, e);
    }
}

// Fetch employees by department
async function fetchEmployeesByDepartment(departmentId) {
    try {
        const response = await fetch(
            `/api/v1/checklists/${props.checklist.id}/employees?department_id=${departmentId}`,
            {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "include",
            },
        );

        if (!response.ok) {
            console.error(
                "Failed to load employees for department",
                departmentId,
            );
            return;
        }

        const data = await response.json();
        if (data.success && data.data) {
            filteredEmployees.value = data.data
                .filter((emp) => !allRespondentIds.value.includes(emp.id))
                .map((emp) => ({
                    id: emp.id,
                    name: emp.name,
                    department: emp.department || null,
                    branch: emp.branch || null,
                }));
        }
    } catch (e) {
        console.error(
            "Error fetching employees for department",
            departmentId,
            e,
        );
    }
}

async function openAddRespondentModal() {
    showAddRespondentModal.value = true;
    respondentForm.value = {
        branch_id: null,
        department_id: null,
        employee_ids: [],
    };
    respondentFormErrors.value = {};

    // Load all respondent IDs first, then load departments and employees
    await loadAllRespondentIds();
    await fetchAllDepartments();
    await fetchAllEmployees();
}

function closeAddRespondentModal() {
    showAddRespondentModal.value = false;
    respondentForm.value = {
        branch_id: null,
        department_id: null,
        employee_ids: [],
    };
    respondentFormErrors.value = {};
    filteredDepartments.value = [];
    filteredEmployees.value = [];
}

function selectAllEmployees() {
    if (filteredEmployees.value.length > 0) {
        respondentForm.value.employee_ids = filteredEmployees.value.map(
            (emp) => emp.id,
        );
    }
}

// Load respondents with pagination and search
async function loadRespondents(cursor = null, search = "") {
    isLoadingRespondents.value = true;
    try {
        const params = new URLSearchParams({
            per_page: "10",
        });

        if (cursor) {
            params.append("cursor", cursor);
        }

        if (search) {
            params.append("search", search);
        }

        const response = await fetch(
            `/api/v1/checklists/${props.checklist.id}/respondents?${params.toString()}`,
            {
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                credentials: "include",
            },
        );

        const result = await response.json();

        if (response.ok && result.success) {
            // Laravel cursor pagination structure:
            // { success: true, data: { data: [...], next_cursor: "...", prev_cursor: "...", ... }, message: "..." }
            const paginationData = result.data || {};

            // Format respondents data
            respondents.value = (paginationData.data || []).map((emp) => ({
                id: emp.id,
                name: emp.name,
                department: emp.department
                    ? { id: emp.department.id, name: emp.department.name }
                    : null,
                branch: emp.branch
                    ? { id: emp.branch.id, name: emp.branch.name }
                    : null,
            }));

            // Extract pagination metadata
            respondentPagination.value = {
                has_more: !!paginationData.next_cursor,
                next_cursor: paginationData.next_cursor || null,
                prev_cursor: paginationData.prev_cursor || null,
            };
            respondentCursor.value = cursor;

            // Reset page number and cursor history if no cursor (first page)
            if (!cursor) {
                respondentPage.value = 1;
                cursorHistory.value = [];
            }

            // Clear selection when loading new page
            selectedRespondents.value = [];
        } else {
            toast.add({
                severity: "error",
                summary: "Error",
                detail:
                    result.message || "Terjadi kesalahan saat memuat responden",
                life: 5000,
            });
        }
    } catch (error) {
        console.error("Error loading respondents:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Terjadi kesalahan saat memuat responden",
            life: 5000,
        });
    } finally {
        isLoadingRespondents.value = false;
    }
}

// Debounced search
function debouncedSearchRespondents() {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
        // Reset pagination on search
        respondentPage.value = 1;
        respondentCursor.value = null;
        cursorHistory.value = [];
        loadRespondents(null, respondentSearch.value);
    }, 500);
}

// Load next page
function loadNextRespondents() {
    if (
        respondentPagination.value.has_more &&
        respondentPagination.value.next_cursor
    ) {
        // Save current cursor to history for back navigation
        if (respondentCursor.value) {
            if (!cursorHistory.value) {
                cursorHistory.value = [];
            }
            cursorHistory.value.push(respondentCursor.value);
        }
        respondentPage.value++;
        loadRespondents(
            respondentPagination.value.next_cursor,
            respondentSearch.value,
        );
    }
}

// Load previous page
function loadPreviousRespondents() {
    // For cursor pagination, we track cursor history when going forward
    // When going back, we use the last cursor from history
    if (cursorHistory.value && cursorHistory.value.length > 0) {
        const prevCursor = cursorHistory.value.pop();
        respondentPage.value = Math.max(1, respondentPage.value - 1);
        loadRespondents(prevCursor, respondentSearch.value);
    } else if (respondentPage.value > 1) {
        // If no history but we're not on first page, go back to first page
        respondentPage.value = 1;
        cursorHistory.value = [];
        loadRespondents(null, respondentSearch.value);
    }
}

// Load respondents on mount and when tab changes
watch(
    () => activeTab.value,
    async (newTab) => {
        if (newTab === "respondents") {
            await loadAllRespondentIds(); // Load all respondent IDs first
            loadRespondents();
        }
    },
    { immediate: true },
);

function formatDateTime(val) {
    if (!val) return "-";
    const date = new Date(val);
    if (isNaN(date.getTime())) return String(val);
    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, "0");
    const dd = String(date.getDate()).padStart(2, "0");
    const hh = String(date.getHours()).padStart(2, "0");
    const mi = String(date.getMinutes()).padStart(2, "0");
    return `${yyyy}-${mm}-${dd} ${hh}.${mi}`;
}

function statusClass(st) {
    const map = {
        draft: "bg-gray-100 text-gray-700",
        submitted: "bg-amber-100 text-amber-800",
        approved: "bg-green-100 text-green-700",
        rejected: "bg-red-100 text-red-700",
    };
    return map[st] || "bg-gray-100 text-gray-700";
}

async function addRespondents() {
    // Reset errors
    respondentFormErrors.value = {};

    // Validation - branch is optional now
    if (respondentForm.value.employee_ids.length === 0) {
        respondentFormErrors.value.employee_ids = "Pilih minimal satu karyawan";
        toast.add({
            severity: "warning",
            summary: "Peringatan",
            detail: "Pilih minimal satu karyawan",
            life: 3000,
        });
        return;
    }

    isProcessing.value = true;

    try {
        // Convert to array of IDs if needed
        const employeeIds = respondentForm.value.employee_ids.map((id) =>
            typeof id === "object" ? id.id : id,
        );

        const response = await fetch(
            `/api/v1/checklists/${props.checklist.id}/respondents`,
            {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
                credentials: "include",
                body: JSON.stringify({
                    employee_ids: employeeIds,
                }),
            },
        );

        const data = await response.json();

        if (response.ok && data.success) {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: data.message || "Responden berhasil ditambahkan",
                life: 3000,
            });

            // Reset pagination and reload respondents list
            respondentPage.value = 1;
            respondentCursor.value = null;
            cursorHistory.value = [];
            await loadAllRespondentIds(); // Reload all respondent IDs
            await loadRespondents(null, respondentSearch.value);

            // Reload employees if modal is still open
            if (showAddRespondentModal.value) {
                if (respondentForm.value.branch_id) {
                    await fetchEmployeesByBranch(
                        respondentForm.value.branch_id,
                    );
                } else if (respondentForm.value.department_id) {
                    await fetchEmployeesByDepartment(
                        respondentForm.value.department_id,
                    );
                } else {
                    await fetchAllEmployees();
                }
            }

            // Close modal and reset form
            closeAddRespondentModal();
        } else {
            toast.add({
                severity: "error",
                summary: "Error",
                detail:
                    data.message ||
                    "Terjadi kesalahan saat menambahkan responden",
                life: 5000,
            });
        }
    } catch (error) {
        console.error("Error adding respondents:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Terjadi kesalahan saat menambahkan responden",
            life: 5000,
        });
    } finally {
        isProcessing.value = false;
    }
}

// Toggle select individual respondent
function toggleSelect(employeeId) {
    const index = selectedRespondents.value.indexOf(employeeId);
    if (index > -1) {
        selectedRespondents.value.splice(index, 1);
    } else {
        selectedRespondents.value.push(employeeId);
    }
}

// Check if respondent is selected
function isSelected(employeeId) {
    return selectedRespondents.value.includes(employeeId);
}

// Toggle select all
function toggleSelectAll(event) {
    if (event.target.checked) {
        selectedRespondents.value = respondents.value.map((r) => r.id);
    } else {
        selectedRespondents.value = [];
    }
}

// Check if all are selected
const isAllSelected = computed(() => {
    return (
        respondents.value.length > 0 &&
        selectedRespondents.value.length === respondents.value.length &&
        respondents.value.every((r) => selectedRespondents.value.includes(r.id))
    );
});

// Remove multiple respondents
async function removeMultipleRespondents() {
    if (selectedRespondents.value.length === 0) {
        return;
    }

    isProcessing.value = true;

    try {
        // Delete all selected respondents
        const deletePromises = selectedRespondents.value.map((employeeId) =>
            fetch(
                `/api/v1/checklists/${props.checklist.id}/respondents/${employeeId}`,
                {
                    method: "DELETE",
                    headers: {
                        Accept: "application/json",
                        "Content-Type": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN":
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content") || "",
                    },
                    credentials: "include",
                },
            ),
        );

        const responses = await Promise.all(deletePromises);
        const results = await Promise.all(responses.map((r) => r.json()));

        // Check if all deletions were successful
        const allSuccess = results.every((r) => r.success);

        if (allSuccess) {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: `${selectedRespondents.value.length} responden berhasil dihapus`,
                life: 3000,
            });

            // Clear selection
            selectedRespondents.value = [];

            // Reload all respondent IDs and respondents list
            await loadAllRespondentIds();
            await loadRespondents(
                respondentCursor.value,
                respondentSearch.value,
            );

            // Reload employees if modal is open
            if (showAddRespondentModal.value) {
                if (respondentForm.value.branch_id) {
                    await fetchEmployeesByBranch(
                        respondentForm.value.branch_id,
                    );
                } else if (respondentForm.value.department_id) {
                    await fetchEmployeesByDepartment(
                        respondentForm.value.department_id,
                    );
                } else {
                    await fetchAllEmployees();
                }
            }
        } else {
            toast.add({
                severity: "warning",
                summary: "Peringatan",
                detail: "Beberapa responden gagal dihapus",
                life: 5000,
            });
        }
    } catch (error) {
        console.error("Error removing multiple respondents:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Terjadi kesalahan saat menghapus responden",
            life: 5000,
        });
    } finally {
        isProcessing.value = false;
    }
}

async function removeRespondent(employeeId) {
    isProcessing.value = true;

    try {
        const response = await fetch(
            `/api/v1/checklists/${props.checklist.id}/respondents/${employeeId}`,
            {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
                credentials: "include",
            },
        );

        const data = await response.json();

        if (response.ok && data.success) {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: data.message || "Responden berhasil dihapus",
                life: 3000,
            });

            // Remove from selection if selected
            const index = selectedRespondents.value.indexOf(employeeId);
            if (index > -1) {
                selectedRespondents.value.splice(index, 1);
            }

            // Reload all respondent IDs and respondents list
            await loadAllRespondentIds();
            await loadRespondents(
                respondentCursor.value,
                respondentSearch.value,
            );

            // Reload employees if modal is open
            if (showAddRespondentModal.value) {
                if (respondentForm.value.branch_id) {
                    await fetchEmployeesByBranch(
                        respondentForm.value.branch_id,
                    );
                } else if (respondentForm.value.department_id) {
                    await fetchEmployeesByDepartment(
                        respondentForm.value.department_id,
                    );
                } else {
                    await fetchAllEmployees();
                }
            }
        } else {
            toast.add({
                severity: "error",
                summary: "Error",
                detail:
                    data.message ||
                    "Terjadi kesalahan saat menghapus responden",
                life: 5000,
            });
        }
    } catch (error) {
        console.error("Error removing respondent:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Terjadi kesalahan saat menghapus responden",
            life: 5000,
        });
    } finally {
        isProcessing.value = false;
    }
}
</script>

<script>
// small presentational info row component
export default {
    components: {
        InfoRow: {
            props: { label: String, value: [String, Number, null] },
            template: `
            <div>
              <label class='block mb-1 text-sm text-gray-600'>{{ label }}</label>
              <div class='p-2 min-h-[40px] rounded border bg-gray-50 text-gray-800'>{{ value ?? '-' }}</div>
            </div>`,
        },
    },
};
</script>
