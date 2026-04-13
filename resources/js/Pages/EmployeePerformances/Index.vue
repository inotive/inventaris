<template>

    <Head title="Kinerja Karyawan" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <div class="flex overflow-hidden flex-col bg-white rounded-lg border border-gray-200">
            <div class="flex justify-between items-center px-6 py-3 text-xl font-semibold text-gray-800 border-b">
                <div>Daftar Kinerja Karyawan</div>
                <button v-if="isCalculateKPI && local.tab === 'performance'" type="button" @click="calculateKPI"
                    :disabled="calculating"
                    class="px-4 h-9 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed whitespace-nowrap flex items-center gap-2">
                    <span v-if="calculating">Menghitung...</span>
                    <span v-else>Hitung KPI Kehadiran & Checklist</span>
                </button>
            </div>

            <!-- Tabs -->
            <div class="px-6 pt-2 border-b">
                <div class="flex gap-4 items-center mb-0 text-sm">
                    <button @click="local.tab = 'performance'" :class="[
                        'px-3 py-3 font-medium transition-colors border-b-2',
                        local.tab === 'performance'
                            ? 'text-blue-600 border-blue-600'
                            : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'
                    ]">
                        Performance
                    </button>
                    <button @click="local.tab = 'history'" :class="[
                        'px-3 py-3 font-medium transition-colors border-b-2',
                        local.tab === 'history'
                            ? 'text-blue-600 border-blue-600'
                            : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300'
                    ]">
                        Riwayat Penilaian
                    </button>
                </div>
            </div>

            <!-- Toolbar: search and filters -->
            <div class="flex flex-col gap-2 px-6 py-3 border-b md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap gap-2 items-center flex-1">
                    <div class="relative">
                        <input v-model="local.q" type="text" placeholder="Cari nama karyawan..."
                            class="py-2.5 pr-8 pl-3 w-64 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20" />
                        <span class="absolute right-2 top-1/2 text-gray-400 -translate-y-1/2">🔍</span>
                    </div>
                    <div class="w-auto min-w-[160px] max-w-[200px]">
                        <select v-model="local.branch_id" :disabled="!isSuperadmin"
                            class="w-full px-3 h-9 text-sm rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100 disabled:bg-gray-100 disabled:cursor-not-allowed"
                            @change="onBranchChange">
                            <option :value="null">Semua Cabang</option>
                            <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                {{ branch.name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-auto min-w-[160px] max-w-[200px]">
                        <select v-model="local.department_id"
                            class="w-full px-3 h-9 text-sm rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option :value="null">Semua Departemen</option>
                            <option v-for="department in departments" :key="department.id" :value="department.id">
                                {{ department.name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-auto min-w-[140px] max-w-[160px]">
                        <select v-model="local.status"
                            class="w-full px-3 h-9 text-sm rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option :value="null">Semua Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div class="w-auto min-w-[100px] max-w-[120px]">
                        <select v-model="local.month"
                            class="w-full px-3 h-9 text-sm rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option v-for="m in monthOptions" :key="m.value" :value="m.value">
                                {{ m.label }}
                            </option>
                        </select>
                    </div>
                    <div class="w-auto min-w-[100px] max-w-[120px]">
                        <select v-model="local.year"
                            class="w-full px-3 h-9 text-sm rounded-lg border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            <option v-for="y in yearOptions" :key="y" :value="y">
                                {{ y }}
                            </option>
                        </select>
                    </div>
                    <button type="button" @click="resetFilter"
                        class="px-3 h-9 text-sm text-gray-700 rounded-lg border border-gray-200 hover:bg-gray-50 whitespace-nowrap">
                        Reset Filter
                    </button>
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <!-- Performance Table -->
                <table v-if="local.tab === 'performance'" class="min-w-full text-sm">
                    <thead>
                        <!-- Row 1: Main Headers with Rowspan & Colspan -->
                        <tr>
                            <th rowspan="2" class="p-3 bg-gray-100 border-gray-200 border-y">
                                <div class="font-medium text-center text-gray-600">
                                    No
                                </div>
                            </th>
                            <th rowspan="2" class="p-3 bg-gray-100 border border-gray-200">
                                <div class="font-medium text-left text-gray-600">
                                    Nama Karyawan
                                </div>
                            </th>
                            <th rowspan="2" class="p-3 bg-gray-100 border border-gray-200">
                                <div class="font-medium text-left text-gray-600">
                                    Cabang
                                </div>
                            </th>
                            <th rowspan="2" class="p-3 bg-gray-100 border border-gray-200">
                                <div class="font-medium text-left text-gray-600">
                                    Departemen
                                </div>
                            </th>
                            <th rowspan="2" class="p-3 bg-gray-100 border border-gray-200">
                                <div class="font-medium text-left text-gray-600">
                                    Jabatan
                                </div>
                            </th>
                            <th colspan="5" class="p-3 bg-blue-50 border-gray-200 border-y">
                                <div class="font-medium text-center text-blue-700">
                                    KPI Karyawan
                                </div>
                            </th>
                            <th rowspan="2" class="p-3 bg-gray-100 border border-gray-200">
                                <div class="font-medium text-center text-gray-600">
                                    Total
                                </div>
                            </th>
                        </tr>
                        <!-- Row 2: Sub Headers -->
                        <tr>
                            <th class="p-2 bg-blue-50 border border-gray-200">
                                <div class="text-xs font-medium text-center text-gray-600">
                                    Kehadiran
                                </div>
                            </th>
                            <th class="p-2 bg-blue-50 border border-gray-200">
                                <div class="text-xs font-medium text-center text-gray-600">
                                    Checklist
                                </div>
                            </th>
                            <th class="p-2 bg-blue-50 border border-gray-200">
                                <div class="text-xs font-medium text-center text-gray-600">
                                    Productivity
                                </div>
                            </th>
                            <th class="p-2 bg-blue-50 border border-gray-200">
                                <div class="text-xs font-medium text-center text-gray-600">
                                    Attitude
                                </div>
                            </th>
                            <th class="p-2 bg-blue-50 border border-gray-200">
                                <div class="text-xs font-medium text-center text-gray-600">
                                    Initiative
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filteredData.length" v-for="(row, idx) in filteredData" :key="row.id"
                            class="border-b border-gray-200 transition-colors hover:bg-gray-50">
                            <td class="p-3 text-center">
                                {{
                                    ((employeePerformances?.current_page || 1) - 1) *
                                    (employeePerformances?.per_page || 15) +
                                    idx +
                                    1
                                }}
                            </td>
                            <td class="p-3">
                                <span class="font-medium text-gray-800">
                                    {{ row.employee || "-" }}
                                </span>
                            </td>
                            <td class="p-3">
                                {{ row.cabang || "-" }}
                            </td>
                            <td class="p-3">
                                {{ row.departemen || "-" }}
                            </td>
                            <td class="p-3">
                                {{ row.jabatan || "-" }}
                            </td>
                            <td class="p-3 text-center">
                                <span :class="getScoreClass(row.Kehadiran, 25)"
                                    class="inline-flex items-center justify-center w-10 h-10 font-semibold rounded-xl">
                                    {{ row.Kehadiran || 0 }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span :class="getScoreClass(row.Checklist, 20)"
                                    class="inline-flex items-center justify-center w-10 h-10 font-semibold rounded-xl">
                                    {{ row.Checklist || 0 }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span :class="getScoreClass(row.Productivity, 25)"
                                    class="inline-flex items-center justify-center w-10 h-10 font-semibold rounded-xl cursor-pointer transition-opacity hover:opacity-80"
                                    @click.stop="openAssessmentList(row.id, 'Productivity')">
                                    {{ row.Productivity || 0 }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span :class="getScoreClass(row.Attitude, 15)"
                                    class="inline-flex items-center justify-center w-10 h-10 font-semibold rounded-xl cursor-pointer transition-opacity hover:opacity-80"
                                    @click.stop="openAssessmentList(row.id, 'Attitude')">
                                    {{ row.Attitude || 0 }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span :class="getScoreClass(row.Initiative, 15)"
                                    class="inline-flex items-center justify-center w-10 h-10 font-semibold rounded-xl cursor-pointer transition-opacity hover:opacity-80"
                                    @click.stop="openAssessmentList(row.id, 'Initiative')">
                                    {{ row.Initiative || 0 }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span :class="getScoreClass(getTotalScore(row), 100)"
                                    class="inline-flex items-center justify-center w-10 h-10 font-semibold rounded-xl">
                                    {{ getTotalScore(row) }}
                                </span>
                            </td>
                        </tr>
                        <!-- Empty State -->
                        <tr v-else-if="!filteredData.length" class="border-b border-gray-200">
                            <td colspan="11" class="py-8 text-center text-gray-500">
                                <div class="flex flex-col gap-2 items-center">
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="text-sm font-medium">
                                        Tidak ada data
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- History Table -->
                <table v-else class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="p-3 bg-gray-100 border-gray-200 border-y">
                                <div class="font-medium text-center text-gray-600">No</div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div class="font-medium text-left text-gray-600">Karyawan yang dinilai</div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div class="font-medium text-left text-gray-600">Kategori Penilaian</div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div class="font-medium text-left text-gray-600">Penilai</div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div class="font-medium text-center text-gray-600">Score</div>
                            </th>
                            <th class="p-3 bg-gray-100 border-gray-200 border-y">
                                <div class="font-medium text-left text-gray-600">Tanggal</div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div class="font-medium text-center text-gray-600">Aksi</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filteredData.length" v-for="(row, idx) in filteredData" :key="row.id"
                            class="border-b border-gray-200 transition-colors hover:bg-gray-50">
                            <td class="p-3 text-center">
                                {{
                                    ((employeePerformances?.current_page || 1) - 1) *
                                    (employeePerformances?.per_page || 15) +
                                    idx +
                                    1
                                }}
                            </td>
                            <td class="p-3">
                                <div class="flex gap-3 items-center">
                                    <AvatarInitials :name="row.employee || '-'" :gender="row.gender || ''" :size="32" />
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800">
                                            {{ row.employee || "-" }}
                                        </span>
                                        <div class="text-xs text-gray-500">
                                            {{ row.cabang || "-" }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full" :class="{
                                    'bg-blue-100 text-blue-700': ['Productivity', 'Keterampilan'].includes(row.category),
                                    'bg-purple-100 text-purple-700': ['Attitude', 'Kerjasama'].includes(row.category),
                                    'bg-orange-100 text-orange-700': ['Initiative', 'Disiplin'].includes(row.category),
                                    'bg-green-100 text-green-700': ['Kehadiran'].includes(row.category),
                                    'bg-cyan-100 text-cyan-700': ['Checklist', 'Kuantitas'].includes(row.category)
                                }">
                                    {{ row.category || "-" }}
                                </span>
                            </td>
                            <td class="p-3">
                                {{ row.penilai || "-" }}
                            </td>
                            <td class="p-3 text-center">
                                <span :class="getScoreClass(row.score, getResultMaxScore(row.category))"
                                    class="inline-flex items-center justify-center w-10 h-10 font-semibold rounded-xl">
                                    {{ row.score }}
                                </span>
                            </td>
                            <td class="p-3 text-gray-500">
                                {{ new Date(row.created_at).toLocaleDateString('id-ID', {
                                    day: 'numeric', month:
                                        'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                                }) }}
                            </td>
                            <td class="p-3 text-center">
                                <button type="button" @click.stop="openDetailModal(row)"
                                    class="px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                        <tr v-else class="border-b border-gray-200">
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                <div class="flex flex-col gap-2 items-center">
                                    <span class="text-sm font-medium">Tidak ada data riwayat</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination v-if="employeePerformances?.data?.length" :pagination="employeePerformances"
                @page-changed="changePage" @per-page-changed="perPageChanged" class="border-t" />
        </div>

        <!-- Score Modal -->
        <Dialog v-model:visible="showScoreModal" modal dismissableMask
            :breakpoints="{ '960px': '80vw', '640px': '95vw' }" :style="{ width: '600px', padding: '6px' }"
            :pt="dialogPt">
            <!-- Header -->
            <template #header>
                <div class="flex justify-between items-center w-full">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ scoreForm.id ? 'Update' : 'Tambah' }} Score {{ scoreForm.category }}
                    </h3>
                </div>
            </template>
            <!-- Content -->
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label class="font-normal text-dark">Nama Karyawan</label>
                    <p class="font-normal text-gray-500">
                        {{ scoreForm.employee_name || "-" }}
                    </p>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-normal text-dark">Periode</label>
                    <p class="font-normal text-gray-500">
                        {{ getMonthName(local.month) }} {{ local.year }}
                    </p>
                </div>
                <div class="flex flex-col gap-2" v-if="getCategoryInfo(scoreForm.category)">
                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="mb-2 text-sm font-semibold text-blue-800">
                            {{ getCategoryInfo(scoreForm.category)?.title }}
                        </p>
                        <p class="mb-3 text-xs text-gray-600">
                            Berikan nilai 1-5 untuk setiap indikator
                        </p>
                    </div>
                </div>
                <div class="flex flex-col gap-4">
                    <label class="font-normal text-dark">Penilaian Per Indikator <span
                            class="text-red-500">*</span></label>
                    <div v-for="(indicator, index) in getCategoryInfo(scoreForm.category)?.items || []" :key="index"
                        class="flex flex-col gap-2">
                        <label class="text-sm font-medium text-gray-700">
                            {{ indicator }}
                        </label>
                        <input v-model.number="scoreForm.score[indicator]" type="number" min="1" max="5" step="1"
                            :placeholder="`Masukkan nilai 1-5`"
                            class="px-3 w-full h-10 rounded border border-gray-300 focus:border-blue-400 focus:ring-2 focus:ring-blue-100" />
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="font-normal text-dark">Catatan</label>
                    <textarea v-model="scoreForm.notes" rows="3" placeholder="Masukkan catatan (opsional)"
                        class="px-3 py-2 w-full rounded border border-gray-300"></textarea>
                </div>
                <div class="flex gap-2 justify-end">
                    <button class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                        type="button" @click="closeScoreModal">
                        Batal
                    </button>
                    <button v-if="isEdit"
                        class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                        type="button" @click="saveScore" :disabled="saving">
                        {{ saving ? 'Menyimpan...' : (scoreForm.id ? 'Update' : 'Simpan') }}
                    </button>
                </div>
            </div>
        </Dialog>

        <!-- Detail History Modal -->
        <Dialog v-model:visible="showDetailModal" modal dismissableMask
            :breakpoints="{ '960px': '80vw', '640px': '95vw' }" :style="{ width: '550px' }" :pt="dialogPt">
            <!-- Header -->
            <template #header>
                <div class="flex items-center gap-3 w-full border-b pb-4">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            Detail Penilaian
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ detailForm.category }}
                        </p>
                    </div>
                </div>
            </template>

            <!-- Content -->
            <div class="flex flex-col gap-6 py-2">
                <!-- Info Cards -->
                <!-- Info Cards -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Employee Info -->
                    <div class="p-4 col-span-2 md:col-span-1 bg-white rounded-xl border border-gray-100 shadow-sm">
                        <div class="flex items-center gap-3 mb-3">
                            <AvatarInitials :name="detailForm.employee_name || '-'" :gender="detailForm.gender || ''"
                                :size="40" />
                            <div>
                                <div class="text-sm font-bold text-gray-900 line-clamp-1"
                                    :title="detailForm.employee_name">
                                    {{ detailForm.employee_name || "-" }}
                                </div>
                                <div class="text-xs text-gray-500 line-clamp-1">
                                    {{ detailForm.cabang || "-" }}
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-gray-50">
                            <div>
                                <div class="text-[10px] uppercase text-gray-400 font-semibold">Periode</div>
                                <div class="text-sm font-medium text-gray-700">
                                    {{ getMonthName(local.month) }} {{ local.year }}
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase text-gray-400 font-semibold">Score</div>
                                <div class="text-sm font-bold">
                                    <span
                                        :class="['px-2 py-0.5 rounded text-xs', getScoreClass(detailForm.accumulated_score, getResultMaxScore(detailForm.category))]">
                                        {{ detailForm.accumulated_score || 0 }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assessment Details -->
                    <div class="p-4 col-span-2 md:col-span-1 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="flex flex-col gap-3">
                            <div>
                                <div class="text-[10px] uppercase text-gray-400 font-semibold mb-1">Penilai</div>
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-500">
                                        {{ (detailForm.penilai || '?').charAt(0) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-700 line-clamp-1">
                                        {{ detailForm.penilai || "-" }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] uppercase text-gray-400 font-semibold mb-1">Tanggal Penilaian
                                </div>
                                <div class="text-sm text-gray-700 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ detailForm.created_at ? new
                                        Date(detailForm.created_at).toLocaleDateString('id-ID', {
                                            day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
                                        }) : '-'
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scores List -->
                <div>
                    <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-blue-600 rounded-full"></span>
                        Indikator Penilaian
                    </h4>
                    <div class="grid gap-2">
                        <div class="grid gap-3">
                            <div v-for="(score, indicator) in detailForm.score" :key="indicator"
                                class="group p-3 bg-white border border-gray-100 rounded-xl shadow-xs transition-all hover:border-blue-100 hover:shadow-sm">
                                <div class="flex justify-between items-end mb-2">
                                    <span class="text-sm font-medium text-gray-700 flex-1 mr-4">
                                        {{ indicator }}
                                    </span>
                                    <span class="text-sm font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                                        {{ score }}<span class="text-xs font-normal text-gray-400">/5</span>
                                    </span>
                                </div>
                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500 ease-out"
                                        :style="{ width: `${(score / 5) * 100}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="detailForm.notes && detailForm.notes !== '-'">
                    <h4 class="text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
                        <span class="w-1 h-4 bg-amber-500 rounded-full"></span>
                        Catatan
                    </h4>
                    <div
                        class="p-4 bg-amber-50 text-amber-900 rounded-xl text-sm border border-amber-100 italic leading-relaxed whitespace-pre-wrap break-words">
                        "{{ detailForm.notes }}"
                    </div>
                </div>
                <div v-else class="text-center py-2">
                    <span class="text-sm text-gray-400 italic">Tidak ada catatan tambahan</span>
                </div>
            </div>

        </Dialog>

        <!-- Assessment List Modal -->
        <Dialog v-model:visible="showAssessmentListModal" modal dismissableMask
            :breakpoints="{ '1200px': '85vw', '960px': '90vw', '640px': '95vw' }"
            :style="{ width: '1000px', maxWidth: '95vw' }" :pt="dialogPt">
            <!-- Header -->
            <template #header>
                <div class="flex flex-col gap-1 w-full border-b pb-4">
                    <div class="flex items-center gap-2 text-gray-500 text-sm">
                        <span>{{ getMonthName(local.month) }} {{ local.year }}</span>
                    </div>
                    <div class="flex justify-between items-end">
                        <h3 class="text-xl font-bold text-gray-900">
                            {{ assessmentListForm.employee_name }}
                        </h3>
                        <div class="text-right">
                            <span class="text-sm text-gray-500 block">Rata - Rata Penilaian</span>
                            <span class="text-lg font-bold text-blue-600">
                                {{ assessmentListForm.average_score }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Content -->
            <div class="flex flex-col gap-4 py-4">
                <!-- Toolbar -->
                <div class="flex justify-between items-center px-1">
                    <div class="relative w-64">
                        <input v-model="assessmentListForm.q" type="text" placeholder="Cari penilai..."
                            class="py-2 pl-8 pr-3 w-full text-sm rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" />
                        <svg class="absolute left-2.5 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div v-if="assessmentListForm.loading" class="flex justify-center py-8">
                        <span class="text-gray-500">Memuat data...</span>
                    </div>
                    <table v-else class="min-w-full text-sm border-collapse">
                        <thead>
                            <tr>
                                <th rowspan="2" class="p-3 bg-gray-100 border border-gray-300 w-12 text-center">No</th>
                                <th rowspan="2" class="p-3 bg-gray-100 border border-gray-300 text-left min-w-[150px]">
                                    Nama
                                    Penilai
                                </th>
                                <th :colspan="assessmentListForm.indicators.length"
                                    class="p-2 bg-blue-50 border border-gray-300 text-center font-semibold text-blue-800">
                                    {{ assessmentListForm.category }}
                                </th>
                                <th rowspan="2" class="p-3 bg-gray-100 border border-gray-300 w-16 text-center">Total
                                </th>
                            </tr>
                            <tr>
                                <th v-for="(indicator, idx) in assessmentListForm.indicators" :key="idx"
                                    class="p-2 bg-white border border-gray-300 text-xs text-center font-medium min-w-[100px]">
                                    {{ indicator }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, idx) in assessmentListForm.details" :key="row.id">
                                <td class="p-2 border border-gray-300 text-center">{{ idx + 1 }}</td>
                                <td class="p-2 border border-gray-300 font-medium">{{
                                    row.reporter_name }}
                                </td>
                                <td v-for="(indicator, i) in assessmentListForm.indicators" :key="i"
                                    class="p-2 border border-gray-300 text-center">
                                    {{ row.scores[indicator] !== undefined ? row.scores[indicator] : '-' }}
                                </td>
                                <td class="p-2 border border-gray-300 text-center font-bold bg-gray-50">
                                    {{ row.total }}
                                </td>
                            </tr>
                            <tr v-if="assessmentListForm.details.length === 0">
                                <td :colspan="assessmentListForm.indicators.length + 3"
                                    class="p-4 text-center text-gray-500 italic">
                                    Belum ada penilaian data.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="assessmentListForm.details.length > 0">
                            <tr>
                                <td :colspan="assessmentListForm.indicators.length + (isSuperadmin ? 2 : 1)"
                                    class="p-3 border border-gray-300 text-right font-bold bg-gray-100">
                                    Rata - Rata Penilaian
                                </td>
                                <td class="p-3 border border-gray-300 text-center font-bold bg-blue-50 text-blue-700">
                                    {{ assessmentListForm.average_score }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Pagination -->
                    <div v-if="assessmentListForm.pagination?.total > 0" class="mt-4">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                Menampilkan {{ assessmentListForm.pagination.from || 0 }} - {{
                                    assessmentListForm.pagination.to
                                    || 0 }} dari {{ assessmentListForm.pagination.total }} data
                            </div>
                            <div class="flex gap-1">
                                <button :disabled="!assessmentListForm.pagination.prev_page_url"
                                    @click="fetchAssessmentList(assessmentListForm.pagination.current_page - 1)"
                                    class="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Prev
                                </button>
                                <button
                                    v-for="link in assessmentListForm.pagination.links?.filter(l => !l.label.includes('Previous') && !l.label.includes('Next'))"
                                    :key="link.label" @click="fetchAssessmentList(link.label)"
                                    :class="['px-3 py-1 text-sm rounded border', link.active ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50']"
                                    v-html="link.label">
                                </button>
                                <button :disabled="!assessmentListForm.pagination.next_page_url"
                                    @click="fetchAssessmentList(assessmentListForm.pagination.current_page + 1)"
                                    class="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end pt-4 border-t">
                    <button class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                        type="button" @click="showAssessmentListModal = false">
                        Tutup
                    </button>
                </div>
            </template>
        </Dialog>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/Pagination.vue";
import AvatarInitials from "@/Components/common/AvatarInitials.vue";
import { Dialog } from "primevue";
import { Head, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import { useAuth } from "@/Composables/useAuth";
import axios from "axios";

defineOptions({ layout: AppLayout });

const { is, can } = useAuth();
const isSuperadmin = computed(() => is("Superadmin"));

const isEdit = computed(() => can("employee_performances.edit"));
const isCalculateKPI = computed(() => can("employee_performances.calculate_kpi"));
const props = defineProps({
    employeePerformances: Object,
    filters: Object,
    branches: Array,
    departments: Array,
});

const breadcrumbs = [
    { label: "Kinerja" },
    { label: "Kinerja Karyawan" },
];

const now = new Date();
const currentMonth = now.getMonth() + 1;
const currentYear = now.getFullYear();

const local = ref({
    q: props.filters?.q || "",
    branch_id: props.filters?.branch_id ?? null,
    department_id: props.filters?.department_id ?? null,
    status: props.filters?.status ?? null,
    month: props.filters?.month ? Number(props.filters.month) : currentMonth,
    year: props.filters?.year ? Number(props.filters.year) : currentYear,
    tab: props.filters?.tab || 'performance',
});

// Reactive departments based on selected branch
const departments = computed(() => {
    if (local.value.branch_id) {
        return props.departments?.filter(d => d.branch_id === local.value.branch_id) || [];
    }
    return props.departments || [];
});

// Get filtered data from paginated response
const filteredData = computed(() => {
    return props.employeePerformances?.data || [];
});

// Check if current month and year are selected
const isCurrentMonth = computed(() => {
    const month = Number(local.value.month) || currentMonth;
    const year = Number(local.value.year) || currentYear;
    return month === currentMonth && year === currentYear;
});

// Watch for filter changes and fetch data
let t = null;
watch(
    () => ({ ...local.value }),
    () => {
        clearTimeout(t);
        t = setTimeout(() => fetchList(), 350);
    },
    { deep: true }
);

function onBranchChange() {
    // Reset department when branch changes
    local.value.department_id = null;
    // Fetch list will update departments automatically
    fetchList();
}

function fetchList() {
    router.get(
        route("employee-performances.index"),
        { ...local.value },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

function changePage(page) {
    router.get(
        route("employee-performances.index"),
        {
            ...local.value,
            page: page,
        },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

function perPageChanged(perPage) {
    router.get(
        route("employee-performances.index"),
        {
            ...local.value,
            per_page: perPage,
            page: 1, // Reset to first page when changing per page
        },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

function resetFilter() {
    local.value = {
        q: "",
        branch_id: null,
        department_id: null,
        status: null,
        month: currentMonth,
        year: currentYear,
        tab: local.value.tab,
    };
    fetchList();
}

async function calculateKPI() {
    if (!confirm('Apakah Anda yakin ingin menghitung KPI Kehadiran dan Checklist untuk bulan ini? Proses ini akan memperbarui data yang sudah ada.')) {
        return;
    }

    calculating.value = true;

    try {
        const response = await axios.post(
            route('employee-performances.calculate-kpi'),
            {
                month: local.value.month,
                year: local.value.year,
                branch_id: local.value.branch_id,
            }
        );

        if (response.data.success) {
            alert(`Berhasil! KPI telah dihitung untuk ${response.data.calculated} karyawan.${response.data.errors?.length ? '\n\nBeberapa error:\n' + response.data.errors.join('\n') : ''}`);
            // Refresh the list
            fetchList();
        } else {
            alert(response.data.message || 'Gagal menghitung KPI');
        }
    } catch (error) {
        console.error('Error calculating KPI:', error);
        const message = error.response?.data?.message || 'Gagal menghitung KPI. Silakan coba lagi.';
        alert(message);
    } finally {
        calculating.value = false;
    }
}

// Score Modal
const showScoreModal = ref(false);
const showDetailModal = ref(false);
const saving = ref(false);
const calculating = ref(false);
const scoreForm = ref({
    id: null,
    employee_id: null,
    employee_name: "",
    category: "",
    score: {},
    notes: "",
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
});

const detailForm = ref({
    id: null,
    employee_id: null,
    employee_name: "",
    gender: "",
    cabang: "",
    penilai: "",
    created_at: "",
    category: "",
    score: {},
    notes: "",
    accumulated_score: 0,
});

const showAssessmentListModal = ref(false);
const assessmentListForm = ref({
    employee_id: null, // Keep track of ID for refreshing
    employee_name: "",
    category: "",
    details: [],
    average_score: 0,
    indicators: [],
    loading: false,
    q: "",
    page: 1,
    per_page: 10,
    pagination: null,
});

// Watch search query in modal
let searchTimeout;
watch(() => assessmentListForm.value.q, (newVal) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        assessmentListForm.value.page = 1;
        fetchAssessmentList(1);
    }, 300);
});

async function openAssessmentList(employeeId, category) {
    showAssessmentListModal.value = true;
    assessmentListForm.value = {
        employee_id: employeeId,
        employee_name: "Loading...",
        category: category,
        details: [],
        average_score: 0,
        indicators: [],
        loading: true,
        q: "",
        page: 1,
        per_page: 10,
        pagination: null,
    };

    await fetchAssessmentList(1);
}

async function fetchAssessmentList(page = 1) {
    if (!assessmentListForm.value.employee_id) return;

    assessmentListForm.value.loading = true;
    assessmentListForm.value.page = Number(page); // Ensure number

    try {
        const response = await axios.get(
            route("employee-performances.get-score-details", { employeeId: assessmentListForm.value.employee_id }),
            {
                params: {
                    category: assessmentListForm.value.category,
                    month: local.value.month,
                    year: local.value.year,
                    q: assessmentListForm.value.q,
                    page: assessmentListForm.value.page,
                    per_page: assessmentListForm.value.per_page,
                },
            }
        );

        if (response.data.success) {
            const data = response.data.data;
            assessmentListForm.value.employee_name = data.employee_name;
            assessmentListForm.value.average_score = data.average_score;
            assessmentListForm.value.indicators = data.indicators;

            // Handle Paginated Data
            if (data.performances) {
                assessmentListForm.value.details = data.performances.data;
                assessmentListForm.value.pagination = data.performances;
            } else {
                // Fallback if structure is different
                assessmentListForm.value.details = data.details || [];
                assessmentListForm.value.pagination = null;
            }
        }
    } catch (error) {
        console.error("Error fetching assessment list:", error);
        assessmentListForm.value.employee_name = "Error loading data";
    } finally {
        assessmentListForm.value.loading = false;
    }
}



const monthOptions = [
    { value: 1, label: "Januari" },
    { value: 2, label: "Februari" },
    { value: 3, label: "Maret" },
    { value: 4, label: "April" },
    { value: 5, label: "Mei" },
    { value: 6, label: "Juni" },
    { value: 7, label: "Juli" },
    { value: 8, label: "Agustus" },
    { value: 9, label: "September" },
    { value: 10, label: "Oktober" },
    { value: 11, label: "November" },
    { value: 12, label: "Desember" },
];

const yearOptions = Array.from({ length: 6 }).map(
    (_, i) => currentYear - 2 + i
);

async function fetchScoreData() {
    if (!scoreForm.value.employee_id || !scoreForm.value.category) return;

    try {
        const response = await axios.get(
            route("employee-performances.get-score", { employeeId: scoreForm.value.employee_id }),
            {
                params: {
                    category: scoreForm.value.category,
                    month: local.value.month,
                    year: local.value.year,
                },
            }
        );

        if (response.data.success) {
            const data = response.data.data;
            scoreForm.value.id = data.id;
            scoreForm.value.score = data.score || {};
            scoreForm.value.notes = data.notes || "";
            // Keep employee_id, employee_name, category
        }
    } catch (error) {
        console.error("Error fetching score:", error);
    }
}

async function openScoreModal(employeeId, category) {
    showScoreModal.value = true;
    saving.value = false;

    // Initialize score object with indicators
    const categoryInfo = getCategoryInfo(category);
    const initialScore = {};
    if (categoryInfo?.items) {
        categoryInfo.items.forEach(indicator => {
            initialScore[indicator] = 1;
        });
    }

    // Reset form - bulan dan tahun diambil dari filter yang aktif
    scoreForm.value = {
        id: null,
        employee_id: employeeId,
        employee_name: "",
        category: category,
        score: { ...initialScore },
        notes: "",
    };

    // Fetch employee name and existing score using filter month and year
    try {
        const response = await axios.get(
            route("employee-performances.get-score", { employeeId }),
            {
                params: {
                    category: category,
                    month: local.value.month,
                    year: local.value.year,
                },
            }
        );

        if (response.data.success) {
            const data = response.data.data;
            scoreForm.value.employee_name = data.employee_name;
            scoreForm.value.id = data.id;
            // Merge existing scores with initial structure
            if (data.score && typeof data.score === 'object') {
                scoreForm.value.score = { ...initialScore, ...data.score };
            }
            scoreForm.value.notes = data.notes || "";
        }
    } catch (error) {
        console.error("Error fetching score:", error);
        alert("Gagal memuat data score. Silakan coba lagi.");
    }
}

function closeScoreModal() {
    showScoreModal.value = false;
    scoreForm.value = {
        id: null,
        employee_id: null,
        employee_name: "",
        category: "",
        score: {},
        notes: "",
    };
}

async function saveScore() {
    // Validate that at least one indicator has a score
    const hasScore = Object.values(scoreForm.value.score).some(
        value => value !== null && value !== '' && value !== undefined
    );

    console.log(hasScore);
    if (!hasScore) {
        alert("Minimal satu indikator harus diisi");
        return;
    }

    // Validate each score is between 1-5
    for (const [indicator, value] of Object.entries(scoreForm.value.score)) {
        if (value !== null && value !== '' && value !== undefined) {
            const numValue = Number(value);
            if (isNaN(numValue) || numValue < 1 || numValue > 5) {
                alert(`Nilai untuk "${indicator}" harus antara 1-5`);
                return;
            }
        }
    }

    saving.value = true;

    try {
        let response;
        if (scoreForm.value.id) {
            // Update existing
            response = await axios.put(
                route("employee-performances.update", { id: scoreForm.value.id }),
                {
                    score: scoreForm.value.score,
                    notes: scoreForm.value.notes,
                }
            );
        } else {
            // Create new - menggunakan bulan dan tahun dari filter yang aktif
            response = await axios.post(route("employee-performances.store"), {
                employee_id: scoreForm.value.employee_id,
                category: scoreForm.value.category,
                score: scoreForm.value.score,
                notes: scoreForm.value.notes,
                month: local.value.month,
                year: local.value.year,
            });
        }

        if (response.data.success) {
            closeScoreModal();
            // Refresh the list
            fetchList();
        } else {
            alert(response.data.message || "Gagal menyimpan score");
        }
    } catch (error) {
        console.error("Error saving score:", error);
        const message = error.response?.data?.message || "Gagal menyimpan score. Silakan coba lagi.";
        alert(message);
    } finally {
        saving.value = false;
    }
}

async function openDetailModal(row) {
    showDetailModal.value = true;
    const { employee_id, category: category_raw, score, penilai, created_at, cabang, gender } = row;

    // Initialize score object with indicators
    const categoryInfo = getCategoryInfo(category_raw);
    const initialScore = {};
    if (categoryInfo?.items) {
        categoryInfo.items.forEach(indicator => {
            initialScore[indicator] = 1;
        });
    }

    detailForm.value = {
        id: null,
        employee_id: employee_id,
        employee_name: row.employee || "Loading...",
        gender: gender,
        cabang: cabang,
        penilai: penilai,
        created_at: created_at,
        category: category_raw,
        score: { ...initialScore },
        notes: "Loading...",
        accumulated_score: score || 0,
    };

    try {
        const response = await axios.get(
            route("employee-performances.get-score", { employeeId: employee_id }),
            {
                params: {
                    category: category_raw,
                    month: local.value.month,
                    year: local.value.year,
                },
            }
        );

        if (response.data.success) {
            const data = response.data.data;
            detailForm.value.employee_name = data.employee_name;
            detailForm.value.id = data.id;
            if (data.score && typeof data.score === 'object') {
                detailForm.value.score = { ...initialScore, ...data.score };
            }
            detailForm.value.notes = data.notes || "Tidak ada catatan";
        } else {
            detailForm.value.notes = "Data tidak ditemukan";
            detailForm.value.employee_name = "-";
        }
    } catch (error) {
        console.error("Error fetching detail score:", error);
        detailForm.value.notes = "Gagal memuat data";
        detailForm.value.employee_name = "Error";
    }
}

function closeDetailModal() {
    showDetailModal.value = false;
    detailForm.value = {
        id: null,
        employee_id: null,
        employee_name: "",
        gender: "",
        cabang: "",
        penilai: "",
        created_at: "",
        category: "",
        score: {},
        notes: "",
        accumulated_score: 0,
    };
}

const dialogPt = {
    root: { class: "rounded-2xl" },
    header: { class: "px-6 pt-6 pb-2" },
    content: { class: "px-6 pb-2" },
    footer: { class: "px-6 pb-6" },
};

// Get month name from month number
function getMonthName(month) {
    const monthOption = monthOptions.find(m => m.value === month);
    return monthOption ? monthOption.label : "";
}

// Get category information based on category name
function getCategoryInfo(category) {
    const categoryInfo = {
        'Productivity': {
            title: 'KPI Kinerja & Output Kerja (Work Quality & Productivity)',
            items: [
                'Kualitas pekerjaan (rapi, sesuai SOP)',
                'Kecepatan dan hasil kerja',
                'Zero mistake / zero repeat job',
                'Inisiatif menyelesaikan masalah',
                'Produktivitas per jam/per shift'
            ]
        },
        'Attitude': {
            title: 'KPI Perilaku & Sikap Kerja (Behavior & Attitude)',
            items: [
                'Kerjasama tim',
                'Komunikasi',
                'Etika kerja',
                'Tidak menyalahkan orang lain',
                'Tidak menciptakan konflik',
                'Sopan santun terhadap atasan & rekan'
            ]
        },
        'Initiative': {
            title: 'KPI Inisiatif & Pengembangan (Initiative & Improvement)',
            items: [
                'Ide perbaikan',
                'Meningkatkan efisiensi',
                'Membantu tugas tambahan',
                'Kemauan belajar & upgrade skill',
                'Melatih junior/anggota tim'
            ]
        }
    };

    return categoryInfo[category] || null;
}

// Get total score (sum of all KPI scores)
function getTotalScore(row) {
    const kehadiran = Number(row.Kehadiran) || 0;
    const checklist = Number(row.Checklist) || 0;
    const productivity = Number(row.Productivity) || 0;
    const attitude = Number(row.Attitude) || 0;
    const initiative = Number(row.Initiative) || 0;

    const total = kehadiran + checklist + productivity + attitude + initiative;
    return Math.round(total * 10) / 10; // Round to 1 decimal place
}


function getResultMaxScore(category) {
    if (['Productivity', 'Keterampilan', 'Kehadiran'].includes(category)) return 25;
    if (['Checklist', 'Kuantitas'].includes(category)) return 20;
    if (['Attitude', 'Kerjasama', 'Initiative', 'Disiplin'].includes(category)) return 15;
    return 10; // Default for single indicator if any? Or 100?
    // Actually, the score in history is already weighted?
    // Wait, let's check controller.
    // calculateDisplayScore returns: round($result, 1).
    // result = ((total / count) * 10) * weight.
    // So if weight is 0.25, max score is 10 * 10 * 0.25 = 25.
    // So yes, the max score is indeed the weight * 10 or similar.
    // wait. ((10/1)*10)*0.25 = 25. Correct.
}

// Get score color class based on value
// Using percentage-based thresholds for consistency across different score ranges
function getScoreClass(score, maxScore = 100) {
    const numScore = Number(score) || 0;

    // Calculate percentage of max score
    const percentage = maxScore > 0 ? (numScore / maxScore) * 100 : 0;

    // Thresholds based on percentage: Excellent (≥80%), Good (≥60%), Fair (≥40%), Poor (<40%)
    if (percentage >= 80) {
        return "bg-emerald-100 text-emerald-700 border-2 border-emerald-300";
    } else if (percentage >= 60) {
        return "bg-blue-100 text-blue-700 border-2 border-blue-300";
    } else if (percentage >= 40) {
        return "bg-amber-100 text-amber-700 border-2 border-amber-300";
    } else {
        return "bg-red-100 text-red-700 border-2 border-red-300";
    }
}
</script>
