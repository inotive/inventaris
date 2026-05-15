<template>

    <Head title="Daftar Kendaraan" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <button v-if="can('vehicles.create')" @click="openAddModal" type="button"
                class="flex gap-2 items-center px-3 py-2 text-white bg-blue-500 rounded">
                <PlusSquareIcon />
                <span class="hidden text-sm md:block">Tambah Kendaraan</span>
            </button>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-2 px-8 h-16 sm:flex-row sm:items-center sm:justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">
                    Daftar Kendaraan
                </div>

                <div class="flex gap-3 items-center">
                    <div class="relative py-2">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input v-model="search" type="text" placeholder="Cari kendaraan"
                            class="h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-400 dark:focus:border-blue-800 xl:w-[200px]" />
                    </div>
                    <!-- Group By -->
                    <div class="relative">
                        <button type="button" @click="toggleGroupMenu"
                            class="px-3 h-10 text-sm rounded-lg border border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:text-white/90">
                            Group by:
                            <span class="font-medium">{{ groupLabel }}</span>
                        </button>
                        <div v-if="showGroupMenu"
                            class="absolute right-0 z-10 mt-2 w-52 bg-white rounded-lg border shadow dark:border-gray-700 dark:bg-gray-800">
                            <ul class="py-1 text-sm">
                                <li>
                                    <button @click="setGroupBy(null)"
                                        class="px-3 py-2 w-full text-left hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Tidak digrup
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('branch')"
                                        class="px-3 py-2 w-full text-left hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Cabang
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('employee')"
                                        class="px-3 py-2 w-full text-left hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Driver
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('status')"
                                        class="px-3 py-2 w-full text-left hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Status
                                    </button>
                                </li>
                                <li>
                                    <button @click="setGroupBy('type')"
                                        class="px-3 py-2 w-full text-left hover:bg-gray-50 dark:hover:bg-gray-700">
                                        Tipe Kendaraan
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Filter dropdown removed; inline filters are rendered in header below -->
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <colgroup>
                        <col style="width:56px" />
                        <col style="width:220px" />
                        <col style="width:160px" />
                        <col style="width:220px" />
                        <col style="width:220px" />
                        <col style="width:180px" />
                        <col style="width:120px" />
                        <col style="width:120px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex justify-center items-center px-3">
                                    <p
                                        class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        No.
                                    </p>
                                </div>
                            </th>
                            <th @click="changeSort('name')"
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex gap-2 justify-center items-center px-3 cursor-pointer">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Nama Kendaraan
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="[
                                            '-mb-1',
                                            sortBy === 'name' &&
                                                sortDirection === 'asc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                        <DownIcon :class="[
                                            '-mt-1',
                                            sortBy === 'name' &&
                                                sortDirection === 'desc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                    </div>
                                </div>
                            </th>
                            <th @click="changeSort('license_code')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex gap-2 justify-center items-center px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Plat Nomor
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="[
                                            '-mb-1',
                                            sortBy === 'license_code' &&
                                                sortDirection === 'asc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                        <DownIcon :class="[
                                            '-mt-1',
                                            sortBy === 'license_code' &&
                                                sortDirection === 'desc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                    </div>
                                </div>
                            </th>
                            <th @click="changeSort('branch_id')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex gap-2 justify-center items-center px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Cabang
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="[
                                            '-mb-1',
                                            sortBy === 'branch_id' &&
                                                sortDirection === 'asc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                        <DownIcon :class="[
                                            '-mt-1',
                                            sortBy === 'branch_id' &&
                                                sortDirection === 'desc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                    </div>
                                </div>
                            </th>
                            <th @click="changeSort('track')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex gap-2 justify-center items-center px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Rute
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="[
                                            '-mb-1',
                                            sortBy === 'track' &&
                                                sortDirection === 'asc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                        <DownIcon :class="[
                                            '-mt-1',
                                            sortBy === 'track' &&
                                                sortDirection === 'desc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                    </div>
                                </div>
                            </th>
                            <th @click="changeSort('employee_id')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex gap-2 justify-center items-center px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Driver
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="[
                                            '-mb-1',
                                            sortBy === 'employee_id' &&
                                                sortDirection === 'asc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                        <DownIcon :class="[
                                            '-mt-1',
                                            sortBy === 'employee_id' &&
                                                sortDirection === 'desc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                    </div>
                                </div>
                            </th>
                            <th @click="changeSort('status')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex gap-2 justify-center items-center px-3">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Status
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="[
                                            '-mb-1',
                                            sortBy === 'status' &&
                                                sortDirection === 'asc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                        <DownIcon :class="[
                                            '-mt-1',
                                            sortBy === 'status' &&
                                                sortDirection === 'desc'
                                                ? 'text-gray-900 dark:text-gray-200'
                                                : 'text-gray-400 dark:text-gray-500',
                                        ]" />
                                    </div>
                                </div>
                            </th>
                            <th class="bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex justify-center items-center">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Aksi
                                    </p>
                                </div>
                            </th>
                        </tr>
                        <!-- Inline column filters -->
                        <tr>
                            <th
                                class="py-2 px-2 bg-gray-50 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                            </th>
                            <th
                                class="py-2 px-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                                <select v-model="filters.vehicle_type_id"
                                    class="w-full h-10 px-3 text-xs sm:text-sm rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                    <option :value="null">Semua</option>
                                    <option v-for="t in vehicleTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                                </select>
                            </th>
                            <th
                                class="py-2 px-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                                <input v-model="filters.license_code" type="text" placeholder="Cari plat"
                                    class="w-full h-10 px-3 text-xs sm:text-sm rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                            </th>
                            <th
                                class="py-2 px-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                                <select v-model="filters.branch_id"
                                    :disabled="!isSuperadmin"
                                    class="w-full h-10 px-3 text-xs sm:text-sm rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 disabled:bg-gray-100 disabled:cursor-not-allowed">
                                    <option :value="null">Semua</option>
                                    <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
                                </select>
                            </th>
                            <th
                                class="py-2 px-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                                <input v-model="filters.track" type="text" placeholder="Cari rute"
                                    class="w-full h-10 px-3 text-xs sm:text-sm rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90" />
                            </th>
                            <th
                                class="py-2 px-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                                <select v-model="filters.employee_id"
                                    class="w-full h-10 px-3 text-xs sm:text-sm rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                    <option :value="null">Semua</option>
                                    <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name }}</option>
                                </select>
                            </th>
                            <th
                                class="py-2 px-2 bg-gray-50 border border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                                <select v-model="filters.status"
                                    class="w-full h-10 px-3 text-xs sm:text-sm rounded border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                    <option :value="null">Semua</option>
                                    <option :value="1">Aktif</option>
                                    <option :value="0">Tidak Aktif</option>
                                </select>
                            </th>
                            <th
                                class="py-2 px-2 bg-gray-50 border-y border-gray-200 dark:border-gray-600 dark:bg-gray-900">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="vehicles.data && vehicles.data.length > 0">
                            <!-- Grouped rows rendering -->
                            <template v-if="groupBy">
                                <template v-for="(row, rIndex) in groupedRows" :key="
                                        row.__group
                                            ? `g-${rIndex}`
                                            : row.data.id
                                    ">
                                    <!-- Group header row -->
                                    <tr v-if="row.__group" class="cursor-pointer select-none"
                                        @click="toggleGroupCollapse(row.key)">
                                        <td :colspan="8"
                                            class="px-4 py-2 font-semibold text-left text-gray-600 bg-gray-50 dark:bg-gray-900/40 dark:text-gray-300">
                                            <span class="inline-flex gap-2 items-center">
                                                <span class="inline-block" :class="collapsedGroups.has(
                                                    row.key
                                                )
                                                        ? 'rotate-[-90deg] transition-transform'
                                                        : 'rotate-0 transition-transform'
                                                    ">
                                                    <DownIcon />
                                                </span>
                                                <span>{{ row.label }}</span>
                                                <span class="ml-2 text-xs font-normal text-gray-500">({{ row.count
                                                    }})</span>
                                            </span>
                                        </td>
                                    </tr>
                                    <!-- Vehicle row -->
                                    <tr v-else class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                                        <td class="py-2.5 border-gray-200 border-y dark:border-gray-600">
                                            <div class="flex justify-center items-center whitespace-nowrap">
                                                <p class="px-3 text-gray-500 dark:text-gray-400">
                                                    {{
                                                        (vehicles.current_page -
                                                            1) *
                                                        vehicles.per_page +
                                                        rIndex +
                                                        1
                                                    }}.
                                                </p>
                                            </div>
                                        </td>
                                        <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                            <div class="flex gap-3 items-center whitespace-nowrap ps-5">
                                                <img class="object-cover w-10 h-10 rounded-full" :src="row.data.photo
                                                        ? `/storage/${row.data.photo}`
                                                        : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                            row.data.type
                                                                .name
                                                        )}&background=3b82f6&color=fff`
                                                    " alt="Vehicle photo" loading="lazy" />
                                                <div class="flex flex-col leading-tight">
                                                    <p class="font-medium text-gray-800 dark:text-white/90">
                                                        {{ row.data.type.name }}
                                                    </p>
                                                    <span class="text-gray-500 dark:text-gray-400">
                                                        {{
                                                            row.data.type
                                                                .category
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                            <div class="flex justify-center items-center px-3 whitespace-nowrap">
                                                <p class="text-gray-500 dark:text-gray-400">
                                                    {{ row.data.license_code }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                            <div class="flex justify-center items-center px-3 whitespace-nowrap">
                                                <p class="text-gray-500 dark:text-gray-400">
                                                    {{ row.data.branch?.name }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                            <div class="flex justify-center items-center px-3 whitespace-nowrap">
                                                <p class="text-gray-500 dark:text-gray-400">
                                                    {{ row.data.track }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                            <div class="flex justify-center items-center px-3 whitespace-nowrap">
                                                <p class="text-gray-500 dark:text-gray-400">
                                                    {{ row.data.driver?.name }}
                                                </p>
                                            </div>
                                        </td>
                                        <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                            <div
                                                class="flex justify-center items-center px-3 text-xs whitespace-nowrap">
                                                <span v-if="row.data.status"
                                                    class="px-3 py-1 font-normal text-green-600 bg-green-100 rounded-lg border-2 border-green-300">
                                                    Aktif
                                                </span>
                                                <span v-else
                                                    class="px-2 py-1 font-normal text-red-600 bg-red-100 rounded-lg border-2 border-red-300">
                                                    Non Aktif
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 border-gray-200 border-y dark:border-gray-600">
                                            <div class="flex gap-3 justify-center px-4 whitespace-nowrap">
                                                <Link v-if="can('vehicles.view')" :href="route(
                                                    'vehicles.show',
                                                    row.data.id
                                                )
                                                    ">
                                                <ShowIcon class="text-blue-400" />
                                                </Link>
                                                <button v-if="can('vehicles.edit')" @click="
                                                    openEditModal(row.data)
                                                    ">
                                                    <EditIcon class="text-yellow-500" />
                                                </button>
                                                <button v-if="can('vehicles.delete')" @click="
                                                    openConfirmModal(
                                                        row.data
                                                    )
                                                    ">
                                                    <TrashIcon class="text-red-500" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>

                            <!-- Non-grouped rendering -->
                            <template v-else>
                                <tr v-for="(vehicle, index) in vehicles.data" :key="vehicle.id"
                                    class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">
                                    <!-- original vehicle row content unchanged -->

                                    <td class="py-2.5 border-gray-200 border-y dark:border-gray-600">
                                        <div class="flex justify-center items-center whitespace-nowrap">
                                            <p class="px-3 text-gray-500 dark:text-gray-400">
                                                {{
                                                    (vehicles.current_page -
                                                        1) *
                                                    vehicles.per_page +
                                                    index +
                                                    1
                                                }}.
                                            </p>
                                        </div>
                                    </td>
                                    <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                        <div class="flex gap-3 items-center whitespace-nowrap ps-5">
                                            <img class="object-cover w-10 h-10 rounded-full" :src="vehicle.photo
                                                    ? `/storage/${vehicle.photo}`
                                                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                        vehicle.type.name
                                                    )}&background=3b82f6&color=fff`
                                                " alt="Vehicle photo" loading="lazy" />
                                            <div class="flex flex-col leading-tight">
                                                <p class="font-medium text-gray-800 dark:text-white/90">
                                                    {{ vehicle.type.name }}
                                                </p>
                                                <span class="text-gray-500 dark:text-gray-400">
                                                    {{ vehicle.type.category }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                        <div class="flex justify-center items-center px-3 whitespace-nowrap">
                                            <p class="text-gray-500 dark:text-gray-400">
                                                {{ vehicle.license_code }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                        <div class="flex justify-center items-center px-3 whitespace-nowrap">
                                            <p class="text-gray-500 dark:text-gray-400">
                                                {{ vehicle.branch?.name }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                        <div class="flex justify-center items-center px-3 whitespace-nowrap">
                                            <p class="text-gray-500 dark:text-gray-400">
                                                {{ vehicle.track }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                        <div class="flex justify-center items-center px-3 whitespace-nowrap">
                                            <p class="text-gray-500 dark:text-gray-400">
                                                {{ vehicle.driver?.name }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                        <div class="flex justify-center items-center px-3 text-xs whitespace-nowrap">
                                            <span v-if="vehicle.status"
                                                class="px-3 py-1 font-normal text-green-600 bg-green-100 rounded-lg border-2 border-green-300">
                                                Aktif
                                            </span>
                                            <span v-else
                                                class="px-2 py-1 font-normal text-red-600 bg-red-100 rounded-lg border-2 border-red-300">
                                                Non Aktif
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-2.5 border-gray-200 border-y dark:border-gray-600">
                                        <div class="flex gap-3 justify-center px-4 whitespace-nowrap">
                                            <Link v-if="can('vehicles.view')" :href="route(
                                                'vehicles.show',
                                                vehicle.id
                                            )
                                                ">
                                            <ShowIcon class="text-blue-400" />
                                            </Link>
                                            <button v-if="can('vehicles.edit')" @click="openEditModal(vehicle)">
                                                <EditIcon class="text-yellow-500" />
                                            </button>
                                            <button v-if="can('vehicles.delete')" @click="
                                                openConfirmModal(vehicle)
                                                ">
                                                <TrashIcon class="text-red-500" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>

                        <tr v-else>
                            <td colspan="8" class="py-6 font-medium text-center text-gray-500 dark:text-gray-400">
                                Tidak ada kendaraan ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>

                <Modal :show="isModalOpen" :title="isEditMode
                        ? `Edit ${selectedItem?.type?.name || 'Kendaraan'} - ${selectedItem?.license_code || ''}`
                        : 'Tambah Kendaraan'
                    " confirmText="Simpan" maxWidth="lg" @close="closeModal" @confirm="saveVehicle">
                    <div class="space-y-3">
                        <div class="space-y-1 text-sm">
                            <label class="block mb-2 font-semibold text-gray-900 dark:text-white">Foto Kendaraan</label>

                            <!-- Preview Existing Images (Edit Mode) -->
                            <div v-if="isEditMode && selectedItem" class="mb-3">
                                <p class="mb-2 text-xs text-gray-600">Foto Saat Ini:</p>
                                <div class="grid gap-2 grid-cols-3">
                                    <!-- Show all files -->
                                    <template v-if="selectedItem.files && selectedItem.files.length > 0">
                                        <div v-for="(file, idx) in selectedItem.files" :key="file.id || idx"
                                            class="relative group"
                                            :class="{ 'opacity-50 border-red-500': isImageDeleted(file.id) }">
                                            <img
                                                :src="`/storage/${file.file_path}`"
                                                :alt="`Vehicle photo ${idx + 1}`"
                                                class="object-cover w-full h-24 rounded-lg border border-gray-300"
                                            />
                                            <!-- Delete Button -->
                                            <div v-if="!isImageDeleted(file.id)"
                                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs cursor-pointer hover:bg-red-600 z-10"
                                                @click="deleteExistingImage(file.id)">
                                                ×
                                            </div>
                                            <!-- Undo Button -->
                                            <div v-else
                                                class="absolute top-1 right-1 bg-green-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs cursor-pointer hover:bg-green-600 z-10"
                                                @click="undoDeleteImage(file.id)">
                                                ↺
                                            </div>
                                            <!-- Delete overlay message -->
                                            <div v-if="isImageDeleted(file.id)"
                                                class="absolute inset-0 flex items-center justify-center bg-red-500/20 rounded-lg">
                                                <span class="text-xs font-semibold text-red-600">Akan dihapus</span>
                                            </div>
                                        </div>
                                    </template>
                                    <div v-if="(!selectedItem.files || selectedItem.files.length === 0)" class="flex items-center justify-center h-24 text-xs text-gray-500 border border-gray-300 rounded-lg">
                                        Tidak ada foto
                                    </div>
                                </div>
                            </div>

                            <!-- Preview New Uploads -->
                            <div v-if="imagePreviews.length > 0" class="mb-3">
                                <p class="mb-2 text-xs text-gray-600">Preview Foto Baru:</p>
                                <div class="grid gap-2 grid-cols-3">
                                    <div v-for="(preview, index) in imagePreviews" :key="index" class="relative group">
                                        <img
                                            :src="preview"
                                            :alt="`Preview ${index + 1}`"
                                            class="object-cover w-full h-24 rounded-lg border border-gray-300"
                                        />
                                        <div class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs cursor-pointer hover:bg-red-600"
                                            @click="removeFile(index)">
                                            ×
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-bind="getRootProps()"
                                :class="[
                                    'p-6 text-center rounded-lg border-2 cursor-pointer border-1',
                                    form.errors.files
                                        ? 'text-red-500 bg-red-50 border-red-300 dark:bg-red-900/20 dark:border-red-500'
                                        : 'text-blue-500 bg-sky-400 bg-opacity-5 border-blue-300'
                                ]">
                                <input v-bind="getInputProps()" />
                                <div class="flex gap-8 items-center justify-stretch">
                                    <UploadIcon :class="form.errors.files ? 'w-12 h-12 text-red-300' : 'w-12 h-12 text-blue-300'" />
                                    <div class="flex flex-col text-start">
                                        <p v-if="isDragActive">
                                            Lepaskan file di sini ...
                                        </p>
                                        <p v-else>
                                            {{ isEditMode ? 'Ganti atau tambah foto dengan drag & drop' : 'Drag & drop file di sini, atau klik untuk pilih' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="form.errors.files" class="mt-2 text-sm font-medium text-red-600 dark:text-red-400">
                                {{ Array.isArray(form.errors.files) ? form.errors.files[0] : form.errors.files }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="vehicle_type_id" class="block mb-1 font-semibold text-gray-900 dark:text-white">Tipe Kendaraan<span class="text-red-500">*</span></label>
                            <Select
                                v-model="form.vehicle_type_id"
                                label="Pilih Tipe Kendaraan"
                                :items="vehicleTypeOptions"
                                :taggable="true"
                            />
                            <div v-if="form.errors.vehicle_type_id" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.vehicle_type_id) ? form.errors.vehicle_type_id[0] :
                                form.errors.vehicle_type_id }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="license_code" class="font-semibold text-gray-900 dark:text-white">No. Plat<span
                                    class="text-red-500">*</span></label>
                            <input id="license_code"
                                class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text" v-model="form.license_code" required
                                placeholder="Masukkan nomor plat kendaraan" />
                            <div v-if="form.errors.license_code" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.license_code) ? form.errors.license_code[0] :
                                form.errors.license_code }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="chassis_code" class="font-semibold text-gray-900 dark:text-white">No.
                                Rangka<span class="text-red-500">*</span></label>
                            <input id="chassis_code"
                                class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text" v-model="form.chassis_code" required
                                placeholder="Masukkan nomor rangka kendaraan" />
                            <div v-if="form.errors.chassis_code" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.chassis_code) ? form.errors.chassis_code[0] :
                                form.errors.chassis_code }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="machine_code" class="font-semibold text-gray-900 dark:text-white">No. Mesin<span
                                    class="text-red-500">*</span></label>
                            <input id="machine_code"
                                class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text" v-model="form.machine_code" required
                                placeholder="Masukkan nomor mesin kendaraan" />
                            <div v-if="form.errors.machine_code" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.machine_code) ? form.errors.machine_code[0] :
                                form.errors.machine_code }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="branch_id" class="font-semibold text-gray-900 dark:text-white">Cabang<span
                                    class="text-red-500">*</span></label>
                            <select v-model="form.branch_id"
                                :disabled="!isSuperadmin"
                                class="px-4 w-full text-sm font-medium text-gray-600 rounded-lg border-gray-300 dark:border-white dark:bg-gray-700 dark:text-white disabled:bg-gray-100 disabled:cursor-not-allowed">
                                <option :value="null">Pilih cabang</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                    {{ branch.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.branch_id" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.branch_id) ? form.errors.branch_id[0] :
                                form.errors.branch_id }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="employee_id" class="font-semibold text-gray-900 dark:text-white">Driver<span
                                    class="text-red-500">*</span></label>
                            <select v-model="form.employee_id"
                                class="px-4 w-full text-sm font-medium text-gray-600 rounded-lg border-gray-300 dark:border-white dark:bg-gray-700 dark:text-white">
                                <option :value="null">Pilih driver</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.employee_id" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.employee_id) ? form.errors.employee_id[0] : form.errors.employee_id }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="track" class="font-semibold text-gray-900 dark:text-white">Rute<span
                                    class="text-red-500">*</span></label>
                            <input id="track"
                                class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text" v-model="form.track" required placeholder="Masukkan rute penugasan" />
                            <div v-if="form.errors.track" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.track) ? form.errors.track[0] : form.errors.track }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="status" class="font-semibold text-gray-900 dark:text-white">Status<span
                                    class="text-red-500">*</span></label>
                            <select id="type" v-model="form.status"
                                class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                                <option :value="1">Aktif</option>
                                <option :value="0">Tidak Aktif</option>
                            </select>
                            <div v-if="form.errors.status" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.status) ? form.errors.status[0] : form.errors.status }}
                            </div>
                        </div>
                    </div>
                </Modal>
                <ConfirmModal :show="isConfirmModalOpen" :question="`Yakin ingin menghapus kendaraan`"
                    :selected="`${selectedItem?.type?.name || 'Kendaraan'} - ${selectedItem?.license_code || ''}`"
                    title="Hapus Kendaraan" confirmText="Ya, Hapus!"
                    maxWidth="md" @close="closeConfirmModal" @confirm="destroyData" />
            </div>

            <Pagination v-if="vehicles.data && vehicles.data.length > 0" :pagination="vehicles" />
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import SearchIcon from "@/Components/icons/SearchIcon.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import UploadIcon from "@/Components/icons/UploadIcon.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import Select from "@/Components/form/SelectBrng.vue";
import { ref, watch, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { useDropzone } from "vue3-dropzone";
import { useAuth } from "@/Composables/useAuth";

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [{ label: "Kendaraan" }, { label: "Data Kendaraan" }];

const props = defineProps({
    vehicles: Object,
    vehicleTypes: Object,
    branches: Object,
    employees: Object,
    search: String,
    sortBy: String,
    sortDirection: String,
    perPage: Number,
    auth: Object,
    // new from backend
    groupBy: {
        type: [String, null],
        default: null,
    },
    filters: {
        type: Object,
        default: () => ({
            branch_id: null,
            employee_id: null,
            status: null,
            vehicle_type_id: null,
            license_code: null,
            track: null,
        }),
    },
});

const { user, is, can } = useAuth();

const isSuperadmin = computed(() => is("Superadmin"));

function fetchVehicles({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    router.get(
        route("vehicles.index"),
        {
            search: search.value,
            sortBy,
            sortDirection,
            group_by: groupBy.value,
            ...filters.value,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onStart: () => {
                if (typeof window !== "undefined") window.__suppressProgress = true;
            },
            onFinish: () => {
                if (typeof window !== "undefined") window.__suppressProgress = false;
            },
        }
    );
}

const search = ref(props.search || "");
const showGroupMenu = ref(false);
const groupBy = ref(props.groupBy || null);
// Initialize branch_id: if not Superadmin, use user's branch_id
const getInitialBranchId = () => {
    if (props.filters?.branch_id) {
        return props.filters.branch_id;
    }
    if (!isSuperadmin.value && user.value?.employee?.branch_id) {
        return user.value.employee.branch_id;
    }
    return null;
};

const filters = ref({
    branch_id: getInitialBranchId(),
    employee_id: props.filters?.employee_id ?? null,
    status: props.filters?.status ?? null,
    vehicle_type_id: props.filters?.vehicle_type_id ?? null,
    license_code: props.filters?.license_code ?? null,
    track: props.filters?.track ?? null,
});

const groupLabel = computed(() => {
    switch (groupBy.value) {
        case "branch":
            return "Cabang";
        case "employee":
            return "Driver";
        case "status":
            return "Status";
        case "type":
            return "Tipe";
        default:
            return "None";
    }
});

const vehicleTypeOptions = computed(() =>
    (props.vehicleTypes ?? []).map((vt) => ({
        id: vt.id,
        name: `${vt.name} - ${vt.category}`,
    }))
);

// Get image preview URLs for newly uploaded files
const imagePreviews = computed(() => {
    if (!form.files || form.files.length === 0) return [];

    return form.files.map(file => {
        if (file instanceof File) {
            // Newly uploaded file
            return URL.createObjectURL(file);
        }
        return null;
    }).filter(Boolean);
});

// Collapsed groups state and helpers
const collapsedGroups = ref(new Set());
function toggleGroupCollapse(key) {
    const s = new Set(collapsedGroups.value);
    if (s.has(key)) s.delete(key);
    else s.add(key);
    collapsedGroups.value = s;
}

// Build flattened rows with group headers + counts and collapse support
const groupedRows = computed(() => {
    const data = props.vehicles?.data || [];
    const g = groupBy.value;
    if (!g) return [];

    const labelFor = (item) => {
        switch (g) {
            case "branch":
                return `Cabang: ${item.branch?.name ?? "Tanpa Cabang"}`;
            case "employee":
                return `Driver: ${item.driver?.name ?? "Tanpa Driver"}`;
            case "status":
                return `Status: ${item.status ? "Aktif" : "Non Aktif"}`;
            case "type":
                return `Tipe: ${item.type?.name ?? "Tanpa Tipe"}`;
            default:
                return "";
        }
    };
    const keyOf = (item) => {
        switch (g) {
            case "branch":
                return item.branch_id ?? "null";
            case "employee":
                return item.employee_id ?? "null";
            case "status":
                return item.status ? 1 : 0;
            case "type":
                return item.vehicle_type_id ?? "null";
            default:
                return "ungrouped";
        }
    };

    // Build ordered groups based on current order from backend
    const groups = [];
    const groupIndex = new Map();
    for (const item of data) {
        const key = keyOf(item);
        if (!groupIndex.has(key)) {
            groupIndex.set(key, groups.length);
            groups.push({ key, label: labelFor(item), items: [] });
        }
        groups[groupIndex.get(key)].items.push(item);
    }

    // Flatten with header + optionally items (respect collapsed state)
    const out = [];
    for (const gitem of groups) {
        out.push({
            __group: true,
            key: gitem.key,
            label: gitem.label,
            count: gitem.items.length,
        });
        if (!collapsedGroups.value.has(gitem.key)) {
            for (const it of gitem.items)
                out.push({ __group: false, data: it });
        }
    }
    return out;
});

function toggleGroupMenu() {
    showGroupMenu.value = !showGroupMenu.value;
}
function setGroupBy(v) {
    groupBy.value = v;
    showGroupMenu.value = false;
    fetchVehicles();
}

let timeout = null;
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        fetchVehicles();
    }, 400);
});

// Debounce text filter inputs
let licenseTimeout = null;
watch(
    () => filters.value.license_code,
    () => {
        clearTimeout(licenseTimeout);
        licenseTimeout = setTimeout(() => {
            fetchVehicles();
        }, 400);
    }
);

let trackTimeout = null;
watch(
    () => filters.value.track,
    () => {
        clearTimeout(trackTimeout);
        trackTimeout = setTimeout(() => {
            fetchVehicles();
        }, 400);
    }
);

// Watch branch_id to prevent changes if user is not Superadmin
watch(
    () => filters.value.branch_id,
    (newVal) => {
        if (!isSuperadmin.value && user.value?.employee?.branch_id) {
            // Force branch_id to user's branch_id if not Superadmin
            if (newVal !== user.value.employee.branch_id) {
                filters.value.branch_id = user.value.employee.branch_id;
            }
        }
    }
);

// Immediate fetch on select filters change
watch(
    () => [
        filters.value.vehicle_type_id,
        filters.value.branch_id,
        filters.value.employee_id,
        filters.value.status,
    ],
    () => {
        fetchVehicles();
    }
);

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchVehicles({ sortBy: column, sortDirection: direction });
}

const isModalOpen = ref(false);
const isEditMode = ref(false);
const deletedImages = ref([]); // Track images to be deleted

const form = useForm({
    id: null,
    vehicle_type_id: null,
    license_code: "",
    chassis_code: "",
    machine_code: "",
    branch_id: null,
    employee_id: null,
    track: "",
    status: 1,
    files: [],
});


function onDrop(acceptedFiles, fileRejections) {
    form.files = acceptedFiles;

    fileRejections.forEach((reject) => {
        reject.errors.forEach((err) => {
            if (err.code === "file-too-large") {
                flash.error(`File ${reject.file.name} terlalu besar!`);
            }
            if (err.code === "file-invalid-type") {
                flash.error(
                    `Tipe file ${reject.file.name} tidak diperbolehkan!`
                );
            }
        });
    });
}

function removeFile(index) {
    // Clean up the object URL before removing
    const file = form.files[index];
    if (file instanceof File) {
        const url = URL.createObjectURL(file);
        URL.revokeObjectURL(url);
    }
    form.files.splice(index, 1);
}

// Delete existing image (mark for deletion)
function deleteExistingImage(imageId) {
    deletedImages.value.push(imageId);
}

// Undo deletion of existing image
function undoDeleteImage(imageId) {
    deletedImages.value = deletedImages.value.filter(id => id !== imageId);
}

// Check if image is marked for deletion
function isImageDeleted(imageId) {
    return deletedImages.value.includes(imageId);
}

const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    multiple: true,
    accept: "image/jpeg, image/png",
    maxSize: 5 * 1024 * 1024, // maksimal 5 MB
});

// Buka modal untuk tambah
function openAddModal() {
    form.reset();
    // Set branch_id to user's branch if not Superadmin
    if (!isSuperadmin.value && user.value?.employee?.branch_id) {
        form.branch_id = user.value.employee.branch_id;
    }
    isEditMode.value = false;
    isModalOpen.value = true;
}

// Buka modal untuk edit
function openEditModal(vehicle) {
    console.log('openEditModal called', vehicle);
    form.id = vehicle.id;
    // Ensure vehicle_type_id is properly set as integer for existing vehicle types
    form.vehicle_type_id = vehicle.vehicle_type_id ? parseInt(vehicle.vehicle_type_id) : null;
    form.license_code = vehicle.license_code;
    form.chassis_code = vehicle.chassis_code;
    form.machine_code = vehicle.machine_code;
    // Set branch_id: if not Superadmin, use user's branch_id
    if (!isSuperadmin.value && user.value?.employee?.branch_id) {
        form.branch_id = user.value.employee.branch_id;
    } else {
    form.branch_id = vehicle.branch_id ? parseInt(vehicle.branch_id) : null;
    }
    form.employee_id = vehicle.employee_id ? parseInt(vehicle.employee_id) : null;
    form.track = vehicle.track;
    form.status = vehicle.status ? 1 : 0;
    form.files = []; // Reset files for new uploads
    deletedImages.value = []; // Reset deleted images
    selectedItem.value = vehicle;
    isEditMode.value = true;
    isModalOpen.value = true;
}

function closeModal() {
    // Clean up object URLs to prevent memory leaks
    if (imagePreviews.value.length > 0) {
        imagePreviews.value.forEach(url => {
            URL.revokeObjectURL(url);
        });
    }

    isModalOpen.value = false;
    selectedItem.value = null;
    deletedImages.value = []; // Reset deleted images
    form.reset();
    form.clearErrors();
}

// Simpan (otomatis create/update)
function saveVehicle() {
    console.log('saveVehicle called');
    console.log('Form data before transform:', form.data());

    if (isEditMode.value) {
        // Validasi: Pastikan minimal ada 1 foto saat edit
        const currentFilesCount = selectedItem.value?.files?.length || 0;
        const deletedCount = deletedImages.value.length;
        const newFilesCount = form.files.length;
        const remainingFiles = currentFilesCount - deletedCount;

        if (remainingFiles <= 0 && newFilesCount === 0) {
            form.setError('files', 'Minimal harus ada 1 foto kendaraan. Silakan tambahkan foto baru atau batalkan penghapusan foto yang ada.');
            return;
        }

        // For update, ensure all required fields are included
        // Include deleted_images in the update data
        const updateData = {
            ...form.data(),
            deleted_images: deletedImages.value,
            _method: 'PUT',
        };

        console.log('Update data:', updateData);

        form.transform(() => updateData).post(route("vehicles.update", form.id), {
            onSuccess: () => {
                console.log('Update success');
                closeModal();
            },
            onError: (errors) => {
                console.log('Update errors:', errors);
            },
        });
    } else {
        // Convert string IDs to integers for create
        // vehicle_type_id can be either a number (existing) or a string (newly created)
        const data = {
            ...form.data(),
            vehicle_type_id: typeof form.vehicle_type_id === 'string'
                ? form.vehicle_type_id
                : (form.vehicle_type_id ? parseInt(form.vehicle_type_id) : null),
            branch_id: form.branch_id ? parseInt(form.branch_id) : null,
            employee_id: form.employee_id ? parseInt(form.employee_id) : null,
        };

        console.log('Form data after transform:', data);

        form.transform(() => data).post(route("vehicles.store"), {
            onSuccess: () => {
                console.log('Create success');
                closeModal();
            },
            onError: (errors) => {
                console.log('Create errors:', errors);
            },
        });
    }
}

// destroy
const selectedItem = ref(null);

const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    selectedItem.value = item;
    isConfirmModalOpen.value = true;
};
const closeConfirmModal = () => {
    selectedItem.value = null;
    isConfirmModalOpen.value = false;
};
const destroyData = () => {
    router.delete(route("vehicles.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        preserveScroll: true,
    });
};
</script>
