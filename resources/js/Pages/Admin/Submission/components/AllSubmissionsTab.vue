<template>
    <div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            No
                        </th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Tanggal Pengajuan
                        </th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Karyawan
                        </th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Cabang
                        </th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                            Status
                        </th>
                        <th class="relative px-6 py-3">
                            <span class="sr-only">Aksi</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(item, index) in submissions.data" :key="item.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{
                                (submissions.current_page - 1) *
                                submissions.per_page +
                                index +
                                1
                            }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                            {{ formatDate(item.submission_date) || "-" }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div
                                    class="flex flex-shrink-0 justify-center items-center w-10 h-10 font-medium text-gray-600 bg-gray-200 rounded-full">
                                    {{ item.employee?.name?.charAt(0) || "?" }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ item.employee?.name || "-" }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ item.employee?.nip || "" }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ item.branch?.name || "-" }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full"
                                :class="getStatusClass(item.status)">
                                {{ getStatusLabel(item.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                            {{ getTypeLabel(item.type) }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                            <button @click="$emit('show-detail', item)" class="mr-4 text-blue-600 hover:text-blue-900"
                                title="Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!submissions.data || !submissions.data.length">
                        <td colspan="7" class="px-6 py-4 text-sm text-center text-gray-500">
                            Tidak ada data yang tersedia
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination v-if="submissions.data && submissions.data.length" :pagination="submissions"
            @page-change="(page) => $emit('page-change', page)" class="border-t" />
    </div>

    <!-- Modal Components -->
    <DetailSick v-if="modalState.type === 'sick'" :is-open="modalState.isOpen && modalState.type === 'sick'"
        :submission="modalState.submission" :type="modalState.type" :submission-types="props.submission_types"
        :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
        @close="closeModal" @update-success="handleModalUpdateSuccess" />

    <DetailOvertime v-if="modalState.type === 'overtime'" :is-open="modalState.isOpen && modalState.type === 'overtime'"
        :submission="modalState.submission" :type="modalState.type" :submission-types="props.submission_types"
        :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
        @close="closeModal" @update-success="handleModalUpdateSuccess" />

    <DetailOther v-if="modalState.type === 'other'" :is-open="modalState.isOpen && modalState.type === 'other'"
        :submission="modalState.submission" :type="modalState.type" :submission-types="props.submission_types"
        :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
        @close="closeModal" @update-success="handleModalUpdateSuccess" />

    <DetailDebt v-if="modalState.type === 'debt'" :is-open="modalState.isOpen && modalState.type === 'debt'"
        :submission="modalState.submission" :type="modalState.type" :submission-types="props.submission_types"
        :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
        @close="closeModal" @update-success="handleModalUpdateSuccess" />

    <DetailDailyReport v-if="modalState.type === 'employee'"
        :is-open="modalState.isOpen && modalState.type === 'employee'" :submission="modalState.submission"
        :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
        @close="closeModal" @update-success="handleModalUpdateSuccess" />

    <DetailAnnualLeave v-if="modalState.type === 'annual-leave'"
        :is-open="modalState.isOpen && modalState.type === 'annual-leave'" :submission="modalState.submission"
        :type="modalState.type" :submission-types="props.submission_types"
        :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
        @close="closeModal" @update-success="handleModalUpdateSuccess" />

    <DetailGeneral v-if="modalState.type === 'general'" :is-open="modalState.isOpen && modalState.type === 'general'"
        :submission="modalState.submission" :type="modalState.type" :submission-types="props.submission_types"
        :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
        @close="closeModal" @update-success="handleModalUpdateSuccess" />
</template>

<script setup>
import { defineProps, defineEmits } from "vue";
import Pagination from "@/Components/common/Pagination.vue";

const statusMap = {
    0: 'Menunggu',
    1: 'Disetujui',
    2: 'Ditolak',
    3: 'Dibatalkan'
};

const typeMap = {
    sick: 'Sakit',
    annual_leave: 'Cuti',
    overtime: 'Lembur',
    permission: 'Izin',
    others: 'Lain-lain',
    general: 'Umum'
};

const props = defineProps({
    submissions: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["show-detail", "page-change"]);

const getStatusClass = (status) => {
    const classes = {
        0: 'bg-yellow-100 text-yellow-800', // Pending
        1: 'bg-green-100 text-green-800',   // Approved
        2: 'bg-red-100 text-red-800',       // Rejected
        3: 'bg-gray-100 text-gray-800'      // Cancelled
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    return statusMap[status] || 'Tidak Diketahui';
};

const getTypeLabel = (type) => {
    return typeMap[type] || type || '-';
};

function formatDate(dateString) {
    if (!dateString) return "";
    const options = { year: "numeric", month: "short", day: "numeric" };
    return new Date(dateString).toLocaleDateString("id-ID", options);
}
</script>
