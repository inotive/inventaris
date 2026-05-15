<template>
    <Head title="Absensi Karyawan" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <div
            class="flex flex-col overflow-hidden bg-white border border-gray-200 rounded-lg"
        >
            <div class="px-6 py-3 text-xl font-semibold text-gray-800 border-b">
                Daftar Absensi Karyawan
            </div>
            <!-- Toolbar in card: search + filters + group by -->
            <div
                class="flex flex-col gap-2 px-6 py-3 border-b md:flex-row md:items-center md:justify-between"
            >
                <div class="relative">
                    <input
                        v-model="local.q"
                        type="text"
                        placeholder="Cari..."
                        class="py-2.5 pr-8 pl-3 w-64 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                    />
                    <span
                        class="absolute text-gray-400 -translate-y-1/2 right-2 top-1/2"
                        >🔍</span
                    >
                </div>
                <!-- Filters & Group By -->
                <div class="flex items-center gap-2">
                    <select
                        v-model="local.user_status"
                        class="w-full px-2 text-xs border-gray-300 rounded h-9 sm:text-sm"
                    >
                        <option :value="null">Semua Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                    <div class="flex items-center gap-2">
                        <input
                            type="date"
                            v-model="local.date"
                            placeholder="Tanggal"
                            class="h-10 px-3 text-sm border border-gray-200 rounded-lg"
                        />
                    </div>
                    <div class="relative">
                        <button
                            type="button"
                            @click="toggleGroupMenu"
                            class="h-10 px-3 text-sm text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 whitespace-nowrap"
                        >
                            Kelompokkan berdasarkan:
                            <span class="font-medium">{{ groupLabel }}</span>
                        </button>
                        <div
                            v-if="showGroupMenu"
                            class="absolute right-0 z-10 mt-2 bg-white border rounded-lg shadow-lg w-52"
                        >
                            <ul class="py-1 text-sm">
                                <li>
                                    <button
                                        @click="setGroupBy(null)"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50"
                                    >
                                        Tidak digrup
                                    </button>
                                </li>
                                <li>
                                    <button
                                        @click="setGroupBy('department')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50"
                                    >
                                        Departemen
                                    </button>
                                </li>
                                <li>
                                    <button
                                        @click="setGroupBy('position')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50"
                                    >
                                        Jabatan
                                    </button>
                                </li>
                                <li>
                                    <button
                                        @click="setGroupBy('shift')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50"
                                    >
                                        Shift Kerja
                                    </button>
                                </li>
                                <li>
                                    <button
                                        @click="setGroupBy('status')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50"
                                    >
                                        Status
                                    </button>
                                </li>
                                <li>
                                    <button
                                        @click="setGroupBy('date')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50"
                                    >
                                        Tanggal
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary chips -->
            <div class="px-6 py-3 border-b">
                <div class="grid grid-cols-2 gap-3 md:grid-cols-6">
                    <div class="flex items-center gap-2 p-3 border rounded-lg">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 font-semibold text-indigo-600 bg-indigo-100 rounded-full"
                            >{{ (summary && summary.running) || 0 }}</span
                        >
                        <span class="text-sm text-gray-700">Berjalan</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 border rounded-lg">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 font-semibold rounded-full text-emerald-600 bg-emerald-100"
                            >{{ (summary && summary.complete) || 0 }}</span
                        >
                        <span class="text-sm text-gray-700">Selesai</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 border rounded-lg">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 font-semibold text-orange-600 bg-orange-100 rounded-full"
                            >{{ (summary && summary.sakit) || 0 }}</span
                        >
                        <span class="text-sm text-gray-700">Sakit</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 border rounded-lg">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 font-semibold text-teal-600 bg-teal-100 rounded-full"
                            >{{ (summary && summary.cuti) || 0 }}</span
                        >
                        <span class="text-sm text-gray-700">Cuti</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 border rounded-lg">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 font-semibold text-blue-600 bg-blue-100 rounded-full"
                            >{{ (summary && summary.izin) || 0 }}</span
                        >
                        <span class="text-sm text-gray-700">Izin</span>
                    </div>
                    <div class="flex items-center gap-2 p-3 border rounded-lg">
                        <span
                            class="inline-flex items-center justify-center w-6 h-6 font-semibold text-red-600 bg-red-100 rounded-full"
                            >{{ (summary && summary.absen) || 0 }}</span
                        >
                        <span class="text-sm text-gray-700"
                            >Belum ada rekam jam</span
                        >
                    </div>
                </div>
            </div>

            <!-- Face Mismatch Alert Banner -->
            <div v-if="face_mismatch_count > 0 && canEdit"
                class="flex items-center gap-3 px-6 py-3 border-b bg-amber-50">
                <div class="relative flex-shrink-0">
                    <div class="absolute inset-0 rounded-full bg-amber-400 animate-ping opacity-60"></div>
                    <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-amber-100">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-amber-800">
                        Terdapat <span class="px-1.5 py-0.5 bg-amber-200 rounded font-bold">{{ face_mismatch_count }}</span>
                        karyawan yang perlu diverifikasi dan dikoreksi
                    </p>
                    <p class="text-xs text-amber-600 mt-0.5">
                        Wajah yang terdeteksi tidak sesuai dengan karyawan terdaftar.
                        Klik <strong>Lihat</strong> untuk membuka daftar data koreksi.
                    </p>
                </div>
                <button
                    type="button"
                    class="flex-shrink-0 text-xs font-medium text-amber-700 underline hover:text-amber-900"
                    @click="openFaceMismatchModal"
                >
                    Lihat
                </button>
            </div>

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
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Nama & Jabatan Pekerja
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Cabang
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="font-medium text-left text-gray-600"
                                >
                                    Jadwal
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 w-32 max-w-[140px]"
                            >
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    Jam Masuk
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 w-32 max-w-[140px]"
                            >
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    Jam Keluar
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200">
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    Durasi Kerja
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border-gray-200 border-y"
                            >
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    Status
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border-gray-200 border-y"
                            >
                                <div
                                    class="font-medium text-center text-gray-600"
                                >
                                    Aksi
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th
                                class="px-2 py-2 border-gray-200 bg-gray-50 border-y"
                            ></th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50"
                            >
                                <div
                                    class="[&>div>button]:h-9 [&>div>button]:text-xs [&>div>button]:px-2"
                                >
                                    <Select
                                        v-model="local.employee_id"
                                        :items="employeesWithAll"
                                        label="Semua"
                                    />
                                </div>
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50"
                            >
                                <div
                                    class="[&>div>button]:h-9 [&>div>button]:text-xs [&>div>button]:px-2"
                                >
                                    <Select
                                        v-model="local.branch_id"
                                        :items="branchesWithAll"
                                        label="Semua"
                                    >
                                        <template #item="{ item }">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-gray-900 dark:text-gray-100"
                                                >
                                                    {{ item.name }}
                                                </span>
                                            </div>
                                        </template>
                                    </Select>
                                </div>
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50"
                            >
                                <select
                                    v-model="local.shift_id"
                                    class="w-full px-2 text-xs border-gray-300 rounded h-9 sm:text-sm"
                                >
                                    <option :value="null">Semua</option>
                                    <option
                                        v-for="s in shiftsWithAll.filter(
                                            (sh) => sh.id,
                                        )"
                                        :key="s.id"
                                        :value="s.id"
                                    >
                                        {{
                                            `${s.name} (${formatTime(s.start_time)} - ${formatTime(s.end_time)})`
                                        }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50"
                            >
                                <div class="flex flex-col gap-1">
                                    <input
                                        v-model="local.time_in_from"
                                        type="time"
                                        class="w-full h-8 px-2 text-xs border-gray-300 rounded sm:text-sm"
                                        placeholder="Dari"
                                    />
                                    <span
                                        class="text-[10px] text-center text-gray-400"
                                        >s/d</span
                                    >
                                    <input
                                        v-model="local.time_in_to"
                                        type="time"
                                        class="w-full h-8 px-2 text-xs border-gray-300 rounded sm:text-sm"
                                        placeholder="Sampai"
                                    />
                                </div>
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50"
                            >
                                <div class="flex flex-col gap-1">
                                    <input
                                        v-model="local.time_out_from"
                                        type="time"
                                        class="w-full h-8 px-2 text-xs border-gray-300 rounded sm:text-sm"
                                        placeholder="Dari"
                                    />
                                    <span
                                        class="text-[10px] text-center text-gray-400"
                                        >s/d</span
                                    >
                                    <input
                                        v-model="local.time_out_to"
                                        type="time"
                                        class="w-full h-8 px-2 text-xs border-gray-300 rounded sm:text-sm"
                                        placeholder="Sampai"
                                    />
                                </div>
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50"
                            ></th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50"
                            >
                                <select
                                    v-model="local.status"
                                    class="w-full px-2 text-xs border-gray-300 rounded h-9 sm:text-sm"
                                >
                                    <option :value="null">Semua</option>
                                    <option
                                        v-for="s in status_options"
                                        :key="s"
                                        :value="s"
                                    >
                                        {{ mapStatusDisplay(s) }}
                                    </option>
                                </select>
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 bg-gray-50"
                            >
                                <button
                                    type="button"
                                    @click="resetFilter"
                                    class="w-full px-2 text-xs border border-gray-300 rounded h-9 hover:bg-gray-50 whitespace-nowrap"
                                >
                                    Reset Filter
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Grouped view -->
                        <template v-if="localGroupBy && groupedRows.length">
                            <template
                                v-for="(row, idx) in groupedRows"
                                :key="row.__group ? 'g-' + idx : row.data.id"
                            >
                                <tr
                                    v-if="row.__group"
                                    class="cursor-pointer select-none"
                                    @click="toggleGroupCollapse(row.key)"
                                >
                                    <td
                                        :colspan="9"
                                        class="px-4 py-2 font-semibold text-left text-gray-600 bg-gray-50"
                                    >
                                        <span
                                            class="inline-flex items-center gap-2"
                                        >
                                            <span
                                                class="inline-block"
                                                :class="
                                                    collapsedGroups.has(row.key)
                                                        ? 'rotate-[-90deg] transition-transform'
                                                        : 'rotate-0 transition-transform'
                                                "
                                                >▾</span
                                            >
                                            <span>{{ row.label }}</span>
                                            <span
                                                class="ml-2 text-xs text-gray-500"
                                                >({{ row.count }})</span
                                            >
                                        </span>
                                    </td>
                                </tr>
                                <tr
                                    v-else
                                    class="transition-colors border-b border-gray-200 cursor-pointer hover:bg-gray-50"
                                    @click="
                                        row.data?.id && openShow(row.data.id)
                                    "
                                >
                                    <td class="p-3 text-center">
                                        {{ row.no }}
                                    </td>
                                    <td class="p-3">
                                        <div class="flex items-center gap-3">
                                            <template
                                                v-if="
                                                    row.data.employee
                                                        ?.photo_url &&
                                                    row.data.employee.photo_url.trim() !==
                                                        ''
                                                "
                                            >
                                                <img
                                                    :src="`/storage/${row.data.employee.photo_url}`"
                                                    alt="avatar"
                                                    class="object-cover w-8 h-8 rounded-full"
                                                    @error="
                                                        (e) => {
                                                            e.target.style.display =
                                                                'none';
                                                        }
                                                    "
                                                />
                                            </template>
                                            <template v-else>
                                                <AvatarInitials
                                                    :name="
                                                        row.data.employee.name
                                                    "
                                                    :gender="
                                                        row.data.employee
                                                            ?.gender || ''
                                                    "
                                                    :size="32"
                                                />
                                            </template>
                                            <div class="flex flex-col">
                                                <span
                                                    class="font-medium text-gray-800"
                                                    >{{
                                                        row.data.employee.name
                                                    }}</span
                                                >
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        {{ row.data.branch || "-" }}
                                    </td>
                                    <td class="p-3">
                                        <span
                                            v-if="
                                                row.data.shift &&
                                                (row.data.shift.name ||
                                                    row.data.shift.start_time)
                                            "
                                        >
                                            {{
                                                row.data.shift.name
                                                    ? formatShiftName(
                                                          row.data.shift.name,
                                                      )
                                                    : "Shift"
                                            }}
                                            <span
                                                v-if="
                                                    row.data.shift.start_time ||
                                                    row.data.shift.end_time
                                                "
                                                class="text-xs text-gray-500"
                                            >
                                                {{
                                                    row.data.shift
                                                        ? `${formatTime(row.data.shift.start_time)} - ${formatTime(row.data.shift.end_time)}`
                                                        : "-"
                                                }}
                                            </span>
                                        </span>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <div
                                            class="flex items-center justify-center gap-2"
                                        >
                                            <div
                                                v-if="row.data.foto_masuk"
                                                class="flex-shrink-0"
                                            >
                                                <img
                                                    :src="row.data.foto_masuk"
                                                    alt="Foto Masuk"
                                                    class="object-cover w-10 h-10 rounded cursor-pointer hover:opacity-80"
                                                    @click="
                                                        showImageModal(
                                                            row.data.foto_masuk,
                                                            'Foto Absen Masuk',
                                                        )
                                                    "
                                                />
                                            </div>
                                            <span class="text-sm">{{
                                                row.data.jam_masuk || "-"
                                            }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center">
                                        <div
                                            class="flex items-center justify-center gap-2"
                                        >
                                            <div
                                                v-if="row.data.foto_keluar"
                                                class="flex-shrink-0"
                                            >
                                                <img
                                                    :src="row.data.foto_keluar"
                                                    alt="Foto Keluar"
                                                    class="object-cover w-10 h-10 rounded cursor-pointer hover:opacity-80"
                                                    @click="
                                                        showImageModal(
                                                            row.data
                                                                .foto_keluar,
                                                            'Foto Absen Keluar',
                                                        )
                                                    "
                                                />
                                            </div>
                                            <span class="text-sm">{{
                                                row.data.jam_keluar || "-"
                                            }}</span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center">
                                        {{ row.data.durasi || "-" }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <span
                                            v-if="getStatusDisplay(row.data)"
                                            :class="[
                                                'inline-block px-2 py-1 rounded text-xs font-semibold',
                                                statusClasses[
                                                    getStatusDisplay(row.data)
                                                ] ||
                                                    'bg-gray-100 border border-gray-300 text-gray-700',
                                            ]"
                                        >
                                            {{
                                                mapStatusDisplay(
                                                    getStatusDisplay(row.data),
                                                ) || "-"
                                            }}
                                        </span>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <div class="flex justify-center gap-2">
                                            <button
                                                type="button"
                                                :class="[
                                                    'px-2 py-1 text-xs rounded border',
                                                    !row.data?.id
                                                        ? 'opacity-50 cursor-not-allowed'
                                                        : '',
                                                ]"
                                                :disabled="!row.data?.id"
                                                @click.stop="
                                                    row.data?.id &&
                                                    openShow(row.data.id)
                                                "
                                            >
                                                Detail
                                            </button>
                                            <button
                                                type="button"
                                                class="px-2 py-1 text-xs border rounded"
                                                @click.stop="openEdit(row.data)"
                                            >
                                                Edit
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>
                        <!-- Flat view -->
                        <tr
                            v-else-if="enhancedRows.length"
                            v-for="(row, idx) in enhancedRows"
                            :key="row.id || idx"
                            class="transition-colors border-b border-gray-200 cursor-pointer hover:bg-gray-50"
                            @click="row.id && openShow(row.id)"
                        >
                            <td class="p-3 text-center">
                                {{
                                    ((attendances?.current_page || 1) - 1) *
                                        (attendances?.per_page || 10) +
                                    idx +
                                    1
                                }}
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <template
                                        v-if="
                                            row.employee?.photo_url &&
                                            row.employee.photo_url.trim() !== ''
                                        "
                                    >
                                        <img
                                            :src="`/storage/${row.employee.photo_url}`"
                                            alt="avatar"
                                            class="object-cover w-8 h-8 rounded-full"
                                            @error="
                                                (e) => {
                                                    e.target.style.display =
                                                        'none';
                                                }
                                            "
                                        />
                                    </template>
                                    <template v-else>
                                        <AvatarInitials
                                            :name="row.employee.name"
                                            :gender="row.employee?.gender || ''"
                                            :size="32"
                                        />
                                    </template>
                                    <div class="flex flex-col">
                                        <span
                                            class="font-medium text-gray-800"
                                            >{{ row.employee.name }}</span
                                        >
                                        <span class="text-xs text-gray-500">{{
                                            row.employee.role || "Staff"
                                        }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">{{ row.branch || "-" }}</td>
                            <td class="p-3">
                                <div class="flex flex-col">
                                    <span
                                        v-if="
                                            row.shift &&
                                            (row.shift.name ||
                                                row.shift.start_time)
                                        "
                                        class="font-medium text-gray-800"
                                    >
                                        {{
                                            row.shift.name
                                                ? formatShiftName(
                                                      row.shift.name,
                                                  )
                                                : "Shift"
                                        }}
                                    </span>
                                    <span
                                        v-if="
                                            row.shift &&
                                            (row.shift.start_time ||
                                                row.shift.end_time)
                                        "
                                        class="text-xs text-gray-500"
                                    >
                                        {{
                                            `${formatTime(row.shift.start_time)} - ${formatTime(row.shift.end_time)}`
                                        }}
                                    </span>
                                    <span v-if="!row.shift">-</span>
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <div
                                        v-if="row.foto_masuk"
                                        class="flex-shrink-0"
                                    >
                                        <img
                                            :src="row.foto_masuk"
                                            alt="Foto Masuk"
                                            class="object-cover w-10 h-10 rounded cursor-pointer hover:opacity-80"
                                            @click.stop="
                                                showImageModal(
                                                    row.foto_masuk,
                                                    'Foto Absen Masuk',
                                                )
                                            "
                                        />
                                    </div>
                                    <span class="text-sm">{{
                                        row.jam_masuk || "-"
                                    }}</span>
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <div
                                        v-if="row.foto_keluar"
                                        class="flex-shrink-0"
                                    >
                                        <img
                                            :src="row.foto_keluar"
                                            alt="Foto Keluar"
                                            class="object-cover w-10 h-10 rounded cursor-pointer hover:opacity-80"
                                            @click.stop="
                                                showImageModal(
                                                    row.foto_keluar,
                                                    'Foto Absen Keluar',
                                                )
                                            "
                                        />
                                    </div>
                                    <span class="text-sm">{{
                                        row.jam_keluar || "-"
                                    }}</span>
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                {{ row.durasi_display }}
                            </td>
                            <td class="p-3 text-center">
                                <span
                                    v-if="row.status_display"
                                    :class="[
                                        'inline-block px-2 py-1 rounded text-xs font-semibold',
                                        statusClasses[row.status_display] ||
                                            'bg-gray-100 border border-gray-300 text-gray-700',
                                    ]"
                                >
                                    {{
                                        mapStatusDisplay(
                                            row.status_display_mapped,
                                        ) || "-"
                                    }}
                                </span>
                                <span v-else>-</span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex justify-center gap-2">
                                    <button
                                        v-if="canView"
                                        type="button"
                                        :class="[
                                            'px-2 py-1 text-xs rounded border',
                                            !row.id
                                                ? 'opacity-50 cursor-not-allowed'
                                                : '',
                                        ]"
                                        :disabled="!row.id"
                                        @click.stop="row.id && openShow(row.id)"
                                    >
                                        Detail
                                    </button>
                                    <button
                                        v-if="canEdit"
                                        type="button"
                                        class="px-2 py-1 text-xs border rounded"
                                        @click.stop="openEdit(row)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="canEdit && row.is_face_correct >= 1"
                                        type="button"
                                        class="px-2 py-1 text-xs font-semibold border rounded text-amber-700 border-amber-400 bg-amber-50 hover:bg-amber-100"
                                        :title="row.is_face_correct === 2 ? 'Wajah salah saat masuk & keluar' : 'Wajah salah saat masuk'"
                                        @click.stop="openCorrectEmployee(row)"
                                    >
                                        Koreksi {{ row.is_face_correct === 2 ? '(2x)' : '' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <!-- Empty State -->
                        <tr v-else class="border-b border-gray-200">
                            <td
                                colspan="9"
                                class="py-8 text-center text-gray-500"
                            >
                                <div class="flex flex-col items-center gap-2">
                                    <svg
                                        class="w-12 h-12 text-gray-300"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                    <span class="text-sm font-medium">
                                        Tidak ada data
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination
                v-if="attendances?.data?.length"
                :pagination="attendances"
                @page-changed="changePage"
                @per-page-changed="perPageChanged"
                class="border-t"
            />
        </div>
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
                <div class="flex items-center justify-between w-full">
                    <h3
                        class="text-xl font-semibold text-gray-900 dark:text-gray-100"
                    >
                        Detail
                    </h3>
                </div>
            </template>
            <!-- {{attendance}} -->
            <!-- Content -->
            <div class="flex flex-col gap-6">
                <!-- Employee Info -->
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-normal text-dark">Nama</label>
                        <p class="font-normal text-gray-500">
                            {{ attendance?.employee?.name || "-" }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-normal text-dark">Departemen</label>
                        <p class="font-normal text-gray-500">
                            {{ attendance?.department || "-" }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-normal text-dark">Cabang</label>
                        <p class="font-normal text-gray-500">
                            {{ attendance?.branch?.name || "-" }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-normal text-dark">Jadwal</label>
                        <p class="font-normal text-gray-500">
                            {{ attendance?.shift?.name || "-" }}
                        </p>
                    </div>
                </div>

                <!-- Photos Section -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Foto Masuk -->
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-dark"
                            >Foto Masuk</label
                        >
                        <img
                            v-if="hasPhotoMasuk"
                            :src="attendance.foto_masuk"
                            alt="Foto Masuk"
                            class="object-cover w-full h-48 transition-opacity border border-gray-200 rounded-md shadow-sm cursor-pointer hover:opacity-90"
                            @click="
                                openImagePreview(
                                    attendance.foto_masuk,
                                    'Foto Masuk',
                                )
                            "
                            @error="handleImageError($event, 'masuk')"
                        />
                        <div
                            v-else
                            class="flex flex-col items-center justify-center w-full h-48 bg-gray-100 border border-gray-200 rounded-md"
                        >
                            <svg
                                class="w-12 h-12 mb-2 text-gray-300"
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
                            <span class="text-sm text-gray-400"
                                >Tidak ada foto</span
                            >
                        </div>
                        <p class="text-sm font-medium text-gray-700">
                            Jam: {{ attendance?.jam_masuk || "-" }}
                        </p>
                    </div>

                    <!-- Foto Keluar -->
                    <div class="flex flex-col gap-2">
                        <label class="font-semibold text-dark"
                            >Foto Keluar</label
                        >
                        <img
                            v-if="hasPhotoKeluar"
                            :src="attendance.foto_keluar"
                            alt="Foto Keluar"
                            class="object-cover w-full h-48 transition-opacity border border-gray-200 rounded-md shadow-sm cursor-pointer hover:opacity-90"
                            @click="
                                openImagePreview(
                                    attendance.foto_keluar,
                                    'Foto Keluar',
                                )
                            "
                            @error="handleImageError($event, 'keluar')"
                        />
                        <div
                            v-else
                            class="flex flex-col items-center justify-center w-full h-48 bg-gray-100 border border-gray-200 rounded-md"
                        >
                            <svg
                                class="w-12 h-12 mb-2 text-gray-300"
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
                            <span class="text-sm text-gray-400"
                                >Tidak ada foto</span
                            >
                        </div>
                        <p class="text-sm font-medium text-gray-700">
                            Jam: {{ attendance?.jam_keluar || "-" }}
                        </p>
                    </div>
                </div>

                <!-- Work Duration & Status -->
                <div class="flex gap-4">
                    <div class="flex flex-col flex-1 gap-2">
                        <label class="font-normal text-dark"
                            >Durasi Kerja</label
                        >
                        <p class="font-normal text-gray-500">
                            {{ attendance?.durasi || "-" }}
                        </p>
                    </div>
                    <div class="flex flex-col flex-1 gap-2">
                        <label class="font-normal text-dark">Status</label>
                        <p class="font-normal text-gray-500">
                            {{ attendance?.status || "-" }}
                        </p>
                    </div>
                </div>
            </div>
        </Dialog>

        <!-- Edit Modal -->
        <Dialog
            v-model:visible="showEdit"
            modal
            dismissableMask
            :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
            :style="{ width: '520px', padding: '6px' }"
            :pt="dialogPt"
        >
            <template #header>
                <div class="flex items-center justify-between w-full">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Koreksi Absensi
                    </h3>
                </div>
            </template>
            <div class="flex flex-col gap-4">
                <div>
                    <label class="block mb-1 text-sm text-gray-600"
                        >Jam Masuk</label
                    >
                    <input
                        type="time"
                        v-model="editForm.jam_masuk"
                        class="w-full h-10 px-3 border border-gray-300 rounded"
                    />
                </div>
                <div>
                    <label class="block mb-1 text-sm text-gray-600"
                        >Jam Keluar</label
                    >
                    <input
                        type="time"
                        v-model="editForm.jam_keluar"
                        class="w-full h-10 px-3 border border-gray-300 rounded"
                    />
                </div>
                <div>
                    <label class="block mb-1 text-sm text-gray-600"
                        >Status</label
                    >
                    <select
                        v-model="editForm.status"
                        class="w-full h-10 px-3 border border-gray-300 rounded"
                    >
                        <option v-for="s in status_options" :key="s" :value="s">
                            {{ s }}
                        </option>
                    </select>
                </div>
                <div v-if="editForm.status === 'IZIN'">
                    <label class="block mb-1 text-sm text-gray-600"
                        >Kategori Izin</label
                    >
                    <select
                        v-model="editForm.leave_type_name"
                        class="w-full h-10 px-3 border border-gray-300 rounded"
                    >
                        <option value="">Pilih kategori izin</option>
                        <option
                            v-for="option in specialLeaveTypes"
                            :key="option.id"
                            :value="option.name"
                        >
                            {{ option.name }}
                        </option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button
                        class="h-10 px-3 border rounded"
                        type="button"
                        @click="showEdit = false"
                    >
                        Batal
                    </button>
                    <button
                        class="h-10 px-3 text-white bg-blue-600 rounded"
                        type="button"
                        @click="submitEdit"
                    >
                        Simpan
                    </button>
                </div>
            </div>
        </Dialog>

        <!-- Image Preview Modal -->
        <Dialog
            v-model:visible="showImagePreview"
            modal
            dismissableMask
            :breakpoints="{ '960px': '90vw', '640px': '95vw' }"
            :style="{ width: '90vw', maxWidth: '1200px', padding: '6px' }"
            :pt="dialogPt"
        >
            <template #header>
                <div class="flex items-center justify-between w-full">
                    <h3
                        class="text-xl font-semibold text-gray-900 dark:text-gray-100"
                    >
                        {{ previewImageTitle }}
                    </h3>
                </div>
            </template>
            <div class="flex items-center justify-center w-full">
                <img
                    v-if="previewImageUrl"
                    :src="previewImageUrl"
                    :alt="previewImageTitle"
                    class="max-w-full max-h-[80vh] object-contain rounded-md"
                />
            </div>
        </Dialog>

        <!-- Image Modal for Attendance Photos -->
        <Dialog
            v-model:visible="showImageModalState"
            modal
            :closable="true"
            :draggable="false"
            class="w-11/12 max-w-3xl"
            @hide="closeImageModal"
        >
            <template #header>
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ imageModalTitle }}
                    </h3>
                </div>
            </template>
            <div class="flex items-center justify-center w-full">
                <img
                    v-if="imageModalSrc"
                    :src="imageModalSrc"
                    :alt="imageModalTitle"
                    class="max-w-full max-h-[80vh] object-contain rounded-md"
                />
            </div>
        </Dialog>
    </div>

        <!-- Face Mismatch List Modal -->
        <Dialog
            v-model:visible="showFaceMismatchModal"
            modal
            dismissableMask
            :breakpoints="{ '960px': '90vw', '640px': '95vw' }"
            :style="{ width: '900px', maxWidth: '95vw', padding: '6px' }"
            :pt="dialogPt"
        >
            <template #header>
                <div class="flex items-center justify-between w-full gap-3">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Daftar Data Perlu Koreksi
                    </h3>
                    <span
                        class="inline-flex items-center justify-center h-6 px-2 text-xs font-semibold text-white bg-amber-500 rounded-full"
                    >
                        {{ faceMismatchItems.length }} data
                    </span>
                </div>
            </template>

            <div v-if="!faceMismatchItems.length" class="py-8 text-sm text-center text-gray-500">
                Tidak ada data koreksi.
            </div>

            <div v-else class="overflow-auto border border-gray-200 rounded-md max-h-[60vh]">
                <table class="min-w-full text-sm">
                    <thead class="sticky top-0 bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left border-b">Tanggal</th>
                            <th class="px-3 py-2 text-left border-b">Nama Karyawan</th>
                            <th class="px-3 py-2 text-left border-b">Cabang</th>
                            <th class="px-3 py-2 text-left border-b">Jam Masuk</th>
                            <th class="px-3 py-2 text-left border-b">Jam Keluar</th>
                            <th class="px-3 py-2 text-left border-b">Status</th>
                            <th class="px-3 py-2 text-center border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in faceMismatchItems"
                            :key="item.id"
                            class="border-b last:border-b-0 hover:bg-gray-50"
                        >
                            <td class="px-3 py-2">{{ item.date || "-" }}</td>
                            <td class="px-3 py-2">{{ item.employee?.name || "-" }}</td>
                            <td class="px-3 py-2">{{ item.branch || "-" }}</td>
                            <td class="px-3 py-2">{{ item.jam_masuk || "-" }}</td>
                            <td class="px-3 py-2">{{ item.jam_keluar || "-" }}</td>
                            <td class="px-3 py-2">{{ item.status || "-" }}</td>
                            <td class="px-3 py-2 text-center">
                                <div class="flex justify-center gap-2">
                                    <button
                                        type="button"
                                        class="px-2 py-1 text-xs border rounded"
                                        @click="openShow(item.id)"
                                    >
                                        Detail
                                    </button>
                                    <button
                                        v-if="canEdit"
                                        type="button"
                                        class="px-2 py-1 text-xs font-semibold border rounded text-amber-700 border-amber-400 bg-amber-50 hover:bg-amber-100"
                                        @click="openCorrectFromModal(item)"
                                    >
                                        Koreksi
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Dialog>

        <!-- Correct Employee Modal -->
        <Dialog
            v-model:visible="showCorrectEmployee"
            modal
            dismissableMask
            :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
            :style="{ width: '480px', padding: '6px' }"
            :pt="dialogPt"
        >
            <template #header>
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-100">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900">Koreksi Data Absensi</h3>
                </div>
            </template>
            <div class="flex flex-col gap-4">
                <!-- Current detected employee info -->
                <div class="p-3 rounded-lg bg-red-50 border border-red-200">
                    <p class="text-xs font-medium text-red-500 uppercase tracking-wide mb-1">Terdeteksi sebagai</p>
                    <p class="font-semibold text-red-800">{{ correctForm.currentEmployeeName }}</p>
                    <p class="text-xs text-red-500 mt-0.5">Tanggal: {{ correctForm.date }}</p>
                </div>

                <!-- Arrow indicator -->
                <div class="flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <!-- Select correct employee -->
                <div>
                    <label class="block mb-1.5 text-sm font-medium text-gray-700">
                        Seharusnya data absensi ini milik karyawan:
                    </label>
                    <select
                        v-model="correctForm.newEmployeeId"
                        class="w-full h-10 px-3 border border-gray-300 rounded focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
                    >
                        <option :value="null" disabled>-- Pilih karyawan --</option>
                        <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                            {{ emp.name }}
                        </option>
                    </select>
                </div>

                <div class="p-3 rounded-lg bg-blue-50 border border-blue-100 text-xs text-blue-700">
                    <strong>Catatan:</strong> Sistem akan memindahkan data absensi ini ke karyawan yang dipilih dan mengubah status verifikasi wajah menjadi terverifikasi.
                </div>

                <div class="flex justify-end gap-2">
                    <button
                        class="h-10 px-4 border rounded text-sm"
                        type="button"
                        @click="showCorrectEmployee = false"
                    >
                        Batal
                    </button>
                    <button
                        class="h-10 px-4 text-white bg-amber-500 hover:bg-amber-600 rounded text-sm font-medium disabled:opacity-50"
                        type="button"
                        :disabled="!correctForm.newEmployeeId || correctSubmitting"
                        @click="submitCorrectEmployee"
                    >
                        {{ correctSubmitting ? 'Menyimpan...' : 'Simpan Koreksi' }}
                    </button>
                </div>
            </div>
        </Dialog>
</template>

<script setup>
import AvatarInitials from "@/Components/common/AvatarInitials.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/Pagination.vue";
import Select from "@/Components/form/SelectPemakaian.vue";
import { Dialog } from "primevue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import { useAuth } from "@/Composables/useAuth";

const { can, user } = useAuth();

const canEdit = computed(() => can("presences.edit"));
const canView = computed(() => can("presences.view"));

const breadcrumbs = [{ label: "Absensi" }, { label: "Absensi Karyawan" }];

defineOptions({ layout: AppLayout });

const props = defineProps({
    attendances: Object,
    filters: Object,
    branches: Array,
    departments: Array,
    employees: Array,
    shifts: Array,
    specialLeaveTypes: { type: Array, default: () => [] },
    status_options: Array,
    groupBy: { type: String, default: null },
    summary: Object,
    face_mismatch_count: { type: Number, default: 0 },
    face_mismatch_items: { type: Array, default: () => [] },
});

const showFaceMismatchModal = ref(false);
const faceMismatchItems = computed(() => props.face_mismatch_items || []);

const today = new Date();
const pad2 = (n) => (n < 10 ? `0${n}` : `${n}`);
const todayStr = `${today.getFullYear()}-${pad2(today.getMonth() + 1)}-${pad2(
    today.getDate(),
)}`;

const getInitialUserStatus = () => {
    // If filter is provided from props (e.g., from URL), use it
    if (props.filters?.user_status) {
        return props.filters.user_status;
    }
    // If user status is "active", default filter to "active"
    if (user.value?.status === "active") {
        return "active";
    }
    // Otherwise, no filter
    return null;
};

const local = ref({
    q: props.filters?.q || "",
    employee_id: props.filters?.employee_id ?? null,
    branch_id: props.filters?.branch_id ?? null,
    department_id: props.filters?.department_id ?? null,
    shift_id: props.filters?.shift_id ?? null,
    status: props.filters?.status ?? null,
    user_status: getInitialUserStatus(),
    // date filter (single date)
    date: props.filters?.date_from || props.filters?.date || todayStr,
    time_in_from: props.filters?.time_in_from || "",
    time_in_to: props.filters?.time_in_to || "",
    time_out_from: props.filters?.time_out_from || "",
    time_out_to: props.filters?.time_out_to || "",
});

const statusClasses = {
    RUNNING: "bg-indigo-100 border border-indigo-400 text-indigo-800",
    COMPLETE: "bg-green-100 border border-green-400 text-green-800",
    SAKIT: "bg-orange-100 border border-orange-400 text-orange-800",
    CUTI: "bg-teal-100 border border-teal-400 text-teal-800",
    IZIN: "bg-blue-100 border border-blue-400 text-blue-800",
    ABSEN: "bg-red-100 border border-red-400 text-red-800",
    default: "bg-gray-100 border border-gray-300 text-gray-700",
};

// Format shift name so it doesn't show trailing year range (e.g. "(2025 - 2025)")
// but keeps code + shift name, misalnya: "OFFC-S2-H7 (Staf Office Libur Minggu)".
function formatShiftName(name) {
    if (!name) return "";
    const str = name.toString();

    // Hapus pola "(YYYY - YYYY)" di bagian akhir string jika ada
    const yearRangeAtEnd = /\(\s*\d{4}\s*-\s*\d{4}\s*\)\s*$/;
    if (yearRangeAtEnd.test(str)) {
        return str.replace(yearRangeAtEnd, "").trim();
    }

    return str;
}

// Menambahkan opsi "Semua" ke dalam daftar branches
const branchesWithAll = computed(() => {
    const allOption = { id: null, name: "Semua" };
    return [allOption, ...(props.branches || [])];
});

// Menambahkan opsi "Semua" ke dalam daftar departments
const departmentsWithAll = computed(() => {
    const allOption = { id: null, name: "Semua" };
    return [allOption, ...(props.departments || [])];
});

// Menambahkan opsi "Semua" ke dalam daftar employees
const employeesWithAll = computed(() => {
    const allOption = { id: null, name: "Semua" };
    return [allOption, ...(props.employees || [])];
});

// Menambahkan opsi "Semua" ke dalam daftar shifts (kategori shift)
const shiftsWithAll = computed(() => {
    const allOption = {
        id: null,
        name: "Semua",
        start_time: null,
        end_time: null,
    };
    return [allOption, ...(props.shifts || [])];
});

let t = null;
watch(
    () => ({ ...local.value }),
    () => {
        clearTimeout(t);
        t = setTimeout(() => fetchList(), 350);
    },
    { deep: true },
);

const showGroupMenu = ref(false);
function toggleGroupMenu() {
    showGroupMenu.value = !showGroupMenu.value;
}
const localGroupBy = ref(props.groupBy || null);

const groupLabel = computed(() => {
    const map = {
        department: "Departemen",
        position: "Jabatan",
        shift: "Shift Kerja",
        status: "Status",
        date: "Tanggal",
    };
    return localGroupBy.value
        ? map[localGroupBy.value] || "Tidak digrup"
        : "Tidak digrup";
});
function setGroupBy(v) {
    localGroupBy.value = v || null;
    showGroupMenu.value = false;
    fetchList();
}

// Helpers to compute duration HH:MM -> "X Jam Y Mnt"
function parseTime(t) {
    if (!t) return null;
    const [h, m] = t.split(":").map(Number);
    if (Number.isNaN(h) || Number.isNaN(m)) return null;
    return h * 60 + m;
}
function formatDuration(mins) {
    if (mins == null) return "-";
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    if (h <= 0) return `${m} Mnt`;
    return `${h} Jam ${m} Mnt`;
}

// Function to map status display
function mapStatusDisplay(status) {
    if (status === "ABSEN") {
        return "Belum ada rekam jam";
    }
    return status;
}

function formatTime(time) {
    if (!time) return "-";
    const s = time.toString();
    const isoMatch = s.match(/T(\d{2}):(\d{2})/);
    if (isoMatch) return `${isoMatch[1]}.${isoMatch[2]}`;
    const hhmmMatch = s.match(/^(\d{2}):(\d{2})/);
    if (hhmmMatch) return `${hhmmMatch[1]}.${hhmmMatch[2]}`;
    return (s.length >= 5 ? s.slice(0, 5) : s).replace(":", ".");
}

// Helper function to get status display for grouped rows
function getStatusDisplay(row) {
    // If status exists, use it
    if (row.status) {
        return row.status;
    }
    // If no status but has jam_masuk and no jam_keluar, it's RUNNING
    if (row.jam_masuk && !row.jam_keluar) {
        return "RUNNING";
    }
    // If no status and no jam_masuk/jam_keluar, it's ABSEN
    if (!row.jam_masuk && !row.jam_keluar) {
        return "ABSEN";
    }
    return null;
}

// Build enhanced rows used by table
const enhancedRows = computed(() => {
    const rows = props.attendances?.data || [];
    return rows.map((row) => {
        const inMin = parseTime(row.jam_masuk);
        const outMin = parseTime(row.jam_keluar);
        const running = !!row.jam_masuk && !row.jam_keluar;
        const durasiMins =
            inMin != null && outMin != null
                ? Math.max(0, outMin - inMin)
                : null;
        const durasi_display = running
            ? "Berjalan…"
            : durasiMins != null
              ? formatDuration(durasiMins)
              : row.durasi || "-";
        let status_display = row.status || "";
        if (running) status_display = "RUNNING";
        if (!row.jam_masuk && !row.jam_keluar && !status_display)
            status_display = "ABSEN";

        // Map status display untuk UI
        const status_display_mapped = mapStatusDisplay(status_display);

        return {
            ...row,
            durasi_display,
            status_display,
            status_display_mapped,
        };
    });
});

// Summary chips - use from props if available (all pages), otherwise calculate from current page
const summary = computed(() => {
    // If summary is provided from backend (calculated from all pages), use it
    if (props.summary) {
        return props.summary;
    }

    // Otherwise, calculate from current page only (fallback)
    const s = {
        running: 0,
        complete: 0,
        sakit: 0,
        cuti: 0,
        izin: 0,
        absen: 0,
    };
    for (const r of enhancedRows.value) {
        switch (r.status_display) {
            case "RUNNING":
                s.running++;
                break;
            case "COMPLETE":
                s.complete++;
                break;
            case "SAKIT":
                s.sakit++;
                break;
            case "CUTI":
                s.cuti++;
                break;
            case "IZIN":
                s.izin++;
                break;
            case "ABSEN":
                s.absen++;
                break;
            default:
                break;
        }
    }
    return s;
});

function fetchList() {
    router.get(
        route("presences.index"),
        { ...local.value, groupBy: localGroupBy.value },
        { preserveScroll: true, preserveState: true, replace: true },
    );
}

function changePage(page) {
    router.get(
        route("presences.index"),
        {
            ...local.value,
            groupBy: localGroupBy.value,
            page: page,
        },
        { preserveScroll: true, preserveState: true, replace: true },
    );
}

function perPageChanged(perPage) {
    router.get(
        route("presences.index"),
        {
            ...local.value,
            groupBy: localGroupBy.value,
            per_page: perPage,
            page: 1, // Reset to first page when changing per page
        },
        { preserveScroll: true, preserveState: true, replace: true },
    );
}

function resetFilter() {
    local.value = {
        q: "",
        employee_id: null,
        department_id: null,
        shift_id: null,
        status: null,
        user_status: getInitialUserStatus(),
        date: todayStr,
        time_in_from: "",
        time_in_to: "",
        time_out_from: "",
        time_out_to: "",
    };
    localGroupBy.value = null;
    fetchList();
}

// Grouped rendering
const collapsedGroups = ref(new Set());
function toggleGroupCollapse(key) {
    const s = new Set(collapsedGroups.value);
    if (s.has(key)) s.delete(key);
    else s.add(key);
    collapsedGroups.value = s;
}

const groupedRows = computed(() => {
    if (!localGroupBy.value) return [];
    const rows = props.attendances?.data || [];
    const baseNo =
        ((props.attendances?.current_page || 1) - 1) *
        (props.attendances?.per_page || rows.length);
    const result = [];
    const groups = {};
    const keyFor = (r) => {
        if (localGroupBy.value === "department") return r.department || "-";
        if (localGroupBy.value === "position") return r.position || "-";
        if (localGroupBy.value === "shift") {
            // Handle shift grouping - show shift name or "-" if no shift
            if (r.shift && r.shift.name) return r.shift.name;
            return "-";
        }
        if (localGroupBy.value === "status") {
            // If status is null/empty and no jam_masuk/jam_keluar, treat as ABSEN
            if (!r.status && !r.jam_masuk && !r.jam_keluar) {
                return "ABSEN";
            }
            // If status is null but has jam_masuk (running), treat as RUNNING
            if (!r.status && r.jam_masuk && !r.jam_keluar) {
                return "RUNNING";
            }
            return r.status || "-";
        }
        if (localGroupBy.value === "date") return r.date || "-";
        return "-";
    };
    rows.forEach((r, idx) => {
        const k = keyFor(r);
        if (!groups[k]) groups[k] = [];
        groups[k].push({ data: r, no: baseNo + idx + 1 });
    });
    Object.entries(groups).forEach(([k, arr]) => {
        result.push({ __group: true, key: k, label: k, count: arr.length });
        if (!collapsedGroups.value.has(k)) result.push(...arr);
    });
    return result;
});

const page = usePage();
const showModal = ref(false);
const showEdit = ref(false);
// When true, do not auto-open the Detail modal on attendance prop update
const suppressDetail = ref(false);

// Image preview state
const showImagePreview = ref(false);
const previewImageUrl = ref("");
const previewImageTitle = ref("");

// Track image load errors
const imageLoadErrors = ref({
    masuk: false,
    keluar: false,
});

// Selected attendance for modal (must be declared before computed properties that use it)
const selectedAttendance = ref(null);

const attendance = computed(
    () => selectedAttendance.value || page.props.attendance || null,
);
const employee = computed(() => attendance.value?.employee ?? null);
const shift = computed(() => attendance.value?.shift ?? null);

// Helper to check if photo exists (handles null, undefined, empty string, and load errors)
const hasPhotoMasuk = computed(() => {
    if (imageLoadErrors.value.masuk) return false;
    const foto = attendance.value?.foto_masuk;
    return foto && foto.trim() !== "";
});

const hasPhotoKeluar = computed(() => {
    if (imageLoadErrors.value.keluar) return false;
    const foto = attendance.value?.foto_keluar;
    return foto && foto.trim() !== "";
});

// Reset image errors when attendance changes
watch(
    () => attendance.value?.id,
    () => {
        imageLoadErrors.value = { masuk: false, keluar: false };
    },
);

function openShow(attId) {
    showFaceMismatchModal.value = false;

    // Find attendance data from current list (no server request)
    let foundAttendance = null;

    // Check in grouped rows
    if (localGroupBy.value && groupedRows.value.length) {
        for (const row of groupedRows.value) {
            if (!row.__group && row.data?.id === attId) {
                foundAttendance = row.data;
                break;
            }
        }
    }

    // Check in flat rows
    if (!foundAttendance && enhancedRows.value.length) {
        foundAttendance = enhancedRows.value.find((r) => r.id === attId);
    }

    router.get(
        route("presences.show", { id: attId }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            only: ["attendance"],
            onSuccess: (page) => {
                selectedAttendance.value = page.props.attendance;
                showModal.value = true;
            },
        },
    );
}
watch(
    () => page.props.attendance,
    (v) => {
        if (suppressDetail.value) {
            // We fetched attendance for Edit; do not show Detail modal
            showModal.value = false;
            suppressDetail.value = false;
            return;
        }
        showModal.value = !!v;
    },
);

function closeModal() {
    showModal.value = false;
    selectedAttendance.value = null;
}

function openImagePreview(imageUrl, title) {
    if (!imageUrl || imageUrl.trim() === "") return;
    previewImageUrl.value = imageUrl;
    previewImageTitle.value = title || "Preview Gambar";
    showImagePreview.value = true;
}

function handleImageError(event, type) {
    // Mark the image as failed to load
    if (type === "masuk") {
        imageLoadErrors.value.masuk = true;
    } else if (type === "keluar") {
        imageLoadErrors.value.keluar = true;
    }
    // Prevent the broken image icon from showing
    event.target.style.display = "none";
}

const dialogPt = {
    root: { class: "rounded-2xl" },
    header: { class: "px-6 pt-6 pb-2" },
    content: { class: "px-6 pb-2" },
    footer: { class: "px-6 pb-6" },
};

// Edit modal state
const editForm = ref({
    id: null,
    employee_id: null,
    date: null,
    jam_masuk: "",
    jam_keluar: "",
    status: "",
    leave_type_name: "",
});

function openEdit(rowData) {
    // Format time from HH:mm:ss to HH:mm for input type="time"
    const formatTimeForInput = (time) => {
        if (!time) return "";
        // Extract HH:mm from HH:mm:ss
        if (time.match(/^\d{2}:\d{2}:\d{2}$/)) {
            return time.substring(0, 5);
        }
        // If already in HH:mm format, return as is
        if (time.match(/^\d{2}:\d{2}$/)) {
            return time;
        }
        return time;
    };

    // If row has ID, fetch full data from server
    if (rowData?.id) {
        suppressDetail.value = true;
        router.get(
            route("presences.show", { id: rowData.id }),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                only: ["attendance"],
                onSuccess: () => {
                    const a = page.props.attendance;
                    editForm.value = {
                        id: a?.id || rowData.id,
                        employee_id: a?.employee?.id || rowData.employee?.id,
                        date: a?.date || rowData.date || local.value.date,
                        jam_masuk: formatTimeForInput(a?.jam_masuk) || "",
                        jam_keluar: formatTimeForInput(a?.jam_keluar) || "",
                        status: a?.status || rowData?.status || "",
                        leave_type_name: a?.keterangan || "",
                    };
                    showEdit.value = true;
                },
                onError: () => {
                    suppressDetail.value = false;
                    alert("Gagal memuat data absensi. Silakan coba lagi.");
                },
            },
        );
    } else {
        // No ID means new attendance - use row data directly
        editForm.value = {
            id: null,
            employee_id: rowData?.employee?.id,
            date: rowData?.date || local.value.date,
            jam_masuk: formatTimeForInput(rowData?.jam_masuk) || "",
            jam_keluar: formatTimeForInput(rowData?.jam_keluar) || "",
            status: rowData?.status || "",
            leave_type_name: rowData?.keterangan || "",
        };
        showEdit.value = true;
    }
}

watch(
    () => editForm.value.status,
    (status) => {
        if (status !== "IZIN") {
            editForm.value.leave_type_name = "";
        }
    },
);

function submitEdit() {
    if (!editForm.value.employee_id || !editForm.value.date) {
        alert("Data karyawan atau tanggal tidak valid. Silakan coba lagi.");
        return;
    }

    if (editForm.value.status === "IZIN" && !editForm.value.leave_type_name) {
        alert("Pilih kategori izin terlebih dahulu.");
        return;
    }

    // Format time values properly
    const formatTime = (time) => {
        if (!time) return null;
        // If time is already in HH:mm format, add :00 for seconds
        if (time.match(/^\d{2}:\d{2}$/)) {
            return time + ":00";
        }
        // If already in HH:mm:ss format, return as is
        if (time.match(/^\d{2}:\d{2}:\d{2}$/)) {
            return time;
        }
        return null;
    };

    const payload = {
        employee_id: editForm.value.employee_id,
        tanggal: editForm.value.date,
        jam_masuk: formatTime(editForm.value.jam_masuk),
        jam_keluar: formatTime(editForm.value.jam_keluar),
        status: editForm.value.status || null,
        keterangan:
            editForm.value.status === "IZIN"
                ? editForm.value.leave_type_name
                : null,
    };

    // If has ID, update existing attendance
    if (editForm.value.id) {
        router.put(
            route("presences.update", { id: editForm.value.id }),
            payload,
            {
                preserveScroll: true,
                preserveState: false,
                onSuccess: () => {
                    showEdit.value = false;
                    editForm.value = {
                        id: null,
                        employee_id: null,
                        date: null,
                        jam_masuk: "",
                        jam_keluar: "",
                        status: "",
                        leave_type_name: "",
                    };
                    fetchList();
                },
                onError: (errors) => {
                    console.error("Error updating attendance:", errors);
                    alert("Gagal mengupdate absensi. Silakan coba lagi.");
                },
            },
        );
    } else {
        // No ID, create new attendance
        router.post(route("presences.store"), payload, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                showEdit.value = false;
                editForm.value = {
                    id: null,
                    employee_id: null,
                    date: null,
                    jam_masuk: "",
                    jam_keluar: "",
                    status: "",
                    leave_type_name: "",
                };
                fetchList();
            },
            onError: (errors) => {
                console.error("Error creating attendance:", errors);
                alert("Gagal membuat absensi. Silakan coba lagi.");
            },
        });
    }
}

// Image modal state and functions
const showImageModalState = ref(false);
const imageModalSrc = ref("");
const imageModalTitle = ref("");

// Correct Employee modal state
const showCorrectEmployee = ref(false);
const correctSubmitting = ref(false);
const correctForm = ref({
    attendanceId: null,
    currentEmployeeName: '',
    date: '',
    newEmployeeId: null,
});

function openCorrectEmployee(row) {
    correctForm.value = {
        attendanceId: row.id,
        currentEmployeeName: row.employee?.name || '-',
        date: row.date || local.value.date,
        newEmployeeId: null,
    };
    showCorrectEmployee.value = true;
}

function submitCorrectEmployee() {
    if (!correctForm.value.attendanceId || !correctForm.value.newEmployeeId) return;
    correctSubmitting.value = true;
    router.patch(
        route('presences.correct-employee', { id: correctForm.value.attendanceId }),
        { employee_id: correctForm.value.newEmployeeId },
        {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                showCorrectEmployee.value = false;
                correctSubmitting.value = false;
                correctForm.value = { attendanceId: null, currentEmployeeName: '', date: '', newEmployeeId: null };
                fetchList();
            },
            onError: (errors) => {
                correctSubmitting.value = false;
                const msg = errors?.employee_id || 'Gagal menyimpan koreksi. Silakan coba lagi.';
                alert(msg);
            },
        },
    );
}

function openFaceMismatchModal() {
    showFaceMismatchModal.value = true;
}

function openCorrectFromModal(item) {
    showFaceMismatchModal.value = false;
    openCorrectEmployee(item);
}

function showImageModal(src, title) {
    imageModalSrc.value = src;
    imageModalTitle.value = title;
    showImageModalState.value = true;
}

function closeImageModal() {
    showImageModalState.value = false;
    imageModalSrc.value = "";
    imageModalTitle.value = "";
}
</script>
