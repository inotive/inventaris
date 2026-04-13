<template>

    <Head title="Daftar Pengajuan" />
    <Toast />

    <div class="flex h-full flex-col gap-6 px-6 py-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <nav class="flex items-center text-sm text-gray-500">
                <a href="/submissions" class="transition hover:text-gray-700">Daftar Pengajuan</a>
                <ChevronRightIcon class="mx-2 h-4 w-4" />
                <span class="font-semibold text-[#002875]">{{ pageTitle }}</span>
            </nav>

            <button
                v-if="props.type === 'employee' && can('submission_daily.create')"
                @click="openCreateModal"
                class="inline-flex items-center gap-2 rounded-lg bg-[#002875] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#001d5c] focus:outline-none focus:ring-2 focus:ring-[#002875]/20"
            >
                <PlusIcon class="h-4 w-4" />
                Tambah Pengajuan
            </button>
        </div>

        <SubmissionsTable
            v-if="props.type === 'submissions'"
            :submissions="submissions.data"
            :types="props.types"
            :current-type="props.type"
            :branches="props.branches"
            :submission-types="props.submission_types"
            :submission-statuses="props.submission_statuses"
            :stats-cards="cardList"
            :tabs="visibleSubmissionTabs"
            @open-modal="openModal"
            @filtered-data-updated="updateFilteredSubmissions"
        />

        <div v-else class="overflow-hidden rounded-xl border border-[#DBDFE9] bg-white shadow-sm">
            <div class="border-b border-[#DBDFE9] px-6 py-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="(list, index) in cardList"
                        :key="index"
                        class="flex items-center gap-4 rounded-xl border border-[#DBDFE9] bg-white px-4 py-4"
                    >
                        <div
                            :class="[
                                'flex h-11 w-11 items-center justify-center rounded-xl ring-1',
                                list.iconWrapperClass,
                            ]"
                        >
                            <component :is="list.icon" class="h-5 w-5" :class="list.iconClass" />
                        </div>

                        <div>
                            <div class="text-2xl font-bold text-gray-900">
                                {{ formatCount(list.total) }}
                            </div>
                            <div class="text-sm text-gray-500">{{ list.label }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="visibleSubmissionTabs.length" class="border-b border-[#DBDFE9]">
                <nav class="flex overflow-x-auto">
                    <a
                        v-for="label in visibleSubmissionTabs"
                        :key="label.name"
                        :href="`/${label.name}`"
                        :class="[
                            'inline-flex items-center gap-2 border-b-2 px-6 py-4 text-sm font-medium whitespace-nowrap transition-colors',
                            getTabClass(label.name),
                        ]"
                    >
                        {{ label.label }}

                        <span
                            v-if="label.pendingCount > 0"
                            :class="[
                                'inline-flex min-w-[20px] items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-medium',
                                getTabBadgeClass(label.name),
                            ]"
                        >
                            {{ formatCount(label.pendingCount) }}
                        </span>
                    </a>
                </nav>
            </div>

            <div class="m-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" style="min-width: 1000px">
                        <thead>
                            <tr class="border-y border-[#DBDFE9] bg-gray-50/50">
                            <th :class="[tableHeadClass, 'w-16 text-center']">No</th>
                            <th :class="[tableHeadClass, 'w-[150px]']">Tanggal Pengajuan</th>
                            <th :class="tableHeadClass">Karyawan</th>
                            <th v-if="props.type === 'others'" :class="tableHeadClass">Kategori Izin</th>
                            <th
                                v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                                :class="tableHeadClass"
                            >
                                Tanggal Mulai
                            </th>
                            <th
                                v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                                :class="tableHeadClass"
                            >
                                Tanggal Akhir
                            </th>
                            <th
                                v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                                :class="tableHeadClass"
                            >
                                Total Hari
                            </th>
                            <th v-if="props.type === 'overtime'" :class="tableHeadClass">Waktu Mulai</th>
                            <th v-if="props.type === 'overtime'" :class="tableHeadClass">Waktu Selesai</th>
                            <th :class="tableHeadClass">Cabang</th>
                            <th v-if="props.type === 'reimbursement'" :class="tableHeadClass">
                                Judul Reimbursement
                            </th>
                            <th v-if="props.type === 'reimbursement'" :class="[tableHeadClass, 'w-[150px]']">Tanggal</th>
                            <th v-if="props.type === 'reimbursement'" :class="tableHeadClass">Jumlah</th>
                            <th v-if="props.type === 'general'" :class="tableHeadClass">Judul</th>
                            <th v-if="props.type === 'general'" :class="tableHeadClass">Tag</th>
                            <th v-if="props.type === 'debt'" :class="tableHeadClass">Tenor</th>
                            <th v-if="props.type === 'debt'" :class="tableHeadClass">Jumlah Piutang</th>
                            <th v-if="props.type === 'debt'" :class="tableHeadClass">Jumlah yang Dibayar</th>
                            <th :class="tableHeadClass">Status</th>
                            <th :class="[tableHeadClass, 'text-center']">Aksi</th>
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
                                    <MagnifyingGlassIcon
                                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                    <input
                                        v-model="filters.name"
                                        type="text"
                                        placeholder="Cari nama..."
                                        :class="[filterInputClass, 'pl-10']"
                                        @input="debounceSearch"
                                    />
                                </div>
                            </th>
                            <th v-if="props.type === 'others'" :class="filterCellClass"></th>
                            <th
                                v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                                :class="filterCellClass"
                            ></th>
                            <th
                                v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                                :class="filterCellClass"
                            ></th>
                            <th
                                v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                                :class="filterCellClass"
                            ></th>
                            <th v-if="props.type === 'overtime'" :class="filterCellClass"></th>
                            <th v-if="props.type === 'overtime'" :class="filterCellClass"></th>
                            <th :class="filterCellClass">
                                <select v-model="filters.branch" :class="filterInputClass" @change="applyFilters">
                                    <option value="">Semua Cabang</option>
                                    <option v-for="branch in props.branches" :key="branch.id" :value="branch.id">
                                        {{ branch.name }}
                                    </option>
                                </select>
                            </th>
                            <th v-if="props.type === 'reimbursement'" :class="filterCellClass">
                                <div class="relative">
                                    <MagnifyingGlassIcon
                                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    />
                                    <input
                                        v-model="filters.title"
                                        type="text"
                                        placeholder="Cari judul..."
                                        :class="[filterInputClass, 'pl-10']"
                                        @input="debounceSearch"
                                    />
                                </div>
                            </th>
                            <th v-if="props.type === 'reimbursement'" :class="filterCellClass">
                                <div class="grid w-[150px] gap-1.5">
                                    <input
                                        v-model="filters.event_start_date"
                                        type="date"
                                        :class="filterDateInputClass"
                                        @change="applyFilters"
                                    />
                                    <input
                                        v-model="filters.event_end_date"
                                        type="date"
                                        :class="filterDateInputClass"
                                        @change="applyFilters"
                                    />
                                </div>
                            </th>
                            <th v-if="props.type === 'reimbursement'" :class="filterCellClass"></th>
                            <th v-if="props.type === 'general'" :class="filterCellClass"></th>
                            <th v-if="props.type === 'general'" :class="filterCellClass"></th>
                            <th v-if="props.type === 'debt'" :class="filterCellClass"></th>
                            <th v-if="props.type === 'debt'" :class="filterCellClass"></th>
                            <th v-if="props.type === 'debt'" :class="filterCellClass"></th>
                            <th :class="filterCellClass">
                                <select v-model="filters.status" :class="filterInputClass" @change="applyFilters">
                                    <option value="">Semua Status</option>
                                    <option v-for="status in props.submission_statuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </th>
                            <th :class="[filterCellClass, 'text-center']">
                                <button type="button" :class="filterButtonClass" @click="clearFilters">
                                    <ArrowPathIcon class="h-4 w-4" />
                                    Clear
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="bg-white">
                        <tr v-if="datatableConfig.data.length === 0">
                            <td :colspan="getTotalColumns()" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div
                                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                                    >
                                        <DocumentTextIcon class="h-8 w-8" />
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-900">Tidak ada data</h3>
                                    <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500">
                                        Belum ada pengajuan yang sesuai dengan filter saat ini.
                                    </p>
                                </div>
                            </td>
                        </tr>

                        <tr
                            v-for="(context, index) in datatableConfig.data"
                            v-else
                            :key="context.id"
                            :class="[
                                'border-b border-gray-100 transition-all duration-200 cursor-pointer',
                                hoveredRow === context.id ? 'bg-[#f5f8ff] shadow-sm' : 'hover:bg-[#f5f8ff]/50'
                            ]"
                            @click="openModal(context, props.type)"
                            @mouseenter="hoveredRow = context.id"
                            @mouseleave="hoveredRow = null"
                            title="Klik untuk melihat detail"
                        >
                            <td :class="[tableCellClass, 'text-center text-slate-500']">
                                <div class="flex items-center justify-center gap-2">
                                    <span>{{ index + (datatableConfig.currentPage - 1) * datatableConfig.perPage + 1 }}</span>
                                    <EyeIcon 
                                        v-if="hoveredRow === context.id" 
                                        class="h-4 w-4 text-blue-500 animate-fade-in"
                                    />
                                </div>
                            </td>
                            <td :class="tableCellClass">{{ formatSubmissionDate(context?.tanggal_pengajuan) }}</td>
                            <td class="px-4 py-4 align-middle text-sm font-medium text-slate-900">
                                {{ props.type === 'employee' ? (context?.name || '-') : (context?.employee?.name || '-') }}
                            </td>
                            <td v-if="props.type === 'others'" :class="tableCellClass">
                                {{ context?.leave_type_name || '-' }}
                            </td>
                            <td
                                v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                                :class="tableCellClass"
                            >
                                {{ formatDisplayDate(context?.start_date) }}
                            </td>
                            <td
                                v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                                :class="tableCellClass"
                            >
                                {{ formatDisplayDate(context?.end_date) }}
                            </td>
                            <td
                                v-if="['sick', 'annual-leave', 'others'].includes(props.type)"
                                :class="[tableCellClass, 'text-center']"
                            >
                                {{ context?.total_days ? `${context.total_days} hari` : '-' }}
                            </td>
                            <td v-if="props.type === 'overtime'" :class="tableCellClass">
                                {{ context?.start_time || '-' }}
                            </td>
                            <td v-if="props.type === 'overtime'" :class="tableCellClass">
                                {{ context?.end_time || '-' }}
                            </td>
                            <td :class="tableCellClass">{{ context?.employee?.branch_name || '-' }}</td>
                            <td v-if="props.type === 'reimbursement'" :class="tableCellClass">
                                {{ context?.title || '-' }}
                            </td>
                            <td v-if="props.type === 'reimbursement'" :class="tableCellClass">
                                {{ formatDisplayDate(context?.event_date) }}
                            </td>
                            <td v-if="props.type === 'reimbursement'" :class="tableCellClass">
                                {{ formatCurrency(context?.amount) || '-' }}
                            </td>
                            <td v-if="props.type === 'general'" :class="tableCellClass">
                                {{ context?.title || '-' }}
                            </td>
                            <td v-if="props.type === 'general'" :class="tableCellClass">
                                <div class="flex flex-wrap gap-2">
                                    <template v-if="Array.isArray(context?.tag)">
                                        <span
                                            v-for="(tag, i) in context.tag"
                                            :key="i"
                                            class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600"
                                        >
                                            {{ tag }}
                                        </span>
                                    </template>
                                    <template v-else>
                                        <span
                                            class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600"
                                        >
                                            {{ context?.tag || '-' }}
                                        </span>
                                    </template>
                                </div>
                            </td>
                            <td v-if="props.type === 'debt'" :class="tableCellClass">
                                {{ context?.tenor ? `${context.tenor} bulan` : '-' }}
                            </td>
                            <td v-if="props.type === 'debt'" :class="tableCellClass">
                                {{ formatCurrency(context?.amount) || '-' }}
                            </td>
                            <td v-if="props.type === 'debt'" :class="tableCellClass">
                                {{ formatCurrency(context?.remaining_amount) }}
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
                                    <div v-if="showApprovalMeta(context)" class="text-[11px] leading-tight text-slate-500">
                                        <div>{{ getApprovalName(context) }}</div>
                                        <div>{{ formatApprovalDate(getApprovalDateValue(context)) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center align-middle">
                                <div class="flex flex-wrap justify-center gap-2" @click.stop>
                                    <button
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 sm:text-sm"
                                        @click.stop="openModal(context, props.type)"
                                    >
                                        <EyeIcon class="h-4 w-4" />
                                        <span>Lihat</span>
                                    </button>

                                    <button
                                        v-if="props.type === 'debt' && isDebtApproved(context)"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 sm:text-sm"
                                        @click.stop="openPaymentModal(context)"
                                    >
                                        <BanknotesIcon class="h-4 w-4" />
                                        <span>Pembayaran</span>
                                    </button>

                                    <template v-if="props.type === 'employee'">
                                        <button
                                            v-if="can('submission_daily.edit')"
                                            :disabled="context?.status === 'approved' || context?.status === 'rejected'"
                                            :class="[
                                                'inline-flex items-center gap-1.5 rounded-xl border px-3 py-2 text-xs font-semibold transition sm:text-sm',
                                                (context?.status === 'approved' || context?.status === 'rejected')
                                                    ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400 opacity-70'
                                                    : 'border-amber-100 bg-amber-50 text-amber-700 hover:bg-amber-100',
                                            ]"
                                            @click.stop="(context?.status !== 'approved' && context?.status !== 'rejected') && openEditModal(context)"
                                        >
                                            <PencilSquareIcon class="h-4 w-4" />
                                            <span>Edit</span>
                                        </button>

                                        <button
                                            v-if="can('submission_daily.delete')"
                                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-100 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 sm:text-sm"
                                            @click.stop="confirmDelete(context.id)"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                            <span>Hapus</span>
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            </div>

            <Pagination
                :pagination="submissions.meta"
                @page-change="changePage"
                @per-page-change="perPageChanged"
                class="border-t border-[#DBDFE9]"
            />
        </div>

        <!-- Modal Components -->
        <DetailSick v-if="modalState.type === 'sick'" :is-open="modalState.isOpen && modalState.type === 'sick'"
            :submission="modalState.submission" :type="modalState.type" :submission-types="props.submission_types"
            :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
            @close="closeModal" @update-success="handleModalUpdateSuccess" />

        <DetailOvertime v-if="modalState.type === 'overtime'"
            :is-open="modalState.isOpen && modalState.type === 'overtime'" :submission="modalState.submission"
            :type="modalState.type" :submission-types="props.submission_types"
            :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
            @close="closeModal" @update-success="handleModalUpdateSuccess" />

        <DetailOther v-if="modalState.type === 'others'" :is-open="modalState.isOpen && modalState.type === 'others'"
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

        <DetailReimbursement v-if="modalState.type === 'reimbursement'"
            :is-open="modalState.isOpen && modalState.type === 'reimbursement'" :submission="modalState.submission"
            :type="modalState.type" :submission-types="props.submission_types"
            :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
            @close="closeModal" @update-success="handleModalUpdateSuccess" />

        <DetailGeneral v-if="modalState.type === 'general'"
            :is-open="modalState.isOpen && modalState.type === 'general'" :submission="modalState.submission"
            :type="modalState.type" :submission-types="props.submission_types"
            :submission-statuses="props.submission_statuses" :loading="modalState.loading" :error="modalState.error"
            @close="closeModal" @update-success="handleModalUpdateSuccess" />

        <!-- Create/Edit Daily Report Modal -->
        <div v-if="props.type === 'employee'"
            class="overflow-y-auto fixed inset-0 z-50 w-full h-full bg-gray-600 bg-opacity-50"
            :class="{ hidden: !formModalState.isOpen }">
            <div class="relative top-20 p-5 mx-auto w-11/12 bg-white rounded-md border shadow-lg md:w-2/3 lg:w-1/2">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{
                                formModalState.isEdit
                                    ? "Edit Pengajuan karyawan Harian"
                                    : "Tambah Pengajuan karyawan Harian"
                            }}
                        </h3>
                        <button @click="closeFormModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12">
                                </path>
                            </svg>
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div v-if="formModalState.loading" class="flex items-center justify-center p-8">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4">
                            </div>
                            <p class="text-gray-600">Memuat data pengajuan...</p>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div v-else-if="formModalState.error" class="p-4 mb-4 bg-red-50 border border-red-200 rounded-md">
                        <p class="text-sm text-red-600">{{ formModalState.error }}</p>
                        <button @click="closeFormModal"
                            class="mt-2 px-4 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700">
                            Tutup
                        </button>
                    </div>

                    <form v-else @submit.prevent="submitForm" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Start Date -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">Tanggal Mulai *</label>
                                <input type="date" v-model="formData.start_date" required
                                    class="px-3 py-2 w-full rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <div v-if="formErrors.start_date" class="mt-1 text-sm text-red-500">
                                    {{ formErrors.start_date }}
                                </div>
                            </div>

                            <!-- End Date -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">Tanggal Akhir *</label>
                                <input type="date" v-model="formData.end_date" required
                                    class="px-3 py-2 w-full rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <div v-if="formErrors.end_date" class="mt-1 text-sm text-red-500">
                                    {{ formErrors.end_date }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Name -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">Nama Karyawan Harian
                                    *</label>
                                <input type="text" v-model="formData.name" required
                                    class="px-3 py-2 w-full rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Masukkan nama karyawan harian" />
                                <div v-if="formErrors.name" class="mt-1 text-sm text-red-500">
                                    {{ formErrors.name }}
                                </div>
                            </div>

                            <!-- Salary -->
                            <div>
                                <label class="block mb-1 text-sm font-medium text-gray-700">Gaji *</label>
                                <input type="text" v-model="salaryDisplay" @input="handleSalaryInput"
                                    @blur="handleSalaryBlur" required
                                    class="px-3 py-2 w-full rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Masukkan gaji" />
                                <div v-if="formErrors.salary" class="mt-1 text-sm text-red-500">
                                    {{ formErrors.salary }}
                                </div>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Alasan *</label>
                            <textarea v-model="formData.reason" required rows="3"
                                class="px-3 py-2 w-full rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Masukkan alasan pengajuan..."></textarea>
                            <div v-if="formErrors.reason" class="mt-1 text-sm text-red-500">
                                {{ formErrors.reason }}
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex gap-3 justify-end pt-4">
                            <button type="button" @click="closeFormModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md border border-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Batal
                            </button>
                            <button type="submit" :disabled="formModalState.loading"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md border border-transparent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50">
                                <span v-if="formModalState.loading">Menyimpan...</span>
                                <span v-else>{{
                                    formModalState.isEdit ? "Update" : "Simpan"
                                }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Payment Modal for Debt -->
        <div v-if="paymentModalState.isOpen" @click="closePaymentModal"
            class="fixed inset-0 z-[9999] bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div @click.stop class="bg-white rounded-lg shadow-xl w-full max-w-4xl"
                style="max-height: 88vh; overflow-y: auto;">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Proses Pembayaran Piutang</h2>
                    <button @click="closePaymentModal"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors"
                        title="Tutup">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div v-if="paymentModalState.loading" class="flex items-center justify-center p-8">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                        <p class="text-gray-600">Memuat data pembayaran...</p>
                    </div>
                </div>

                <div v-else-if="paymentModalState.error" class="flex items-center justify-center p-8">
                    <div class="text-center">
                        <div class="text-red-500 mb-4">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <p class="text-red-600 mb-4">{{ paymentModalState.error }}</p>
                        <button @click="closePaymentModal"
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Tutup
                        </button>
                    </div>
                </div>

                <div v-else class="p-6 space-y-4">
                    <!-- Info Piutang -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg border">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                            <p class="text-gray-900 text-sm">
                                {{ paymentModalState.submission?.employee?.name || '-' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Piutang</label>
                            <p class="text-gray-900 text-sm font-semibold">
                                {{ formatCurrency(paymentModalState.submission?.amount) || '-' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Dibayar</label>
                            <p class="text-sm font-semibold text-green-600">
                                {{ formatCurrency(totalPaid) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sisa Piutang</label>
                            <p class="text-sm font-semibold text-red-600">
                                {{ formatCurrency(remainingDebt) }}
                            </p>
                        </div>
                    </div>

                    <!-- Header untuk Form dan Riwayat -->
                    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                        <h3 class="text-lg font-semibold text-gray-900">Riwayat Pembayaran</h3>
                        <button @click="showPaymentForm = !showPaymentForm"
                            class="inline-flex gap-2 items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>Tambah Pembayaran</span>
                        </button>
                    </div>

                    <!-- Form Pembayaran -->
                    <div v-if="showPaymentForm" class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pembayaran *</label>
                                <input type="date" v-model="paymentForm.paid_date" required
                                    class="w-full p-3 bg-white rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    :class="{ 'border-red-300': paymentFormErrors.paid_date }" />
                                <p v-if="paymentFormErrors.paid_date" class="text-red-500 text-xs mt-1">{{
                                    paymentFormErrors.paid_date }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pembayaran *</label>
                                <input type="text" v-model="paymentFormDisplay.amount" @input="handlePaymentAmountInput"
                                    @blur="handlePaymentAmountBlur" required
                                    class="w-full p-3 bg-white rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                    :class="{ 'border-red-300': paymentFormErrors.amount }"
                                    placeholder="Masukkan jumlah pembayaran" />
                                <p v-if="paymentFormErrors.amount" class="text-red-500 text-xs mt-1">{{
                                    paymentFormErrors.amount
                                    }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3 justify-end mt-4">
                            <button @click="cancelPaymentForm"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md border border-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                                Batal
                            </button>
                            <button @click="submitPayment" :disabled="paymentModalState.processing"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md border border-transparent hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span v-if="paymentModalState.processing">Menyimpan...</span>
                                <span v-else>Simpan Pembayaran</span>
                            </button>
                        </div>
                    </div>

                    <!-- Riwayat Pembayaran -->
                    <div class="space-y-2">
                        <div v-if="paymentHistory.length === 0" class="text-center py-8 text-gray-500">
                            <p>Belum ada riwayat pembayaran</p>
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full text-sm divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">No
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                            Tanggal
                                            Pembayaran</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                            Jumlah
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                            Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(payment, index) in paymentHistory" :key="payment.id"
                                        class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-900">{{ index + 1 }}</td>
                                        <td class="px-4 py-3 text-gray-900">{{ formatDisplayDate(payment.paid_date) }}</td>
                                        <td class="px-4 py-3 text-gray-900 font-semibold">{{
                                            formatCurrency(payment.amount) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <button @click="deletePayment(payment.id)"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                Hapus
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="deleteModalState.isOpen"
            class="overflow-y-auto fixed inset-0 z-50 w-full h-full bg-gray-600 bg-opacity-50">
            <div class="relative top-20 p-5 mx-auto w-96 bg-white rounded-md border shadow-lg">
                <div class="mt-3 text-center">
                    <div class="flex justify-center items-center mx-auto w-12 h-12 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">
                        Konfirmasi Hapus
                    </h3>
                    <div class="px-7 py-3 mt-2">
                        <p class="text-sm text-gray-500">
                            Apakah Anda yakin ingin menghapus pengajuan laporan
                            harian ini? Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                    <div class="flex gap-3 justify-center mt-4">
                        <button @click="closeDeleteModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md border border-gray-300 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Batal
                        </button>
                        <button @click="deleteSubmission" :disabled="deleteModalState.loading"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md border border-transparent hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50">
                            <span v-if="deleteModalState.loading">Menghapus...</span>
                            <span v-else>Hapus</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Pagination from "@/Components/common/PaginationResource.vue";
import SubmissionsTable from "./components/SubmissionsTable.vue";
import DetailSick from "./components/DetailSick.vue";
import DetailOvertime from "./components/DetailOvertime.vue";
import DetailOther from "./components/DetailOther.vue";
import DetailDebt from "./components/DetailDebt.vue";
import DetailDailyReport from "./components/DetailDailyReport.vue";
import DetailAnnualLeave from "./components/DetailAnnualLeave.vue";
import DetailReimbursement from "./components/DetailReimbursement.vue";
import DetailGeneral from "./components/DetailGeneral.vue";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";
import { computed, reactive, ref, watch } from "vue";
import {
    ArrowPathIcon,
    BanknotesIcon,
    ChevronRightIcon,
    CheckCircleIcon as OutlineCheckCircleIcon,
    ClockIcon as OutlineClockIcon,
    DocumentTextIcon,
    EyeIcon,
    MagnifyingGlassIcon,
    NoSymbolIcon,
    PencilSquareIcon,
    PlusIcon,
    TrashIcon,
    XCircleIcon as OutlineXCircleIcon,
} from "@heroicons/vue/24/outline";
import { Head, router, usePage } from "@inertiajs/vue3";
import { useAuth } from "@/Composables/useAuth";
import {
    formatIndonesianDate,
    formatIndonesianDateTime,
    parseDateValue,
} from "@/Helpers/dateFormat";

const props = defineProps({
    submissions: Object,
    sort_by: String,
    sort_direction: String,
    search: String,
    start_date: String,
    end_date: String,
    name_filter: String,
    branch_filter: String,
    type_filter: String,
    status_filter: String,
    title_filter: String,
    event_start_date: String,
    event_end_date: String,
    branches: Array,
    employees: Array,
    submission_types: Array,
    submission_statuses: Array,
    statistics: Object,
    type: String,
    types: [Array, Object],
    auth: Object,
});

const { user, can } = useAuth();
const toast = useToast();

// Get pending counts from page props
const page = usePage();
const pendingCounts = computed(() => page.props.pendingSubmissionsCount || { total: 0, by_type: {} });

const datatableConfig = reactive({
    showHeader: true,
    perPage: props.submissions?.meta?.per_page ?? 10,
    search: props.search ?? "",
    title: "Daftar Pengajuan",
    columns: [
        {
            label: "No",
            key: "id",
            sortable: true,
            style: "width: 8%; text-align: center;",
        },
        {
            label: "Tanggal",
            key: "submission_date",
            sortable: true,
            style: "width: 15%; text-align: left;",
        },
        {
            label: "Nama",
            key: "employee_id",
            sortable: true,
            style: "width: 25%; text-align: left;",
        },
        {
            label: "Cabang",
            key: "branch_id",
            sortable: true,
            style: "width: 25%; text-align: left;",
        },
        {
            label: "Tipe",
            key: "type",
            sortable: true,
            style: "width: 15%; text-align: left;",
        },
        {
            label: "Status",
            key: "status",
            sortable: true,
            style: "width: 8%; text-align: center;",
        },
        {
            label: "Aksi",
            key: "actions",
            sortable: false,
            style: "width: 5%; text-align: center;",
        },
    ],
    data: props.submissions?.data ?? [],
    loading: false,
    totalItems: props.submissions?.meta?.total ?? 0,
    currentPage: props.submissions?.meta?.current_page ?? 1,
    sortBy: props.sort_by ?? "id",
    sortDirection: props.sort_direction ?? "desc",
});

// Filter states
const filters = reactive({
    start_date: props.start_date ?? "",
    end_date: props.end_date ?? "",
    name: props.name_filter ?? "",
    branch: props.branch_filter ?? "",
    type: props.type_filter ?? "",
    status: props.status_filter ?? "",
    title: props.title_filter ?? "",
    event_start_date: props.event_start_date ?? "",
    event_end_date: props.event_end_date ?? "",
});

const pageTitle = computed(() => props.types?.[props.type] || "Daftar Pengajuan");

const visibleSubmissionTabs = computed(() => {
    return (props.submission_types || [])
        .filter((label) => !label.permission || can(label.permission))
        .map((label) => ({
            ...label,
            pendingCount:
                label.value == 0
                    ? pendingCounts.value.total || 0
                    : pendingCounts.value.by_type?.[label.value] || 0,
        }));
});

// Modal state management
const modalState = reactive({
    isOpen: false,
    type: null,
    submission: null,
    loading: false,
    error: null,
});

// Form modal state management
const formModalState = reactive({
    isOpen: false,
    isEdit: false,
    loading: false,
    error: null,
});

// Delete modal state management
const deleteModalState = reactive({
    isOpen: false,
    loading: false,
    submissionId: null,
});

// Payment modal state management
const paymentModalState = reactive({
    isOpen: false,
    loading: false,
    error: null,
    processing: false,
    submission: null,
    receivableId: null,
});

// Payment form state
const showPaymentForm = ref(false);
const paymentForm = reactive({
    paid_date: "",
    amount: 0,
});

const paymentFormDisplay = reactive({
    amount: "",
});

const paymentFormErrors = reactive({});
const paymentHistory = ref([]);

// Form data
const formData = reactive({
    employee_id: "",
    start_date: "",
    end_date: "",
    name: "",
    salary: 0,
    reason: "",
    status: "pending",
});

// Form errors
const formErrors = reactive({});

// Salary display (formatted as rupiah)
const salaryDisplay = ref("");

// Reactive reference to track filtered submissions data from SubmissionsTable
const filteredSubmissions = ref(props.submissions?.data || []);

// Track hovered row for visual feedback
const hoveredRow = ref(null);

const filterInputClass =
    "h-10 w-full rounded-lg border border-[#DBDFE9] bg-[#fbfbfb] px-3 text-sm text-gray-700 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-[#002875] focus:ring-2 focus:ring-[#002875]/20";
const filterDateInputClass =
    "h-9 w-full min-w-0 rounded-lg border border-[#DBDFE9] bg-[#fbfbfb] px-2.5 text-xs text-gray-700 shadow-sm outline-none transition focus:border-[#002875] focus:ring-2 focus:ring-[#002875]/20";
const filterCellClass = "px-4 py-3 align-top";
const tableHeadClass =
    "px-4 py-3 text-left font-medium text-gray-700 whitespace-nowrap";
const tableCellClass = "px-4 py-4 align-middle text-sm text-gray-900";
const filterButtonClass =
    "inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-[#002875] px-4 text-sm font-medium text-white shadow-sm transition hover:bg-[#001d5c] focus:outline-none focus:ring-2 focus:ring-[#002875]/20";

// Function to calculate statistics from submissions data
function calculateStatisticsFromData(submissions) {
    const stats = {
        pending: 0,
        approved: 0,
        rejected: 0,
        cancelled: 0,
    };

    submissions.forEach(submission => {
        const status = submission.status;
        if (stats.hasOwnProperty(status)) {
            stats[status]++;
        }
    });

    return stats;
}

// Handle statistics - use filtered data when SubmissionsTable is active, otherwise use server-side statistics
const cardList = computed(() => {
    let statistics;

    if (props.type === 'submissions') {
        // Calculate statistics from filtered submissions data
        statistics = calculateStatisticsFromData(filteredSubmissions.value);
    } else {
        // Use server-side statistics for other types
        statistics = props.statistics || {};
    }

    return [
        {
            icon: OutlineClockIcon,
            total: statistics.pending ?? 0,
            label: "Menunggu Direspon",
            iconClass: "text-amber-600",
            iconWrapperClass: "bg-amber-50 ring-amber-100",
            accentBarClass: "bg-amber-400/80",
        },
        {
            icon: OutlineCheckCircleIcon,
            total: statistics.approved ?? 0,
            label: "Disetujui",
            iconClass: "text-emerald-600",
            iconWrapperClass: "bg-emerald-50 ring-emerald-100",
            accentBarClass: "bg-emerald-400/80",
        },
        {
            icon: OutlineXCircleIcon,
            total: statistics.rejected ?? 0,
            label: "Ditolak",
            iconClass: "text-rose-600",
            iconWrapperClass: "bg-rose-50 ring-rose-100",
            accentBarClass: "bg-rose-400/80",
        },
        {
            icon: NoSymbolIcon,
            total: statistics.cancelled ?? 0,
            label: "Dibatalkan",
            iconClass: "text-slate-500",
            iconWrapperClass: "bg-slate-100 ring-slate-200",
            accentBarClass: "bg-slate-300",
        },
    ];
});

function formatCount(value) {
    return Number(value ?? 0).toLocaleString("id-ID");
}

function getTabClass(name) {
    return name === props.type
        ? "border-[#002875] text-[#002875]"
        : "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700";
}

function getTabBadgeClass(name) {
    return name === props.type ? "bg-[#002875] text-white" : "bg-gray-100 text-gray-600";
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
    return formatIndonesianDate(value);
}

function formatDisplayDate(value) {
    return formatIndonesianDate(value);
}

// Check if debt/receivable status is approved
function isDebtApproved(context) {
    if (!context || !context.status) return false;

    const status = context.status;

    // Handle string status
    if (typeof status === 'string') {
        return status === 'approved' || status === 'disetujui';
    }

    // Handle object with value property
    if (typeof status === 'object' && status !== null) {
        const statusValue = status.value || status.status || status;
        if (typeof statusValue === 'string') {
            return statusValue === 'approved' || statusValue === 'disetujui';
        }
    }

    return false;
}

// Format date for HTML date input (YYYY-MM-DD)
const formatDateForInput = (date) => {
    const parsed = parseDateValue(date);

    if (!parsed) {
        return "";
    }

    const year = parsed.getFullYear();
    const month = String(parsed.getMonth() + 1).padStart(2, "0");
    const day = String(parsed.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};

// Format currency to Indonesian Rupiah
const formatCurrency = (amount) => {
    if (!amount && amount !== 0) return "-";
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Format number to rupiah string (without currency symbol)
const formatRupiah = (amount) => {
    if (!amount && amount !== 0) return "";
    return new Intl.NumberFormat("id-ID", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Parse rupiah string to number
const parseRupiah = (rupiahString) => {
    if (!rupiahString) return 0;
    // Remove all non-digit characters
    const cleaned = rupiahString.toString().replace(/\D/g, "");
    return cleaned ? parseInt(cleaned, 10) : 0;
};

// Handle salary input
function handleSalaryInput(event) {
    const value = event.target.value;
    // Remove all non-digit characters
    const numericValue = parseRupiah(value);
    // Update formData with numeric value
    formData.salary = numericValue;
    // Update display with formatted value
    salaryDisplay.value = numericValue > 0 ? formatRupiah(numericValue) : "";
}

// Handle salary blur (final format)
function handleSalaryBlur() {
    if (formData.salary > 0) {
        salaryDisplay.value = formatRupiah(formData.salary);
    } else {
        salaryDisplay.value = "";
    }
}

// Calculate total columns based on submission type
function getTotalColumns() {
    // Base columns that are always present
    let columns = 4; // No, Tanggal Pengajuan, Karyawan, Cabang

    // Add Kategori Izin for 'others' type
    if (props.type === 'others') {
        columns += 1;
    }

    // Add date range columns for sick/annual-leave/others
    if (['sick', 'annual-leave', 'others'].includes(props.type)) {
        columns += 3; // Tanggal Mulai, Tanggal Akhir, Total Hari
    }

    // Add time columns for overtime
    if (props.type === 'overtime') {
        columns += 2; // Waktu Mulai, Waktu Selesai
    }

    // Add title, date, and amount columns for reimbursement
    if (props.type === 'reimbursement') {
        columns += 3; // Judul Reimbursement, Tanggal, Jumlah
    }

    // Add tenor, amount, and total paid columns for debt
    if (props.type === 'debt') {
        columns += 3; // Tenor, Jumlah Piutang, Jumlah yang Dibayar
    }

    // Add columns for general
    if (props.type === 'general') {
        columns += 2; // Title, Tag
    }

    // Add Status and Aksi (always at the end)
    columns += 2; // Status, Aksi

    return columns;
}

function fetchSubmissions({
    page = 1,
    perPage = datatableConfig.perPage,
    search = datatableConfig.search,
    sortBy = datatableConfig.sortBy,
    sortDirection = datatableConfig.sortDirection,
    startDate = filters.start_date,
    endDate = filters.end_date,
    nameFilter = filters.name,
    branchFilter = filters.branch,
    typeFilter = filters.type,
    statusFilter = filters.status,
    titleFilter = filters.title,
    eventStartDate = filters.event_start_date,
    eventEndDate = filters.event_end_date,
    onSuccess,
    onFinish,
} = {}) {
    datatableConfig.loading = true;
    const params = {
        page,
        per_page: perPage,
        search,
        sort_by: sortBy,
        sort_direction: sortDirection,
        start_date: startDate,
        end_date: endDate,
        name_filter: nameFilter,
        branch_filter: branchFilter,
        type_filter: typeFilter,
        status_filter: statusFilter,
        type: props.type,
    };

    // Add reimbursement-specific filters
    if (props.type === 'reimbursement') {
        params.title_filter = titleFilter;
        params.event_start_date = eventStartDate;
        params.event_end_date = eventEndDate;
    }

    router.get(
        `/${props.type}`,
        params,
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onSuccess: (page) => {
                datatableConfig.data = page.props.submissions.data;
                datatableConfig.totalItems = page.props.submissions.meta.total;
                datatableConfig.currentPage =
                    page.props.submissions.meta.current_page;
                datatableConfig.loading = false;

                if (onSuccess) onSuccess(page);
            },
            onFinish: () => {
                datatableConfig.loading = false;
                if (onFinish) onFinish();
            },
        }
    );
}

function perPageChanged(newPerPage) {
    console.log("perPageChanged called with:", newPerPage);
    datatableConfig.perPage = newPerPage;
    datatableConfig.currentPage = 1;
    fetchSubmissions({
        page: 1,
        perPage: newPerPage,
        startDate: filters.start_date,
        endDate: filters.end_date,
        nameFilter: filters.name,
        branchFilter: filters.branch,
        typeFilter: filters.type,
        statusFilter: filters.status,
        titleFilter: filters.title,
        eventStartDate: filters.event_start_date,
        eventEndDate: filters.event_end_date,
    });
}

function changeSort(col) {
    const key = typeof col === "string" ? col : col?.key;
    const direction =
        datatableConfig.sortBy === key &&
            datatableConfig.sortDirection === "asc"
            ? "desc"
            : "asc";
    datatableConfig.sortBy = key;
    datatableConfig.sortDirection = direction;
    fetchSubmissions({
        page: datatableConfig.currentPage,
        sortBy: key,
        sortDirection: direction,
    });
}

function changeSearch(value) {
    datatableConfig.search = value;
    datatableConfig.currentPage = 1;
    fetchSubmissions({ page: 1, search: value });
}

function changePage(page) {
    datatableConfig.currentPage = page;
    fetchSubmissions({
        page,
        startDate: filters.start_date,
        endDate: filters.end_date,
        nameFilter: filters.name,
        branchFilter: filters.branch,
        typeFilter: filters.type,
        statusFilter: filters.status,
        titleFilter: filters.title,
        eventStartDate: filters.event_start_date,
        eventEndDate: filters.event_end_date,
    });
}

// Filter functions
function applyFilters() {
    datatableConfig.currentPage = 1;
    fetchSubmissions({
        page: 1,
        startDate: filters.start_date,
        endDate: filters.end_date,
        nameFilter: filters.name,
        branchFilter: filters.branch,
        typeFilter: filters.type,
        statusFilter: filters.status,
        titleFilter: filters.title,
        eventStartDate: filters.event_start_date,
        eventEndDate: filters.event_end_date,
    });
}

function clearFilters() {
    filters.start_date = "";
    filters.end_date = "";
    filters.name = "";
    filters.branch = "";
    filters.type = "";
    filters.status = "";
    filters.title = "";
    filters.event_start_date = "";
    filters.event_end_date = "";
    datatableConfig.currentPage = 1;
    fetchSubmissions({ page: 1 });
}

// Debounce search for name filter
let searchTimeout;
function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
}

// Modal functions
async function openModal(submission, type) {
    modalState.isOpen = true;
    modalState.type = type;
    modalState.loading = true;
    modalState.error = null;

    // Set initial submission data
    modalState.submission = submission;
    console.log(type);

    try {
        // Fetch detailed submission data using fetch API
        const response = await fetch(
            route(`submission.${type}.show`, submission.id),
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
            }
        );

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            // Wrap the data in the expected structure for modal components
            modalState.submission = {
                data: data.data,
                submission_types: data.submission_types,
                submission_statuses: data.submission_statuses,
                type: data.type,
            };
            modalState.loading = false;
        } else {
            throw new Error(data.message || "Gagal memuat detail pengajuan");
        }
    } catch (error) {
        console.error("Error fetching submission details:", error);
        modalState.error = error.message || "Gagal memuat detail pengajuan";
        modalState.loading = false;
    }
}

function closeModal() {
    modalState.isOpen = false;
    modalState.submission = null;
    modalState.type = null;
    modalState.loading = false;
    modalState.error = null;
}

// Function to update filtered submissions data from SubmissionsTable
function updateFilteredSubmissions(filteredData) {
    filteredSubmissions.value = filteredData;
}

// Function to refresh table data after successful update
function refreshTableData() {
    console.log("Refreshing table data...");
    // Use router.reload to refresh the current page data
    router.reload({
        only: ["submissions", "statistics"],
        onSuccess: () => {
            datatableConfig.data = props.submissions?.data ?? [];
            // Also update filtered submissions for submissions type
            if (props.type === 'submissions') {
                filteredSubmissions.value = props.submissions?.data ?? [];
            }
            console.log("Table data refreshed successfully");
        },
        onError: (errors) => {
            console.error("Error refreshing table data:", errors);
        },
    });
}

// Handle modal update success
function handleModalUpdateSuccess() {
    console.log("Modal update successful, refreshing table...");
    // Close modal first
    closeModal();
    // Then refresh table data
    refreshTableData();
}

// Form modal functions
function openCreateModal() {
    formModalState.isOpen = true;
    formModalState.isEdit = false;
    formModalState.loading = false;
    formModalState.error = null;

    // Reset form data
    Object.assign(formData, {
        employee_id: user.employee.id,
        start_date: "",
        end_date: "",
        name: "",
        salary: 0,
        reason: "",
        status: "pending",
    });

    // Reset salary display
    salaryDisplay.value = "";

    // Clear errors
    Object.keys(formErrors).forEach((key) => delete formErrors[key]);

    // Employees are already loaded from props
}

async function openEditModal(submission) {
    formModalState.isOpen = true;
    formModalState.isEdit = true;
    formModalState.loading = true;
    formModalState.error = null;

    // Store submission ID for update
    modalState.submission = submission;

    try {
        // Fetch detailed submission data using fetch API
        const response = await fetch(
            route('submission.employee.show', submission.id),
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
            }
        );

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success && data.data) {
            const submissionData = data.data;

            // Populate form data with detailed data
            Object.assign(formData, {
                employee_id: submissionData.employee_id || submissionData.employee?.id || "",
                start_date: submissionData.start_date
                    ? formatDateForInput(submissionData.start_date)
                    : "",
                end_date: submissionData.end_date
                    ? formatDateForInput(submissionData.end_date)
                    : "",
                name: submissionData.name || "",
                salary: submissionData.salary || 0,
                reason: submissionData.reason || "",
            });

            // Format salary display
            salaryDisplay.value = formData.salary > 0 ? formatRupiah(formData.salary) : "";

            formModalState.loading = false;
        } else {
            throw new Error(data.message || "Gagal memuat detail pengajuan");
        }
    } catch (error) {
        console.error("Error fetching submission details:", error);
        formModalState.error = error.message || "Gagal memuat detail pengajuan";
        formModalState.loading = false;
    }

    // Clear errors
    Object.keys(formErrors).forEach((key) => delete formErrors[key]);
}

function closeFormModal() {
    formModalState.isOpen = false;
    formModalState.isEdit = false;
    formModalState.loading = false;
    formModalState.error = null;

    // Clear form data
    Object.assign(formData, {
        employee_id: "",
        start_date: "",
        end_date: "",
        name: "",
        salary: 0,
        reason: "",
        status: "pending",
    });

    // Reset salary display
    salaryDisplay.value = "";

    // Clear errors
    Object.keys(formErrors).forEach((key) => delete formErrors[key]);
}

async function submitForm() {
    formModalState.loading = true;
    formModalState.error = null;

    // Clear previous errors
    Object.keys(formErrors).forEach((key) => delete formErrors[key]);

    // Ensure salary is numeric (parse from display if needed)
    if (typeof formData.salary === 'string') {
        formData.salary = parseRupiah(formData.salary);
    }

    // Prepare data to submit
    const submitData = { ...formData };

    // For create, ensure status is "pending" and employee_id is from logged-in user
    if (!formModalState.isEdit) {
        submitData.status = "pending";
        submitData.employee_id = user.employee.id;
    }
    // For edit, don't send status (let backend keep existing status)
    else {
        delete submitData.status;
    }

    try {
        const url = formModalState.isEdit
            ? route("submission.employee.update", modalState.submission?.id)
            : route("submission.employee.store");

        const method = formModalState.isEdit ? "PUT" : "POST";

        const response = await fetch(url, {
            method: method,
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
            body: JSON.stringify(submitData),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: data.message || (formModalState.isEdit
                    ? "Pengajuan karyawan Harian berhasil diperbarui"
                    : "Pengajuan karyawan Harian berhasil dibuat"),
                life: 3000,
            });
            closeFormModal();
            refreshTableData();
        } else {
            // Handle validation errors
            if (data.errors) {
                Object.assign(formErrors, data.errors);
            } else {
                const errorMessage = data.message || "Terjadi kesalahan saat menyimpan data";
                formModalState.error = errorMessage;
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: errorMessage,
                    life: 5000,
                });
            }
        }
    } catch (error) {
        console.error("Error submitting form:", error);
        const errorMessage = "Terjadi kesalahan saat menyimpan data";
        formModalState.error = errorMessage;
        toast.add({
            severity: "error",
            summary: "Error",
            detail: errorMessage,
            life: 5000,
        });
    } finally {
        formModalState.loading = false;
    }
}

// Delete modal functions
function confirmDelete(submissionId) {
    deleteModalState.isOpen = true;
    deleteModalState.submissionId = submissionId;
    deleteModalState.loading = false;
}

function closeDeleteModal() {
    deleteModalState.isOpen = false;
    deleteModalState.submissionId = null;
    deleteModalState.loading = false;
}

async function deleteSubmission() {
    if (!deleteModalState.submissionId) return;

    deleteModalState.loading = true;

    try {
        const response = await fetch(
            route("submission.employee.destroy", deleteModalState.submissionId),
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
            }
        );

        const data = await response.json();

        if (response.ok && data.success) {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: data.message || "Pengajuan karyawan Harian berhasil dihapus",
                life: 3000,
            });
            closeDeleteModal();
            refreshTableData();
        } else {
            const errorMessage = data.message || "Terjadi kesalahan saat menghapus data";
            toast.add({
                severity: "error",
                summary: "Error",
                detail: errorMessage,
                life: 5000,
            });
        }
    } catch (error) {
        console.error("Error deleting submission:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Terjadi kesalahan saat menghapus data",
            life: 5000,
        });
    } finally {
        deleteModalState.loading = false;
    }
}

// Computed for payment modal
const totalPaid = computed(() => {
    return paymentHistory.value.reduce((sum, payment) => sum + (parseFloat(payment.amount) || 0), 0);
});

const remainingDebt = computed(() => {
    const totalAmount = parseFloat(paymentModalState.submission?.amount) || 0;
    return Math.max(0, totalAmount - totalPaid.value);
});

// Payment modal functions
async function openPaymentModal(submission) {
    paymentModalState.isOpen = true;
    paymentModalState.loading = true;
    paymentModalState.error = null;
    paymentModalState.submission = submission;
    paymentModalState.receivableId = submission.id;
    showPaymentForm.value = false;
    paymentHistory.value = [];

    // Reset form - set default date to today
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, "0");
    const day = String(today.getDate()).padStart(2, "0");
    paymentForm.paid_date = `${year}-${month}-${day}`;
    paymentForm.amount = 0;
    paymentFormDisplay.amount = "";
    Object.keys(paymentFormErrors).forEach((key) => delete paymentFormErrors[key]);

    try {
        // Fetch payment history - using API endpoint
        const response = await fetch(
            `/api/v1/receivables/${submission.id}/payments`,
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
                credentials: "include", // Include cookies for Sanctum authentication
            }
        );

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            paymentHistory.value = data.data || [];
        } else {
            throw new Error(data.message || "Gagal memuat riwayat pembayaran");
        }
    } catch (error) {
        console.error("Error fetching payment history:", error);
        paymentModalState.error = error.message || "Gagal memuat riwayat pembayaran";
    } finally {
        paymentModalState.loading = false;
    }
}

function closePaymentModal() {
    paymentModalState.isOpen = false;
    paymentModalState.submission = null;
    paymentModalState.receivableId = null;
    paymentModalState.loading = false;
    paymentModalState.error = null;
    showPaymentForm.value = false;
    paymentHistory.value = [];

    // Reset form
    paymentForm.paid_date = "";
    paymentForm.amount = 0;
    paymentFormDisplay.amount = "";
    Object.keys(paymentFormErrors).forEach((key) => delete paymentFormErrors[key]);
}

function cancelPaymentForm() {
    showPaymentForm.value = false;
    // Reset form - set default date to today
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, "0");
    const day = String(today.getDate()).padStart(2, "0");
    paymentForm.paid_date = `${year}-${month}-${day}`;
    paymentForm.amount = 0;
    paymentFormDisplay.amount = "";
    Object.keys(paymentFormErrors).forEach((key) => delete paymentFormErrors[key]);
}

function handlePaymentAmountInput(event) {
    const value = event.target.value;
    const numericValue = parseRupiah(value);
    paymentForm.amount = numericValue;
    paymentFormDisplay.amount = numericValue > 0 ? formatRupiah(numericValue) : "";
}

function handlePaymentAmountBlur() {
    if (paymentForm.amount > 0) {
        paymentFormDisplay.amount = formatRupiah(paymentForm.amount);
    } else {
        paymentFormDisplay.amount = "";
    }
}

async function submitPayment() {
    paymentModalState.processing = true;
    Object.keys(paymentFormErrors).forEach((key) => delete paymentFormErrors[key]);

    // Validation
    if (!paymentForm.paid_date) {
        paymentFormErrors.paid_date = "Tanggal pembayaran wajib diisi";
        paymentModalState.processing = false;
        return;
    }

    if (!paymentForm.amount || paymentForm.amount <= 0) {
        paymentFormErrors.amount = "Jumlah pembayaran wajib diisi dan harus lebih dari 0";
        paymentModalState.processing = false;
        return;
    }

    if (paymentForm.amount > remainingDebt.value) {
        paymentFormErrors.amount = "Jumlah pembayaran tidak boleh melebihi sisa piutang";
        paymentModalState.processing = false;
        return;
    }

    try {
        const response = await fetch(
            `/api/v1/receivables/${paymentModalState.receivableId}/payments`,
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
                credentials: "include", // Include cookies for Sanctum authentication
                body: JSON.stringify({
                    paid_date: paymentForm.paid_date,
                    amount: paymentForm.amount,
                }),
            }
        );

        const data = await response.json();

        if (response.ok && data.success) {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: data.message || "Pembayaran berhasil ditambahkan",
                life: 3000,
            });

            // Refresh payment history
            await openPaymentModal(paymentModalState.submission);
            showPaymentForm.value = false;

            // Refresh table data to update the debt table
            refreshTableData();
        } else {
            if (data.errors) {
                Object.assign(paymentFormErrors, data.errors);
            } else {
                paymentModalState.error = data.message || "Terjadi kesalahan saat menyimpan pembayaran";
            }
            toast.add({
                severity: "error",
                summary: "Error",
                detail: data.message || "Terjadi kesalahan saat menyimpan pembayaran",
                life: 5000,
            });
        }
    } catch (error) {
        console.error("Error submitting payment:", error);
        paymentModalState.error = "Terjadi kesalahan saat menyimpan pembayaran";
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Terjadi kesalahan saat menyimpan pembayaran",
            life: 5000,
        });
    } finally {
        paymentModalState.processing = false;
    }
}

async function deletePayment(paymentId) {
    if (!confirm("Apakah Anda yakin ingin menghapus pembayaran ini?")) {
        return;
    }

    try {
        const response = await fetch(
            `/api/v1/receivables/payments/${paymentId}`,
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
                credentials: "include", // Include cookies for Sanctum authentication
            }
        );

        const data = await response.json();

        if (response.ok && data.success) {
            toast.add({
                severity: "success",
                summary: "Berhasil",
                detail: data.message || "Pembayaran berhasil dihapus",
                life: 3000,
            });

            // Refresh payment history
            await openPaymentModal(paymentModalState.submission);

            // Refresh table data to update the debt table
            refreshTableData();
        } else {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: data.message || "Terjadi kesalahan saat menghapus pembayaran",
                life: 5000,
            });
        }
    } catch (error) {
        console.error("Error deleting payment:", error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Terjadi kesalahan saat menghapus pembayaran",
            life: 5000,
        });
    }
}

// Watch for submissions data changes and update filtered submissions
watch(
    () => props.submissions?.data,
    (newData) => {
        if (props.type === 'submissions' && newData) {
            filteredSubmissions.value = newData;
        }
    },
    { deep: true, immediate: true }
);

defineOptions({
    layout: AppLayout,
});
</script>
