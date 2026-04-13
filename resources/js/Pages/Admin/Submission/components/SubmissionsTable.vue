<template>
    <div class="overflow-hidden rounded-xl border border-[#DBDFE9] bg-white shadow-sm">
        <div v-if="statsCards.length" class="border-b border-[#DBDFE9] px-6 py-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="(stat, index) in statsCards"
                    :key="index"
                    class="flex items-center gap-4 rounded-xl border border-[#DBDFE9] bg-white px-4 py-4"
                >
                    <div
                        :class="[
                            'flex h-11 w-11 items-center justify-center rounded-xl',
                            stat.iconWrapperClass ? 'ring-1' : '',
                            stat.iconWrapperClass || stat.bgClass || 'bg-gray-100',
                        ]"
                    >
                        <component :is="stat.icon" class="h-5 w-5" :class="stat.iconClass || 'text-gray-600'" />
                    </div>

                    <div>
                        <div class="text-2xl font-bold text-gray-900">
                            {{ formatCount(stat.total ?? stat.value) }}
                        </div>
                        <div class="text-sm text-gray-500">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="tabs.length" class="border-b border-[#DBDFE9]">
            <nav class="flex overflow-x-auto">
                <a
                    v-for="tab in tabs"
                    :key="tab.name"
                    :href="`/${tab.name}`"
                    :class="[
                        'inline-flex items-center gap-2 border-b-2 px-6 py-4 text-sm font-medium whitespace-nowrap transition-colors',
                        getTabClass(tab.name),
                    ]"
                >
                    {{ tab.label }}

                    <span
                        v-if="tab.pendingCount > 0"
                        :class="[
                            'inline-flex min-w-[20px] items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-medium',
                            getTabBadgeClass(tab.name),
                        ]"
                    >
                        {{ formatCount(tab.pendingCount) }}
                    </span>
                </a>
            </nav>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width: 980px">
                <thead>
                    <tr class="border-y border-[#DBDFE9] bg-gray-50/50">
                        <th :class="[tableHeadClass, 'w-[56px] text-center']">
                            <div class="flex items-center justify-center gap-1">
                                No
                                <ArrowUpDown class="h-3 w-3 text-gray-400" />
                            </div>
                        </th>
                        <th :class="[tableHeadClass, 'min-w-[150px] w-[150px]']">
                            <div class="flex items-center gap-1">
                                Tanggal Pengajuan
                                <ArrowUpDown class="h-3 w-3 text-gray-400" />
                            </div>
                        </th>
                        <th :class="[tableHeadClass, 'min-w-[180px]']">
                            <div class="flex items-center gap-1">
                                Karyawan
                                <ArrowUpDown class="h-3 w-3 text-gray-400" />
                            </div>
                        </th>
                        <th :class="[tableHeadClass, 'min-w-[180px]']">
                            <div class="flex items-center gap-1">
                                Cabang
                                <ArrowUpDown class="h-3 w-3 text-gray-400" />
                            </div>
                        </th>
                        <th v-if="currentType === 'debt'" :class="[tableHeadClass, 'min-w-[110px]']">Tenor</th>
                        <th v-if="currentType === 'debt'" :class="[tableHeadClass, 'min-w-[150px]']">Jumlah</th>
                        <th :class="[tableHeadClass, 'min-w-[150px]']">
                            <div class="flex items-center gap-1">
                                Tipe
                                <ArrowUpDown class="h-3 w-3 text-gray-400" />
                            </div>
                        </th>
                        <th :class="[tableHeadClass, 'min-w-[150px]']">
                            <div class="flex items-center gap-1">
                                Status
                                <ArrowUpDown class="h-3 w-3 text-gray-400" />
                            </div>
                        </th>
                        <th :class="[tableHeadClass, 'min-w-[96px] text-center']">Aksi</th>
                    </tr>

                    <tr class="border-b border-[#DBDFE9] bg-gray-50">
                        <th :class="[filterCellClass, 'text-center']"></th>
                        <th :class="filterCellClass">
                            <div class="grid w-[150px] gap-1.5">
                                <input
                                    v-model="filters.start_date"
                                    type="date"
                                    :class="filterDateInputClass"
                                    @change="applyFilters"
                                />
                                <input
                                    v-model="filters.end_date"
                                    type="date"
                                    :class="filterDateInputClass"
                                    @change="applyFilters"
                                />
                            </div>
                        </th>
                        <th :class="filterCellClass">
                            <div class="relative">
                                <input
                                    v-model="filters.name"
                                    type="text"
                                    placeholder="Cari Nama"
                                    :class="filterInputIconClass"
                                    @input="debounceSearch"
                                />
                                <Search class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            </div>
                        </th>
                        <th :class="filterCellClass">
                            <div class="relative">
                                <select v-model="filters.branch" :class="filterSelectClass" @change="applyFilters">
                                    <option value="">Pilih Cabang</option>
                                    <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                        {{ branch.name }}
                                    </option>
                                </select>
                                <Filter class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            </div>
                        </th>
                        <th v-if="currentType === 'debt'" :class="filterCellClass"></th>
                        <th v-if="currentType === 'debt'" :class="filterCellClass"></th>
                        <th :class="filterCellClass">
                            <div class="relative">
                                <select v-model="filters.type" :class="filterSelectClass" @change="applyFilters">
                                    <option value="">Semua Tipe</option>
                                    <option v-for="type in submissionTypes" :key="type.value" :value="type.name">
                                        {{ type.label }}
                                    </option>
                                </select>
                                <Filter class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            </div>
                        </th>
                        <th :class="filterCellClass">
                            <div class="relative">
                                <select v-model="filters.status" :class="filterSelectClass" @change="applyFilters">
                                    <option value="">Pilih Status</option>
                                    <option v-for="status in submissionStatuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                                <Filter class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            </div>
                        </th>
                        <th :class="[filterCellClass, 'text-center']">
                            <button type="button" :class="filterButtonClass" @click="clearFilters">
                                <RotateCcw class="h-4 w-4" />
                                Clear
                            </button>
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white">
                    <tr v-if="!paginatedData || paginatedData.length === 0">
                        <td :colspan="getTotalColumns()" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100">
                                    <FileText class="h-10 w-10 text-gray-400" />
                                </div>
                                <h3 class="mb-2 text-xl font-semibold text-gray-900">Tidak ada data</h3>
                                <p class="text-sm text-gray-500">Belum ada pengajuan yang sesuai dengan filter saat ini.</p>
                            </div>
                        </td>
                    </tr>

                    <tr
                        v-for="(context, index) in paginatedData"
                        v-else
                        :key="context.id"
                        class="cursor-pointer border-b border-gray-100 transition-colors hover:bg-[#f5f8ff]"
                        @click="openModal(context)"
                    >
                        <td :class="[tableCellClass, 'text-center']">
                            {{ index + (currentPage - 1) * perPage + 1 }}
                        </td>
                        <td :class="tableCellClass">{{ formatSubmissionDate(context?.tanggal_pengajuan) }}</td>
                        <td :class="[tableCellClass, 'font-medium']">
                            {{ context?.employee?.name || '-' }}
                        </td>
                        <td :class="tableCellClass">
                            {{ context?.branch?.name || context?.employee?.branch_name || '-' }}
                        </td>
                        <td v-if="currentType === 'debt'" :class="tableCellClass">
                            {{ context?.tenor ?? '-' }}
                        </td>
                        <td v-if="currentType === 'debt'" :class="tableCellClass">
                            {{ formatCurrency(context?.amount) }}
                        </td>
                        <td :class="tableCellClass">
                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium"
                                :class="getTypeClass(context.submission_type)"
                            >
                                {{ getTypeLabel(context.submission_type) || '-' }}
                            </span>
                        </td>
                        <td :class="tableCellClass">
                            <div class="flex flex-col gap-1.5">
                                <div
                                    class="inline-flex w-fit items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium whitespace-nowrap"
                                    :class="getStatusClass(context.status)"
                                >
                                    <span class="h-2 w-2 rounded-full" :class="getStatusDotClass(context.status)"></span>
                                    <span class="truncate">{{ getStatusLabel(context.status) }}</span>
                                </div>
                                <div v-if="showApprovalMeta(context)" class="text-[11px] leading-tight text-gray-500">
                                    <div>{{ getApprovalName(context) }}</div>
                                    <div>{{ formatApprovalDate(getApprovalDateValue(context)) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center align-middle" @click.stop>
                            <button
                                class="inline-flex items-center gap-1.5 rounded-md border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-medium text-blue-700 transition hover:bg-blue-100 sm:text-sm"
                                @click.stop="openModal(context)"
                            >
                                <Eye class="h-4 w-4" />
                                <span>Lihat</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="border-t border-[#DBDFE9] px-6 py-3">
            <div class="flex flex-1 justify-between sm:hidden">
                <button
                    class="relative inline-flex items-center rounded-lg border border-[#DBDFE9] bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50"
                    :disabled="currentPage <= 1"
                    @click="goToPage(currentPage - 1)"
                >
                    Sebelumnya
                </button>
                <button
                    class="relative ml-3 inline-flex items-center rounded-lg border border-[#DBDFE9] bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:opacity-50"
                    :disabled="currentPage >= totalPages"
                    @click="goToPage(currentPage + 1)"
                >
                    Berikutnya
                </button>
            </div>

            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div class="flex flex-col gap-1 items-start">
                    <div class="flex items-center gap-2">
                        <label for="per-page" class="text-sm text-gray-500">Tampilkan:</label>
                        <select
                            id="per-page"
                            :value="perPage"
                            class="rounded-lg border border-[#DBDFE9] bg-white px-3 py-2 text-sm text-gray-700 shadow-sm outline-none transition focus:border-[#002875] focus:ring-2 focus:ring-[#002875]/20"
                            @change="changePerPage"
                        >
                            <option v-for="option in perPageOptions" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                        <span class="text-sm text-gray-500">per halaman</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Menampilkan
                        <span class="font-medium text-gray-700">{{ paginationInfo.from }}</span>
                        ke
                        <span class="font-medium text-gray-700">{{ paginationInfo.to }}</span>
                        dari
                        <span class="font-medium text-gray-700">{{ paginationInfo.total }}</span>
                        hasil
                    </p>
                </div>

                <div>
                    <nav class="isolate inline-flex items-center gap-1" aria-label="Pagination">
                        <template v-for="(link, i) in paginationLinks" :key="i">
                            <span
                                v-if="!link.url"
                                class="inline-flex items-center px-3 py-2 text-sm font-semibold text-gray-400"
                                v-html="link.label"
                            ></span>
                            <button
                                v-else
                                :class="[
                                    'inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold transition focus:z-20',
                                    link.active
                                        ? 'bg-[#002875] text-white shadow-sm'
                                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-800',
                                ]"
                                @click="goToPage(link.page)"
                                v-html="link.label"
                            ></button>
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, ref, watch } from "vue";
import {
    formatIndonesianDate,
    formatIndonesianDateTime,
    hasTimePart,
    parseDateValue,
} from "@/Helpers/dateFormat";
import {
    ArrowUpDown,
    Eye,
    FileText,
    Filter,
    RotateCcw,
    Search,
} from "lucide-vue-next";

const props = defineProps({
    submissions: Array,
    types: Object,
    currentType: String,
    branches: Array,
    submissionTypes: Array,
    submissionStatuses: Array,
    statsCards: {
        type: Array,
        default: () => [],
    },
    tabs: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["open-modal", "filtered-data-updated"]);

// Reactive data
const perPage = ref(10);
const currentPage = ref(1);
const perPageOptions = [10, 15, 25, 50, 100];

// Filter states
const filters = reactive({
    start_date: "",
    end_date: "",
    name: "",
    branch: "",
    type: "",
    status: "",
});

const filterInputClass =
    "w-full rounded-lg border border-[#DBDFE9] bg-[#fbfbfb] px-3 py-2 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-[#002875] focus:ring-2 focus:ring-[#002875]/20";
const filterDateInputClass =
    "h-9 w-full min-w-0 rounded-lg border border-[#DBDFE9] bg-[#fbfbfb] px-2.5 text-xs text-gray-700 outline-none transition focus:border-[#002875] focus:ring-2 focus:ring-[#002875]/20";
const filterInputIconClass = `${filterInputClass} pr-10`;
const filterSelectClass = `${filterInputClass} appearance-none pr-10`;
const filterCellClass = "px-4 py-3 align-top";
const tableHeadClass = "px-4 py-3 text-left font-medium text-gray-700 whitespace-nowrap";
const tableCellClass = "px-4 py-4 align-middle text-sm text-gray-900";
const filterButtonClass =
    "inline-flex items-center gap-2 rounded-lg bg-[#002875] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#001d5c]";

// Computed properties
const filteredData = computed(() => {
    let filtered = [...props.submissions];

    // Apply date range filter
    if (filters.start_date || filters.end_date) {
        filtered = filtered.filter((item) => {
            if (!item.tanggal_pengajuan) return false;

            const itemDate = parseDateValue(item.tanggal_pengajuan);
            const startDate = filters.start_date ? parseDateValue(filters.start_date) : null;
            const endDate = filters.end_date ? parseDateValue(filters.end_date) : null;

            if (!itemDate) return false;

            // Set time to start of day for comparison
            if (startDate) startDate.setHours(0, 0, 0, 0);
            if (endDate) endDate.setHours(23, 59, 59, 999);
            itemDate.setHours(0, 0, 0, 0);

            const afterStart = !startDate || itemDate >= startDate;
            const beforeEnd = !endDate || itemDate <= endDate;

            return afterStart && beforeEnd;
        });
    }

    if (filters.name) {
        filtered = filtered.filter((item) =>
            item.employee?.name
                ?.toLowerCase()
                .includes(filters.name.toLowerCase())
        );
    }

    if (filters.branch) {
        filtered = filtered.filter(
            (item) =>
                item.branch?.id?.toString() === filters.branch.toString() ||
                item.employee?.branch_id?.toString() ===
                filters.branch.toString()
        );
    }

    if (filters.type && filters.type !== 'submissions') {
        filtered = filtered.filter((item) => {
            if (!item.submission_type) return false;

            // Normalize both values for comparison (handle dash vs underscore)
            const itemType = item.submission_type.toLowerCase().replace(/_/g, '-');
            const filterType = filters.type.toLowerCase().replace(/_/g, '-');

            return itemType === filterType;
        });
    }

    if (filters.status) {
        filtered = filtered.filter((item) => item.status === filters.status);
    }

    // Sort by created_at descending (newest first)
    filtered.sort((a, b) => {
        const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
        const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
        return dateB - dateA; // Descending order (newest first)
    });

    return filtered;
});

const totalPages = computed(() =>
    Math.ceil(filteredData.value.length / perPage.value)
);

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredData.value.slice(start, end);
});

const paginationInfo = computed(() => {
    const total = filteredData.value.length;
    const from = total === 0 ? 0 : (currentPage.value - 1) * perPage.value + 1;
    const to = Math.min(currentPage.value * perPage.value, total);

    return { from, to, total };
});

const paginationLinks = computed(() => {
    const links = [];
    const total = totalPages.value;
    const current = currentPage.value;

    // Don't show pagination if no pages
    if (total === 0) {
        return links;
    }

    // Previous link
    links.push({
        label: "&laquo; Previous",
        url: current > 1 ? "#" : null,
        page: current > 1 ? current - 1 : null,
        active: false,
    });

    // Generate page numbers
    if (total <= 7) {
        // Show all pages if total is 7 or less
        for (let i = 1; i <= total; i++) {
            links.push({
                label: String(i),
                url: "#",
                page: i,
                active: i === current,
            });
        }
    } else {
        // Always show first page
        links.push({
            label: "1",
            url: "#",
            page: 1,
            active: current === 1,
        });

        // Show ellipsis if current page is far from start
        if (current > 3) {
            links.push({
                label: "...",
                url: null,
                page: null,
                active: false,
            });
        }

        // Show pages around current page
        const start = Math.max(2, current - 1);
        const end = Math.min(total - 1, current + 1);

        for (let i = start; i <= end; i++) {
            links.push({
                label: String(i),
                url: "#",
                page: i,
                active: i === current,
            });
        }

        // Show ellipsis if current page is far from end
        if (current < total - 2) {
            links.push({
                label: "...",
                url: null,
                page: null,
                active: false,
            });
        }

        // Always show last page if total > 1
        if (total > 1) {
            links.push({
                label: String(total),
                url: "#",
                page: total,
                active: current === total,
            });
        }
    }

    // Next link
    links.push({
        label: "Next &raquo;",
        url: current < total ? "#" : null,
        page: current < total ? current + 1 : null,
        active: false,
    });

    return links;
});

// Methods
function goToPage(page) {
    if (page && page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
}

function changePerPage(event) {
    perPage.value = parseInt(event.target.value);
    currentPage.value = 1;
}

function applyFilters() {
    currentPage.value = 1;
}

function clearFilters() {
    filters.start_date = "";
    filters.end_date = "";
    filters.name = "";
    filters.branch = "";
    filters.type = "";
    filters.status = "";
    currentPage.value = 1;
}

function formatCount(value) {
    return Number(value ?? 0).toLocaleString("id-ID");
}

function formatCurrency(amount) {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(amount ?? 0));
}

// Debounce search for name filter
let searchTimeout;
function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
}

// Status styling functions
function getStatusLabel(status) {
    const statusMap = {
        pending: "Menunggu",
        approved: "Disetujui",
        rejected: "Ditolak",
        cancelled: "Dibatalkan",
    };
    return statusMap[status] || "Menunggu";
}

function getStatusClass(status) {
    const statusMap = {
        pending: "bg-yellow-100 text-yellow-800",
        approved: "bg-green-100 text-green-800",
        rejected: "bg-red-100 text-red-800",
        cancelled: "bg-gray-100 text-gray-800",
    };
    return statusMap[status] || "bg-gray-100 text-gray-800";
}

function getStatusDotClass(status) {
    const dotMap = {
        pending: "bg-amber-500",
        approved: "bg-emerald-500",
        rejected: "bg-rose-500",
        cancelled: "bg-slate-400",
    };
    return dotMap[status] || "bg-slate-400";
}

function showApprovalMeta(context) {
    return (
        ["approved", "rejected"].includes(context?.status) &&
        (getApprovalName(context) || getApprovalDateValue(context))
    );
}

function getApprovalName(context) {
    const approvedBy = context?.approved_by;

    if (!approvedBy) return "";
    if (typeof approvedBy === "string") return approvedBy;
    if (typeof approvedBy === "object") return approvedBy.name || "";

    return "";
}

function formatApprovalDate(value) {
    return formatIndonesianDateTime(value, "");
}

function getApprovalDateValue(context) {
    return context?.approved_at || context?.updated_at || "";
}

function formatSubmissionDate(value) {
    return hasTimePart(value)
        ? formatIndonesianDateTime(value)
        : formatIndonesianDate(value);
}

function getTypeLabel(type) {
    const typeMap = {
        sick: "Sakit",
        overtime: "Lembur",
        employee: "Karyawan",
        debt: "Piutang",
        annual_leave: "Cuti Tahunan",
        others: "Lainnya",
        general: "Umum",
        reimbursement: "Reimbursement",
    };
    return typeMap[type] || type || "-";
}

function getTypeClass(type) {
    const typeMap = {
        sick: "bg-rose-100 text-rose-700",
        overtime: "bg-amber-100 text-amber-700",
        employee: "bg-indigo-100 text-indigo-700",
        debt: "bg-violet-100 text-violet-700",
        annual_leave: "bg-emerald-100 text-emerald-700",
        others: "bg-orange-100 text-orange-700",
        general: "bg-gray-100 text-gray-700",
        reimbursement: "bg-sky-100 text-sky-700",
    };

    return typeMap[type] || "bg-gray-100 text-gray-700";
}

function getTabClass(name) {
    return name === props.currentType
        ? "border-[#002875] text-[#002875]"
        : "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700";
}

function getTabBadgeClass(name) {
    return name === props.currentType
        ? "bg-[#002875] text-white"
        : "bg-gray-100 text-gray-600";
}

function getSubmissionTypeRoute(type) {
    const routeMap = {
        sick: "sick",
        overtime: "overtime",
        employee: "employee",
        debt: "debt",
        annual_leave: "annual-leave",
        others: "others",
        reimbursement: "reimbursement",
        general: "general",
    };
    return routeMap[type] || "sick";
}

// Calculate total columns based on submission type
function getTotalColumns() {
    // Base columns: No, Tanggal Pengajuan, Karyawan, Cabang, Tipe, Status, Aksi
    let columns = 7;

    // Add Tenor and Jumlah for debt type
    if (props.currentType === 'debt') {
        columns += 2;
    }

    return columns;
}

// Modal function
function openModal(submission) {
    const submissionType = getSubmissionTypeRoute(submission.submission_type);
    emit("open-modal", submission, submissionType);
}

// Watch for data changes to reset pagination
watch(
    () => props.submissions,
    () => {
        currentPage.value = 1;
    },
    { deep: true }
);

// Watch for filtered data changes and emit to parent
watch(
    filteredData,
    (newFilteredData) => {
        emit("filtered-data-updated", newFilteredData);
    },
    { deep: true, immediate: true }
);
</script>
