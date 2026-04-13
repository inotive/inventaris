<template>
    <Head title="Daftar Service Kendaraan" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <button
                v-if="canCreate"
                @click="openAddModal"
                type="button"
                class="flex gap-2 items-center px-3 py-2 text-white bg-blue-500 rounded"
            >
                <PlusSquareIcon />
                <span class="hidden text-sm md:block">Tambah Data Service</span>
            </button>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col gap-2 px-8 h-16 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Daftar Service Kendaraan
                </div>

                <div class="flex gap-3 items-center">
                    <div class="relative py-2">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari data service"
                            class="h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-400 dark:focus:border-blue-800 xl:w-[200px]"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex justify-center items-center px-3"
                                >
                                    <p
                                        class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        No.
                                    </p>
                                </div>
                            </th>
                            <th
                                @click="changeSort('name')"
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3 cursor-pointer"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Nama Kendaraan
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'name' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'name' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('license_code')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Plat Nomor
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'license_code' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'license_code' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Kategori
                                    </p>
                                </div>
                            </th>
                            <th
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Sub Kategori
                                    </p>
                                </div>
                            </th>
                            <th
                                @click="changeSort('date')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Tanggal Service
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'date' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'date' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>

                            <th
                                class="bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Aksi
                                    </p>
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <!-- Filter Row -->
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                            ></th>
                            <!-- Filter Nama Kendaraan -->
                            <th
                                class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                            >
                                <input
                                    v-model="vehicleNameFilter"
                                    type="text"
                                    placeholder="Cari nama kendaraan"
                                    class="w-full h-8 px-2 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </th>
                            <!-- Filter Plat Nomor -->
                            <th
                                class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                            >
                                <input
                                    v-model="licenseFilter"
                                    type="text"
                                    placeholder="Cari plat nomor"
                                    class="w-full h-8 px-2 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 dark:border-gray-600 min-w-[150px]"
                            >
                                <SelectMultiple
                                    v-model="categoryNameFilter"
                                    :items="categories"
                                    label="Filter Kategori"
                                    searchKey="name"
                                    labelKey="name"
                                    class="w-full"
                                />
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 dark:border-gray-600 min-w-[150px]"
                            >
                                <SelectMultiple
                                    v-model="subCategoryNameFilter"
                                    :items="subCategories"
                                    label="Filter Sub Kategori"
                                    searchKey="name"
                                    labelKey="name"
                                    class="w-full"
                                />
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                            >
                                <div class="flex flex-col gap-1">
                                    <input
                                        v-model="dateFilter.date_from"
                                        type="date"
                                        placeholder="Dari"
                                        class="w-full h-8 px-2 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyDateFilter"
                                    />
                                    <input
                                        v-model="dateFilter.date_to"
                                        type="date"
                                        placeholder="Sampai"
                                        class="w-full h-8 px-2 text-xs rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        @change="applyDateFilter"
                                    />
                                </div>
                            </th>
                            <th
                                class="px-2 py-2 border border-gray-200 dark:border-gray-600"
                            >
                                <button
                                    @click="clearFilter"
                                    class="px-2 py-1 text-xs text-gray-600 bg-white rounded border border-gray-300 hover:bg-gray-50"
                                >
                                    Clear
                                </button>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <template
                            v-if="
                                vehicleServices.data &&
                                vehicleServices.data.length > 0
                            "
                        >
                            <tr
                                v-for="(service, index) in vehicleServices.data"
                                :key="service.id"
                                class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <td
                                    class="py-2.5 border-gray-200 border-y dark:border-gray-600"
                                >
                                    <div
                                        class="flex justify-center items-center whitespace-nowrap"
                                    >
                                        <p
                                            class="px-3 text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                (vehicleServices.current_page -
                                                    1) *
                                                    vehicleServices.per_page +
                                                index +
                                                1
                                            }}.
                                        </p>
                                    </div>
                                </td>
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex gap-3 items-center whitespace-nowrap ps-5"
                                    >
                                        <img
                                            class="object-cover w-10 h-10 rounded-full"
                                            :src="
                                                service.photo
                                                    ? `/storage/${service.photo}`
                                                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                          service.name,
                                                      )}&background=3b82f6&color=fff`
                                            "
                                            alt="Vehicle photo"
                                            loading="lazy"
                                        />
                                        <div
                                            class="flex flex-col leading-tight"
                                        >
                                            <p
                                                class="font-medium text-gray-800 dark:text-white/90"
                                            >
                                                {{ service.name }}
                                            </p>
                                            <span
                                                class="text-gray-500 dark:text-gray-400"
                                            >
                                                {{ service.category }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex justify-center items-center px-3 whitespace-nowrap"
                                    >
                                        <p
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            {{ service.vehicle.license_code }}
                                        </p>
                                    </div>
                                </td>
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex flex-wrap gap-1 justify-center items-center px-3"
                                    >
                                        <template
                                            v-if="
                                                Array.isArray(
                                                    service.category_name,
                                                ) &&
                                                service.category_name.length > 0
                                            "
                                        >
                                            <span
                                                v-for="(
                                                    cat, idx
                                                ) in service.category_name"
                                                :key="idx"
                                                class="px-2 py-0.5 text-xs font-medium text-blue-800 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300"
                                            >
                                                {{ cat }}
                                            </span>
                                        </template>
                                        <span v-else class="text-gray-400"
                                            >-</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex flex-wrap gap-1 justify-center items-center px-3"
                                    >
                                        <template
                                            v-if="
                                                Array.isArray(
                                                    service.sub_category_name,
                                                ) &&
                                                service.sub_category_name
                                                    .length > 0
                                            "
                                        >
                                            <span
                                                v-for="(
                                                    sub, idx
                                                ) in service.sub_category_name"
                                                :key="idx"
                                                class="px-2 py-0.5 text-xs font-medium text-purple-800 bg-purple-100 rounded-full dark:bg-purple-900 dark:text-purple-300"
                                            >
                                                {{ sub }}
                                            </span>
                                        </template>
                                        <span v-else class="text-gray-400"
                                            >-</span
                                        >
                                    </div>
                                </td>
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex justify-center items-center px-3 whitespace-nowrap"
                                    >
                                        <p
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            {{ formatTime(service.date) }}
                                        </p>
                                    </div>
                                </td>

                                <td
                                    class="py-2.5 border-gray-200 border-y dark:border-gray-600"
                                >
                                    <div
                                        class="flex gap-3 justify-center px-4 whitespace-nowrap"
                                    >
                                        <Link
                                            v-if="canView"
                                            :href="
                                                route(
                                                    'vehicle-services.show',
                                                    service.id,
                                                )
                                            "
                                        >
                                            <ShowIcon class="text-blue-400" />
                                        </Link>
                                        <button
                                            v-if="canEdit"
                                            @click="openEditModal(service)"
                                        >
                                            <EditIcon class="text-yellow-500" />
                                        </button>
                                        <button
                                            v-if="canDelete"
                                            @click="openConfirmModal(service)"
                                        >
                                            <TrashIcon class="text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <template v-else>
                            <tr>
                                <td
                                    colspan="8"
                                    class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                                >
                                    Tidak ada servis ditemukan.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <Modal
                    :show="isModalOpen"
                    :title="
                        isEditMode
                            ? `Edit ${selectedItem?.name}`
                            : 'Tambah Data Service'
                    "
                    confirmText="Simpan"
                    maxWidth="xl"
                    @close="closeModal"
                    @confirm="saveService"
                >
                    <div class="space-y-3">
                        <div
                            class="font-semibold text-gray-900 dark:text-white"
                        >
                            Detail Kendaraan
                        </div>
                        <div class="flex gap-2 justify-between">
                            <div class="space-y-1 text-sm w-[50%]">
                                <label
                                    for="branch_id"
                                    class="text-gray-900 dark:text-white"
                                    >Pilih Cabang</label
                                >
                                <SearchableSelect
                                    v-model="form.branch_id"
                                    :options="branches"
                                    placeholder="Pilih kantor cabang"
                                    label-key="name"
                                    track-by-key="id"
                                    :disabled="!isSuperadmin"
                                    :allow-empty="true"
                                />
                                <div
                                    v-if="form.errors.branch_id"
                                    class="text-sm text-red-500"
                                >
                                    {{ form.errors.branch_id }}
                                </div>
                            </div>
                            <div class="space-y-1 text-sm w-[50%]">
                                <label
                                    for="vehicle_id"
                                    class="text-gray-900 dark:text-white"
                                    >Pilih Kendaraan<span class="text-red-500"
                                        >*</span
                                    ></label
                                >
                                <SearchableSelect
                                    v-model="form.vehicle_id"
                                    :options="filteredVehicles"
                                    placeholder="Pilih kendaraan"
                                    label-key="display_name"
                                    track-by-key="id"
                                    :allow-empty="true"
                                />
                            </div>
                        </div>
                        <div
                            v-if="form.errors.vehicle_id"
                            class="text-sm text-red-500 text-end"
                        >
                            {{ form.errors.vehicle_id[0] }}
                        </div>
                        <div
                            class="font-semibold text-gray-900 dark:text-white"
                        >
                            Detail Service
                        </div>
                        <div class="flex gap-2 justify-between">
                            <div class="space-y-1 text-sm w-[50%]">
                                <label
                                    for="date"
                                    class="text-gray-900 dark:text-white"
                                    >Tanggal Service<span class="text-red-500"
                                        >*</span
                                    ></label
                                >
                                <input
                                    id="date"
                                    type="date"
                                    v-model="form.date"
                                    class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                />
                                <div
                                    v-if="form.errors.date"
                                    class="text-sm text-red-500"
                                >
                                    {{ form.errors.date[0] }}
                                </div>
                            </div>
                            <div class="space-y-1 text-sm w-[50%]">
                                <label
                                    for="distance"
                                    class="text-gray-900 dark:text-white"
                                    >Jarak Tempuh</label
                                >
                                <div class="flex rounded-lg shadow-sm">
                                    <input
                                        id="distance"
                                        type="number"
                                        v-model="form.distance"
                                        class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-l-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                        placeholder="Jarak"
                                    />
                                    <span
                                        class="inline-flex items-center px-2 text-sm text-gray-600 bg-gray-100 rounded-r-lg border border-l-0 border-gray-300"
                                    >
                                        KM
                                    </span>
                                </div>
                                <div
                                    v-if="form.errors.distance"
                                    class="text-sm text-red-500"
                                >
                                    {{ form.errors.distance[0] }}
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 justify-between">
                            <div class="space-y-1 text-sm w-[50%]">
                                <TagInput
                                    id="category_name"
                                    label="Kategori"
                                    :suggestions="categories"
                                    v-model="form.category_name"
                                    placeholder="Tambah kategori..."
                                    :required="true"
                                    displayKey="name"
                                    :error="
                                        form.errors.category_name
                                            ? form.errors.category_name[0]
                                            : ''
                                    "
                                />
                            </div>
                            <div class="space-y-1 text-sm w-[50%]">
                                <TagInput
                                    id="sub_category_name"
                                    label="Sub Kategori"
                                    :suggestions="subCategories"
                                    v-model="form.sub_category_name"
                                    placeholder="Tambah sub kategori..."
                                    :required="true"
                                    displayKey="name"
                                    :error="
                                        form.errors.sub_category_name
                                            ? form.errors.sub_category_name[0]
                                            : ''
                                    "
                                />
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="note"
                                class="text-gray-900 dark:text-white"
                                >Catatan Service<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <textarea
                                id="note"
                                v-model="form.note"
                                rows="2"
                                class="px-4 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-300 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                placeholder="Masukkan deskripsi checklist"
                            ></textarea>
                            <div
                                v-if="form.errors.note"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.note[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="attachment"
                                class="text-gray-900 dark:text-white"
                                >Lampiran / Bukti Service<span
                                    class="text-red-500"
                                    >*</span
                                ></label
                            >

                            <!-- Preview gambar yang sudah ada (hanya tampil saat edit) -->
                            <div
                                v-if="
                                    isEditMode && existingAttachments.length > 0
                                "
                                class="mb-4"
                            >
                                <p
                                    class="mb-2 text-sm text-gray-600 dark:text-gray-400"
                                >
                                    Gambar yang sudah diupload:
                                </p>
                                <div class="grid grid-cols-3 gap-3">
                                    <div
                                        v-for="attachment in existingAttachments"
                                        :key="attachment.id"
                                        class="relative group"
                                    >
                                        <img
                                            :src="getAttachmentUrl(attachment)"
                                            :alt="`Attachment ${attachment.id}`"
                                            class="object-cover w-full h-32 rounded-lg border border-gray-300 dark:border-gray-600"
                                            @error="handleImageError"
                                        />
                                        <button
                                            type="button"
                                            @click="
                                                removeExistingAttachment(
                                                    attachment.id,
                                                )
                                            "
                                            class="absolute top-1 right-1 p-1 text-white bg-red-500 rounded-full opacity-0 transition-opacity group-hover:opacity-100 hover:bg-red-600"
                                            title="Hapus gambar"
                                        >
                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                ></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-bind="getRootProps()"
                                class="p-4 text-center text-blue-500 bg-sky-400 bg-opacity-5 rounded-lg border-2 border-blue-300 cursor-pointer border-1"
                            >
                                <input
                                    id="attachment"
                                    v-bind="getInputProps()"
                                />
                                <div
                                    class="flex gap-8 items-center justify-stretch"
                                >
                                    <UploadIcon
                                        class="w-12 h-12 text-blue-300"
                                    />
                                    <div class="flex flex-col text-start">
                                        <p v-if="isDragActive">
                                            Lepaskan file di sini ...
                                        </p>
                                        <p v-else>
                                            Drag & drop file di sini, atau klik
                                            untuk pilih
                                        </p>
                                        <ul class="mt-2 text-sm text-gray-600">
                                            <li
                                                v-for="file in form.files"
                                                :key="file.name"
                                            >
                                                {{ file.name }} ({{
                                                    Math.round(file.size / 1024)
                                                }}
                                                KB)
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-if="form.errors.files"
                                class="mt-2 text-sm font-medium text-red-600 dark:text-red-400"
                            >
                                {{
                                    Array.isArray(form.errors.files)
                                        ? form.errors.files[0]
                                        : form.errors.files
                                }}
                            </div>
                            <div
                                v-if="attachmentError"
                                class="mt-2 text-sm font-medium text-red-600 dark:text-red-400"
                            >
                                {{ attachmentError }}
                            </div>
                        </div>
                    </div>
                </Modal>
                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus Data Service"
                    confirmText="Ya, Hapus!"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>

            <Pagination
                v-if="vehicleServices.data && vehicleServices.data.length > 0"
                :pagination="vehicleServices"
            />
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import SearchIcon from "@/Components/icons/SearchIcon.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import UploadIcon from "@/Components/icons/UploadIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import Select from "@/Components/form/Select.vue";
import TagInput from "@/Components/form/TagInput.vue";
import SearchBar from "@/Components/layout/header/SearchBar.vue";
import SelectMultiple from "@/Components/form/SelectMultiple.vue";
import SearchableSelect from "@/Components/common/SearchableSelect.vue";
import { ref, watch, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { useDropzone } from "vue3-dropzone";
import { useAuth } from "@/Composables/useAuth";

const { can, is, user } = useAuth();

const canView = computed(() => can("vehicle_services.view"));
const canCreate = computed(() => can("vehicle_services.create"));
const canDelete = computed(() => can("vehicle_services.delete"));
const canEdit = computed(() => can("vehicle_services.edit"));

const isSuperadmin = computed(() => is("Superadmin"));

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [{ label: "Kendaraan" }, { label: "Riwayat Service" }];

const props = defineProps({
    vehicleServices: Object,
    vehicles: { type: Array, default: () => [] },
    branches: { type: Array, default: () => [] },

    categories: { type: Array, default: () => [] },
    subCategories: { type: Array, default: () => [] },
    search: String,
    sortBy: String,
    sortDirection: String,
});

function formatTime(time) {
    const date = new Date(time);
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function formatRupiah(value) {
    if (!value) return "Rp 0";
    return "Rp " + Number(value).toLocaleString("id-ID");
}

function fetchVehicleServices({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    router.get(
        route("vehicle-services.index"),
        {
            search: search.value,
            sortBy,
            sortDirection,
            date_from: dateFilter.value.date_from || null,
            date_to: dateFilter.value.date_to || null,
            vehicle_name: vehicleNameFilter.value || null,
            license_code: licenseFilter.value || null,
            category_name: categoryNameFilter.value || null,
            sub_category_name: subCategoryNameFilter.value || null,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}

const search = ref(props.search || "");
const vehicleNameFilter = ref("");
const licenseFilter = ref("");
const categoryNameFilter = ref([]);
const subCategoryNameFilter = ref([]);

// Date filter state
const dateFilter = ref({
    date_from: "",
    date_to: "",
});

// Apply date filter
function applyDateFilter() {
    fetchVehicleServices();
}

// Clear date filter
function clearFilter() {
    vehicleNameFilter.value = "";
    licenseFilter.value = "";
    categoryNameFilter.value = [];
    subCategoryNameFilter.value = [];
    dateFilter.value = {
        date_from: "",
        date_to: "",
    };
    fetchVehicleServices();
}

let timeout = null;
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        fetchVehicleServices();
    }, 400);
});

// Debounce filter nama kendaraan dan plat nomor
watch(
    [
        vehicleNameFilter,
        licenseFilter,
        categoryNameFilter,
        subCategoryNameFilter,
    ],
    () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            fetchVehicleServices();
        }, 400);
    },
);

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchVehicleServices({ sortBy: column, sortDirection: direction });
}

const isModalOpen = ref(false);
const isEditMode = ref(false);
const existingAttachments = ref([]);
const attachmentError = ref("");
const vehicleSearch = ref("");

// Initialize branch_id: if not Superadmin, use user's branch_id
const getInitialBranchId = () => {
    if (!isSuperadmin.value && user.value?.employee?.branch_id) {
        return user.value.employee.branch_id;
    }
    return "";
};

const form = useForm({
    id: null,
    branch_id: getInitialBranchId(),
    vehicle_id: "",
    note: "",
    date: "",
    distance: "",

    category_name: [],
    sub_category_name: [],
    cost: "",
    files: [],
    deleted_attachments: [],
});

const filteredVehicles = computed(() => {
    // cast ke number supaya cocok dengan branch_id dari backend
    const branchId = form.branch_id ? Number(form.branch_id) : null;

    let list = props.vehicles || [];
    if (branchId) {
        list = list.filter((v) => Number(v.branch_id) === branchId);
    }

    const q = (vehicleSearch.value || "").toLowerCase();
    if (!q) {
        // Add display_name for SearchableSelect
        return list.map((v) => ({
            ...v,
            display_name: `${v.license_code} - ${v.type?.name || ""}`,
        }));
    }

    return list
        .filter((v) => {
            const license = (v.license_code || "").toLowerCase();
            const typeName = (v.type?.name || "").toLowerCase();
            return license.includes(q) || typeName.includes(q);
        })
        .map((v) => ({
            ...v,
            display_name: `${v.license_code} - ${v.type?.name || ""}`,
        }));
});

// Watch branch_id to prevent changes if user is not Superadmin
watch(
    () => form.branch_id,
    (newVal) => {
        if (!isSuperadmin.value && user.value?.employee?.branch_id) {
            // Force branch_id to user's branch_id if not Superadmin
            if (newVal !== user.value.employee.branch_id) {
                form.branch_id = user.value.employee.branch_id;
            }
        }
    },
);

// Jika cabang diganti, otomatis pilih kendaraan pertama pada cabang tersebut
watch(
    () => form.branch_id,
    (branchId) => {
        const idNum = branchId ? Number(branchId) : null;
        if (!idNum) {
            form.vehicle_id = "";
            return;
        }

        const first = props.vehicles.find((v) => Number(v.branch_id) === idNum);
        form.vehicle_id = first ? first.id : "";
    },
);

watch(
    () => form.vehicle_id,
    (vehicleId) => {
        const vehicle = props.vehicles.find((v) => v.id === vehicleId);
        if (vehicle) {
            form.branch_id = vehicle.branch_id;
        }
    },
);

function onDrop(acceptedFiles, fileRejections) {
    form.files = acceptedFiles;
    // Clear error saat file diupload
    if (acceptedFiles.length > 0) {
        attachmentError.value = "";
    }

    fileRejections.forEach((reject) => {
        reject.errors.forEach((err) => {
            if (err.code === "file-too-large") {
                attachmentError.value = `File ${reject.file.name} terlalu besar!`;
            } else if (err.code === "file-invalid-type") {
                attachmentError.value = `Tipe file ${reject.file.name} tidak diperbolehkan!`;
            } else if (err.code === "too-many-files") {
                attachmentError.value = `Maksimal hanya 5 file yang diperbolehkan!`;
            } else {
                console.warn(
                    "Unhandled dropzone error:",
                    err.code,
                    reject.file,
                );
            }
        });
    });
}

const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    multiple: true,
    accept: "image/jpeg, image/png",
    maxFiles: 5,
    maxSize: 5 * 1024 * 1024, // maksimal 5 MB
});

// Buka modal untuk tambah
function openAddModal() {
    form.reset();
    // Set branch_id to user's branch if not Superadmin
    if (!isSuperadmin.value && user.value?.employee?.branch_id) {
        form.branch_id = user.value.employee.branch_id;
    }
    form.deleted_attachments = [];
    existingAttachments.value = [];
    attachmentError.value = "";
    isEditMode.value = false;
    isModalOpen.value = true;
}

// Buka modal untuk edit
function openEditModal(vehicle) {
    form.id = vehicle.id;
    form.vehicle_id = vehicle.vehicle_id;
    form.note = vehicle.note;
    form.distance = vehicle.distance;
    form.date = vehicle.date;

    form.category_name = vehicle.category_name || [];
    form.sub_category_name = vehicle.sub_category_name || [];
    form.cost = vehicle.cost;
    // Set branch_id: if not Superadmin, use user's branch_id
    if (!isSuperadmin.value && user.value?.employee?.branch_id) {
        form.branch_id = user.value.employee.branch_id;
    } else {
        // Get branch_id from vehicle
        const vehicleData = props.vehicles.find(
            (v) => v.id === vehicle.vehicle_id,
        );
        form.branch_id = vehicleData?.branch_id || "";
    }
    form.files = [];
    form.deleted_attachments = [];
    attachmentError.value = "";

    // Set existing attachments untuk preview
    existingAttachments.value = vehicle.attachments || [];

    selectedItem.value = vehicle;
    isEditMode.value = true;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    isEditMode.value = false;
    selectedItem.value = null;
    existingAttachments.value = [];
    attachmentError.value = "";
    form.reset();
    form.deleted_attachments = [];
    form.clearErrors();
}

// Hapus existing attachment dari preview
function removeExistingAttachment(attachmentId) {
    existingAttachments.value = existingAttachments.value.filter(
        (att) => att.id !== attachmentId,
    );
    // Tambahkan ID ke array deleted_attachments untuk dikirim ke backend
    if (!form.deleted_attachments.includes(attachmentId)) {
        form.deleted_attachments.push(attachmentId);
    }
    // Clear error saat menghapus attachment (user mungkin akan upload file baru)
    attachmentError.value = "";
}

// Get attachment URL
function getAttachmentUrl(attachment) {
    if (attachment.url) {
        return attachment.url.startsWith("http") ||
            attachment.url.startsWith("/")
            ? attachment.url
            : `/storage/${attachment.url}`;
    }
    return `/storage/${attachment.file_path}`;
}

// Handle error saat load gambar
function handleImageError(event) {
    event.target.src = "/images/placeholder.png"; // Fallback image
}

// Simpan (otomatis create/update)
function saveService() {
    // Reset error
    attachmentError.value = "";

    if (isEditMode.value) {
        // Validasi: minimal harus ada 1 attachment (existing yang tidak dihapus atau file baru)
        const remainingAttachments = existingAttachments.value.length;
        const newFilesCount = form.files.length;

        if (remainingAttachments === 0 && newFilesCount === 0) {
            attachmentError.value =
                "Minimal harus ada 1 lampiran. Silakan tambahkan file baru atau batalkan penghapusan gambar yang ada.";
            return;
        }

        const data = {
            ...form.data(),
            _method: "PUT",
            deleted_attachments: form.deleted_attachments,
        };
        if (form.files.length > 0) {
            data.files = form.files;
        }
        form.transform(() => data).post(
            route("vehicle-services.update", form.id),
            {
                onSuccess: closeModal,
            },
        );
    } else {
        // Validasi untuk create: harus ada file
        if (form.files.length === 0) {
            attachmentError.value = "Lampiran / Bukti Service wajib diisi.";
            return;
        }

        form.post(route("vehicle-services.store"), {
            onSuccess: closeModal,
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
    router.delete(route("vehicle-services.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
            fetchVehicleServices();
        },
        preserveScroll: true,
    });
};
</script>
