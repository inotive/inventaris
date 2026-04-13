<template>
    <Head :title="`Detail Inspeksi #${inspection.inspection_number}`" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <div class="flex gap-2">
                <!-- Dev/Local Only: Manual SPV Reminder Trigger -->
                <button
                    v-if="isDevEnvironment"
                    @click="sendSpvReminderManually"
                    :disabled="isSendingSpvReminder"
                    class="px-3 py-2 text-sm text-white bg-orange-600 rounded border hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                >
                    <svg
                        v-if="isSendingSpvReminder"
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
                        isSendingSpvReminder
                            ? "Mengirim..."
                            : "🔔 Kirim Reminder SPV (Dev)"
                    }}
                </button>
                <Link
                    :href="route('inspections.index')"
                    class="px-3 py-2 text-sm text-gray-700 rounded border"
                    >Kembali
                </Link>
            </div>
        </div>

        <div
            class="space-y-5 overflow-hidden rounded-lg border border-gray-200 bg-white px-10 py-6 dark:border-gray-700 dark:bg-white/[0.03]"
        >
            <div class="flex items-center justify-between">
                <div class="font-bold text-sky-400 md:text-xl">
                    Detail {{ general.inspection_number }}
                </div>
                <div class="flex items-center gap-3">
                    <button
                        v-if="false"
                        @click="printInspection"
                        class="flex items-center gap-2 px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded transition-colors"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                            />
                        </svg>
                        Print
                    </button>
                    <button
                        @click="exportBackend"
                        class="flex items-center gap-2 px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded transition-colors"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                            />
                        </svg>
                        Print
                    </button>
                    <span
                        :class="badgeClass(general.status)"
                        class="px-2.5 py-1 text-xs font-medium rounded-full"
                        >{{ statusLabel(general.status) }}</span
                    >
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <div class="text-sm text-gray-600">Nomor Inspeksi</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ general.inspection_number ?? "-" }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">No. SOP</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ general.sop_code ?? "-" }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Departemen</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ general.department ?? "-" }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">
                        Nama Checklist (Tipe)
                    </div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ general.checklist_name }} ({{
                            general.checklist_type
                        }})
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Cabang</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ general.location ?? "-" }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Kategori Checklist</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ general.category ?? "-" }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Tipe Pengecekan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ general.inspection_type ?? "-" }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Target Pengecekan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ general.inspection_target ?? "-" }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Dibuat Oleh</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ general.submitted_by ?? "-" }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Tanggal Dibuat</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ formatDateTime(general.created_at) }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Tanggal Inspeksi</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ formatDateTime(general.submit_date) }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600">Catatan</div>
                    <div class="font-semibold text-gray-800 dark:text-gray-200">
                        {{ general.remarks ?? "-" }}
                    </div>
                </div>
                <!-- Vehicle Information (only for Vehicle category) -->
                <template
                    v-if="general.category === 'Kendaraan' && inspection?.model"
                >
                    <div>
                        <div class="text-sm text-gray-600">Tipe Kendaraan</div>
                        <div
                            class="font-semibold text-gray-800 dark:text-gray-200"
                        >
                            {{ inspection.model.type?.name ?? "-" }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">No. Plat</div>
                        <div
                            class="font-semibold text-gray-800 dark:text-gray-200"
                        >
                            {{
                                inspection.model.license_plate ??
                                inspection.model.license_code ??
                                "-"
                            }}
                        </div>
                    </div>
                </template>
                <div>
                    <!-- Signature of submitter under header -->
                    <div
                        v-if="submitterSignatureUrl"
                        class="flex flex-col gap-2"
                    >
                        <div class="text-sm text-gray-600">
                            Tanda Tangan Pengaju
                        </div>
                        <img
                            :src="submitterSignatureUrl"
                            alt="Tanda tangan pengaju"
                            class="h-12 object-contain float-left"
                            style="width: 75px; height: 75px"
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200">
            <div
                class="px-6 py-4 border-b flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
            >
                <div class="flex gap-4">
                    <button
                        v-for="t in tabs"
                        :key="t.value"
                        type="button"
                        @click="activeTab = t.value"
                        :class="[
                            'px-3 py-2 text-sm rounded',
                            activeTab === t.value
                                ? 'bg-sky-100 text-sky-700'
                                : 'text-gray-600 hover:bg-gray-50 border',
                        ]"
                    >
                        {{ t.label }}
                    </button>
                </div>
            </div>

            <!-- Tab: Pertanyaan (table, mengikuti desain: No, Pertanyaan, Kategori, Jawaban) -->
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

                <div class="overflow-auto max-h-[500px]" data-simplebar>
                    <!-- Table for checklist type multiple (group) -->
                    <table v-if="isMultiple" class="min-w-full text-sm">
                        <thead>
                            <tr>
                                <th
                                    class="sticky top-0 left-0 z-20 p-3 bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 border-y"
                                    style="min-width: 60px; width: 60px"
                                >
                                    <div
                                        class="font-medium text-center text-gray-600 dark:text-gray-300"
                                    >
                                        No
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 left-[60px] z-20 p-3 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                                    style="min-width: 200px; width: 200px"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600 dark:text-gray-300"
                                    >
                                        Pertanyaan
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 left-[260px] z-20 p-3 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                                    style="min-width: 150px; width: 150px"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600 dark:text-gray-300"
                                    >
                                        Kategori
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 z-20 p-3 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                                    style="min-width: 200px; width: 200px"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600 dark:text-gray-300"
                                    >
                                        Petunjuk
                                    </div>
                                </th>
                                <th
                                    v-for="(employee, idx) in uniqueEmployees"
                                    :key="idx"
                                    class="sticky top-0 p-3 bg-gray-100 border border-gray-200 z-10"
                                >
                                    <div
                                        class="font-medium text-center text-gray-600"
                                    >
                                        Jawaban {{ employee.name }}
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="vehicleQuestions.length">
                                <tr
                                    v-for="(q, idx) in vehicleQuestions"
                                    :key="idx"
                                    class="border-b border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 group"
                                >
                                    <td
                                        class="sticky left-0 z-10 p-3 text-center bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-700/50"
                                        style="min-width: 60px; width: 60px"
                                    >
                                        {{ (qPage - 1) * qPerPage + idx + 1 }}
                                    </td>
                                    <td
                                        class="sticky left-[60px] z-10 p-3 bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-700/50"
                                        style="min-width: 200px; width: 200px"
                                    >
                                        {{ q.title }}
                                    </td>
                                    <td
                                        class="sticky left-[260px] z-10 p-3 bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-700/50"
                                        style="min-width: 150px; width: 150px"
                                    >
                                        {{ q.category || "-" }}
                                    </td>
                                    <td
                                        class="p-3 bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-700/50"
                                        style="min-width: 200px"
                                    >
                                        {{ q.guidance || "-" }}
                                    </td>
                                    <td
                                        v-for="(
                                            employee, empIdx
                                        ) in uniqueEmployees"
                                        :key="empIdx"
                                        class="p-3 text-center"
                                    >
                                        <div class="flex flex-col gap-1">
                                            <span
                                                >{{
                                                    getAnswerForEmployee(
                                                        q,
                                                        employee.id,
                                                    )
                                                }}
                                            </span>
                                            <button
                                                v-if="
                                                    getAttachmentForEmployee(
                                                        q,
                                                        employee.id,
                                                    ) ||
                                                    getNoteForEmployee(
                                                        q,
                                                        employee.id,
                                                    )
                                                "
                                                @click="
                                                    openAttachmentModal(
                                                        getAttachmentForEmployee(
                                                            q,
                                                            employee.id,
                                                        ),
                                                        employee.name,
                                                        q.title,
                                                        getNoteForEmployee(
                                                            q,
                                                            employee.id,
                                                        ),
                                                    )
                                                "
                                                class="text-xs text-blue-600 hover:underline cursor-pointer"
                                            >
                                                📎 Lampiran
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td
                                    :colspan="3 + uniqueEmployees.length"
                                    class="py-6 text-center text-gray-500"
                                    style="position: sticky; left: 0"
                                >
                                    Tidak ada pertanyaan
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Table for Non-Kendaraan Category -->
                    <table v-else class="min-w-full text-sm">
                        <thead>
                            <tr>
                                <th
                                    class="sticky top-0 left-0 z-20 p-3 bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700 border-y"
                                    style="min-width: 60px; width: 60px"
                                >
                                    <div
                                        class="font-medium text-center text-gray-600 dark:text-gray-300"
                                    >
                                        No
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 left-[60px] z-20 p-3 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                                    style="min-width: 200px; width: 200px"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600 dark:text-gray-300"
                                    >
                                        Pertanyaan
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 left-[260px] z-20 p-3 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                                    style="min-width: 150px; width: 150px"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600 dark:text-gray-300"
                                    >
                                        Kategori
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 left-[410px] z-20 p-3 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                                    style="min-width: 200px; width: 200px"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600 dark:text-gray-300"
                                    >
                                        Petunjuk
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 p-3 bg-gray-100 border border-gray-200 z-10"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Jawaban
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 p-3 bg-gray-100 border border-gray-200 z-10"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Lampiran
                                    </div>
                                </th>
                                <th
                                    class="sticky top-0 p-3 bg-gray-100 border border-gray-200 z-10"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Catatan
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="filteredQuestions.length">
                                <tr
                                    v-for="(q, idx) in filteredQuestions"
                                    :key="idx"
                                    class="border-b border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 group"
                                >
                                    <td
                                        class="sticky left-0 z-10 p-3 text-center bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-700/50"
                                        style="min-width: 60px; width: 60px"
                                    >
                                        {{ (qPage - 1) * qPerPage + idx + 1 }}
                                    </td>
                                    <td
                                        class="sticky left-[60px] z-10 p-3 bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-700/50"
                                        style="min-width: 200px; width: 200px"
                                    >
                                        {{ q.title }}
                                    </td>
                                    <td
                                        class="sticky left-[260px] z-10 p-3 bg-white dark:bg-gray-800 group-hover:bg-gray-50 dark:group-hover:bg-gray-700/50"
                                        style="min-width: 150px; width: 150px"
                                    >
                                        {{ q.category || "-" }}
                                    </td>
                                    <td class="p-3">{{ q.guidance || "-" }}</td>
                                    <td class="p-3">{{ q.answer ?? "-" }}</td>
                                    <td class="p-3">
                                        <div
                                            v-if="q.attachment"
                                            class="flex items-center gap-2"
                                        >
                                            <img
                                                v-if="isImage(q.attachment)"
                                                :src="q.attachment"
                                                alt="Lampiran"
                                                class="w-16 h-16 object-cover rounded border cursor-pointer"
                                                @click="
                                                    openImageModal(q.attachment)
                                                "
                                            />
                                            <button
                                                @click="
                                                    openAttachmentModal(
                                                        q.attachment,
                                                        null,
                                                        q.title,
                                                        q.note,
                                                    )
                                                "
                                                class="text-xs text-blue-600 hover:underline flex items-center gap-1 cursor-pointer"
                                            >
                                                <i
                                                    class="lni lni-paperclip"
                                                ></i>
                                                Lihat Lampiran
                                            </button>
                                        </div>
                                        <span v-else class="text-gray-400"
                                            >-</span
                                        >
                                    </td>
                                    <td class="p-3">
                                        <span v-if="q.note" class="text-sm">{{
                                            q.note
                                        }}</span>
                                        <span v-else class="text-gray-400"
                                            >-</span
                                        >
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td
                                    colspan="6"
                                    class="py-6 text-center text-gray-500"
                                    style="position: sticky; left: 0"
                                >
                                    Tidak ada pertanyaan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="totalQuestionPages > 1"
                    class="flex justify-center items-center gap-2 mt-4"
                >
                    <button
                        @click="qPage = Math.max(1, qPage - 1)"
                        :disabled="qPage === 1"
                        class="px-3 py-1 text-sm rounded border disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                    >
                        Sebelumnya
                    </button>
                    <span class="text-sm text-gray-600">
                        Halaman {{ qPage }} dari {{ totalQuestionPages }}
                    </span>
                    <button
                        @click="qPage = Math.min(totalQuestionPages, qPage + 1)"
                        :disabled="qPage >= totalQuestionPages"
                        class="px-3 py-1 text-sm rounded border disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                    >
                        Selanjutnya
                    </button>
                </div>
            </div>

            <!-- Tab: Responden / User yang mengisi questionnaire -->
            <div v-show="activeTab === 'approvals'" class="p-4">
                <div class="overflow-auto" data-simplebar>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr>
                                <th
                                    class="p-3 bg-gray-100 border-gray-200 border-y"
                                >
                                    <div
                                        class="font-medium text-center text-gray-600"
                                    >
                                        No
                                    </div>
                                </th>
                                <th
                                    class="p-3 bg-gray-100 border border-gray-200"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Nama Karyawan
                                    </div>
                                </th>
                                <th
                                    class="p-3 bg-gray-100 border border-gray-200"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Created At
                                    </div>
                                </th>
                                <th
                                    class="p-3 bg-gray-100 border border-gray-200"
                                >
                                    <div
                                        class="font-medium text-left text-gray-600"
                                    >
                                        Tanda Tangan
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="respondents && respondents.length">
                                <tr
                                    v-for="(respondent, idx) in respondents"
                                    :key="idx"
                                    class="border-b border-gray-200"
                                >
                                    <td class="p-3 text-center">
                                        {{ idx + 1 }}
                                    </td>
                                    <td class="p-3">
                                        {{ respondent.employee_name || "-" }}
                                    </td>
                                    <td class="p-3">
                                        {{
                                            formatDateTime(
                                                respondent.created_at,
                                            )
                                        }}
                                    </td>
                                    <td class="p-3">
                                        <img
                                            v-if="respondent.signature_url"
                                            :src="respondent.signature_url"
                                            alt="signature"
                                            class="h-10 cursor-pointer"
                                            @click="
                                                openImageModal(
                                                    respondent.signature_url,
                                                )
                                            "
                                        />
                                        <span v-else class="text-gray-400"
                                            >-</span
                                        >
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td
                                    colspan="4"
                                    class="py-6 text-center text-gray-500"
                                >
                                    Belum ada responden
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Image Modal -->
        <div
            v-if="imageModalUrl"
            @click="closeImageModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
        >
            <div class="relative max-w-4xl max-h-[90vh]">
                <button
                    @click="closeImageModal"
                    class="absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full w-8 h-8 flex items-center justify-center hover:bg-opacity-75"
                >
                    ×
                </button>
                <img
                    :src="imageModalUrl"
                    alt="Preview"
                    class="max-w-full max-h-[90vh] object-contain"
                />
            </div>
        </div>

        <!-- Attachment Modal -->
        <div
            v-if="attachmentModalData"
            @click.self="closeAttachmentModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
        >
            <div
                @click.stop
                class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden flex flex-col"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between p-4 border-b bg-gray-50"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">
                            Detail Lampiran
                        </h3>
                        <p
                            class="text-sm text-gray-600 mt-1"
                            v-if="
                                attachmentModalData &&
                                attachmentModalData.employeeName
                            "
                        >
                            Karyawan: {{ attachmentModalData.employeeName }}
                        </p>
                        <p
                            class="text-sm text-gray-600"
                            v-if="
                                attachmentModalData &&
                                attachmentModalData.questionTitle
                            "
                        >
                            Pertanyaan: {{ attachmentModalData.questionTitle }}
                        </p>
                    </div>
                    <button
                        @click="closeAttachmentModal"
                        class="text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded hover:bg-gray-200 transition-colors"
                    >
                        ×
                    </button>
                </div>

                <!-- Content -->
                <div
                    class="flex-1 overflow-y-auto p-6"
                    v-if="
                        attachmentModalData &&
                        (attachmentModalData.url || attachmentModalData.note)
                    "
                >
                    <div class="space-y-4">
                        <!-- Attachment Section -->
                        <div v-if="attachmentModalData.url">
                            <div class="text-sm font-medium text-gray-700 mb-2">
                                Lampiran:
                            </div>

                            Image Preview
                            <div
                                v-if="isImage(attachmentModalData.url)"
                                class="mb-4"
                            >
                                <div
                                    class="border rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center min-h-[200px]"
                                >
                                    <img
                                        :src="attachmentModalData.url"
                                        alt="Lampiran"
                                        class="max-w-full max-h-[60vh] object-contain hover:opacity-90 transition-opacity"
                                    />
                                </div>
                            </div>

                            <!-- File Info for non-image -->
                            <div
                                v-else
                                class="p-4 bg-gray-50 rounded-lg border border-gray-200"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <i
                                            class="lni lni-paperclip text-2xl text-gray-600"
                                        ></i>
                                    </div>
                                    <div class="flex-1">
                                        <div
                                            class="text-sm font-medium text-gray-700"
                                        >
                                            {{
                                                getFileType(
                                                    attachmentModalData.url,
                                                )
                                            }}
                                        </div>
                                        <div
                                            class="text-xs text-gray-500 mt-1 break-all"
                                        >
                                            {{ attachmentModalData.url }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Note/Tindak Lanjut Section -->
                        <div v-if="attachmentModalData.note">
                            <div class="text-sm font-medium text-gray-700 mb-2">
                                Tindak Lanjut / Catatan:
                            </div>
                            <div
                                class="p-4 bg-blue-50 rounded-lg border border-blue-200 text-sm text-gray-800 whitespace-pre-wrap"
                            >
                                {{ attachmentModalData.note }}
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-if="
                                !attachmentModalData.url &&
                                !attachmentModalData.note
                            "
                            class="text-center py-8 text-gray-500"
                        >
                            Tidak ada lampiran atau catatan
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex items-center justify-end gap-3 p-4 border-t bg-gray-50"
                >
                    <a
                        v-if="attachmentModalData && attachmentModalData.url"
                        :href="attachmentModalData.url"
                        target="_blank"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                    >
                        Buka di Tab Baru
                    </a>
                    <button
                        @click="closeAttachmentModal"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

defineOptions({ layout: AppLayout });

const props = defineProps({
    inspection: Object,
    general: Object,
    questions: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    approvals: { type: Array, default: () => [] },
    respondents: { type: Array, default: () => [] },
});

const submitterSignatureUrl = computed(() => {
    const first = (props.approvals || [])[0];
    return first && first.signature_url ? first.signature_url : null;
});

const breadcrumbs = [
    { label: "Menu Utama" },
    { label: "Inspeksi", href: route("inspections.index") },
    { label: `${props.general?.inspection_number}` },
];

// Check if current environment is dev/local
const isDevEnvironment = computed(() => {
    const env = import.meta.env.VITE_APP_DEPLOYMENT || "production";
    return ["local", "dev", "development"].includes(env.toLowerCase());
});

const isSendingSpvReminder = ref(false);

// Function to manually trigger SPV reminder (dev/local only)
async function sendSpvReminderManually() {
    if (!isDevEnvironment.value) {
        alert("Fitur ini hanya tersedia di environment development");
        return;
    }

    if (!props.inspection?.id) {
        alert("Inspection ID tidak ditemukan");
        return;
    }

    isSendingSpvReminder.value = true;

    try {
        const response = await fetch(
            route("inspections.send-spv-reminder", { id: props.inspection.id }),
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    Accept: "application/json",
                },
                credentials: "include",
            },
        );

        const data = await response.json();

        if (data.success) {
            alert("✅ " + data.message);
        } else {
            throw new Error(data.message || "Gagal mengirim reminder");
        }
    } catch (error) {
        console.error("Error sending SPV reminder:", error);
        alert("❌ Gagal mengirim reminder: " + error.message);
    } finally {
        isSendingSpvReminder.value = false;
    }
}

// Check if checklist is group (multiple people) based on type text OR presence of employee-specific answers
const isMultipleChecklist = computed(() => {
    // 1) From checklist type text, e.g. "Banyak Orang" vs "Perorang"
    const rawType =
        props.general?.checklist_type ?? props.general?.checklist?.type ?? "";
    console.log(rawType);
    const type = String(rawType).toLowerCase().trim();
    const typeIndicatesMultiple = type.includes("banyak");

    // 2) Fallback: if any question has employee_id, treat as multiple
    const hasEmployeeAnswers = (props.questions || []).some(
        (q) => q.employee_id != null,
    );

    return typeIndicatesMultiple || hasEmployeeAnswers;
});

const isMultiple = computed(() => {
    // 1) From checklist type text, e.g. "Banyak Orang" vs "Perorang"
    const rawType =
        props.general?.checklist_type ?? props.general?.checklist?.type ?? "";
    console.log(rawType);

    if (rawType.includes("Banyak Orang")) {
        return true;
    } else {
        return false;
    }
});

// Get unique employees who submitted answers (only for multiple checklist)
const uniqueEmployees = computed(() => {
    if (!isMultipleChecklist.value) return [];

    const employeeMap = new Map();
    (props.questions || []).forEach((q) => {
        if (q.employee_id && q.employee_name) {
            employeeMap.set(q.employee_id, {
                id: q.employee_id,
                name: q.employee_name,
            });
        }
    });
    return Array.from(employeeMap.values());
});

// Group questions by question_id for multiple checklist
const vehicleQuestionsAll = computed(() => {
    if (!isMultipleChecklist.value) return [];

    const questionMap = new Map();
    (props.questions || []).forEach((q) => {
        if (!questionMap.has(q.question_id)) {
            questionMap.set(q.question_id, {
                question_id: q.question_id,
                title: q.title,
                category: q.category,
                guidance: q.guidance,
                answers: [],
            });
        }
        questionMap.get(q.question_id).answers.push({
            employee_id: q.employee_id,
            employee_name: q.employee_name,
            answer: q.answer,
            attachment: q.attachment,
            note: q.note,
        });
    });
    return Array.from(questionMap.values());
});

// Filtered vehicle questions (with search and category filter)
const filteredVehicleQuestionsAll = computed(() => {
    if (!isMultipleChecklist.value) return [];

    let list = vehicleQuestionsAll.value || [];

    // Filter by category
    if (qCategory.value) {
        list = list.filter((q) => (q.category || "") === qCategory.value);
    }

    // Filter by search
    if (qSearch.value) {
        const s = qSearch.value.toLowerCase();
        list = list.filter(
            (q) =>
                String(q.title).toLowerCase().includes(s) ||
                q.answers.some((a) =>
                    String(a.answer ?? "")
                        .toLowerCase()
                        .includes(s),
                ),
        );
    }

    return list;
});

// Paginated vehicle questions
const vehicleQuestions = computed(() => {
    if (!isMultipleChecklist.value) return [];

    const start = (qPage.value - 1) * qPerPage;
    const end = start + qPerPage;
    return filteredVehicleQuestionsAll.value.slice(start, end);
});

// Get answer for specific employee
function getAnswerForEmployee(question, employeeId) {
    const answer = question.answers.find((a) => a.employee_id === employeeId);
    return answer?.answer || "-";
}

// Get attachment for specific employee
function getAttachmentForEmployee(question, employeeId) {
    const answer = question.answers.find((a) => a.employee_id === employeeId);
    console.log(answer);
    const attachment = answer?.attachment;
    if (!attachment) return null;
    // Jika attachment adalah string, pastikan tidak kosong
    if (typeof attachment === "string" && attachment.trim() === "") return null;
    return attachment;
}

// Get note for specific employee
function getNoteForEmployee(question, employeeId) {
    const answer = question.answers.find((a) => a.employee_id === employeeId);
    const note = answer?.note;
    if (!note) return null;
    // Jika note adalah string, pastikan tidak kosong
    if (typeof note === "string" && note.trim() === "") return null;
    return note;
}

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
        note: q.note,
        attachment: q.attachment || null,
        guidance: q.guidance,
    }));
});

// Filters & helpers for Questions/Activities tabs
const qSearch = ref("");
const qCategory = ref("");
const qPage = ref(1);
const qPerPage = 10;

// Reset pagination when filters change
watch([qSearch, qCategory], () => {
    qPage.value = 1;
});

const questionCategories = computed(() => {
    const set = new Set();

    // Add categories from flat questions
    (flatQuestions.value || []).forEach((q) => {
        if (q.category) set.add(q.category);
    });

    // Add categories from vehicle questions
    (vehicleQuestionsAll.value || []).forEach((q) => {
        if (q.category) set.add(q.category);
    });

    return Array.from(set).sort();
});

const filteredQuestionsAll = computed(() => {
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

// Total pages based on current table type
const totalQuestionPages = computed(() => {
    if (isMultipleChecklist.value) {
        return Math.ceil(filteredVehicleQuestionsAll.value.length / qPerPage);
    } else {
        return Math.ceil(filteredQuestionsAll.value.length / qPerPage);
    }
});

const filteredQuestions = computed(() => {
    const start = (qPage.value - 1) * qPerPage;
    const end = start + qPerPage;
    return filteredQuestionsAll.value.slice(start, end);
});

function isImage(url) {
    if (!url) return false;
    const imageExtensions = [
        ".jpg",
        ".jpeg",
        ".png",
        ".gif",
        ".bmp",
        ".webp",
        ".svg",
    ];
    return imageExtensions.some((ext) => url.toLowerCase().includes(ext));
}

const imageModalUrl = ref(null);
function openImageModal(url) {
    imageModalUrl.value = url;
}
function closeImageModal() {
    imageModalUrl.value = null;
}

// Attachment Modal
const attachmentModalData = ref(null);
function openAttachmentModal(
    url,
    employeeName = null,
    questionTitle = null,
    note = null,
) {
    attachmentModalData.value = {
        url,
        employeeName,
        questionTitle,
        note,
    };
}
function closeAttachmentModal() {
    attachmentModalData.value = null;
}

function getFileType(url) {
    if (!url) return "Unknown";
    const extension = url.split(".").pop()?.toLowerCase() || "";
    const typeMap = {
        pdf: "PDF Document",
        doc: "Word Document",
        docx: "Word Document",
        xls: "Excel Spreadsheet",
        xlsx: "Excel Spreadsheet",
        txt: "Text File",
        zip: "ZIP Archive",
        rar: "RAR Archive",
        mp4: "Video (MP4)",
        mp3: "Audio (MP3)",
    };
    return typeMap[extension] || extension.toUpperCase() + " File";
}

function printInspection() {
    // Group questions by employee and category
    const employeeGroups = {};

    (props.questions || []).forEach((q) => {
        const empName =
            q.employee_name || props.general.submitted_by || "Karyawan";
        if (!employeeGroups[empName]) {
            employeeGroups[empName] = {};
        }

        const category = q.category || "Umum";
        if (!employeeGroups[empName][category]) {
            employeeGroups[empName][category] = [];
        }

        employeeGroups[empName][category].push(q);
    });

    // Generate content for each employee
    let employeeContent = "";
    Object.entries(employeeGroups).forEach(([empName, categories]) => {
        employeeContent += `
            <div class="employee-section">
                <h2 class="employee-name">${empName}</h2>
        `;

        Object.entries(categories).forEach(([category, questions]) => {
            employeeContent += `
                <div class="category-section">
                    <h3 class="category-title">${category}</h3>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 40%;">Pertanyaan</th>
                                <th style="width: 20%;">Jawaban</th>
                                <th style="width: 35%;">Tindak Lanjut</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${questions
                                .map(
                                    (q, i) => `
                                <tr>
                                    <td>${i + 1}</td>
                                    <td>${q.title || "-"}</td>
                                    <td>${q.answer || "-"}</td>
                                    <td>${q.note || "-"}</td>
                                </tr>
                            `,
                                )
                                .join("")}
                        </tbody>
                    </table>
                </div>
            `;
        });

        // Add signature section for each employee
        employeeContent += `
                <div class="signature-section">
                    <div class="signature-box">
                        <p class="signature-label">Nama:</p>
                        <p class="signature-name">${empName}</p>
                        <p class="signature-label" style="margin-top: 60px;">Tanda Tangan:</p>
                        <div class="signature-line"></div>
                    </div>
                </div>
            </div>
            <div class="page-break"></div>
        `;
    });

    const printWindow = window.open("", "_blank");

    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Inspeksi ${props.general.inspection_number}</title>
            <style>
                @media print {
                    @page { margin: 1cm; }
                    body { margin: 0; }
                    .page-break { page-break-after: always; }
                }
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #0ea5e9; padding-bottom: 20px; }
                .logo { font-size: 28px; font-weight: bold; color: #0ea5e9; margin-bottom: 5px; }
                .subtitle { font-size: 14px; color: #666; }
                .title { font-size: 20px; font-weight: bold; margin: 20px 0; color: #333; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; }
                .info-item { padding: 10px; background: #f8f9fa; border-left: 3px solid #0ea5e9; }
                .info-label { font-size: 12px; color: #666; margin-bottom: 5px; }
                .info-value { font-size: 14px; font-weight: 600; color: #333; }

                .employee-section { margin-bottom: 40px; }
                .employee-name { font-size: 18px; font-weight: bold; color: #0ea5e9; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #0ea5e9; }
                .category-section { margin-bottom: 25px; }
                .category-title { font-size: 16px; font-weight: bold; color: #333; margin-bottom: 10px; background: #f0f9ff; padding: 8px 12px; border-left: 4px solid #0ea5e9; }

                table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
                th { background: #0ea5e9; color: white; padding: 12px; text-align: left; font-size: 13px; }
                td { padding: 10px; border-bottom: 1px solid #ddd; font-size: 12px; }
                tr:hover { background: #f8f9fa; }

                .signature-section { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }
                .signature-box { max-width: 300px; }
                .signature-label { font-size: 12px; color: #666; margin-bottom: 5px; }
                .signature-name { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 10px; }
                .signature-line { border-bottom: 2px solid #333; margin-top: 10px; }

                .footer { margin-top: 40px; text-align: center; font-size: 11px; color: #666; border-top: 2px solid #0ea5e9; padding-top: 15px; }
                .page-break { page-break-after: always; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">SISTEM PENYIMPANAN</div>
                <div class="subtitle">BackOffice Website</div>
            </div>

            <div class="title">Laporan Inspeksi ${props.general.inspection_number}</div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nomor Inspeksi</div>
                    <div class="info-value">${props.general.inspection_number || "-"}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">No. SOP</div>
                    <div class="info-value">${props.general.sop_code || "-"}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Checklist</div>
                    <div class="info-value">${props.general.checklist_name || "-"}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Kategori</div>
                    <div class="info-value">${props.general.category || "-"}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tipe Pengecekan</div>
                    <div class="info-value">${props.general.inspection_type || "-"}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Target Pengecekan</div>
                    <div class="info-value">${props.general.inspection_target || "-"}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Dibuat Oleh</div>
                    <div class="info-value">${props.general.submitted_by || "-"}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Dibuat</div>
                    <div class="info-value">${formatDateTime(props.general.created_at)}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tanggal Inspeksi</div>
                    <div class="info-value">${formatDateTime(props.general.submit_date)}</div>
                </div>
            </div>

            <div class="page-break"></div>

            ${employeeContent}

            <div class="footer">
                <p>© ${new Date().getFullYear()} Sistem Penyimpanan - BackOffice Website</p>
                <p>Dicetak pada: ${formatDateTime(new Date().toISOString())}</p>
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();

    // Wait for content to load then print
    setTimeout(() => {
        printWindow.print();
    }, 250);
}

function exportBackend() {
    if (!props.inspection?.id) return;
    const url = route("inspections.export", props.inspection.id);
    window.open(url, "_blank");
}

function exportInspection() {
    // Generate PDF-style HTML export with Henkristal branding
    const exportContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Export Inspeksi ${props.general.inspection_number}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
                .container { max-width: 1200px; margin: 0 auto; background: white; padding: 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; margin-bottom: 40px; border-bottom: 4px solid #0ea5e9; padding-bottom: 25px; }
                .logo { font-size: 36px; font-weight: bold; color: #0ea5e9; margin-bottom: 8px; letter-spacing: 2px; }
                .subtitle { font-size: 16px; color: #666; font-weight: 500; }
                .title { font-size: 24px; font-weight: bold; margin: 30px 0; color: #333; text-align: center; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
                .info-item { padding: 15px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #0ea5e9; border-radius: 4px; }
                .info-label { font-size: 13px; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
                .info-value { font-size: 16px; font-weight: 600; color: #333; }
                table { width: 100%; border-collapse: collapse; margin-top: 30px; }
                th { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: white; padding: 15px; text-align: left; font-size: 14px; font-weight: 600; }
                td { padding: 12px 15px; border-bottom: 1px solid #e5e7eb; font-size: 13px; }
                tr:nth-child(even) { background: #f9fafb; }
                tr:hover { background: #f3f4f6; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; border-top: 3px solid #0ea5e9; padding-top: 20px; }
                .footer p { margin: 5px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">SISTEM PENYIMPANAN</div>
                    <div class="subtitle">BackOffice Website</div>
                </div>

                <div class="title">Laporan Inspeksi ${props.general.inspection_number}</div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nomor Inspeksi</div>
                        <div class="info-value">${props.general.inspection_number || "-"}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">No. SOP</div>
                        <div class="info-value">${props.general.sop_code || "-"}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Checklist</div>
                        <div class="info-value">${props.general.checklist_name || "-"}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kategori</div>
                        <div class="info-value">${props.general.category || "-"}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tipe Pengecekan</div>
                        <div class="info-value">${props.general.inspection_type || "-"}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Target Pengecekan</div>
                        <div class="info-value">${props.general.inspection_target || "-"}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Dibuat Oleh</div>
                        <div class="info-value">${props.general.submitted_by || "-"}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tanggal Dibuat</div>
                        <div class="info-value">${formatDateTime(props.general.created_at)}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Tanggal Inspeksi</div>
                        <div class="info-value">${formatDateTime(props.general.submit_date)}</div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 35%;">Pertanyaan</th>
                            <th style="width: 20%;">Kategori</th>
                            <th style="width: 15%;">Jawaban</th>
                            <th style="width: 25%;">Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${(props.questions || [])
                            .map(
                                (q, i) => `
                            <tr>
                                <td>${i + 1}</td>
                                <td>${q.title || "-"}</td>
                                <td>${q.category || "-"}</td>
                                <td>${q.answer || "-"}</td>
                                <td>${q.note || "-"}</td>
                            </tr>
                        `,
                            )
                            .join("")}
                    </tbody>
                </table>

                <div class="footer">
                    <p><strong>© ${new Date().getFullYear()} Sistem Penyimpanan</strong></p>
                    <p>BackOffice Website - Versi 1.0.0</p>
                    <p>Diekspor pada: ${formatDateTime(new Date().toISOString())}</p>
                </div>
            </div>
        </body>
        </html>
    `;

    // Open in new window for download/save as PDF
    const exportWindow = window.open("", "_blank");
    exportWindow.document.write(exportContent);
    exportWindow.document.close();
}

function downloadQuestionsCsv() {
    const rows = [
        [
            "No",
            "Pertanyaan",
            "Kategori",
            "Jawaban",
            "Lampiran",
            "Tindak Lanjut",
        ],
    ];
    (filteredQuestions.value || []).forEach((q, i) => {
        rows.push([
            String(i + 1),
            String(q.title || ""),
            String(q.category || ""),
            String(q.answer ?? ""),
            q.attachment ? "Ada" : "-",
            String(q.note || "-"),
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
    a.download = `inspection_${
        props.general?.inspection_number || props.inspection?.id
    }_questions.csv`;
    a.click();
    URL.revokeObjectURL(url);
}

const tabs = [
    { value: "questions", label: "Daftar Pertanyaan" },
    { value: "approvals", label: "Responden" },
];

const activeTab = ref("questions");

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

function statusLabel(s) {
    const map = {
        on_progress: "Menunggu Persetujuan",
        approved: "Disetujui",
        rejected: "Ditolak",
        canceled: "Dibatalkan",
        partial_approved: "Disetujui Sebagian",
    };
    return map[s] ?? s;
}

function badgeClass(s) {
    const map = {
        "Belum Selesai":
            "bg-gray-100 text-gray-600 dark:bg-gray-500/20 dark:text-gray-300",
        Selesai:
            "bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300",
        Disetujui:
            "bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300",
        Ditolak: "bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300",
    };
    return map[s] || "bg-gray-100 text-gray-600";
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
