<template>
    <div class="space-y-4">
        <!-- Top Panels: Info + KPI -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div
                class="bg-white rounded-lg border border-gray-200 md:col-span-2"
            >
                <div class="px-6 py-3 border-b">
                    <div class="flex justify-between items-center">
                        <div class="flex gap-3 items-center">
                            <Link
                                :href="route('employees.index')"
                                as="button"
                                class="inline-flex justify-center items-center w-10 h-10 text-gray-700 bg-white rounded-lg border border-gray-300 transition-colors duration-200 hover:bg-gray-50"
                            >
                                <i class="text-lg lni lni-chevron-left" />
                            </Link>
                            <img
                                :src="employee?.photopath || placeholderPhoto"
                                alt="Employee Photo"
                                class="object-cover w-12 h-12 rounded-full border-2 border-gray-200 bg-gray-100 cursor-pointer hover:border-blue-400 transition-colors"
                                @click="showPhotoPreview = true"
                            />
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">
                                    {{ employee?.name ?? "Informasi Karyawan" }}
                                </h2>
                                <p class="mt-0.5 text-sm text-gray-500">
                                    {{
                                        employee?.position?.name ??
                                        "Detail dan riwayat data karyawan"
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-2 items-center">
                            <Link
                                :href="route('employees.edit', employee?.id)"
                                as="button"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg transition-colors duration-200 hover:bg-blue-700"
                            >
                                <i class="mr-1.5 lni lni-pencil" /> Edit
                                Karyawan
                            </Link>
                            <button
                                v-if="employee?.verification === false"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg transition-colors duration-200 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                type="button"
                                @click="openVerifyModal"
                            >
                                <svg
                                    class="mr-1.5 w-4 h-4"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Verifikasi Karyawan
                            </button>
                            <button
                                v-if="employee?.verification === true"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-lg transition-colors duration-200 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                type="button"
                                @click="openUnverifyModal"
                            >
                                <svg
                                    class="mr-1.5 w-4 h-4"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                Batalkan Verifikasi
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Data Pribadi Section -->
                <div class="p-6">
                    <h3 class="mb-4 text-base font-semibold text-gray-900">
                        Data Pribadi
                    </h3>
                    <div
                        class="grid grid-cols-1 gap-y-4 gap-x-6 md:grid-cols-3"
                    >
                        <!-- Baris 1: Nama Lengkap dan Email -->
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Nama Lengkap</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.name ?? "-" }}
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Email</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.user?.email ?? "-" }}
                            </div>
                        </div>
                        <!-- Baris 2: No. Telepon, Jenis Kelamin, Tempat Lahir -->
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >No. Telepon</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.contact ?? "-" }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Jenis Kelamin</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.gender ?? "-" }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Tempat Lahir</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.birthplace ?? "-" }}
                            </div>
                        </div>
                        <!-- Baris 3: Agama dan Alamat -->
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Agama</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.religion ?? "-" }}
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Alamat</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.address ?? "-" }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Pekerjaan Section -->
                <div class="p-6 border-t">
                    <h3 class="mb-4 text-base font-semibold text-gray-900">
                        Data Pekerjaan
                    </h3>
                    <div
                        class="grid grid-cols-1 gap-y-4 gap-x-6 md:grid-cols-3"
                    >
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Username</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.user?.username ?? "-" }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Departemen</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.department?.name ?? "-" }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Jabatan</label
                            >
                            <div class="text-sm text-gray-900">
                                {{
                                    employee?.user?.roles?.length
                                        ? employee.user.roles
                                              .map((r) => r.name)
                                              .join(", ")
                                        : (employee?.role?.name ?? "-")
                                }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Position</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.position?.name ?? "-" }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Cabang</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.branch?.name ?? "-" }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Shift Kerja</label
                            >
                            <div class="text-sm text-gray-900">
                                {{
                                    employee?.shift
                                        ? `${employee.shift.name} (${employee.shift.start_time} - ${employee.shift.end_time})`
                                        : "-"
                                }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Tanggal Mulai</label
                            >
                            <div class="text-sm text-gray-900">
                                {{
                                    dateFormat(employee?.working_start_date) ??
                                    "-"
                                }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Gaji Pokok</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ numberFormat(employee?.salary) ?? "-" }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >Status Karyawan</label
                            >
                            <div class="flex gap-2 items-center">
                                <span
                                    :class="
                                        employee?.verification === false
                                            ? 'text-red-600 bg-red-100 border-red-300'
                                            : 'text-green-700 bg-green-100 border-green-300'
                                    "
                                    class="px-2 py-0.5 text-[11px] rounded-full border"
                                    >{{
                                        employee?.verification === false
                                            ? "Belum Verifikasi"
                                            : "Sudah Terverifikasi"
                                    }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Dokumen Section -->
                <div class="p-6 border-t">
                    <h3 class="mb-4 text-base font-semibold text-gray-900">
                        Data Dokumen
                    </h3>
                    <div
                        class="grid grid-cols-1 gap-y-4 gap-x-6 md:grid-cols-3"
                    >
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >NIK</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.nik ?? "-" }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >KTP</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.ktp ?? "-" }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >BPJS Kesehatan</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.bpjs_kesehatan ?? "-" }}
                            </div>
                        </div>
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >BPJS Ketenagakerjaan</label
                            >
                            <div class="text-sm text-gray-900">
                                {{ employee?.bpjs_ketenagakerjaan ?? "-" }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="grid grid-cols-1 gap-y-4 gap-x-6 md:grid-cols-3 mt-4"
                    >
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-gray-500"
                                >TTD</label
                            >
                            <div class="text-sm text-gray-900">
                                <img
                                    :src="employee?.signature_url"
                                    alt="TTD"
                                    class="w-25 h-25 object-cover rounded-lg border border-gray-200"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4">
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-6 py-3 border-b">
                        <h2 class="text-lg font-semibold text-gray-800">
                            KPI Absensi Perbulan
                        </h2>
                    </div>
                    <div class="p-6">
                        <!-- Gauge moved to top -->
                        <div class="flex justify-center mb-4">
                            <div
                                class="flex flex-col items-center p-4 w-full bg-gray-50 rounded-lg border"
                            >
                                <svg
                                    width="120"
                                    height="80"
                                    viewBox="0 0 120 80"
                                >
                                    <!-- Background arc -->
                                    <path
                                        d="M 10 70 A 50 50 0 0 1 110 70"
                                        stroke="#e5e7eb"
                                        stroke-width="8"
                                        fill="none"
                                    />
                                    <!-- Value arc -->
                                    <path
                                        :d="gaugePath"
                                        stroke="#3b82f6"
                                        stroke-width="8"
                                        fill="none"
                                        stroke-linecap="round"
                                    />
                                    <text
                                        x="60"
                                        y="65"
                                        text-anchor="middle"
                                        class="text-2xl font-bold fill-gray-800"
                                    >
                                        {{ presentaseAbsenMonth }}%
                                    </text>
                                </svg>
                                <div class="mt-2 text-sm text-gray-600">
                                    Skor Keseluruhan
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div
                                class="flex flex-col justify-center p-4 h-full bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100"
                            >
                                <div class="mb-1 text-sm text-gray-500">
                                    Skor Kehadiran
                                </div>
                                <div
                                    class="text-3xl font-semibold text-gray-800"
                                >
                                    {{
                                        (presentaseScorePresensi ?? 0).toFixed(
                                            1,
                                        )
                                    }}<span class="ml-1 text-base font-medium"
                                        >%</span
                                    >
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    (Hadir / Total Hari Kerja) × 100
                                </div>
                            </div>

                            <div
                                class="flex flex-col justify-center p-4 h-full bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100"
                            >
                                <div class="mb-1 text-sm text-gray-500">
                                    Skor Tepat Waktu
                                </div>
                                <div
                                    class="text-3xl font-semibold text-gray-800"
                                >
                                    {{ (presentaseScoreOntime ?? 0).toFixed(1)
                                    }}<span class="ml-1 text-base font-medium"
                                        >%</span
                                    >
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    (Hadir Tepat Waktu / Hari Hadir) × 100
                                </div>
                            </div>
                            <!-- Absensi Hari Ini -->
                        </div>
                    </div>
                </div>

                <!-- Statistik card replaces removed KPI Keseluruhan -->
                <div class="bg-white rounded-lg border border-gray-200">
                    <div
                        class="flex flex-col col-span-2 p-4 h-full bg-gray-50 rounded-lg border"
                    >
                        <div class="mb-2 text-sm text-gray-500">
                            Absensi Hari Ini
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Jam Masuk</span>
                            <span class="font-semibold text-gray-800">{{
                                todayIn
                            }}</span>
                        </div>
                        <div
                            class="flex justify-between mt-2 text-sm text-gray-600"
                        >
                            <span>Jam Keluar</span>
                            <span class="font-semibold text-gray-800">{{
                                todayOut
                            }}</span>
                        </div>
                        <div
                            class="mt-2 text-xs text-gray-500"
                            v-if="todayStatus"
                        >
                            Status:
                            <span class="font-medium text-gray-700">{{
                                todayStatus
                            }}</span>
                        </div>
                        <div class="mt-2 text-xs text-gray-400" v-else>
                            Belum ada absensi hari ini
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="px-6 py-3 border-b">
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-semibold text-gray-800">
                                Statistik
                            </h2>
                            <button
                                @click="showAddLeaveModal = true"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg transition-colors duration-200 hover:bg-green-700"
                                type="button"
                            >
                                <i class="mr-1.5 lni lni-plus"></i>
                                Tambah Hak Cuti
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div
                            class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                class="flex flex-col justify-center p-4 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100"
                            >
                                <div class="mb-1 text-sm text-gray-500">
                                    Jumlah Kehadiran
                                </div>
                                <div
                                    class="text-2xl font-semibold text-gray-800"
                                >
                                    {{ kpi.present || 0 }}
                                </div>
                            </div>
                            <div
                                class="flex flex-col justify-center p-4 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100"
                            >
                                <div class="mb-1 text-sm text-gray-500">
                                    Terlambat
                                </div>
                                <div
                                    class="text-2xl font-semibold text-gray-800"
                                >
                                    {{ kpi.late || 0 }}
                                </div>
                            </div>
                            <div
                                class="flex flex-col justify-center p-4 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100"
                            >
                                <div class="mb-1 text-sm text-gray-500">
                                    Alpha
                                </div>
                                <div
                                    class="text-2xl font-semibold text-gray-800"
                                >
                                    {{ kpi.alpha || 0 }}
                                </div>
                            </div>
                            <!-- Leave balance card -->
                            <div
                                class="flex flex-col justify-center p-4 bg-gray-50 rounded-lg border"
                            >
                                <div class="mb-1 text-sm text-gray-500">
                                    Cuti
                                </div>
                                <div
                                    class="text-xl font-semibold text-gray-800"
                                >
                                    {{
                                        numberOnly(
                                            (leaveBalance?.annual?.used_quota ??
                                                leaveBalance?.used_quota) ||
                                                0,
                                        )
                                    }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Terpakai/Jatah:
                                    {{
                                        numberOnly(
                                            (leaveBalance?.annual?.used_quota ??
                                                leaveBalance?.used_quota) ||
                                                0,
                                        )
                                    }}
                                    /
                                    {{
                                        numberOnly(
                                            (leaveBalance?.annual
                                                ?.total_quota ??
                                                leaveBalance?.total_quota) ||
                                                0,
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                class="flex flex-col justify-center p-4 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100"
                            >
                                <div class="mb-1 text-sm text-gray-500">
                                    Sakit
                                </div>
                                <div
                                    class="text-2xl font-semibold text-gray-800"
                                >
                                    {{ kpi.sick || 0 }}
                                </div>
                            </div>
                            <div
                                class="flex flex-col justify-center p-4 bg-gray-50 rounded-lg border cursor-pointer hover:bg-gray-100"
                            >
                                <div class="mb-1 text-sm text-gray-500">
                                    Piutang Terpakai
                                </div>
                                <div
                                    class="text-xl font-semibold text-gray-800"
                                >
                                    {{
                                        numberFormat(
                                            (receivable && receivable.used) ||
                                                0,
                                        )
                                    }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    Terpakai/Jatah Piutang:
                                    {{
                                        numberOnly(
                                            (receivable && receivable.used) ||
                                                0,
                                        )
                                    }}
                                    /
                                    {{
                                        numberOnly(
                                            (receivable && receivable.limit) ||
                                                0,
                                        )
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Tabs (plain HTML) -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-3 border-b">
                <h2 class="text-lg font-semibold text-gray-800">
                    Detail Karyawan
                </h2>
            </div>
            <div class="w-full">
                <div class="flex gap-2 px-4 pt-3 border-b border-gray-200">
                    <button
                        v-for="tab in tabHeaders"
                        :key="tab.value"
                        type="button"
                        class="px-4 -mb-px h-11 border-b-2"
                        :class="
                            activeTab === tab.value
                                ? 'text-blue-600 border-blue-500'
                                : 'text-gray-600 border-transparent hover:text-gray-800'
                        "
                        @click="activeTab = tab.value"
                    >
                        {{ tab.label }}
                    </button>
                </div>
                <div class="p-4">
                    <div v-show="activeTab === 'absensi'">
                        <div class="overflow-auto" data-simplebar>
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 border-b">
                                        <th
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Tanggal
                                        </th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Jam Masuk
                                        </th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Jam Keluar
                                        </th>
                                        <th
                                            class="px-4 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr v-if="!pagedAttendances.length">
                                        <td
                                            colspan="4"
                                            class="px-4 py-8 text-center text-gray-500"
                                        >
                                            Tidak ada absensi
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(
                                            attendance, index
                                        ) in pagedAttendances"
                                        :key="index"
                                        class="hover:bg-gray-50"
                                    >
                                        <td
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            {{ attendance["Tanggal"] }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            {{ attendance["Jam Masuk"] }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            {{ attendance["Jam Keluar"] }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm text-gray-900"
                                        >
                                            {{ attendance["Status"] }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="flex justify-between items-center pt-4 mt-4 border-t"
                        >
                            <button
                                @click="
                                    pages.absensi = Math.max(
                                        1,
                                        pages.absensi - 1,
                                    )
                                "
                                :disabled="pages.absensi <= 1"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <span class="text-sm text-gray-700">
                                Page {{ pages.absensi }} of
                                {{ totalPages.absensi }}
                            </span>
                            <button
                                @click="
                                    pages.absensi = Math.min(
                                        totalPages.absensi,
                                        pages.absensi + 1,
                                    )
                                "
                                :disabled="pages.absensi >= totalPages.absensi"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                    <div v-show="activeTab === 'slip'">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            No
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Periode
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Tanggal Terbit
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr v-if="!pagedPayrolls.length">
                                        <td
                                            colspan="4"
                                            class="px-6 py-4 text-center text-gray-500"
                                        >
                                            Tidak ada slip gaji
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(
                                            payroll, index
                                        ) in pagedPayrolls"
                                        :key="index"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{
                                                (pages.slip - 1) * pageSize +
                                                index +
                                                1
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ payroll["Periode"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ payroll["Tanggal Terbit"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a
                                                v-if="payroll['Aksi']"
                                                :href="payroll['Aksi']"
                                                target="_blank"
                                                class="inline-flex items-center px-3 py-1 text-white bg-blue-500 rounded"
                                                >Download</a
                                            >
                                            <span v-else class="text-gray-400"
                                                >-</span
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="flex justify-between items-center pt-4 mt-4 border-t"
                        >
                            <button
                                @click="
                                    pages.slip = Math.max(1, pages.slip - 1)
                                "
                                :disabled="pages.slip <= 1"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <span class="text-sm text-gray-700">
                                Page {{ pages.slip }} of
                                {{ totalPages.slip }}
                            </span>
                            <button
                                @click="
                                    pages.slip = Math.min(
                                        totalPages.slip,
                                        pages.slip + 1,
                                    )
                                "
                                :disabled="pages.slip >= totalPages.slip"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                    <div v-show="activeTab === 'jadwal'">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            No
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Tanggal
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Hari
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Shift
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Jam Masuk
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Jam Keluar
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-center text-gray-500 uppercase"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Keterangan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr v-if="!pagedSchedules.length">
                                        <td
                                            colspan="8"
                                            class="px-6 py-4 text-center text-gray-500"
                                        >
                                            Tidak ada jadwal
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(
                                            schedule, index
                                        ) in pagedSchedules"
                                        :key="index"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{
                                                (pages.jadwal - 1) * pageSize +
                                                index +
                                                1
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{
                                                schedule.tanggal ||
                                                schedule["Tanggal"] ||
                                                "-"
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{
                                                schedule.hari ||
                                                getDayName(
                                                    schedule.tanggal ||
                                                        schedule["Tanggal"],
                                                )
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{
                                                schedule.shift_name ||
                                                schedule["Shift"] ||
                                                "-"
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{
                                                schedule.jam_masuk ||
                                                schedule.start_time ||
                                                "-"
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{
                                                schedule.jam_keluar ||
                                                schedule.end_time ||
                                                "-"
                                            }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-center whitespace-nowrap"
                                        >
                                            <span
                                                :class="
                                                    getStatusClass(
                                                        schedule.status,
                                                    )
                                                "
                                            >
                                                {{
                                                    schedule.status ||
                                                    "Terjadwal"
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{
                                                schedule.keterangan ||
                                                schedule["Keterangan"] ||
                                                "-"
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="flex justify-between items-center pt-4 mt-4 border-t"
                        >
                            <button
                                @click="
                                    pages.jadwal = Math.max(1, pages.jadwal - 1)
                                "
                                :disabled="pages.jadwal <= 1"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <span class="text-sm text-gray-700">
                                Page {{ pages.jadwal }} of
                                {{ totalPages.jadwal }}
                            </span>
                            <button
                                @click="
                                    pages.jadwal = Math.min(
                                        totalPages.jadwal,
                                        pages.jadwal + 1,
                                    )
                                "
                                :disabled="pages.jadwal >= totalPages.jadwal"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                    <div v-show="activeTab === 'cuti'">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            No
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Tanggal
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Jenis
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Durasi
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr v-if="!pagedLeaves.length">
                                        <td
                                            colspan="5"
                                            class="px-6 py-4 text-center text-gray-500"
                                        >
                                            Tidak ada cuti
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(leave, index) in pagedLeaves"
                                        :key="index"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{
                                                (pages.cuti - 1) * pageSize +
                                                index +
                                                1
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ leave["Tanggal"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ leave["Jenis"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ leave["Durasi"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ leave["Status"] }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="flex justify-between items-center pt-4 mt-4 border-t"
                        >
                            <button
                                @click="
                                    pages.cuti = Math.max(1, pages.cuti - 1)
                                "
                                :disabled="pages.cuti <= 1"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <span class="text-sm text-gray-700">
                                Page {{ pages.cuti }} of
                                {{ totalPages.cuti }}
                            </span>
                            <button
                                @click="
                                    pages.cuti = Math.min(
                                        totalPages.cuti,
                                        pages.cuti + 1,
                                    )
                                "
                                :disabled="pages.cuti >= totalPages.cuti"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                    <div v-show="activeTab === 'lembur'">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Tanggal
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Jam
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Durasi
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Keterangan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr v-if="!pagedOvertimes.length">
                                        <td
                                            colspan="4"
                                            class="px-6 py-4 text-center text-gray-500"
                                        >
                                            Tidak ada lembur
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(
                                            overtime, index
                                        ) in pagedOvertimes"
                                        :key="index"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ overtime["Tanggal"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ overtime["Jam"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ overtime["Durasi"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ overtime["Keterangan"] }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="flex justify-between items-center pt-4 mt-4 border-t"
                        >
                            <button
                                @click="
                                    pages.lembur = Math.max(1, pages.lembur - 1)
                                "
                                :disabled="pages.lembur <= 1"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <span class="text-sm text-gray-700">
                                Page {{ pages.lembur }} of
                                {{ totalPages.lembur }}
                            </span>
                            <button
                                @click="
                                    pages.lembur = Math.min(
                                        totalPages.lembur,
                                        pages.lembur + 1,
                                    )
                                "
                                :disabled="pages.lembur >= totalPages.lembur"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                    <div v-show="activeTab === 'piutang'">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Tanggal
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Nominal
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Keterangan
                                        </th>
                                        <th
                                            class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
                                        >
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="(debt, index) in pagedDebts"
                                        :key="index"
                                        class="hover:bg-gray-50"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ debt["Tanggal"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ debt["Nominal"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ debt["Keterangan"] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ debt["Status"] }}
                                        </td>
                                    </tr>
                                    <tr v-if="pagedDebts.length === 0">
                                        <td
                                            colspan="4"
                                            class="px-6 py-4 text-center text-gray-500"
                                        >
                                            Tidak ada piutang
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="flex justify-between items-center pt-4 mt-4 border-t"
                        >
                            <button
                                @click="
                                    pages.piutang = Math.max(
                                        1,
                                        pages.piutang - 1,
                                    )
                                "
                                :disabled="pages.piutang <= 1"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <span class="text-sm text-gray-700">
                                Page {{ pages.piutang }} of
                                {{ totalPages.piutang }}
                            </span>
                            <button
                                @click="
                                    pages.piutang = Math.min(
                                        totalPages.piutang,
                                        pages.piutang + 1,
                                    )
                                "
                                :disabled="pages.piutang >= totalPages.piutang"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                    <div v-show="activeTab === 'dokumen'">
                        <!-- Upload form -->
                        <form
                            class="grid grid-cols-1 gap-2 items-end mb-4 md:grid-cols-3"
                            @submit.prevent="submitDocument"
                        >
                            <div>
                                <label class="block mb-1 text-sm text-gray-600"
                                    >Judul Dokumen</label
                                >
                                <input
                                    v-model="docForm.title"
                                    type="text"
                                    class="px-3 w-full h-10 rounded border"
                                    placeholder="Mis. KTP / Kontrak"
                                />
                            </div>
                            <div>
                                <label class="block mb-1 text-sm text-gray-600"
                                    >File</label
                                >
                                <input
                                    @change="onDocFile"
                                    type="file"
                                    class="px-3 w-full h-10 rounded border"
                                />
                            </div>
                            <div>
                                <button
                                    type="submit"
                                    class="px-4 h-10 text-white bg-blue-500 rounded"
                                >
                                    Upload
                                </button>
                            </div>
                        </form>

                        <div class="overflow-auto" data-simplebar>
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-gray-600"
                                        >
                                            Nama
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-gray-600"
                                        >
                                            Nilai
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-gray-600"
                                        >
                                            Tanggal Upload
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-gray-600"
                                        >
                                            Preview
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!pagedDocuments.length">
                                        <td
                                            colspan="4"
                                            class="px-4 py-8 text-center text-gray-500"
                                        >
                                            Tidak ada dokumen
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(doc, index) in pagedDocuments"
                                        :key="index"
                                        class="border-t"
                                    >
                                        <td class="px-4 py-2">
                                            {{ doc["Nama"] }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ doc["Nilai"] }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ doc["Tanggal Upload"] }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <div
                                                v-if="isImageFile(doc['Nilai'])"
                                                class="flex items-center"
                                            >
                                                <img
                                                    :src="
                                                        getDocumentUrl(
                                                            doc['Nilai'],
                                                        )
                                                    "
                                                    alt="Preview"
                                                    class="w-16 h-16 object-cover rounded cursor-pointer hover:opacity-80 transition-opacity"
                                                    @click="
                                                        openDocumentPreview(
                                                            getDocumentUrl(
                                                                doc['Nilai'],
                                                            ),
                                                            doc['Nama'],
                                                        )
                                                    "
                                                />
                                            </div>
                                            <div
                                                v-else
                                                class="flex items-center"
                                            >
                                                <a
                                                    :href="
                                                        getDocumentUrl(
                                                            doc['Nilai'],
                                                        )
                                                    "
                                                    target="_blank"
                                                    class="flex items-center gap-2 text-blue-600 hover:text-blue-800 hover:underline"
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
                                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                                        />
                                                    </svg>
                                                    <span class="text-sm"
                                                        >Lihat</span
                                                    >
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <button
                                @click="
                                    pages.dokumen = Math.max(
                                        1,
                                        pages.dokumen - 1,
                                    )
                                "
                                :disabled="pages.dokumen <= 1"
                                class="px-3 py-1 text-sm bg-gray-100 rounded disabled:opacity-50"
                            >
                                Prev
                            </button>
                            <span class="text-sm text-gray-600">
                                Page {{ pages.dokumen }} of
                                {{ totalPages.dokumen }}
                            </span>
                            <button
                                @click="
                                    pages.dokumen = Math.min(
                                        totalPages.dokumen,
                                        pages.dokumen + 1,
                                    )
                                "
                                :disabled="pages.dokumen >= totalPages.dokumen"
                                class="px-3 py-1 text-sm bg-gray-100 rounded disabled:opacity-50"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                    <div v-show="activeTab === 'log'">
                        <div class="overflow-auto" data-simplebar>
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-gray-600"
                                        >
                                            Waktu
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-gray-600"
                                        >
                                            Aksi
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-gray-600"
                                        >
                                            Oleh
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-gray-600"
                                        >
                                            Catatan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!pagedActivities.length">
                                        <td
                                            colspan="4"
                                            class="px-4 py-8 text-center text-gray-500"
                                        >
                                            Tidak ada aktivitas
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(
                                            activity, index
                                        ) in pagedActivities"
                                        :key="index"
                                        class="border-t"
                                    >
                                        <td class="px-4 py-2">
                                            {{ activity["Waktu"] }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ activity["Aksi"] }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ activity["Oleh"] }}
                                        </td>
                                        <td class="px-4 py-2">
                                            {{ activity["Catatan"] }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-between items-center mt-4">
                            <button
                                @click="pages.log = Math.max(1, pages.log - 1)"
                                :disabled="pages.log <= 1"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Prev
                            </button>
                            <span class="text-sm text-gray-600">
                                Page {{ pages.log }} of {{ totalPages.log }}
                            </span>
                            <button
                                @click="
                                    pages.log = Math.min(
                                        totalPages.log,
                                        pages.log + 1,
                                    )
                                "
                                :disabled="pages.log >= totalPages.log"
                                class="px-3 py-2 text-sm bg-white rounded-md border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Statistic modal state and logic -->
    <div
        v-if="statModal.open"
        class="flex fixed inset-0 z-50 justify-center items-center bg-black/40"
    >
        <div
            class="mx-3 w-full max-w-3xl bg-white rounded-xl border border-gray-200 shadow-lg"
        >
            <div class="flex justify-between items-center px-5 py-3 border-b">
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ statModal.title }}
                </h3>
                <button
                    class="w-9 h-9 rounded-full hover:bg-gray-100"
                    @click="closeStatModal"
                >
                    ✕
                </button>
            </div>
            <div class="p-5">
                <component
                    :is="SimpleTable"
                    :headers="statModal.headers"
                    :rows="statModal.rows"
                    :empty="statModal.empty"
                />
            </div>
        </div>
    </div>

    <!-- Photo Preview Modal -->
    <div
        v-if="showPhotoPreview"
        class="flex fixed inset-0 z-50 justify-center items-center bg-black/80 p-4"
        @click="showPhotoPreview = false"
    >
        <div class="relative max-w-4xl max-h-[90vh]" @click.stop>
            <button
                @click="showPhotoPreview = false"
                class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors"
            >
                <svg
                    class="w-8 h-8"
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
            <img
                :src="employee?.photopath || placeholderPhoto"
                alt="Employee Photo Preview"
                class="max-w-full max-h-[85vh] rounded-lg shadow-2xl"
            />
            <div class="mt-4 text-center text-white">
                <p class="text-lg font-semibold">{{ employee?.name }}</p>
                <p class="text-sm text-gray-300">
                    {{ employee?.position?.name }}
                </p>
            </div>
        </div>
    </div>

    <!-- Document Preview Modal -->
    <div
        v-if="showDocumentPreview"
        class="flex fixed inset-0 z-50 justify-center items-center bg-black/80 p-4"
        @click="showDocumentPreview = false"
    >
        <div class="relative max-w-4xl max-h-[90vh]" @click.stop>
            <button
                @click="showDocumentPreview = false"
                class="absolute -top-10 right-0 text-white hover:text-gray-300 transition-colors"
            >
                <svg
                    class="w-8 h-8"
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
            <img
                :src="documentPreviewUrl"
                :alt="documentPreviewTitle"
                class="max-w-full max-h-[85vh] rounded-lg shadow-2xl"
            />
            <div class="mt-4 text-center text-white">
                <p class="text-lg font-semibold">{{ documentPreviewTitle }}</p>
            </div>
        </div>
    </div>

    <!-- Verify Modal -->
    <div
        v-if="showVerify"
        class="flex fixed inset-0 z-50 justify-center items-center bg-black/40"
    >
        <div class="p-6 w-full max-w-lg bg-white rounded-lg shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">
                    Konfirmasi & Lengkapi Data
                </h3>
                <button class="text-gray-500" @click="showVerify = false">
                    ✕
                </button>
            </div>
            <div class="grid grid-cols-1 gap-3">
                <div>
                    <label class="block mb-1 text-sm text-gray-600"
                        >Nama Lengkap</label
                    >
                    <input
                        v-model="verifyForm.name"
                        type="text"
                        class="px-3 w-full h-10 rounded border"
                    />
                </div>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Username</label
                        >
                        <input
                            v-model="verifyForm.username"
                            type="text"
                            class="px-3 w-full h-10 rounded border"
                        />
                    </div>
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >Email</label
                        >
                        <input
                            v-model="verifyForm.email"
                            type="email"
                            class="px-3 w-full h-10 rounded border"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-sm text-gray-600"
                            >No. Telepon</label
                        >
                        <input
                            v-model="verifyForm.contact"
                            type="text"
                            class="px-3 w-full h-10 rounded border"
                        />
                    </div>
                </div>
                <div>
                    <label class="block mb-1 text-sm text-gray-600"
                        >Alamat</label
                    >
                    <textarea
                        v-model="verifyForm.address"
                        class="px-3 w-full rounded border"
                        rows="3"
                    ></textarea>
                </div>
            </div>
            <div class="flex gap-2 justify-end mt-5">
                <button
                    class="px-3 h-10 rounded border"
                    @click="showVerify = false"
                >
                    Batal
                </button>
                <button
                    class="px-3 h-10 text-white bg-green-600 rounded"
                    @click="submitVerify"
                >
                    Simpan & Verifikasi
                </button>
            </div>
        </div>
    </div>

    <!-- Unverify Confirmation Modal -->
    <div
        v-if="showUnverify"
        class="flex fixed inset-0 z-50 justify-center items-center bg-black/40"
    >
        <div class="p-6 w-full max-w-md bg-white rounded-lg shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Batalkan Verifikasi Karyawan
                </h3>
                <button
                    class="text-gray-500 hover:text-gray-700"
                    @click="showUnverify = false"
                >
                    ✕
                </button>
            </div>
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-4">
                    Apakah Anda yakin ingin membatalkan verifikasi karyawan
                    <strong>{{ employee?.name }}</strong
                    >?
                </p>
                <div
                    class="p-3 bg-yellow-50 border border-yellow-200 rounded-md"
                >
                    <p class="text-xs text-yellow-800">
                        <strong>Perhatian:</strong> Membatalkan verifikasi akan
                        mengubah status karyawan menjadi "Belum Verifikasi" dan
                        mungkin mempengaruhi akses sistem.
                    </p>
                </div>
            </div>
            <div class="flex gap-2 justify-end">
                <button
                    class="px-4 h-10 text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50"
                    @click="showUnverify = false"
                >
                    Batal
                </button>
                <button
                    class="px-4 h-10 text-white bg-red-600 rounded hover:bg-red-700"
                    @click="submitUnverify"
                    :disabled="isUnverifying"
                >
                    <span v-if="isUnverifying">Memproses...</span>
                    <span v-else>Ya, Batalkan Verifikasi</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Add Leave Balance Modal -->
    <div
        v-if="showAddLeaveModal"
        class="flex fixed inset-0 z-50 justify-center items-center bg-black/40"
    >
        <div class="p-6 w-full max-w-md bg-white rounded-lg shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Tambah Hak Cuti
                </h3>
                <button
                    class="text-gray-500 hover:text-gray-700"
                    @click="closeAddLeaveModal"
                >
                    ✕
                </button>
            </div>
            <form @submit.prevent="submitAddLeave">
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Jenis Cuti <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="leaveForm.leave_type_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">Pilih Jenis Cuti</option>
                        <option
                            v-for="type in leaveTypes"
                            :key="type.id"
                            :value="type.id"
                        >
                            {{ type.name }}
                        </option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Tahun <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model.number="leaveForm.year"
                        type="number"
                        min="2000"
                        max="2100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    />
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Jumlah Hari <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model.number="leaveForm.total_quota"
                        type="number"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    />
                </div>
                <div
                    class="p-3 mb-4 bg-blue-50 border border-blue-200 rounded-md"
                >
                    <p class="text-xs text-blue-800">
                        <i class="mr-1 lni lni-information"></i>
                        Jika sudah ada data untuk tahun dan jenis cuti ini,
                        quota akan diperbarui.
                    </p>
                </div>
                <div class="flex gap-2 justify-end">
                    <button
                        type="button"
                        class="px-4 h-10 text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50"
                        @click="closeAddLeaveModal"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 h-10 text-white bg-green-600 rounded hover:bg-green-700"
                        :disabled="isSubmittingLeave"
                    >
                        <span v-if="isSubmittingLeave">Menyimpan...</span>
                        <span v-else>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, reactive } from "vue";
import { usePage, useForm, Link, router } from "@inertiajs/vue3";
defineOptions({ layout: AppLayout });
const { props } = usePage();
const counts = props.counts || {};
const presentaseAbsenMonth = props.presentaseAbsenMonth || 0;
const presentaseScorePresensi = props.presentaseScorePresensi || 0;
const presentaseScoreOntime = props.presentaseScoreOntime || 0;
const leaveTypes = props.leaveTypes || [];
// Simple gray placeholder (photo)
const placeholderPhoto =
    "data:image/svg+xml;charset=UTF-8," +
    encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="112" height="112" viewBox="0 0 112 112">\n  <rect width="112" height="112" fill="#f3f4f6"/>\n  <circle cx="56" cy="44" r="18" fill="#e5e7eb"/>\n  <rect x="20" y="72" width="72" height="20" rx="10" fill="#e5e7eb"/>\n</svg>',
    );

// Signature placeholder (dashed box)
const placeholderSignature =
    "data:image/svg+xml;charset=UTF-8," +
    encodeURIComponent(
        '<svg xmlns="http://www.w3.org/2000/svg" width="240" height="64" viewBox="0 0 240 64">\n  <rect x="0.5" y="0.5" width="239" height="63" rx="6" fill="#fafafa" stroke="#d1d5db" stroke-dasharray="6 4"/>\n  <text x="120" y="38" text-anchor="middle" font-family="sans-serif" font-size="12" fill="#9ca3af">Tanda Tangan</text>\n</svg>',
    );

// Document upload form state
const docForm = ref({ title: "", file: null });
const onDocFile = (e) => {
    docForm.value.file = e.target.files?.[0] || null;
};
const submitDocument = () => {
    if (!docForm.value.title || !docForm.value.file) {
        alert("Judul dan file wajib diisi.");
        return;
    }
    const up = useForm({
        title: docForm.value.title,
        file: docForm.value.file,
    });
    up.post(route("employees.documents.store", props.employee?.id), {
        forceFormData: true,
        onSuccess: () => {
            docForm.value = { title: "", file: null };
            // refresh page data
            window.location.reload();
        },
        onError: () => alert("Gagal mengunggah dokumen"),
    });
};
const tabHeaders = computed(() => [
    { value: "absensi", label: `Riwayat Absensi (${counts.attendances || 0})` },
    { value: "slip", label: `Slip Gaji (${counts.payrolls || 0})` },
    { value: "jadwal", label: `Jadwal Kerja (${counts.schedules || 0})` },
    { value: "cuti", label: `Cuti (${counts.leaves || 0})` },
    { value: "lembur", label: `Lembur (${counts.overtimes || 0})` },
    { value: "piutang", label: `Piutang (${counts.debts || 0})` },
    { value: "dokumen", label: `Dokumen (${counts.documents || 0})` },
    { value: "log", label: `Log Aktivitas (${counts.activities || 0})` },
]);

const employee = props.employee || null;
const activeTab = ref("absensi");
const numberFormat = (value) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
    }).format(value);
};

// plain number formatter without currency symbol (e.g., 100.000)
const numberOnly = (value) => {
    const n = Number(value || 0);
    return n.toLocaleString("id-ID", { maximumFractionDigits: 0 });
};

// KPI and tabs data (fallback simple)
const kpi = props.kpi || { present: 0, late: 0, leave: 0, sick: 0, alpha: 0 };
const receivable = props.receivable || { used: 0, limit: 0 };
const leaveBalance = props.employee?.annual_leave_balance || null;
const spark = props.kpi_series || [10, 30, 50, 20, 60, 40, 50];
const attendances = props.attendances || [];
const payrolls = props.payrolls || [];
const schedules = props.schedules || [];
const leaves = props.leaves || [];
const overtimes = props.overtimes || [];
const debts = props.debts || [];
const activities = props.activities || [];
const documents = props.documents || [];

// Scores computation
const hadirDays = computed(() => (kpi.present || 0) + (kpi.late || 0));
const totalWorkDays = computed(
    () => hadirDays.value + (kpi.leave || 0) + (kpi.sick || 0),
);
const attendanceScore = computed(() =>
    totalWorkDays.value > 0
        ? Number(((hadirDays.value / totalWorkDays.value) * 100).toFixed(1))
        : 0,
);
const onTimeScore = computed(() =>
    hadirDays.value > 0
        ? Number((((kpi.present || 0) / hadirDays.value) * 100).toFixed(1))
        : 0,
);
const overallScore = computed(() =>
    Number((attendanceScore.value * 0.6 + onTimeScore.value * 0.4).toFixed(1)),
);

// Gauge path calculation for semicircle gauge
const gaugePath = computed(() => {
    const score = Math.max(0, Math.min(100, overallScore.value)); // Clamp between 0-100
    if (score === 0) {
        // No arc if score is 0
        return "M 10 70";
    }

    // Calculate the angle in radians
    // Start at 180° (left point at 10, 70), move clockwise to 0° (right point at 110, 70)
    // For score p%, end angle = 180° - (p/100) * 180° = π - (p/100) * π
    const centerX = 60;
    const centerY = 70;
    const radius = 50;
    const angle = Math.PI - (score / 100) * Math.PI;

    // Calculate endpoint coordinates
    const endX = centerX + radius * Math.cos(angle);
    const endY = centerY - radius * Math.sin(angle);

    // For semicircle gauge, we always take the shorter path (≤ 180°), so largeArcFlag = 0
    // Sweep flag = 1 for clockwise direction
    return `M 10 70 A ${radius} ${radius} 0 0 1 ${endX} ${endY}`;
});

// Today's attendance (Absensi Hari Ini)
const todayISO = new Date().toISOString().slice(0, 10);
const todayRow = computed(() => {
    try {
        return (
            (attendances || []).find((r) => r && r["Tanggal"] === todayISO) ||
            null
        );
    } catch {
        return null;
    }
});
const todayIn = computed(() =>
    todayRow.value ? todayRow.value["Jam Masuk"] || "-" : "-",
);
const todayOut = computed(() =>
    todayRow.value ? todayRow.value["Jam Keluar"] || "-" : "-",
);
const todayStatus = computed(() =>
    todayRow.value ? todayRow.value["Status"] || "" : "",
);

// Pagination logic (client-side)
const pageSize = ref(10);
const pages = reactive({
    absensi: 1,
    slip: 1,
    jadwal: 1,
    cuti: 1,
    lembur: 1,
    piutang: 1,
    dokumen: 1,
    log: 1,
});
const paginate = (items, page) => {
    const start = (page - 1) * pageSize.value;
    return items.slice(start, start + pageSize.value);
};
const totalPages = {
    get absensi() {
        return Math.max(1, Math.ceil(attendances.length / pageSize.value));
    },
    get slip() {
        return Math.max(1, Math.ceil(payrolls.length / pageSize.value));
    },
    get jadwal() {
        return Math.max(1, Math.ceil(schedules.length / pageSize.value));
    },
    get cuti() {
        return Math.max(1, Math.ceil(leaves.length / pageSize.value));
    },
    get lembur() {
        return Math.max(1, Math.ceil(overtimes.length / pageSize.value));
    },
    get piutang() {
        return Math.max(1, Math.ceil(debts.length / pageSize.value));
    },
    get dokumen() {
        return Math.max(1, Math.ceil(documents.length / pageSize.value));
    },
    get log() {
        return Math.max(1, Math.ceil(activities.length / pageSize.value));
    },
};
const pagedAttendances = computed(() => paginate(attendances, pages.absensi));
const pagedPayrolls = computed(() => paginate(payrolls, pages.slip));
const pagedSchedules = computed(() => paginate(schedules, pages.jadwal));
const pagedLeaves = computed(() => paginate(leaves, pages.cuti));
const pagedOvertimes = computed(() => paginate(overtimes, pages.lembur));
const pagedDebts = computed(() => paginate(debts, pages.piutang));
const pagedDocuments = computed(() => paginate(documents, pages.dokumen));
const pagedActivities = computed(() => paginate(activities, pages.log));

// Helper function to get day name from date
const getDayName = (dateString) => {
    if (!dateString) return "-";
    const days = [
        "Minggu",
        "Senin",
        "Selasa",
        "Rabu",
        "Kamis",
        "Jumat",
        "Sabtu",
    ];
    const date = new Date(dateString);
    return days[date.getDay()];
};

// Helper function to check if file is an image
const isImageFile = (filePath) => {
    if (!filePath) return false;
    const imageExtensions = [
        ".jpg",
        ".jpeg",
        ".png",
        ".gif",
        ".webp",
        ".bmp",
        ".svg",
    ];
    const lowerPath = filePath.toLowerCase();
    return imageExtensions.some((ext) => lowerPath.endsWith(ext));
};

// Helper function to get full URL for document
const getDocumentUrl = (filePath) => {
    if (!filePath) return "";
    // If it's already a full URL, return as is
    if (filePath.startsWith("http://") || filePath.startsWith("https://")) {
        return filePath;
    }
    // If it already starts with '/', return as is (assuming it's already a valid path)
    if (filePath.startsWith("/")) {
        return filePath;
    }
    // If it starts with 'storage/', prepend with '/'
    if (filePath.startsWith("storage/")) {
        return "/" + filePath;
    }
    // Otherwise, prepend with '/storage/'
    return "/storage/" + filePath.replace(/^\/+/, "");
};

// Function to open document preview modal
const openDocumentPreview = (url, title) => {
    documentPreviewUrl.value = url;
    documentPreviewTitle.value = title;
    showDocumentPreview.value = true;
};

// Helper function to get status class
const getStatusClass = (status) => {
    if (!status)
        return "px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700";
    const statusLower = status.toLowerCase();
    if (statusLower.includes("hadir") || statusLower.includes("selesai")) {
        return "px-2 py-1 text-xs rounded-full bg-green-100 text-green-700";
    }
    if (statusLower.includes("terlambat")) {
        return "px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-700";
    }
    if (statusLower.includes("alpha") || statusLower.includes("tidak")) {
        return "px-2 py-1 text-xs rounded-full bg-red-100 text-red-700";
    }
    return "px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700";
};

// Statistic modal state and handlers
const statModal = reactive({
    open: false,
    title: "",
    headers: [],
    rows: [],
    empty: "Tidak ada data",
});
function openStatModal(type) {
    const att = Array.isArray(attendances) ? attendances : [];
    const toLower = (s) => (s || "").toString().toLowerCase();
    const rowsBy = (pred) =>
        att.filter((r) => {
            try {
                return pred(r);
            } catch {
                return false;
            }
        });
    if (type === "present") {
        statModal.title = "Detail Kehadiran";
        statModal.headers = ["Tanggal", "Jam Masuk", "Jam Keluar", "Status"];
        statModal.rows = rowsBy((r) =>
            ["hadir", "on time", "terlambat"].includes(toLower(r["Status"])),
        ).map((r) => [
            r["Tanggal"] || "-",
            r["Jam Masuk"] || "-",
            r["Jam Keluar"] || "-",
            r["Status"] || "-",
        ]);
        statModal.empty = "Tidak ada data kehadiran";
    } else if (type === "late") {
        statModal.title = "Detail Terlambat";
        statModal.headers = ["Tanggal", "Jam Masuk", "Jam Keluar", "Status"];
        statModal.rows = rowsBy(
            (r) => toLower(r["Status"]) === "terlambat",
        ).map((r) => [
            r["Tanggal"] || "-",
            r["Jam Masuk"] || "-",
            r["Jam Keluar"] || "-",
            r["Status"] || "-",
        ]);
        statModal.empty = "Tidak ada data terlambat";
    } else if (type === "alpha") {
        statModal.title = "Detail Alpha";
        statModal.headers = ["Tanggal", "Status"];
        statModal.rows = rowsBy((r) => toLower(r["Status"]) === "alpha").map(
            (r) => [r["Tanggal"] || "-", r["Status"] || "-"],
        );
        statModal.empty = "Tidak ada data alpha";
    } else if (type === "leave" || type === "sick") {
        const lv = Array.isArray(leaves) ? leaves : [];
        statModal.title = type === "leave" ? "Detail Cuti" : "Detail Sakit";
        statModal.headers = ["Tanggal", "Jenis", "Status"];
        statModal.rows = lv.map((r) => [
            r.date || r.start_date || r.created_at || "-",
            r.type || r.category || "-",
            r.status || "-",
        ]);
        statModal.empty =
            type === "leave" ? "Tidak ada data cuti" : "Tidak ada data sakit";
    } else if (type === "receivable") {
        const db = Array.isArray(debts) ? debts : [];
        statModal.title = "Detail Piutang";
        statModal.headers = ["Tanggal", "Deskripsi", "Jumlah"];
        const numberOnly = (val) =>
            Number(val || 0).toLocaleString("id-ID", {
                maximumFractionDigits: 0,
            });
        statModal.rows = db.map((r) => [
            r.date || r.created_at || "-",
            r.description || r.remark || "-",
            numberOnly(r.amount || r.value || 0),
        ]);
        statModal.empty = "Tidak ada data piutang";
    } else {
        statModal.title = "Detail";
        statModal.headers = [];
        statModal.rows = [];
        statModal.empty = "Tidak ada data";
    }
    statModal.open = true;
}
function closeStatModal() {
    statModal.open = false;
}

// small presentational components
const Info = {
    props: { label: String, value: [String, Number, null] },
    template: `<div><label class='block mb-1 text-sm text-gray-600'>{{ label }}</label><div class='p-2 min-h-[40px]  bg-gray-50 text-gray-800'>{{ value ?? '-' }}</div></div>`,
};

const MiniStat = {
    props: { label: String, value: [String, Number], color: String },
    template: `<div class='p-3 bg-gray-50'><div class='mb-1 text-xs text-gray-500'>{{ label }}</div><div class='flex gap-2 items-baseline'><span class='text-xl font-semibold'>{{ value ?? 0 }}</span><span :class='color' class='inline-block w-2 h-2 rounded-full'></span></div></div>`,
};

// Lightweight metric card
const MetricCard = {
    props: {
        title: String,
        value: Number,
        suffix: { type: String, default: "" },
        subtitle: String,
    },
    template: `
      <div class='flex flex-col justify-center p-4 h-full bg-gray-50 rounded-lg border'>
        <div class='mb-1 text-sm text-gray-500'>{{ title }}</div>
        <div class='text-3xl font-semibold text-gray-800'>{{ (value ?? 0).toFixed(1) }}<span class='ml-1 text-base font-medium'>{{ suffix }}</span></div>
        <div class='mt-1 text-xs text-gray-500' v-if='subtitle'>{{ subtitle }}</div>
      </div>`,
};

// Simple SVG semicircle gauge
const GaugeChart = {
    props: { value: { type: Number, default: 0 }, label: String },
    setup(props) {
        const clamped = computed(() =>
            Math.max(0, Math.min(100, props.value || 0)),
        );
        const radius = 50;
        const circumference = Math.PI * radius;
        const dash = computed(() => (clamped.value / 100) * circumference);
        const remainder = computed(() => circumference - dash.value);
        const color = computed(() =>
            clamped.value >= 80
                ? "#10b981"
                : clamped.value >= 60
                  ? "#3b82f6"
                  : clamped.value >= 40
                    ? "#f59e0b"
                    : "#ef4444",
        );
        return { clamped, radius, circumference, dash, remainder, color };
    },
    template: `
      <div class='w-full max-w-[220px]'>
        <svg :width="(radius*2)+20" :height="radius+30" viewBox="0 0 140 90">
          <!-- background arc -->
          <path d="M10 80 A60 60 0 0 1 130 80" fill="none" stroke="#e5e7eb" stroke-width="12" stroke-linecap="round" />
          <!-- value arc -->
          <g transform="translate(70,80)">
            <g :transform="'rotate(' + (-180) + ')'"><!-- rotate to start at left end -->
              <circle :r="60" cx="0" cy="0" fill="transparent" stroke="#e5e7eb" stroke-width="0"/>
              <circle :r="60" cx="0" cy="0" fill="transparent" :stroke="color" stroke-width="12" stroke-linecap="round" :stroke-dasharray="dash + ',' + remainder" />
            </g>
          </g>
          <!-- labels -->
          <text x="70" y="60" text-anchor="middle" font-size="22" font-weight="700" fill="#111827">{{ clamped.toFixed(1) }}%</text>
          <text x="70" y="78" text-anchor="middle" font-size="10" fill="#6b7280">{{ label }}</text>
        </svg>
      </div>`,
};

const SimpleTable = {
    props: { headers: Array, rows: Array, empty: String },
    template: `
      <div class='overflow-auto'>
        <table class='min-w-full text-sm table-fixed'>
          <thead>
            <tr>
              <th v-for='(h,i) in headers' :key="i" class='py-2.5 bg-gray-100 border border-gray-200'>
                <div class='px-3 font-medium text-left text-gray-600'>{{ h }}</div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if='!rows || rows.length===0'>
              <td :colspan='headers.length' class='py-6 text-center text-gray-500 border-b border-gray-200'>{{ empty }}</td>
            </tr>
            <tr v-for='(r,idx) in rows' :key='idx' class='border-b border-gray-200'>
              <td v-for='(h,i) in headers' :key="i" class='py-2.5 border border-gray-200'>
                <div class='px-3 text-gray-700'>
                  <template v-if="h==='Aksi' && r[h]">
                    <a :href="r[h]" target="_blank" class="inline-flex items-center px-3 py-1 text-white bg-blue-500 rounded">Download</a>
                  </template>
                  <template v-else>
                    {{ Array.isArray(r) ? (r[i] ?? '-') : (r[h]?.toString?.() ?? '-') }}
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>`,
};

// Simple pagination controls component
const PaginationControls = {
    props: { page: Number, total: Number, pageSize: Number },
    emits: ["prev", "next"],
    computed: {
        totalPages() {
            return Math.max(
                1,
                Math.ceil((this.total || 0) / (this.pageSize || 10)),
            );
        },
    },
    template: `
    <div class='flex justify-between items-center mt-3 text-sm text-gray-600'>
      <div>Halaman {{ page }} dari {{ totalPages }}</div>
      <div class='flex gap-2'>
        <button type='button' class='px-3 py-1 rounded border' @click="$emit('prev')">Sebelumnya</button>
        <button type='button' class='px-3 py-1 rounded border' @click="$emit('next')">Berikutnya</button>
      </div>
    </div>
  `,
};

const dateFormat = (dateString) => {
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(dateString).toLocaleDateString("id-ID", options);
};

const form = useForm({});
const verifyEmployee = () => {
    form.put(route("employees.updateVerify", props.employee?.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            const first = Object.values(form.errors)[0];
            alert(first || "Terjadi kesalahan. Silakan cek data Anda.");
        },
    });
};

// Photo preview modal state
const showPhotoPreview = ref(false);

// Document preview modal state
const showDocumentPreview = ref(false);
const documentPreviewUrl = ref("");
const documentPreviewTitle = ref("");

// Verify modal state
const showVerify = ref(false);
const verifyForm = ref({
    name: employee?.name || "",
    username: employee?.user?.username || "",
    email: employee?.user?.email || "",
    contact: employee?.contact || "",
    address: employee?.address || "",
});
function openVerifyModal() {
    verifyForm.value = {
        name: employee?.name || "",
        username: employee?.user?.username || "",
        email: employee?.user?.email || "",
        contact: employee?.contact || "",
        address: employee?.address || "",
    };
    showVerify.value = true;
}
function submitVerify() {
    const vf = useForm({ ...verifyForm.value });
    vf.put(route("employees.verifyUpdate", employee?.id), {
        preserveScroll: true,
        onSuccess: () => {
            showVerify.value = false;
            // Refresh the current employee detail so verification badge and fields update
            router.get(
                route("employees.show", props.employee?.id),
                {},
                {
                    replace: true,
                    preserveScroll: true,
                },
            );
        },
        onError: () => {
            const first = Object.values(vf.errors)[0];
            alert(first || "Gagal menyimpan perubahan.");
        },
    });
}

// Unverify modal state
const showUnverify = ref(false);
const isUnverifying = ref(false);
function openUnverifyModal() {
    showUnverify.value = true;
}
function submitUnverify() {
    isUnverifying.value = true;
    const unverifyForm = useForm({
        verification: false,
    });
    unverifyForm.put(route("employees.updateVerify", employee?.id), {
        preserveScroll: true,
        onSuccess: () => {
            showUnverify.value = false;
            isUnverifying.value = false;
            // Refresh the current employee detail so verification badge and fields update
            router.get(
                route("employees.show", props.employee?.id),
                {},
                {
                    replace: true,
                    preserveScroll: true,
                },
            );
        },
        onError: () => {
            isUnverifying.value = false;
            const first = Object.values(unverifyForm.errors)[0];
            alert(first || "Gagal membatalkan verifikasi.");
        },
    });
}

// Add Leave Balance Modal state and functions
const showAddLeaveModal = ref(false);
const isSubmittingLeave = ref(false);
const leaveForm = ref({
    leave_type_id: "",
    year: new Date().getFullYear(),
    total_quota: 0,
});

function closeAddLeaveModal() {
    showAddLeaveModal.value = false;
    leaveForm.value = {
        leave_type_id: "",
        year: new Date().getFullYear(),
        total_quota: 0,
    };
}

async function submitAddLeave() {
    // Validate form
    if (
        !leaveForm.value.leave_type_id ||
        !leaveForm.value.year ||
        leaveForm.value.total_quota < 0
    ) {
        // Use SweetAlert2 for validation errors
        const Swal = (await import("sweetalert2")).default;
        const errors = [];
        if (!leaveForm.value.leave_type_id)
            errors.push("Jenis cuti wajib dipilih");
        if (!leaveForm.value.year) errors.push("Tahun wajib diisi");
        if (leaveForm.value.total_quota < 0)
            errors.push("Jumlah hari tidak boleh negatif");

        Swal.fire({
            title: "Validasi Gagal",
            html: `<ul style="text-align: left; padding-left: 20px;">${errors.map((e) => `<li>${e}</li>`).join("")}</ul>`,
            icon: "error",
            confirmButtonText: "OK",
            customClass: {
                popup: "swal-validation-popup",
                confirmButton: "swal-confirm-button",
            },
        });
        return;
    }

    isSubmittingLeave.value = true;
    const form = useForm({ ...leaveForm.value });

    form.post(route("employees.leave-balances.store", employee?.id), {
        preserveScroll: true,
        onSuccess: async () => {
            isSubmittingLeave.value = false;
            closeAddLeaveModal();

            // Show success message with SweetAlert2
            const Swal = (await import("sweetalert2")).default;
            Swal.fire({
                title: "Berhasil!",
                text: "Hak cuti berhasil ditambahkan",
                icon: "success",
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    popup: "swal-success-popup",
                },
            });

            // Refresh the page to show updated data
            router.get(
                route("employees.show", props.employee?.id),
                {},
                {
                    replace: true,
                    preserveScroll: true,
                },
            );
        },
        onError: async (errors) => {
            isSubmittingLeave.value = false;

            // Show error with SweetAlert2
            const Swal = (await import("sweetalert2")).default;
            const errorMessages = Object.values(errors).flat();

            Swal.fire({
                title: "Gagal Menyimpan",
                html: `<ul style="text-align: left; padding-left: 20px;">${errorMessages.map((e) => `<li>${e}</li>`).join("")}</ul>`,
                icon: "error",
                confirmButtonText: "OK",
                customClass: {
                    popup: "swal-validation-popup",
                    confirmButton: "swal-confirm-button",
                },
            });
        },
    });
}
</script>
