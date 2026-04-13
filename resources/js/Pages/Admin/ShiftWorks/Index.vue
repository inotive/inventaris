<template>
    <div class="">
        <Head title="Shift Kerja" />
        <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
                <div class="flex items-center justify-between h-10">
                <Breadcrumb :items="breadcrumbs" />
                <div class="flex gap-2">
                    <!-- Bulk Edit Shift Button -->
                    <button
                        v-if="can('work_shifts.edit') && selectedItems.length > 0"
                        type="button"
                        class="px-3 py-2 text-sm text-white bg-amber-500 rounded hover:bg-amber-600"
                        @click="openBulkEditModal"
                    >
                        Ubah Shift ({{ selectedItems.length }})
                    </button>
                    <!-- Delete Multiple Button -->
                    <button
                        v-if="can('work_shifts.delete') && selectedItems.length > 0"
                        type="button"
                        class="px-3 py-2 text-sm text-white bg-red-600 rounded hover:bg-red-700"
                        @click="deleteMultiple"
                    >
                        Hapus ({{ selectedItems.length }})
                    </button>
                    <!-- Hanya tampil jika punya izin create -->
                    <button
                        v-if="can('work_shifts.create')"
                        type="button"
                        class="px-3 py-2 text-sm text-white bg-blue-600 rounded"
                        @click="showCreate = true"
                    >
                        Tambah Shift Kerja
                    </button>
                    <Link
                        v-if="false"
                        :href="route('shifts.index')"
                        as="button"
                        type="button"
                        class="px-3 py-2 text-sm border rounded"
                    >
                        Kelola Shift Kerja
                    </Link>
                </div>
            </div>

            <div
                class="flex flex-col overflow-hidden bg-white border border-gray-200 rounded-lg"
            >
                <div
                    class="flex flex-col gap-2 px-6 py-3 border-b sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="text-xl font-semibold text-gray-800">
                        Daftar Shift Kerja Karyawan
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input
                                v-model="localQ"
                                type="text"
                                placeholder="Cari..."
                                class="py-2.5 pr-8 pl-3 w-64 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                            />
                            <MagnifyingGlassIcon
                                class="absolute w-5 h-5 text-gray-400 -translate-y-1/2 right-2 top-1/2"
                            />
                        </div>
                        <!-- Date Range Filter -->
                        <div class="flex gap-2 items-center">
                            <input
                                type="date"
                                v-model="filtersLocal.date_from"
                                placeholder="Dari"
                                class="px-3 h-10 text-sm rounded-lg border border-gray-200"
                            />
                            <span class="text-sm text-gray-500">s/d</span>
                            <input
                                type="date"
                                v-model="filtersLocal.date_to"
                                placeholder="Sampai"
                                class="px-3 h-10 text-sm rounded-lg border border-gray-200"
                            />
                        </div>
                        <!-- Group By -->
                        <div class="relative">
                            <button
                                type="button"
                                @click="toggleGroupMenu"
                                class="h-10 px-3 text-sm border border-gray-200 rounded-lg hover:bg-gray-50"
                            >
                                Group by:
                                <span class="font-medium">{{
                                    groupLabel
                                }}</span>
                            </button>
                            <div
                                v-if="showGroupMenu"
                                class="absolute right-0 z-10 mt-2 bg-white border rounded-lg shadow w-52"
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
                                            @click="setGroupBy('shift')"
                                            class="w-full px-3 py-2 text-left hover:bg-gray-50"
                                        >
                                            Kategori Shift
                                        </button>
                                    </li>
                                    <li>
                                        <button
                                            @click="setGroupBy('work_date')"
                                            class="w-full px-3 py-2 text-left hover:bg-gray-50"
                                        >
                                            Tanggal
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- Month/Year Filters -->
                        <!-- <select
                            v-model.number="selectedMonth"
                            @change="onMonthYearChange"
                            class="h-10 px-3 text-sm border border-gray-200 rounded-lg"
                        >
                            <option
                                v-for="m in monthOptions"
                                :key="m.value"
                                :value="m.value"
                            >
                                {{ m.label }}
                            </option>
                        </select>
                        <select
                            v-model.number="selectedYear"
                            @change="onMonthYearChange"
                            class="h-10 px-3 text-sm border border-gray-200 rounded-lg"
                        >
                            <option
                                v-for="y in yearOptions"
                                :key="y"
                                :value="y"
                            >
                                {{ y }}
                            </option>
                        </select> -->
                    </div>
                    <!-- Toggle Tambah Serentak disembunyikan -->
                    <div class="flex items-center gap-2" v-if="false">
                        <button
                            type="button"
                            @click="isConfig = !isConfig"
                            :class="
                                isConfig
                                    ? 'bg-amber-500 text-white'
                                    : 'bg-white text-gray-700 border border-gray-200'
                            "
                            class="h-10 px-3 text-sm rounded-lg"
                        >
                            {{ isConfig ? "Selesai" : "Tambah Serentak" }}
                        </button>
                    </div>
                </div>

                <div class="overflow-auto" data-simplebar>
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr>
                                <th class="p-3 bg-gray-100 border-gray-200 border-y">
                                    <div class="flex items-center justify-center">
                                        <input
                                            type="checkbox"
                                            :checked="isAllSelected"
                                            @change="toggleSelectAll"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                    </div>
                                </th>
                                <th class="p-3 bg-gray-100 border-gray-200 border-y">
                                    <div class="font-medium text-center text-gray-600">No</div>
                                </th>
                                <th class="p-3 bg-gray-100 border border-gray-200">
                                    <div class="font-medium text-left text-gray-600">Nama & Jabatan Pekerja</div>
                                </th>
                                <th class="p-3 bg-gray-100 border border-gray-200">
                                    <div class="font-medium text-left text-gray-600">Tanggal</div>
                                </th>
                                <th class="p-3 bg-gray-100 border border-gray-200">
                                    <div class="font-medium text-left text-gray-600">Kategori Shift</div>
                                </th>
                                <th class="p-3 bg-gray-100 border border-gray-200">
                                    <div class="font-medium text-left text-gray-600">Absensi Aktual</div>
                                </th>
                                <th class="p-3 bg-gray-100 border-gray-200 border-y">
                                    <div class="font-medium text-center text-gray-600">Waktu ditambahkan</div>
                                </th>
                                <th class="p-3 bg-gray-100 border border-gray-200">
                                    <div class="font-medium text-center text-gray-600">Aksi</div>
                                </th>
                            </tr>
                            <!-- Inline filters -->
                            <tr>
                                <th class="px-2 py-2 border border-gray-200 bg-gray-50"></th>
                                <th class="px-2 py-2 border border-gray-200 bg-gray-50"></th>
                                <th class="px-2 py-2 border-gray-200 bg-gray-50 border-y">
                                    <select
                                        v-model="filtersLocal.employee_id"
                                        class="w-full px-2 text-xs border-gray-300 rounded h-9 sm:text-sm"
                                    >
                                        <option :value="null">Semua</option>
                                        <option v-for="e in employees" :key="e.id" :value="e.id">
                                            {{ e.name }}
                                        </option>
                                    </select>
                                </th>
                                <th class="px-2 py-2 border border-gray-200 bg-gray-50"></th>
                                <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                    <select
                                        v-model="filtersLocal.shift_id"
                                        class="w-full px-2 text-xs border-gray-300 rounded h-9 sm:text-sm"
                                    >
                                        <option :value="null">Semua</option>
                                        <option v-for="s in shifts" :key="s.id" :value="s.id">
                                            {{ `${s.name} (${formatTime(s.start_time)} - ${formatTime(s.end_time)})` }}
                                        </option>
                                    </select>
                                </th>
                                <th class="px-2 py-2 border-gray-200 bg-gray-50 border-y"></th>
                                <th class="px-2 py-2 border-gray-200 bg-gray-50 border-y"></th>
                                <th class="px-2 py-2 border-gray-200 bg-gray-50 border-y"></th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Grouped rendering -->
                            <template v-if="localGroupBy && groupedRows.length">
                                <template v-for="(row, idx) in groupedRows">
                                    <tr
                                        v-if="row.__group"
                                        :key="'g-' + idx"
                                        class="cursor-pointer select-none"
                                        @click="toggleGroupCollapse(row.key)"
                                    >
                                        <td :colspan="8" class="px-4 py-2 font-semibold text-left text-gray-600 bg-gray-50">
                                            <span class="inline-flex items-center gap-2">
                                                <span
                                                    class="inline-block"
                                                    :class="collapsedGroups.has(row.key) ? 'rotate-[-90deg] transition-transform' : 'rotate-0 transition-transform'"
                                                >▾</span>
                                                <span>{{ row.label }}</span>
                                                <span class="ml-2 text-xs text-gray-500">({{ row.count }})</span>
                                            </span>
                                        </td>
                                    </tr>

                                    <tr v-else :key="row.data.id" class="border-b border-gray-200 cursor-pointer hover:bg-gray-50" @click="toggleSelect(row.data.id)">
                                        <td class="p-3" @click.stop>
                                            <div class="flex items-center justify-center">
                                                <input
                                                    type="checkbox"
                                                    :checked="selectedItems.includes(row.data.id)"
                                                    @change="toggleSelect(row.data.id)"
                                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                />
                                            </div>
                                        </td>
                                        <td class="p-3 text-center">{{ row.no }}</td>
                                        <td class="p-3">
                                            <div class="flex items-center gap-3">
                                                <template v-if="row.data.employee?.photo_url">
                                                    <img
                                                        :src="
                                                            row.data.employee.photo_url
                                                                ? row.data.employee.photo_url.startsWith('http')
                                                                    ? row.data.employee.photo_url
                                                                    : `/storage/${row.data.employee.photo_url}`
                                                                : ''
                                                        "
                                                        alt="avatar"
                                                        class="object-cover w-8 h-8 rounded-full"
                                                    />
                                                </template>
                                                <template v-else>
                                                    <AvatarInitials
                                                        :name="row.data.employee.name"
                                                        :gender="row.data.employee?.gender || ''"
                                                        :size="32"
                                                        :color-class="colorClass(row.data.employee?.name)"
                                                    />
                                                </template>
                                                <div class="flex flex-col">
                                                    <span class="font-medium text-gray-800">{{ row.data.employee.name }}</span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ row.data.employee?.role || row.data.department || "-" }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-800">
                                                    {{
                                                        new Date(row.data.work_date).toLocaleDateString("id-ID", { weekday: "long" })
                                                    }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{
                                                        new Date(row.data.work_date).toLocaleDateString("id-ID", {
                                                            day: "2-digit",
                                                            month: "long",
                                                            year: "numeric",
                                                        })
                                                    }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <div class="flex flex-col">
                                                <template v-if="!isConfig || !can('work_shifts.edit')">
                                                    <span class="font-medium text-gray-800">
                                                        {{ row.data.shift?.name || "-" }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{
                                                            row.data.shift
                                                                ? `${formatTime(row.data.shift.start_time)} - ${formatTime(row.data.shift.end_time)}`
                                                                : "-"
                                                        }}
                                                    </span>
                                                </template>
                                                <select
                                                    v-else
                                                    class="px-2 text-sm border border-gray-300 rounded h-9"
                                                    :value="row.data.shift_id"
                                                    @change="(e) => updateShift(row.data.id, e.target.value)"
                                                >
                                                    <option v-for="s in shifts" :key="s.id" :value="s.id">
                                                        {{ `${s.name} (${formatTime(s.start_time)} - ${formatTime(s.end_time)})` }}
                                                    </option>
                                                </select>
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <span v-if="row.data.jam_masuk || row.data.jam_keluar" class="font-medium text-gray-800">
                                                {{ formatTime(row.data.jam_masuk) }} - {{ formatTime(row.data.jam_keluar) }}
                                            </span>
                                            <span v-else class="text-gray-500">-</span>
                                        </td>
                                        <td class="p-3 text-center">
                                            {{ formatDateTime(row.data.created_at) }}
                                        </td>
                                        <td class="flex justify-center gap-2" @click.stop>
                                            <Link
                                                v-if="can('work_shifts.view')"
                                                :href="route('shift-works.show', row.data.id)"
                                                class="p-1 text-blue-500 hover:text-blue-700"
                                                title="Detail"
                                            >
                                                <Eye class="w-5 h-5" />
                                            </Link>
                                            <button
                                                v-if="can('work_shifts.edit')"
                                                @click="openEdit(row.data)"
                                                class="p-1 text-yellow-500 hover:text-yellow-700"
                                                title="Edit"
                                            >
                                                <Pencil class="w-5 h-5" />
                                            </button>
                                            <button
                                                v-if="can('work_shifts.delete')"
                                                @click="deleteItem(row.data.id)"
                                                class="p-1 text-red-500 hover:text-red-700"
                                                title="Hapus"
                                            >
                                                <Trash2 class="w-5 h-5" />
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </template>

                            <!-- Flat rendering -->
                            <tr
                                v-else-if="assignments.data && assignments.data.length"
                                v-for="(row, idx) in assignments.data"
                                :key="row.id"
                                class="border-b border-gray-200 cursor-pointer hover:bg-gray-50"
                                @click="toggleSelect(row.id)"
                            >
                                <td class="p-3" @click.stop>
                                    <div class="flex items-center justify-center">
                                        <input
                                            type="checkbox"
                                            :checked="selectedItems.includes(row.id)"
                                            @change="toggleSelect(row.id)"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        />
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    {{ (assignments.current_page - 1) * assignments.per_page + idx + 1 }}
                                </td>
                                <td class="p-3">
                                    <div class="flex items-center gap-3">
                                        <template v-if="row.employee.photo_url">
                                            <img
                                                :src="
                                                    row.employee.photo_url
                                                        ? row.employee.photo_url.startsWith('http')
                                                            ? row.employee.photo_url
                                                            : `/storage/${row.employee.photo_url}`
                                                        : ''
                                                "
                                                alt="avatar"
                                                class="object-cover w-8 h-8 rounded-full"
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
                                            <span class="font-medium text-gray-800">{{ row.employee.name }}</span>
                                            <span class="text-xs text-gray-500">{{ row.employee.role || "Staff" }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800">
                                            {{ new Date(row.work_date).toLocaleDateString("id-ID", { weekday: "long" }) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{
                                                new Date(row.work_date).toLocaleDateString("id-ID", {
                                                    day: "2-digit",
                                                    month: "long",
                                                    year: "numeric",
                                                })
                                            }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="flex flex-col">
                                        <div v-if="!isConfig || !can('work_shifts.edit')" class="flex flex-col">
                                            <span class="font-medium text-gray-800">
                                                {{ row.shift?.name || "-" }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ row.shift ? `${row.shift.start_time} - ${row.shift.end_time}` : "-" }}
                                            </span>
                                        </div>
                                        <select
                                            v-else
                                            class="px-2 text-sm border border-gray-300 rounded h-9"
                                            :value="row.shift_id"
                                            @change="(e) => updateShift(row.id, e.target.value)"
                                        >
                                            <option v-for="s in shifts" :key="s.id" :value="s.id">
                                                {{ `${s.name} (${formatTime(s.start_time)} - ${formatTime(s.end_time)})` }}
                                            </option>
                                        </select>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <span v-if="row.jam_masuk || row.jam_keluar" class="font-medium text-gray-800">
                                        {{ formatTime(row.jam_masuk) }} - {{ formatTime(row.jam_keluar) }}
                                    </span>
                                    <span v-else class="text-gray-500">-</span>
                                </td>
                                <td class="p-3 text-center">{{ formatDateTime(row.created_at) }}</td>
                                <td class="flex justify-center gap-2" @click.stop>
                                    <Link
                                        v-if="can('work_shifts.view')"
                                        :href="route('shift-works.show', row.id)"
                                        class="p-1 text-blue-500 hover:text-blue-700"
                                        title="Detail"
                                    >
                                        <Eye class="w-5 h-5" />
                                    </Link>
                                    <button
                                        v-if="can('work_shifts.edit')"
                                        @click="openEdit(row)"
                                        class="p-1 text-yellow-500 hover:text-yellow-700"
                                        title="Edit"
                                    >
                                        <Pencil class="w-5 h-5" />
                                    </button>
                                    <button
                                        v-if="can('work_shifts.delete')"
                                        @click="deleteItem(row.id)"
                                        class="p-1 text-red-500 hover:text-red-700"
                                        title="Hapus"
                                    >
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </td>
                            </tr>

                            <tr v-else>
                                <td colspan="8" class="py-6 text-center text-gray-500">
                                    Tidak ada data
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <Pagination
                    v-if="assignments.data && assignments.data.length"
                    :pagination="assignments"
                    class="border-t"
                />
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <Dialog
        v-model:visible="showCreate"
        modal
        dismissableMask
        :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
        :style="{ width: '560px', padding: '6px' }"
    >
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h3 class="text-xl font-semibold text-gray-900">Tambah Shift Kerja</h3>
            </div>
        </template>
        <div class="flex flex-col gap-4">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label class="block mb-1 text-sm text-gray-600">Dari Tanggal</label>
                    <input
                        type="date"
                        v-model="createForm.date_from"
                        class="w-full h-10 px-3 border border-gray-300 rounded"
                        @change="validateDateRange"
                    />
                    <p v-if="dateError" class="mt-1 text-xs text-red-600">{{ dateError }}</p>
                </div>
                <div>
                    <label class="block mb-1 text-sm text-gray-600">Sampai Tanggal</label>
                    <input
                        type="date"
                        v-model="createForm.date_to"
                        :min="createForm.date_from"
                        class="w-full h-10 px-3 border border-gray-300 rounded"
                        :class="{ 'border-red-500': dateError }"
                        @change="validateDateRange"
                    />
                    <p v-if="dateError" class="mt-1 text-xs text-red-600">{{ dateError }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label class="block mb-1 text-sm text-gray-600">Cabang</label>
                    <select v-model="createForm.branch_id" class="w-full h-10 px-3 border border-gray-300 rounded">
                        <option :value="null">Semua Cabang</option>
                        <option v-for="b in branches" :key="b.id" :value="b.id">
                            {{ b.name }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block mb-1 text-sm text-gray-600">Departemen</label>
                    <select v-model="createForm.department_id" class="w-full h-10 px-3 border border-gray-300 rounded">
                        <option :value="null">Semua Departemen</option>
                        <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">
                            {{ d.name }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input id="all-emps" type="checkbox" v-model="createForm.all_employees" class="w-4 h-4" />
                <label for="all-emps" class="text-sm text-gray-700">Semua karyawan</label>
            </div>
            <div v-if="!createForm.all_employees">
                <label class="block mb-1 text-sm font-medium text-gray-700">Pilih karyawan</label>
                <MultiSelect
                    v-model="createForm.user_ids"
                    :options="filteredEmployees"
                    optionLabel="name"
                    optionValue="user_id"
                    placeholder="Pilih karyawan"
                    filter
                    filterPlaceholder="Cari karyawan..."
                    :maxSelectedLabels="3"
                    class="w-full"
                    :pt="{
                        root: { class: 'w-full border border-gray-300 rounded-lg' },
                        label: { class: 'text-sm font-normal text-gray-700' },
                        trigger: { class: 'text-gray-600' },
                        panel: { class: 'shadow-lg border border-gray-200' },
                        header: { class: 'p-3 border-b' },
                        filterInput: { class: 'w-full px-3 py-2 text-sm border border-gray-300 rounded' },
                        item: { class: 'px-3 py-2 text-sm hover:bg-gray-50 cursor-pointer' },
                        checkboxContainer: { class: 'mr-2' },
                    }"
                />
                <p class="mt-1 text-xs text-gray-500">Anda dapat memilih lebih dari satu karyawan</p>
            </div>
            <div>
                <label class="block mb-1 text-sm text-gray-600">Kategori Shift</label>
                <select v-model="createForm.shift_id" class="w-full h-10 px-3 border border-gray-300 rounded">
                    <option :value="null">Pilih shift</option>
                    <option v-for="s in shifts" :key="s.id" :value="s.id">
                        {{ `${s.name} (${formatTime(s.start_time)} - ${formatTime(s.end_time)})` }}
                    </option>
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <button class="h-10 px-3 border rounded" type="button" @click="closeCreateModal">Batal</button>
                <button
                    class="h-10 px-3 text-white bg-blue-600 rounded"
                    type="button"
                    @click="submitCreate"
                    :disabled="!can('work_shifts.create')"
                    :class="!can('work_shifts.create') ? 'opacity-50 cursor-not-allowed' : ''"
                >
                    Simpan
                </button>
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
    >
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h3 class="text-xl font-semibold text-gray-900">Edit Shift Kerja</h3>
            </div>
        </template>
        <div class="flex flex-col gap-4">
            <div>
                <label class="block mb-1 text-sm text-gray-600">Tanggal Kerja</label>
                <input
                    type="date"
                    v-model="editForm.work_date"
                    class="w-full h-10 px-3 border border-gray-300 rounded"
                    :class="{ 'border-red-500': editDateError }"
                />
                <p v-if="editDateError" class="mt-1 text-xs text-red-600">{{ editDateError }}</p>
            </div>
            <div>
                <label class="block mb-1 text-sm text-gray-600">Shift</label>
                <select
                    v-model="editForm.shift_id"
                    class="w-full h-10 px-3 border border-gray-300 rounded"
                    :class="{ 'border-red-500': editShiftError }"
                >
                    <option :value="null">Pilih shift</option>
                    <option v-for="s in shifts" :key="s.id" :value="s.id">
                        {{ `${s.name} (${formatTime(s.start_time)} - ${formatTime(s.end_time)})` }}
                    </option>
                </select>
                <p v-if="editShiftError" class="mt-1 text-xs text-red-600">{{ editShiftError }}</p>
            </div>
            <div class="flex justify-end gap-2">
                <button
                    class="h-10 px-3 border rounded"
                    type="button"
                    @click="closeEditModal"
                >
                    Batal
                </button>
                <button
                    class="h-10 px-3 text-white bg-blue-600 rounded"
                    type="button"
                    @click="submitEdit"
                    :disabled="!can('work_shifts.edit')"
                    :class="!can('work_shifts.edit') ? 'opacity-50 cursor-not-allowed' : ''"
                >
                    Simpan
                </button>
            </div>
        </div>
    </Dialog>

    <!-- Bulk Edit Modal -->
    <Dialog
        v-model:visible="showBulkEdit"
        modal
        dismissableMask
        :breakpoints="{ '960px': '80vw', '640px': '95vw' }"
        :style="{ width: '520px', padding: '6px' }"
    >
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h3 class="text-xl font-semibold text-gray-900">Ubah Shift Kerja ({{ selectedItems.length }} data)</h3>
            </div>
        </template>
        <div class="flex flex-col gap-4">
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-sm text-amber-800">
                    <strong>Perhatian:</strong> Anda akan mengubah shift untuk {{ selectedItems.length }} data yang dipilih.
                    Pastikan Anda telah memilih data yang benar.
                </p>
            </div>
            <div>
                <label class="block mb-1 text-sm text-gray-600">Pilih Shift Baru</label>
                <select
                    v-model="bulkEditForm.shift_id"
                    class="w-full h-10 px-3 border border-gray-300 rounded"
                    :class="{ 'border-red-500': bulkEditError }"
                >
                    <option :value="null">Pilih shift</option>
                    <option v-for="s in shifts" :key="s.id" :value="s.id">
                        {{ `${s.name} (${formatTime(s.start_time)} - ${formatTime(s.end_time)})` }}
                    </option>
                </select>
                <p v-if="bulkEditError" class="mt-1 text-xs text-red-600">{{ bulkEditError }}</p>
            </div>
            <div class="flex justify-end gap-2">
                <button
                    class="h-10 px-3 border rounded"
                    type="button"
                    @click="closeBulkEditModal"
                >
                    Batal
                </button>
                <button
                    class="h-10 px-3 text-white bg-amber-500 rounded hover:bg-amber-600"
                    type="button"
                    @click="submitBulkEdit"
                    :disabled="!can('work_shifts.edit') || bulkEditLoading"
                    :class="(!can('work_shifts.edit') || bulkEditLoading) ? 'opacity-50 cursor-not-allowed' : ''"
                >
                    <span v-if="bulkEditLoading">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import AvatarInitials from "@/Components/common/AvatarInitials.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { Head, router, usePage } from "@inertiajs/vue3";

import { ref, watch, computed, onMounted, reactive, nextTick } from "vue";
import { MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import { Link } from "@inertiajs/vue3";
import { Eye, Pencil, Trash2 } from "lucide-vue-next";
import Swal from "sweetalert2";
import { Dialog } from "primevue";
import MultiSelect from "primevue/multiselect";
import { useAuth } from "@/Composables/useAuth"; // <<— ADD

const { can } = useAuth(); // <<— ADD
const page = usePage();
const duplicateErrorShown = ref(false);

// Multiple selection state
const selectedItems = ref([]);

const breadcrumbs = [{ label: "Absensi" }, { label: "Shift Kerja" }];

defineOptions({ layout: AppLayout });

const props = defineProps({
    assignments: { type: Object, default: () => ({}) },
    employees: { type: Array, default: () => [] },
    shifts: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    q: { type: String, default: "" },
    groupBy: { type: String, default: null },
});

// Utility: stable Tailwind color per name for AvatarInitials fallback
function colorClass(name) {
    const palette = [
        "bg-indigo-100 text-indigo-700",
        "bg-violet-100 text-violet-700",
        "bg-emerald-100 text-emerald-700",
        "bg-amber-100 text-amber-700",
        "bg-cyan-100 text-cyan-700",
        "bg-rose-100 text-rose-700",
        "bg-sky-100 text-sky-700",
        "bg-lime-100 text-lime-700",
    ];
    const s = (name || "").toString();
    if (!s) return palette[0];
    let hash = 0;
    for (let i = 0; i < s.length; i++) {
        hash = (hash * 31 + s.charCodeAt(i)) >>> 0;
    }
    return palette[hash % palette.length];
}

// Helper: year options for filter (current ±2)
const yearOptions = computed(() => {
    const y = new Date().getFullYear();
    return [y - 2, y - 1, y, y + 1, y + 2];
});

// Month options (1-12)
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

function statusBadgeClass(status) {
    const s = (status || "").toString().toLowerCase();
    if (s.includes("on time") || s.includes("hadir")) return "bg-emerald-50 text-emerald-700 border-emerald-200";
    if (s.includes("terlambat")) return "bg-amber-50 text-amber-700 border-amber-200";
    if (s.includes("cuti")) return "bg-sky-50 text-sky-700 border-sky-200";
    if (s.includes("sakit")) return "bg-rose-50 text-rose-700 border-rose-200";
    if (s.includes("absen") || s.includes("tanpa")) return "bg-gray-50 text-gray-700 border-gray-200";
    return "bg-gray-50 text-gray-700 border-gray-200";
}

const isConfig = ref(false);

// Helper to get today's date string
const today = new Date();
const pad2 = (n) => (n < 10 ? `0${n}` : `${n}`);
const todayStr = `${today.getFullYear()}-${pad2(today.getMonth() + 1)}-${pad2(today.getDate())}`;

const filtersLocal = ref({
    employee_id: null,
    shift_id: null,
    date_from: "",
    date_to: "",
    month: "",
    year: new Date().getFullYear(),
});

onMounted(() => {
    try {
        if (props.filters) {
            filtersLocal.value.employee_id = props.filters.employee_id ?? null;
            filtersLocal.value.shift_id = props.filters.shift_id ?? null;
            filtersLocal.value.date_from = props.filters.date_from ?? "";
            filtersLocal.value.date_to = props.filters.date_to ?? "";
            filtersLocal.value.month = props.filters.month ?? "";
            filtersLocal.value.year = props.filters.year ?? new Date().getFullYear();
        } else {
            // Set default date range to today if no filters provided
            filtersLocal.value.date_from = todayStr;
            filtersLocal.value.date_to = todayStr;
        }
    } catch (e) {}
});

const localQ = ref(props.q || "");
const showGroupMenu = ref(false);

const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(new Date().getFullYear());

if (filtersLocal.value.month) {
    const m = String(filtersLocal.value.month);
    const parts = m.split("-");
    if (parts.length === 2) {
        const y = parseInt(parts[0], 10);
        const mm = parseInt(parts[1], 10);
        if (!isNaN(y)) selectedYear.value = y;
        if (!isNaN(mm)) selectedMonth.value = mm;
    }
} else if (filtersLocal.value.year) {
    selectedYear.value = Number(filtersLocal.value.year) || selectedYear.value;
}

function onMonthYearChange() {
    const y = Number(selectedYear.value);
    const m = Number(selectedMonth.value);
    const mm = String(m).padStart(2, "0");
    filtersLocal.value.year = y;
    filtersLocal.value.month = `${y}-${mm}`;
    fetchList();
}
function toggleGroupMenu() {
    showGroupMenu.value = !showGroupMenu.value;
}
const localGroupBy = ref(props.groupBy || null);
watch(() => props.groupBy, (v) => { localGroupBy.value = v || null; });

let t = null;
watch(localQ, () => {
    clearTimeout(t);
    t = setTimeout(() => { fetchList(); }, 350);
});
watch(() => props.filters, (f) => {
    if (!f) {
        // Set default date range to today if no filters
        filtersLocal.value.date_from = todayStr;
        filtersLocal.value.date_to = todayStr;
        return;
    }
    filtersLocal.value.employee_id = f.employee_id ?? null;
    filtersLocal.value.shift_id = f.shift_id ?? null;
    filtersLocal.value.date_from = f.date_from ?? "";
    filtersLocal.value.date_to = f.date_to ?? "";
    filtersLocal.value.month = f.month ?? filtersLocal.value.month;
    filtersLocal.value.year = f.year ?? filtersLocal.value.year;
});

const groupedRows = computed(() => {
    if (!localGroupBy.value) return [];
    const rows = props.assignments?.data || [];
    const result = [];
    const groups = {};

    const baseNo = ((props.assignments?.current_page || 1) - 1) * (props.assignments?.per_page || rows.length);

    const keyFor = (r) => {
        if (localGroupBy.value === "shift") return r.shift?.name || "-";
        if (localGroupBy.value === "department") return r.department || "-";
        if (localGroupBy.value === "work_date") return r.work_date || "-";
        return "-";
    };

    rows.forEach((row, idx) => {
        const key = keyFor(row);
        if (!groups[key]) groups[key] = [];
        groups[key].push({ data: row, no: baseNo + idx + 1 });
    });
    Object.entries(groups).forEach(([key, items]) => {
        result.push({ __group: true, key, label: key, count: items.length });
        if (!collapsedGroups.value.has(key)) {
            result.push(...items);
        }
    });
    return result;
});

const collapsedGroups = ref(new Set());
function toggleGroupCollapse(key) {
    const s = new Set(collapsedGroups.value);
    if (s.has(key)) s.delete(key);
    else s.add(key);
    collapsedGroups.value = s;
}

const groupLabel = computed(() => {
    const map = { department: "Departemen", shift: "Kategori Shift", work_date: "Tanggal" };
    return localGroupBy.value ? map[localGroupBy.value] || "None" : "None";
});

function setGroupBy(v) {
    localGroupBy.value = v || null;
    showGroupMenu.value = false;
    fetchList();
}

function fetchList() {
    // Clear selection when fetching new data
    selectedItems.value = [];
    router.get(
        route("shift-works.index"),
        {
            q: localQ.value,
            employee_id: filtersLocal.value.employee_id,
            shift_id: filtersLocal.value.shift_id,
            date_from: filtersLocal.value.date_from,
            date_to: filtersLocal.value.date_to,
            month: filtersLocal.value.month,
            year: filtersLocal.value.year,
            groupBy: localGroupBy.value,
        },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

// ===== Permission-aware Actions =====
function updateShift(assignmentId, newShiftId) {
    if (!can('work_shifts.edit')) {
        Swal.fire({ icon: "warning", title: "Tidak diizinkan", text: "Anda tidak memiliki akses untuk mengubah shift." });
        return;
    }
    if (!assignmentId || !newShiftId) return;
    router.put(
        route("shift-works.update", assignmentId),
        { shift_id: Number(newShiftId), work_date: undefined },
        {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({ icon: "success", title: "Tersimpan", text: "Shift berhasil diperbarui.", timer: 1200, showConfirmButton: false });
                fetchList();
            },
            onError: () => {
                Swal.fire({ icon: "error", title: "Gagal", text: "Tidak dapat memperbarui shift." });
            },
        }
    );
}

// Edit modal state
const showEdit = ref(false);
const editForm = ref({ id: null, work_date: "", shift_id: null });
const editDateError = ref(null);
const editShiftError = ref(null);

// Bulk Edit modal state
const showBulkEdit = ref(false);
const bulkEditForm = reactive({ shift_id: null });
const bulkEditError = ref(null);
const bulkEditLoading = ref(false);

function openBulkEditModal() {
    if (!can('work_shifts.edit')) {
        Swal.fire({ icon: "warning", title: "Tidak diizinkan", text: "Anda tidak memiliki akses untuk mengubah shift kerja." });
        return;
    }
    if (selectedItems.value.length === 0) {
        Swal.fire({ icon: "warning", title: "Peringatan", text: "Pilih minimal satu data untuk diubah." });
        return;
    }
    bulkEditForm.shift_id = null;
    bulkEditError.value = null;
    showBulkEdit.value = true;
}

function closeBulkEditModal() {
    showBulkEdit.value = false;
    bulkEditForm.shift_id = null;
    bulkEditError.value = null;
}

function submitBulkEdit() {
    if (!can('work_shifts.edit')) return;

    // Validation
    if (!bulkEditForm.shift_id) {
        bulkEditError.value = 'Pilih shift terlebih dahulu';
        return;
    }

    bulkEditLoading.value = true;
    bulkEditError.value = null;

    router.put(route("shift-works.bulkUpdate"), {
        ids: selectedItems.value,
        shift_id: bulkEditForm.shift_id,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showBulkEdit.value = false;
            bulkEditForm.shift_id = null;
            selectedItems.value = [];
            bulkEditLoading.value = false;
            fetchList();

            const flashSuccess = page.props.flash?.success;
            const flashError = page.props.flash?.error;
            const flashWarning = page.props.flash?.warning;

            if (flashSuccess) {
                Swal.fire({ icon: "success", title: "Berhasil", text: flashSuccess, timer: 2000, showConfirmButton: false });
            } else if (flashWarning) {
                Swal.fire({ icon: "warning", title: "Peringatan", text: flashWarning, timer: 3000 });
            } else if (flashError) {
                Swal.fire({ icon: "error", title: "Gagal", text: flashError });
            }
        },
        onError: (errors) => {
            bulkEditLoading.value = false;
            const flashError = page.props.flash?.error;
            Swal.fire({
                icon: "error",
                title: "Gagal",
                text: flashError || "Tidak dapat mengubah data."
            });
        },
    });
}

// Create modal state
const showCreate = ref(false);
const dateError = ref(null);
const createForm = reactive({
    date_from: null,
    date_to: null,
    all_employees: false,
    user_ids: [],
    shift_id: null,
    branch_id: null,
    department_id: null,
});

function validateDateRange() {
    dateError.value = null;
    if (createForm.date_from && createForm.date_to) {
        const dateFrom = new Date(createForm.date_from);
        const dateTo = new Date(createForm.date_to);
        if (dateTo < dateFrom) {
            dateError.value = 'Tanggal akhir tidak boleh lebih dulu dari tanggal mulai';
            return false;
        }
    }
    return true;
}

function closeCreateModal() {
    showCreate.value = false;
    dateError.value = null;
}

function closeEditModal() {
    showEdit.value = false;
    editDateError.value = null;
    editShiftError.value = null;
}

function submitCreate() {
    if (!can('work_shifts.create')) {
        Swal.fire({ icon: "warning", title: "Tidak diizinkan", text: "Anda tidak memiliki akses untuk menambah shift kerja." });
        return;
    }
    if (!createForm.date_from || !createForm.date_to || !createForm.shift_id) {
        alert("Harap isi semua field yang diperlukan");
        return;
    }
    if (!validateDateRange()) {
        return;
    }
    if (!createForm.all_employees && (!createForm.user_ids || createForm.user_ids.length === 0)) {
        alert("Pilih minimal satu karyawan atau centang 'Semua karyawan'");
        return;
    }
    const payload = {
        date_from: createForm.date_from,
        date_to: createForm.date_to,
        all_employees: createForm.all_employees ? 1 : 0,
        user_ids: createForm.user_ids,
        shift_id: createForm.shift_id,
        branch_id: createForm.branch_id,
        department_id: createForm.department_id,
    };
    router.post(route("shift-works.store"), payload, {
        preserveScroll: true,
        onSuccess: () => {
            showCreate.value = false;
            createForm.date_from = null;
            createForm.date_to = null;
            createForm.all_employees = false;
            createForm.user_ids = [];
            createForm.shift_id = null;
            createForm.branch_id = null;
            createForm.department_id = null;
            dateError.value = null;
            fetchList();
        },
        onError: (errors) => {
            // Use nextTick to ensure flash messages are available
            nextTick(() => {
                // Check if there's a flash error message with duplicate information
                const flashError = page.props.flash?.error;
                const duplicates = page.props.flash?.duplicates;

                if (flashError && duplicates && duplicates.length > 0) {
                    duplicateErrorShown.value = true;
                    // Build detailed message with employee names and dates
                    let htmlContent = `
                        <div style="text-align: left; max-height: 60vh; overflow-y: auto;">
                            <p style="margin-bottom: 16px; font-weight: 600; color: #1f2937; font-size: 15px;">
                                Karyawan yang sudah memiliki shift:
                            </p>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                    `;

                    duplicates.forEach((dup, index) => {
                        const datesStr = dup.dates.join(', ');
                        htmlContent += `
                            <div style="
                                background: #f9fafb;
                                border: 1px solid #e5e7eb;
                                border-radius: 8px;
                                padding: 12px 16px;
                                transition: background 0.2s;
                            ">
                                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                    <span style="
                                        display: inline-flex;
                                        align-items: center;
                                        justify-content: center;
                                        width: 24px;
                                        height: 24px;
                                        background: #ef4444;
                                        color: white;
                                        border-radius: 50%;
                                        font-size: 12px;
                                        font-weight: 600;
                                        margin-right: 10px;
                                    ">${index + 1}</span>
                                    <strong style="color: #111827; font-size: 14px;">${dup.employee_name}</strong>
                                </div>
                                <div style="margin-left: 34px;">
                                    <span style="color: #6b7280; font-size: 12px; font-weight: 500; display: block; margin-bottom: 4px;">Tanggal yang duplikat:</span>
                                    <div style="
                                        color: #374151;
                                        font-size: 13px;
                                        line-height: 1.6;
                                        background: white;
                                        padding: 8px 12px;
                                        border-radius: 6px;
                                        border: 1px solid #e5e7eb;
                                    ">${datesStr}</div>
                                </div>
                            </div>
                        `;
                    });

                    htmlContent += `
                            </div>
                        </div>
                    `;

                    Swal.fire({
                        icon: "error",
                        title: "Data Duplikat",
                        html: htmlContent,
                        width: '650px',
                        padding: '24px',
                        confirmButtonText: "Mengerti",
                        confirmButtonColor: "#ef4444",
                        customClass: {
                            popup: 'swal2-popup-custom',
                            htmlContainer: 'swal2-html-container-custom'
                        }
                    }).then(() => {
                        duplicateErrorShown.value = false;
                    });
                } else if (flashError) {
                    // Fallback to simple error message
                    Swal.fire({
                        icon: "error",
                        title: "Gagal menyimpan",
                        text: flashError,
                        width: '600px'
                    });
                } else {
                    Swal.fire({ icon: "error", title: "Gagal menyimpan" });
                }
            });
        },
    });
}

function openEdit(row) {
    if (!can('work_shifts.edit')) return;
    editForm.value = {
        id: row?.id,
        work_date: row?.work_date || "",
        shift_id: row?.shift?.id || null,
    };
    editDateError.value = null;
    editShiftError.value = null;
    showEdit.value = true;
}
function submitEdit() {
    if (!can('work_shifts.edit')) return;
    if (!editForm.value.id) return;

    // Reset errors
    editDateError.value = null;
    editShiftError.value = null;

    // Client-side validation
    if (!editForm.value.work_date) {
        editDateError.value = 'Tanggal kerja wajib diisi';
        return;
    }
    if (!editForm.value.shift_id) {
        editShiftError.value = 'Shift wajib dipilih';
        return;
    }

    router.put(
        route("shift-works.update", editForm.value.id),
        { work_date: editForm.value.work_date, shift_id: editForm.value.shift_id },
        {
            preserveScroll: true,
            onSuccess: () => {
                showEdit.value = false;
                editDateError.value = null;
                editShiftError.value = null;
                fetchList();
            },
            onError: (errors) => {
                // Handle validation errors
                if (errors.errors) {
                    if (errors.errors.work_date) {
                        editDateError.value = Array.isArray(errors.errors.work_date)
                            ? errors.errors.work_date[0]
                            : errors.errors.work_date;
                    }
                    if (errors.errors.shift_id) {
                        editShiftError.value = Array.isArray(errors.errors.shift_id)
                            ? errors.errors.shift_id[0]
                            : errors.errors.shift_id;
                    }
                }

                // Handle flash error messages
                const flashError = page.props.flash?.error;
                if (flashError) {
                    // Check if it's a duplicate error
                    if (flashError.includes('duplikasi') || flashError.includes('sudah ada')) {
                        editDateError.value = flashError;
                    } else {
                        Swal.fire({ icon: "error", title: "Gagal", text: flashError });
                    }
                } else if (!editDateError.value && !editShiftError.value) {
                    Swal.fire({ icon: "error", title: "Gagal", text: "Tidak dapat memperbarui data." });
                }
            },
        }
    );
}

const flashMessage = ref(null);

// Multiple selection functions
const toggleSelect = (id) => {
    const index = selectedItems.value.indexOf(id);
    if (index > -1) {
        selectedItems.value.splice(index, 1);
    } else {
        selectedItems.value.push(id);
    }
};

const toggleSelectAll = () => {
    const allIds = getAllRowIds();
    if (isAllSelected.value) {
        selectedItems.value = [];
    } else {
        selectedItems.value = [...allIds];
    }
};

const getAllRowIds = () => {
    if (localGroupBy.value && groupedRows.value.length) {
        return groupedRows.value
            .filter(row => !row.__group)
            .map(row => row.data.id);
    } else if (props.assignments?.data && props.assignments.data.length) {
        return props.assignments.data.map(row => row.id);
    }
    return [];
};

const isAllSelected = computed(() => {
    const allIds = getAllRowIds();
    return allIds.length > 0 && allIds.every(id => selectedItems.value.includes(id));
});

const deleteMultiple = () => {
    if (!can('work_shifts.delete')) {
        Swal.fire({ icon: "warning", title: "Tidak diizinkan", text: "Anda tidak memiliki akses untuk menghapus shift kerja." });
        return;
    }

    if (selectedItems.value.length === 0) {
        Swal.fire({ icon: "warning", title: "Peringatan", text: "Pilih minimal satu data untuk dihapus." });
        return;
    }

    Swal.fire({
        title: "Yakin ingin menghapus?",
        text: `Anda akan menghapus ${selectedItems.value.length} data. Data yang dihapus tidak bisa dikembalikan!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal",
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route("shift-works.bulkDestroy"), {
                ids: selectedItems.value,
            }, {
                preserveScroll: true,
                onSuccess: () => {
                    selectedItems.value = [];
                    fetchList();
                    const flashSuccess = page.props.flash?.success;
                    const flashError = page.props.flash?.error;
                    const flashWarning = page.props.flash?.warning;

                    if (flashSuccess) {
                        Swal.fire({ icon: "success", title: "Berhasil", text: flashSuccess, timer: 2000, showConfirmButton: false });
                    } else if (flashWarning) {
                        Swal.fire({ icon: "warning", title: "Peringatan", text: flashWarning, timer: 3000 });
                    } else if (flashError) {
                        Swal.fire({ icon: "error", title: "Gagal", text: flashError });
                    }
                },
                onError: (errors) => {
                    const flashError = page.props.flash?.error;
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: flashError || "Tidak dapat menghapus data."
                    });
                },
            });
        }
    });
};

const deleteItem = (id) => {
    if (!can('work_shifts.delete')) {
        Swal.fire({ icon: "warning", title: "Tidak diizinkan", text: "Anda tidak memiliki akses untuk menghapus shift kerja." });
        return;
    }
    Swal.fire({
        title: "Yakin ingin menghapus?",
        text: "Data yang dihapus tidak bisa dikembalikan!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya",
        cancelButtonText: "Tidak",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route("shift-works.destroy", id), {
                preserveScroll: true,
                onSuccess: () => {
                    fetchList();
                },
                onError: () => {
                    Swal.fire({ icon: "error", title: "Gagal", text: "Tidak dapat menghapus data." });
                },
            });
        }
    });
};

// Debounced filter syncing
watch(
    () => ({ ...filtersLocal.value }),
    () => {
        clearTimeout(t);
        t = setTimeout(() => fetchList(), 300);
    },
    { deep: true }
);

// Formatting helpers
function formatLocalTime(time) {
    if (!time) return "-";
    const date = new Date(`1970-01-01T${time}+08:00`);
    return date
        .toLocaleTimeString([], { hour: "2-digit", minute: "2-digit", hour12: false })
        .replace(":", ".");
}

// Simple formatter to show only HH.mm
function formatTime(time) {
    if (!time) return "-";
    const s = time.toString();
    const isoMatch = s.match(/T(\d{2}):(\d{2})/);
    if (isoMatch) return `${isoMatch[1]}.${isoMatch[2]}`;
    const hhmmMatch = s.match(/^(\d{2}):(\d{2})/);
    if (hhmmMatch) return `${hhmmMatch[1]}.${hhmmMatch[2]}`;
    return (s.length >= 5 ? s.slice(0, 5) : s).replace(":", ".");
}

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

// Filter employees berdasarkan cabang dan departemen
const filteredEmployees = computed(() => {
    let filtered = props.employees;

    if (createForm.branch_id) {
        filtered = filtered.filter(emp => emp.branch_id === createForm.branch_id);
    }

    if (createForm.department_id) {
        filtered = filtered.filter(emp => emp.department_id === createForm.department_id);
    }

    return filtered;
});

// Filter departments berdasarkan branch yang dipilih
const filteredDepartments = computed(() => {
    if (!createForm.branch_id) {
        return props.departments;
    }
    return props.departments.filter(dept => dept.branch_id === createForm.branch_id);
});

// Reset department ketika branch berubah
watch(() => createForm.branch_id, () => {
    createForm.department_id = null;
});

// Validasi tanggal saat date_from atau date_to berubah
watch([() => createForm.date_from, () => createForm.date_to], () => {
    if (createForm.date_from && createForm.date_to) {
        validateDateRange();
    } else {
        dateError.value = null;
    }
});

// Watch for flash error with duplicates (fallback if onError doesn't catch it)
watch(
    () => page.props.flash?.error && page.props.flash?.duplicates,
    (hasDuplicates) => {
        if (hasDuplicates && page.props.flash?.duplicates?.length > 0 && !duplicateErrorShown.value) {
            duplicateErrorShown.value = true;
            const duplicates = page.props.flash.duplicates;
            const flashError = page.props.flash.error;

            // Build detailed message with employee names and dates
            let htmlContent = `
                <div style="text-align: left; max-height: 60vh; overflow-y: auto;">
                    <p style="margin-bottom: 16px; font-weight: 600; color: #1f2937; font-size: 15px;">
                        Karyawan yang sudah memiliki shift:
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
            `;

            duplicates.forEach((dup, index) => {
                const datesStr = dup.dates.join(', ');
                htmlContent += `
                    <div style="
                        background: #f9fafb;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        padding: 12px 16px;
                        transition: background 0.2s;
                    ">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <span style="
                                display: inline-flex;
                                align-items: center;
                                justify-content: center;
                                width: 24px;
                                height: 24px;
                                background: #ef4444;
                                color: white;
                                border-radius: 50%;
                                font-size: 12px;
                                font-weight: 600;
                                margin-right: 10px;
                            ">${index + 1}</span>
                            <strong style="color: #111827; font-size: 14px;">${dup.employee_name}</strong>
                        </div>
                        <div style="margin-left: 34px;">
                            <span style="color: #6b7280; font-size: 12px; font-weight: 500; display: block; margin-bottom: 4px;">Tanggal yang duplikat:</span>
                            <div style="
                                color: #374151;
                                font-size: 13px;
                                line-height: 1.6;
                                background: white;
                                padding: 8px 12px;
                                border-radius: 6px;
                                border: 1px solid #e5e7eb;
                            ">${datesStr}</div>
                        </div>
                    </div>
                `;
            });

            htmlContent += `
                    </div>
                </div>
            `;

            Swal.fire({
                icon: "error",
                title: "Data Duplikat",
                html: htmlContent,
                width: '650px',
                padding: '24px',
                confirmButtonText: "Mengerti",
                confirmButtonColor: "#ef4444",
                customClass: {
                    popup: 'swal2-popup-custom',
                    htmlContainer: 'swal2-html-container-custom'
                }
            }).then(() => {
                duplicateErrorShown.value = false;
            });
        }
    },
    { immediate: false }
);
</script>
