<template>

    <Head title="Karyawan" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <div class="flex flex-col overflow-hidden bg-white border border-gray-200 rounded-lg">
            <div class="px-6 py-3 text-xl font-semibold text-gray-800 border-b">
                Daftar Karyawan
            </div>
            <!-- Toolbar: search, group-by, add/export -->
            <div class="flex flex-col gap-2 px-6 py-3 border-b md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <input v-model="local.q" type="text" placeholder="Cari nama/username..."
                            class="py-2.5 pr-8 pl-3 w-64 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20" />
                        <span class="absolute text-gray-400 -translate-y-1/2 right-2 top-1/2">🔍</span>
                    </div>
                    <div class="relative">
                        <button type="button" @click="toggleGroupMenu"
                            class="h-10 px-3 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">
                            Kelompokan Berdasarkan :
                            <span class="font-medium">{{ groupLabel }}</span>
                        </button>
                        <div v-if="showGroupMenu" class="absolute z-10 w-56 mt-2 bg-white border rounded-lg shadow">
                            <ul class="py-1 text-sm">
                                <li>
                                    <button @click="setGroupBy(null)"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50">
                                        Tidak digrup
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('gender')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50">
                                        Jenis Kelamin
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('department')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50">
                                        Departemen
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('branch')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50">
                                        Cabang
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('shift')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50">
                                        Shift
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('status')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50">
                                        Status
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('position')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50">
                                        Posisi
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('role')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50">
                                        Level Akses
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('religion')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50">
                                        Agama
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('birthplace')"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50">
                                        Tempat Lahir
                                    </button>
                                </li>
                            </ul>
                        </div>

                    </div>
                    <div class="relative">
                        <select v-model="filter.active_status"
                            class="h-10 px-3 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-300">
                            <option :value="null">Status Aktif: {{ filter.active_status || 'Semua' }}</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <Link v-if="can('employees.create')" :href="route('employees.create')"
                        class="flex items-center gap-2 px-3 py-2 text-white bg-blue-500 rounded-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="hidden text-sm md:block">Tambah Karyawan</span>
                    </Link>
                    <a :href="exportUrl" target="_blank" rel="noopener"
                        class="flex items-center gap-2 px-3 py-2 text-gray-700 bg-white border border-gray-200 rounded-md hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="hidden text-sm md:block">Export Data</span>
                    </a>
                </div>
            </div>

            <!-- Bulk Edit Bar -->
            <div v-show="selectedIds.length"
                class="flex flex-col px-6 py-3 border-b bg-gray-50 md:flex-row md:items-center md:gap-3">
                <div class="mb-2 text-sm text-gray-600 md:mb-0">
                    Bulk edit ({{ selectedIds.length }} dipilih)
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select v-model="bulk.department_id" class="px-2 text-sm border border-gray-300 rounded h-9">
                        <option :value="null">Ubah Departemen...</option>
                        <option v-for="opt in departmentOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <select v-model="bulk.branch_id" class="px-2 text-sm border border-gray-300 rounded h-9">
                        <option :value="null">Ubah Cabang...</option>
                        <option v-for="opt in branchOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <select v-model="bulk.status" class="px-2 text-sm border border-gray-300 rounded h-9">
                        <option :value="null">Ubah Status...</option>
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <button @click="applyBulk" :disabled="!selectedIds.length ||
                        (!bulk.department_id &&
                            !bulk.branch_id &&
                            !bulk.status)
                        " class="px-3 text-white bg-blue-500 rounded h-9 disabled:opacity-50">
                        Terapkan
                    </button>
                    <button @click="bulkDelete" :disabled="!selectedIds.length"
                        class="px-3 text-white rounded h-9 bg-rose-600 disabled:opacity-50">
                        Hapus Terpilih
                    </button>
                    <button @click="clearSelection" :disabled="!selectedIds.length" class="px-3 border rounded h-9">
                        Bersihkan
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="p-3 bg-gray-100 border-gray-200 border-y" style="min-width: 140px">
                                <div class="font-medium text-center text-gray-600">
                                    Aksi
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border-gray-200 border-y" style="min-width: 60px">
                                <div class="font-medium text-center text-gray-600">
                                    No
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 200px">
                                <div class="font-medium text-left text-gray-600">
                                    Nama & Username
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 120px">
                                <div class="font-medium text-left text-gray-600">
                                    Jenis Kelamin
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 150px">
                                <div class="font-medium text-left text-gray-600">
                                    Position
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 150px">
                                <div class="font-medium text-left text-gray-600">
                                    Cabang
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 150px">
                                <div class="font-medium text-left text-gray-600">
                                    Departemen
                                </div>
                            </th>

                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 120px">
                                <div class="font-medium text-left text-gray-600">
                                    Level Akses
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 120px">
                                <div class="font-medium text-left text-gray-600">
                                    Shift
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 80px">
                                <div class="font-medium text-left text-gray-600">
                                    Umur
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 100px">
                                <div class="font-medium text-left text-gray-600">
                                    Status
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 120px">
                                <div class="font-medium text-left text-gray-600">
                                    Agama
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 130px">
                                <div class="font-medium text-left text-gray-600">
                                    No. Telepon
                                </div>
                            </th>
                            <th class="p-3 bg-gray-100 border border-gray-200" style="min-width: 150px">
                                <div class="font-medium text-left text-gray-600">
                                    Tempat Lahir
                                </div>
                            </th>
                        </tr>
                        <tr>
                            <th class="px-2 py-2 border-gray-200 bg-gray-50 border-y">
                                <button
                                    @click="clearFilters"
                                    class="flex items-center gap-2 px-2 py-2 mx-auto text-sm font-semibold text-white transition-all duration-150 bg-red-600 rounded shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1"
                                    title="Bersihkan semua filter"
                                >
                                    <span class="hidden md:inline">Clear Filter</span>
                                </button>
                            </th>
                            <th class="px-2 py-2 border-gray-200 bg-gray-50 border-y">
                                <div class="flex items-center justify-center gap-2">
                                    <input ref="headerCheckbox" type="checkbox" class="w-4 h-4"
                                        :checked="allOnPageSelected" @change="toggleSelectAll" />
                                </div>
                            </th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <input v-model="filter.name" type="text" placeholder="Cari nama/username"
                                    class="w-full px-2 text-xs border-gray-300 rounded h-9 sm:text-sm" />
                            </th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <FormSelect v-model="filter.gender" :items="genderOptions" label="Semua" labelKey="name"
                                    searchKey="name" />
                            </th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <FormSelect v-model="filter.position_id" :items="positionOptions" label="Semua"
                                    labelKey="name" searchKey="name" />
                            </th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <FormSelect v-model="filter.branch_id" :items="branchOptionsWithAll"
                                    label="Semua Cabang" labelKey="name" searchKey="name" />
                            </th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <FormSelect v-model="filter.department_id" :items="filteredDepartments"
                                    label="Semua Departemen" labelKey="name" searchKey="name">
                                    <template #item="{ item }">
                                        <span>{{ item.name }}</span>
                                        <span v-if="item.branch_id" class="ml-2 text-xs text-gray-500">
                                            -
                                            {{
                                                branches?.find(
                                                    (b) =>
                                                        b.id === item.branch_id
                                                )?.name
                                            }}
                                        </span>
                                    </template>
                                </FormSelect>
                            </th>

                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <FormSelect v-model="filter.role_id" :items="roleOptions" label="Semua" labelKey="name"
                                    searchKey="name" />
                            </th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <FormSelect v-model="filter.shift_id" :items="shiftOptions" label="Semua"
                                    labelKey="name" searchKey="name" />
                            </th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50"></th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <FormSelect v-model="filter.status" :items="statusOptions" label="Semua" labelKey="name"
                                    searchKey="name" />
                            </th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <FormSelect v-model="filter.religion" :items="religionOptions" label="Semua"
                                    labelKey="name" searchKey="name" />
                            </th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <input type="text" v-model="filter.phone" placeholder="Cari no. telepon..."
                                    class="w-full px-2 text-xs text-gray-800 bg-white border-gray-300 rounded h-9 sm:text-sm" />
                            </th>
                            <th class="px-2 py-2 border border-gray-200 bg-gray-50">
                                <input type="text" v-model="filter.birthplace" placeholder="Cari tempat lahir..."
                                    class="w-full px-2 text-xs text-gray-800 bg-white border-gray-300 rounded h-9 sm:text-sm" />
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="localGroupBy && groupedRows.length">
                            <template v-for="(row, idx) in groupedRows" :key="row.__group ? 'g-' + idx : row.data.id">
                                <tr v-if="row.__group" class="cursor-pointer select-none"
                                    @click="toggleGroupCollapse(row.key)">
                                    <td :colspan="11"
                                        class="px-4 py-2 font-semibold text-left text-gray-600 bg-gray-50">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="inline-block" :class="collapsedGroups.has(row.key)
                                                    ? 'rotate-[-90deg] transition-transform'
                                                    : 'rotate-0 transition-transform'
                                                ">▾</span>
                                            <span>{{ row.label }}</span>
                                            <span class="ml-2 text-xs text-gray-500">({{ row.count }})</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr v-else class="border-b border-gray-200">
                                    <td class="p-3 text-center">
                                        <div class="flex justify-center gap-1">
                                            <Link v-if="
                                                can('employees.viewDetail')
                                            " :href="route(
                                                    'employees.show',
                                                    row.data.id
                                                )
                                                    "
                                                class="p-1.5 text-blue-600 rounded border border-blue-200 hover:bg-blue-50"
                                                title="Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            </Link>
                                            <Link v-if="can('employees.edit')" :href="route(
                                                'employees.edit',
                                                row.data.id
                                            )
                                                "
                                                class="p-1.5 text-amber-600 rounded border border-amber-200 hover:bg-amber-50"
                                                title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            </Link>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center">
                                        {{ row.no }}
                                    </td>
                                    <td class="p-3">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" v-model="selectedIds" :value="row.data.id"
                                                class="w-4 h-4" />
                                            <AvatarInitials :name="row.data?.name" :gender="row.data?.gender"
                                                :size="32" />
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-800">{{
                                                    row.data?.name || "-"
                                                }}</span>
                                                <span class="text-xs text-gray-500">@{{
                                                    row.data?.user
                                                        ?.username || "-"
                                                }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3">
                                        {{ row.data?.gender || "-" }}
                                    </td>
                                    <td class="p-3">
                                        {{ row.data?.position?.name || "-" }}
                                    </td>
                                    <td class="p-3">
                                        {{ row.data?.branch?.name || "-" }}
                                    </td>
                                    <td class="p-3">
                                        {{ row.data?.department?.name || "-" }}
                                    </td>

                                    <td class="p-3">
                                        {{
                                            row.data?.rolesLabel ||
                                            row.data?.role?.name ||
                                            "-"
                                        }}
                                    </td>
                                    <td class="p-3">
                                        {{ row.data?.shift?.name || "-" }}
                                    </td>
                                    <td class="p-3">
                                        {{ generateUmur(row.data?.birth_date) }}
                                    </td>
                                    <td class="p-3">
                                        <span v-if="!row.data?.verification"
                                            :class="statusBadgeClass(null, 'Belum Verifikasi')">
                                            Belum Verifikasi
                                        </span>
                                        <span v-else-if="row.data?.status"
                                            :class="statusBadgeClass(null, row.data.status)">
                                            {{ row.data.status }}
                                        </span>
                                        <span v-else>-</span>
                                    </td>
                                    <td class="p-3">
                                        {{ row.data?.religion || "-" }}
                                    </td>
                                    <td class="p-3">
                                        {{ row.data?.contact || "-" }}
                                    </td>
                                    <td class="p-3">
                                        {{ row.data?.birthplace || "-" }}
                                    </td>
                                </tr>
                            </template>
                        </template>
                        <tr v-else-if="employees?.data && employees.data.length" v-for="row in employees.data"
                            :key="row.id" class="border-b border-gray-200">
                            <td class="p-3 text-center">
                                <div class="flex justify-center gap-1">
                                    <Link v-if="can('employees.viewDetail')" :href="route('employees.show', row.id)"
                                        class="p-1.5 text-blue-600 rounded border border-blue-200 hover:bg-blue-50"
                                        title="Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    </Link>
                                    <Link v-if="can('employees.edit')" :href="route('employees.edit', row.id)"
                                        class="p-1.5 text-amber-600 rounded border border-amber-200 hover:bg-amber-50"
                                        title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    </Link>
                                    <button v-if="can('employees.delete')" type="button"
                                        class="p-1.5 text-rose-600 rounded border border-rose-200 hover:bg-rose-50"
                                        title="Hapus" @click="deleteOne(row)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="p-3 text-center">
                                <input type="checkbox" v-model="selectedIds" :value="row.id" class="w-4 h-4" />
                            </td>
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <template v-if="row.path">
                                        <img :src="employeePhotoUrl(row.path)" alt="avatar" class="object-cover w-8 h-8 rounded-full cursor-zoom-in"
                                            @click="openAvatar(row)" />
                                    </template>
                                    <template v-else>
                                        <button type="button" @click="openAvatar(row)" class="inline-flex">
                                            <AvatarInitials :name="row.name" :gender="row?.gender || ''" :size="32" />
                                        </button>
                                    </template>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-gray-800">{{ row?.name || "-" }}</span>
                                        <span class="text-xs text-gray-500">@{{
                                            row?.user?.username || "-"
                                        }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">{{ row?.gender || "-" }}</td>
                            <td class="p-3">
                                {{ row?.position?.name || "-" }}
                            </td>
                            <td class="p-3">{{ row?.branch?.name || "-" }}</td>
                            <td class="p-3">
                                {{ row?.department?.name || "-" }}
                            </td>

                            <td class="p-3">
                                {{
                                    row?.user?.roles?.length
                                        ? row.user.roles
                                            .map((r) => r.name)
                                            .join(", ")
                                        : row?.role?.name || "-"
                                }}
                            </td>
                            <td class="p-3">{{ row?.shift?.name || "-" }}</td>
                            <td class="p-3">{{ computeAge(row) }}</td>
                            <td class="p-3">
                                <span v-if="!row?.verification" :class="statusBadgeClass(null, 'Belum Verifikasi')">
                                    Belum Verifikasi
                                </span>
                                <span v-else-if="row?.status" :class="statusBadgeClass(row.status)">
                                    {{ row.status }}
                                </span>
                                <span v-else>-</span>
                            </td>
                            <td class="p-3">{{ row?.religion || "-" }}</td>
                            <td class="p-3">{{ row?.contact || "-" }}</td>
                            <td class="p-3">{{ row?.birthplace || "-" }}</td>
                        </tr>
                        <tr v-else>
                            <td :colspan="14" class="py-6 text-center text-gray-500">
                                Tidak ada data
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination v-if="employees?.data && employees.data.length" :pagination="employees" class="border-t" />
            <!-- Avatar Preview Modal -->
            <Modal :show="showAvatar" title="Foto Karyawan" closeText="Tutup" maxWidth="sm" @close="closeAvatar">
                <div class="flex flex-col items-center justify-center py-4">
                    <template v-if="avatarTarget?.path">
                        <img :src="employeePhotoUrl(avatarTarget.path)" alt="avatar" class="object-cover w-64 h-64 shadow-md rounded-xl" />
                    </template>
                    <template v-else>
                        <AvatarInitials :name="avatarTarget?.name" :gender="avatarTarget?.gender || ''" :size="160" />
                    </template>
                    <div class="mt-3 text-sm text-gray-600">
                        {{ avatarTarget?.name }}
                    </div>
                </div>
            </Modal>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Pagination from "@/Components/common/Pagination.vue";
import AvatarInitials from "@/Components/common/AvatarInitials.vue";
import Modal from "@/Components/common/Modal.vue";
import FormSelect from "@/Components/form/SelectPemakaian.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import Swal from "sweetalert2";
import { useAuth } from "@/Composables/useAuth";

const breadcrumbs = [{ label: "Karyawan" }, { label: "Daftar Karyawan" }];

defineOptions({ layout: AppLayout });

const { can } = useAuth();

// Single delete with confirm
function deleteOne(row) {
    if (!row?.id) return;
    Swal.fire({
        title: "Hapus karyawan?",
        text: `Anda akan menghapus ${row.name || "(tanpa nama)"}. Lanjutkan?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal",
    }).then((res) => {
        if (res.isConfirmed) {
            router.delete(route("employees.destroy", row.id), {
                preserveScroll: true,
            });
        }
    });
}

const props = defineProps({
    employees: Object,
    departments: Array,
    branches: Array,
    permissions: Array,
    roles: Array,
    positions: Array,
    shifts: Array,
    status_options: Array,
    groupBy: { type: String, default: null },
    search: String,
    auth: Object,
});

// Filters for Employees page header selects
const filter = ref({
    name: null,
    gender: null,
    department_id: null,
    branch_id: null,
    role_id: null,
    position_id: null,
    shift_id: null,
    status: null,
    active_status: 'active',
    permission_id: null,
    religion: null,
    phone: null,
    birthplace: null,
});

// Filtered departments based on selected branch
const filteredDepartments = computed(() => {
    let depts = [];
    if (!filter.value.branch_id) {
        depts = props.departments || [];
    } else {
        depts = (props.departments || []).filter(
            (d) => d.branch_id === filter.value.branch_id
        );
    }
    return [{ id: null, name: "Semua Departemen" }, ...depts];
});

const departmentOptions = computed(() =>
    filteredDepartments.value.map((d) => ({
        label: d.name,
        value: d.id,
        branch_name:
            props.branches?.find((b) => b.id === d.branch_id)?.name || "",
    }))
);
const branchOptions = computed(() =>
    (props.branches || []).map((b) => ({ label: b.name, value: b.id }))
);

const branchOptionsWithAll = computed(() => [
    { id: null, name: "Semua Cabang" },
    ...(props.branches || []).map((b) => ({ id: b.id, name: b.name }))
]);

// Watch branch_id changes to reset department_id if not in filtered list
watch(
    () => filter.value.branch_id,
    (newBranchId) => {
        if (newBranchId && filter.value.department_id) {
            const deptExists = filteredDepartments.value.some(
                (d) => d.id === filter.value.department_id
            );
            if (!deptExists) {
                filter.value.department_id = null;
            }
        }
    }
);
const permissionOptions = computed(() =>
    (props.permissions || []).map((p) => ({ label: p.name, value: p.id }))
);
const roleOptions = computed(() => [
    { id: null, name: "Semua", label: "Semua" },
    ...(props.roles || []).map((r) => ({ id: r.id, name: r.name, label: r.name }))
]);
const positionOptions = computed(() => [
    { id: null, name: "Semua", label: "Semua" },
    ...(props.positions || []).map((p) => ({
        id: p.id,
        name: p.name,
        label: p.name,
    }))
]);

// Build export URL with current filters so export respects filters
const exportUrl = computed(() =>
    route("employees.export", {
        search: local.value.q,
        name: filter.value?.name,
        gender: filter.value?.gender,
        department_id: filter.value?.department_id,
        branch_id: filter.value?.branch_id,
        role_id: filter.value?.role_id,
        position_id: filter.value?.position_id,
        shift_id: filter.value?.shift_id,
        status: filter.value?.status,
        active_status: filter.value?.active_status,
        permission_id: filter.value?.permission_id,
        groupBy: localGroupBy.value,
    })
);

const shiftOptions = computed(() => [
    { id: null, name: "Semua", label: "Semua" },
    ...(props.shifts || []).map((s) => ({ id: s.id, name: s.name, label: s.name }))
]);

const genderOptions = [
    { id: null, name: "Semua", label: "Semua" },
    { id: "Laki-laki", name: "Laki-laki", label: "Laki-laki" },
    { id: "Perempuan", name: "Perempuan", label: "Perempuan" },
];

const religionOptions = [
    { id: null, name: "Semua", label: "Semua" },
    { id: "ISLAM", name: "Islam", label: "Islam" },
    { id: "Kristen", name: "Kristen", label: "Kristen" },
    { id: "Katolik", name: "Katolik", label: "Katolik" },
    { id: "Hindu", name: "Hindu", label: "Hindu" },
    { id: "Buddha", name: "Buddha", label: "Buddha" },
    { id: "Konghucu", name: "Konghucu", label: "Konghucu" },
];
const statusOptions = computed(() => [
    { id: null, name: "Semua", label: "Semua" },
    { id: "belum_verifikasi", name: "Belum Verifikasi", label: "Belum Verifikasi" },
    ...(props.status_options || []).map((st) => ({ id: st, name: st, label: st }))
]);

const local = ref({
    q: props.search || "",
});

let t = null;
watch(
    () => [
        local.value.q,
        filter.value.name,
        filter.value.gender,
        filter.value.department_id,
        filter.value.branch_id,
        filter.value.role_id,
        filter.value.position_id,
        filter.value.shift_id,
        filter.value.status,
        filter.value.active_status,
        filter.value.permission_id,
        filter.value.religion,
        filter.value.phone,
        filter.value.birthplace,
    ],
    () => {
        clearTimeout(t);
        t = setTimeout(() => fetchList(), 350);
    },
    { deep: true }
);

function fetchList() {
    router.get(
        route("employees.index"),
        {
            search: local.value.q,
            name: filter.value.name,
            gender: filter.value.gender,
            department_id: filter.value.department_id,
            branch_id: filter.value.branch_id,
            role_id: filter.value.role_id,
            position_id: filter.value.position_id,
            shift_id: filter.value.shift_id,
            status: filter.value.status,
            active_status: filter.value.active_status,
            permission_id: filter.value.permission_id,
            religion: filter.value.religion,
            phone: filter.value.phone,
            birthplace: filter.value.birthplace,
            groupBy: localGroupBy.value,
        },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

// Group-by state and helpers
const showGroupMenu = ref(false);
function toggleGroupMenu() {
    showGroupMenu.value = !showGroupMenu.value;
}
const localGroupBy = ref(props.groupBy || null);
const groupLabel = computed(() => {
    const map = {
        gender: "Jenis Kelamin",
        department: "Departemen",
        branch: "Cabang",
        shift: "Shift",
        status: "Status",
        position: "Posisi",
        role: "Level Akses",
        religion: "Agama",
        birthplace: "Tempat Lahir",
    };
    return localGroupBy.value ? map[localGroupBy.value] || "None" : "None";
});
function setGroupBy(v) {
    localGroupBy.value = v || null;
    showGroupMenu.value = false;
    fetchList();
}

// Grouped rows builder
const collapsedGroups = ref(new Set());
function toggleGroupCollapse(key) {
    const s = new Set(collapsedGroups.value);
    if (s.has(key)) s.delete(key);
    else s.add(key);
    collapsedGroups.value = s;
}
const groupedRows = computed(() => {
    if (!localGroupBy.value) return [];
    const rows = props.employees?.data || [];
    const baseNo =
        ((props.employees?.current_page || 1) - 1) *
        (props.employees?.per_page || rows.length);
    const groups = {};
    const keyFor = (r) => {
        if (localGroupBy.value === "gender") return r.gender || "-";
        if (localGroupBy.value === "department")
            return r?.department?.name || "-";
        if (localGroupBy.value === "branch") return r?.branch?.name || "-";
        if (localGroupBy.value === "shift") return r?.shift?.name || "-";
        if (localGroupBy.value === "status") return r?.status || "-";
        if (localGroupBy.value === "position") return r?.position?.name || "-";
        if (localGroupBy.value === "role") {
            return r?.user?.roles?.length
                ? r.user.roles.map((role) => role.name).join(", ")
                : r?.role?.name || "-";
        }
        if (localGroupBy.value === "religion") return r?.religion || "-";
        if (localGroupBy.value === "birthplace") return r?.birthplace || "-";
        return "-";
    };
    rows.forEach((r, idx) => {
        const k = keyFor(r);
        if (!groups[k]) groups[k] = [];
        // precompute roles label for display in grouped body
        const rolesLabel =
            Array.isArray(r?.roles) && r.roles.length
                ? r.roles.map((rr) => rr.name).join(", ")
                : r?.position?.name || "-";
        groups[k].push({ data: { ...r, rolesLabel }, no: baseNo + idx + 1 });
    });
    const result = [];
    Object.entries(groups).forEach(([k, arr]) => {
        result.push({ __group: true, key: k, label: k, count: arr.length });
        if (!collapsedGroups.value.has(k)) result.push(...arr);
    });
    return result;
});

// Helpers
function generateUmur(birth_date) {
    if (!birth_date) return "-";

    const today = new Date();
    const birthDate = new Date(birth_date);
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
    return `${age} th`;
}

// Normalize employee photo path for display
// Handles old formats: './employees/file.jpg', 'employees/file.jpg', 'uploads/employees/file.jpg'
function employeePhotoUrl(path) {
    if (!path) return '';
    let p = path.replace(/^\.\//, '').replace(/^\/+/, '');
    if (!p.startsWith('uploads/') && !p.startsWith('storage/')) {
        p = 'uploads/' + p;
    }
    if (p.startsWith('storage/')) {
        return '/' + p; // /storage/...
    }
    return '/storage/' + p; // /storage/uploads/employees/...
}

function computeAge(row) {
    const dob =
        row?.birth_date || row?.birthdate || row?.dob || row?.date_of_birth;
    if (!dob) return "-";
    try {
        const d = new Date(dob);
        if (isNaN(d)) return "-";
        const now = new Date();
        let age = now.getFullYear() - d.getFullYear();
        const m = now.getMonth() - d.getMonth();
        if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--;
        return `${age} th`;
    } catch {
        return "-";
    }
}

// Bulk selection and actions
const selectedIds = ref([]);
const bulk = ref({ department_id: null, branch_id: null, status: null });
const headerCheckbox = ref(null);

const visibleIds = computed(() => {
    // when grouped, groupedRows already hides collapsed groups; collect ids from non-group rows
    if (localGroupBy.value) {
        return groupedRows.value
            .filter((r) => !r.__group)
            .map((r) => r.data?.id)
            .filter((id) => !!id);
    }
    const rows = props.employees?.data || [];
    return rows.map((r) => r.id).filter((id) => !!id);
});

const allOnPageSelected = computed(() => {
    const set = new Set(selectedIds.value);
    return (
        visibleIds.value.length > 0 &&
        visibleIds.value.every((id) => set.has(id))
    );
});

watch([selectedIds, visibleIds], () => {
    const ind =
        selectedIds.value.length > 0 &&
        !allOnPageSelected.value &&
        visibleIds.value.some((id) => selectedIds.value.includes(id));
    if (headerCheckbox.value) headerCheckbox.value.indeterminate = ind;
});

function toggleSelectAll() {
    if (allOnPageSelected.value) {
        // unselect only ids on this page
        const remove = new Set(visibleIds.value);
        selectedIds.value = selectedIds.value.filter((id) => !remove.has(id));
    } else {
        // add all ids on this page
        const set = new Set(selectedIds.value);
        visibleIds.value.forEach((id) => set.add(id));
        selectedIds.value = Array.from(set);
    }
}

function clearSelection() {
    selectedIds.value = [];
    bulk.value = { department_id: null, branch_id: null, status: null };
}

function applyBulk() {
    if (!selectedIds.value.length) return;
    // Backend endpoint may not exist yet; this posts when available
    try {
        router.post(
            route("employees.bulk-update"),
            { ids: selectedIds.value, ...bulk.value },
            { preserveScroll: true }
        );
    } catch (e) {
        // Silently ignore if route helper not present
        console.warn("Bulk update route not available:", e?.message || e);
    }
}

async function bulkDelete() {
    if (!selectedIds.value.length) return;
    const res = await Swal.fire({
        title: "Hapus data terpilih?",
        text: `Anda akan menghapus ${selectedIds.value.length} karyawan. Tindakan ini tidak dapat dibatalkan.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, hapus",
        cancelButtonText: "Batal",
        confirmButtonColor: "#dc2626",
    });
    if (!res.isConfirmed) return;
    router.post(
        route("employees.bulk-delete"),
        { ids: selectedIds.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: "Data karyawan terpilih telah dihapus.",
                    timer: 1500,
                    showConfirmButton: false,
                });
                clearSelection();
                fetchList();
            },
            onError: (errors) => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: errors?.error || "Tidak dapat menghapus data.",
                });
            },
        }
    );
}

function statusBadgeClass(status) {
    const s = (status || "").toString().toLowerCase();
    if (s.includes("tetap") || s.includes("aktif"))
        return "inline-block px-2 py-0.5 text-xs rounded-full border bg-emerald-50 text-emerald-700 border-emerald-200";
    if (s.includes("kontrak"))
        return "inline-block px-2 py-0.5 text-xs rounded-full border bg-amber-50 text-amber-700 border-amber-200";
    if (s.includes("magang"))
        return "inline-block px-2 py-0.5 text-xs rounded-full border bg-sky-50 text-sky-700 border-sky-200";
    if (s.includes("belum verifikasi"))
        return "inline-block px-2 py-0.5 text-xs rounded-full border bg-gray-50 text-gray-700 border-gray-200";
    return "inline-block px-2 py-0.5 text-xs rounded-full border bg-red-50 text-red-700 border-red-200";
}

// Avatar preview modal
const showAvatar = ref(false);
const avatarTarget = ref(null);
function openAvatar(row) {
    avatarTarget.value = row || null;
    showAvatar.value = true;
}
function closeAvatar() {
    showAvatar.value = false;
    avatarTarget.value = null;
}

// Clear all filters
function clearFilters() {
    filter.value = {
        name: null,
        gender: null,
        department_id: null,
        branch_id: null,
        role_id: null,
        position_id: null,
        shift_id: null,
        status: null,
        active_status: null,
        permission_id: null,
        religion: null,
        phone: null,
        birthplace: null,
    };
    local.value.q = "";
    fetchList();
}
</script>
