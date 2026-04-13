<template>
    <Head title="Daftar Pengguna" />

    <div class="flex flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
            <button
                v-if="can('users.create')"
                @click="openAddModal"
                type="button"
                class="flex gap-2 items-center px-3 py-2 text-white bg-blue-500 rounded hover:bg-blue-600"
            >
                <PlusSquareIcon />
                <span class="hidden text-sm md:block">Tambah Pengguna</span>
            </button>
        </div>

        <div
            class="h-[90%] grid-cols-12 gap-4 md:gap-6 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col gap-2 px-8 py-1 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Daftar Pengguna
                </div>

                <div class="flex flex-wrap gap-3 items-center">
                    <!-- Branch Filter -->
                    <select
                        v-model="branchFilter"
                        class="px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                    >
                        <option value="">Semua Cabang</option>
                        <option
                            v-for="branch in filteredBranches"
                            :key="branch.id"
                            :value="branch.id"
                        >
                            {{ branch.name }}
                        </option>
                    </select>

                    <!-- Position Filter -->
                    <select
                        v-model="positionFilter"
                        class="px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                    >
                        <option value="">Semua Jabatan</option>
                        <option
                            v-for="position in filteredPositions"
                            :key="position.id"
                            :value="position.id"
                        >
                            {{ position.name }}
                        </option>
                    </select>

                    <!-- Role Filter -->
                    <select
                        v-model="roleFilter"
                        class="px-3 h-10 text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                    >
                        <option value="">Semua Peran</option>
                        <option
                            v-for="role in props.roles"
                            :key="role.id"
                            :value="role.name"
                        >
                            {{ role.name }}
                        </option>
                    </select>
                    <div class="relative py-2">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari pengguna"
                            class="h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-400 dark:focus:border-blue-800 xl:w-[200px]"
                        />
                    </div>
                </div>
            </div>

            <!-- Status Filter Tabs -->
            <div class="px-8 border-b border-gray-200 dark:border-gray-600">
                <nav class="flex gap-4 -mb-px">
                    <button
                        @click="statusFilter = 'all'"
                        :class="[
                            'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                            statusFilter === 'all'
                                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                        ]"
                    >
                        Semua
                        <span
                            class="px-2 py-0.5 ml-2 text-xs text-blue-600 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300"
                        >
                            {{ getStatusCount('all') }}
                        </span>
                    </button>
                    <button
                        @click="statusFilter = 'active'"
                        :class="[
                            'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                            statusFilter === 'active'
                                ? 'border-green-500 text-green-600 dark:text-green-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                        ]"
                    >
                        Aktif
                        <span
                            class="px-2 py-0.5 ml-2 text-xs text-green-600 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-300"
                        >
                            {{ getStatusCount('active') }}
                        </span>
                    </button>
                    <button
                        @click="statusFilter = 'pending'"
                        :class="[
                            'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                            statusFilter === 'pending'
                                ? 'border-yellow-500 text-yellow-600 dark:text-yellow-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                        ]"
                    >
                        Menunggu Verifikasi
                        <span
                            class="px-2 py-0.5 ml-2 text-xs text-yellow-600 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-300"
                        >
                            {{ getStatusCount('pending') }}
                        </span>
                    </button>
                    <button
                        @click="statusFilter = 'inactive'"
                        :class="[
                            'px-4 py-2 text-sm font-medium border-b-2 transition-colors',
                            statusFilter === 'inactive'
                                ? 'border-red-500 text-red-600 dark:text-red-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                        ]"
                    >
                        Tidak Aktif
                        <span
                            class="px-2 py-0.5 ml-2 text-xs text-red-600 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300"
                        >
                            {{ getStatusCount('inactive') }}
                        </span>
                    </button>
                </nav>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        No.
                                    </p>
                                </div>
                            </th>
                            <th
                                @click="changeSort('name')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Nama & Email Pengguna
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
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex justify-center items-center px-3">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Cabang
                                    </p>
                                </div>
                            </th>
                            <th
                                @click="changeSort('username')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Username
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'username' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'username' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('role')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Peran & Akses
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'role' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'role' &&
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
                                <div class="flex justify-center items-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Status
                                    </p>
                                </div>
                            </th>
                            <th
                                @click="changeSort('updated_at')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Terakhir Dilihat
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'updated_at' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'updated_at' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                v-if="can('users.edit') || can('users.delete')"
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
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
                    <tbody>
                        <template v-if="users.data && users.data.length > 0">
                            <tr
                                v-for="(user, index) in users.data"
                                :key="user.id"
                                class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex justify-center items-center whitespace-nowrap"
                                    >
                                        <p
                                            class="px-3 text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                (users.current_page - 1) *
                                                    users.per_page +
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
                                                user.profile_photo_path
                                                    ? `/storage/${user.profile_photo_path}`
                                                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                          user.name
                                                      )}&background=3b82f6&color=fff`
                                            "
                                            alt="User profile"
                                            loading="lazy"
                                        />
                                        <div
                                            class="flex flex-col leading-tight"
                                        >
                                            <p
                                                class="font-medium text-gray-800 dark:text-white/90"
                                            >
                                                {{ user.name }}
                                            </p>
                                            <span
                                                class="text-gray-500 dark:text-gray-400"
                                            >
                                                {{ user.email }}
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
                                            {{ user.employee?.branch?.name || '-' }}
                                        </p>
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
                                            {{ user.username }}
                                        </p>
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
                                            {{ user.roles[0]?.name || '-' }}
                                        </p>
                                    </div>
                                </td>
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex justify-center items-center px-3 whitespace-nowrap"
                                    >
                                        <span
                                            :class="[
                                                'px-3 py-1 rounded-full text-xs font-medium',
                                                user.status === 'active'
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                                                    : user.status ===
                                                      'pending'
                                                    ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'
                                                    : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                            ]"
                                        >
                                            {{
                                                user.status === "active"
                                                    ? "Aktif"
                                                    : user.status ===
                                                      "pending"
                                                    ? "Menunggu Verifikasi"
                                                    : "Tidak Aktif"
                                            }}
                                        </span>
                                    </div>
                                </td>
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex justify-center items-center px-3 text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        <span
                                            v-if="
                                                user.last_seen &&
                                                isOnline(user.last_seen)
                                            "
                                            class="px-3 py-1 text-teal-500 bg-teal-200 rounded-full dark:bg-teal-400 dark:text-teal-100"
                                        >
                                            Online
                                        </span>

                                        <span v-else-if="user.last_seen">
                                            {{ formatTime(user.last_seen) }}
                                        </span>

                                        <span v-else class="text-gray-400">
                                            Tidak pernah terlihat
                                        </span>
                                    </div>
                                </td>
                                <td
                                    v-if="
                                        can('users.edit') ||
                                        can('users.delete')
                                    "
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex gap-3 justify-center px-4 whitespace-nowrap sm:px-0"
                                    >
                                        <!-- Tombol Verifikasi untuk user pending -->
                                        <button
                                            v-if="user.status === 'pending' && can('users.edit')"
                                            @click.stop="openVerificationModal(user)"
                                            class="text-green-500 hover:text-green-600"
                                            title="Verifikasi"
                                        >
                                            <CheckCircleIcon />
                                        </button>
                                        <button
                                            v-if="can('users.edit')"
                                            @click.stop="openEditModal(user)"
                                            class="text-yellow-500 hover:text-yellow-600"
                                            title="Edit"
                                        >
                                            <EditIcon />
                                        </button>
                                        <button
                                            v-if="can('users.delete')"
                                            @click.stop="openConfirmModal(user)"
                                            class="text-red-500 hover:text-red-600"
                                            title="Hapus"
                                        >
                                            <TrashIcon />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-else>
                            <td
                                :colspan="
                                    can('users.edit') || can('users.delete')
                                        ? 8
                                        : 7
                                "
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600"
                            >
                                Tidak ada pengguna ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>

                <Modal
                    :show="isModalOpen"
                    :title="
                        isEditMode
                            ? `Edit ${selectedItem?.name}`
                            : 'Tambah Pengguna Baru'
                    "
                    confirmText="Simpan"
                    maxWidth="lg"
                    @close="closeModal"
                    @confirm="saveUser"
                >
                    <div class="space-y-3">
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="text-gray-900 dark:text-white"
                                >Nama Lengkap</label
                            >
                            <input
                                id="name"
                                class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="Masukkan nama lengkap pengguna"
                            />
                            <div
                                v-if="form.errors.name"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.name }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="username"
                                class="text-gray-900 dark:text-white"
                                >Nama Pengguna</label
                            >
                            <input
                                id="username"
                                class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.username"
                                required
                                placeholder="Masukkan username"
                            />
                            <div
                                v-if="form.errors.username"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.username }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="email"
                                class="text-gray-900 dark:text-white"
                                >Email</label
                            >
                            <input
                                id="email"
                                v-model="form.email"
                                class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="email"
                                placeholder="Masukkan email pengguna"
                            />
                            <div
                                v-if="form.errors.email"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.email }}
                            </div>
                        </div>

                        <div class="space-y-1 text-sm">
                            <label
                                for="password"
                                class="text-gray-900 dark:text-white"
                            >
                                Kata Sandi
                                <span v-if="!isEditMode" class="text-red-500">
                                    *
                                </span>
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 pr-10"
                                    :type="showPassword ? 'text' : 'password'"
                                    :placeholder="isEditMode ? 'Kosongkan jika tidak ingin mengubah kata sandi' : 'Masukkan kata sandi pengguna'"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                >
                                    <svg
                                        v-if="showPassword"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m13.42 13.42l-3.29-3.29M3 3l18 18"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <div
                                v-if="form.errors.password"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.password }}
                            </div>
                        </div>

                        <div class="space-y-1 text-sm">
                            <label
                                for="password_confirmation"
                                class="text-gray-900 dark:text-white"
                            >
                                Konfirmasi Kata Sandi
                                <span v-if="!isEditMode" class="text-red-500">
                                    *
                                </span>
                            </label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500 pr-10"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    :placeholder="isEditMode ? 'Ulangi kata sandi baru (jika diubah)' : 'Ulangi kata sandi pengguna'"
                                />
                                <button
                                    type="button"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                >
                                    <svg
                                        v-if="showPasswordConfirmation"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.29 3.29m13.42 13.42l-3.29-3.29M3 3l18 18"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="w-5 h-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <div
                                v-if="form.errors.password_confirmation"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.password_confirmation }}
                            </div>
                        </div>

                        <div class="space-y-1 text-sm">
                            <label
                                for="role"
                                class="text-gray-900 dark:text-white"
                                >Peran</label
                            >
                            <select
                                id="role"
                                v-model="form.role"
                                class="block p-2.5 w-full text-sm text-gray-600 rounded-lg border-gray-400 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            >
                                <option selected hidden value="">
                                    Pilih peran pengguna
                                </option>
                                <option
                                    v-for="role in props.roles"
                                    :key="role.name"
                                    :value="role.name"
                                >
                                    {{ role.name }}
                                </option>
                            </select>
                            <div
                                v-if="form.errors.role"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.role }}
                            </div>
                        </div>

                        <div class="space-y-1 text-sm">
                            <label
                                for="status"
                                class="text-gray-900 dark:text-white"
                                >Status</label
                            >
                            <select
                                id="status"
                                v-model="form.status"
                                class="block p-2.5 w-full text-sm text-gray-600 rounded-lg border-gray-400 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            >
                                <option value="active">Aktif</option>
                                <option value="pending">
                                    Menunggu Verifikasi
                                </option>
                                <option value="inactive">Tidak Aktif</option>
                            </select>
                            <div
                                v-if="form.errors.status"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.status }}
                            </div>
                        </div>
                    </div>
                </Modal>

                <!-- Verification Modal -->
                <Modal
                    :show="isVerificationModalOpen"
                    title="Verifikasi & Lengkapi Data Karyawan"
                    confirmText="Simpan & Verifikasi"
                    maxWidth="full"
                    @close="closeVerificationModal"
                    @confirm="submitVerification"
                >
                    <div class="space-y-6">
                        <!-- Basic Info -->
                        <div>
                            <h3 class="text-md font-semibold text-gray-800 dark:text-white mb-3">Informasi Dasar</h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nama Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="verificationForm.name"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Masukkan nama lengkap karyawan"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Username <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="verificationForm.username"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Masukkan username"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        v-model="verificationForm.email"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Masukkan email"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        v-model="verificationForm.password"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="******"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Penempatan Cabang & Informasi Tambahan -->
                        <div>
                            <h3 class="text-md font-semibold text-gray-800 dark:text-white mb-3">Informasi Tambahan</h3>
                            <!-- Row 1: Cabang, Departemen -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Penempatan Cabang <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="verificationForm.branch_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    >
                                        <option :value="null">Pilih cabang</option>
                                        <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                            {{ branch.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Departemen <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="verificationForm.department_id"
                                        required
                                        :disabled="!verificationForm.branch_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <option :value="null">{{ verificationForm.branch_id ? 'Pilih departemen' : 'Pilih cabang terlebih dahulu' }}</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                            {{ dept.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 2: Jabatan, Position -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Jabatan <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="verificationForm.jabatan_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    >
                                        <option :value="null">Pilih Jabatan</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">
                                            {{ role.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Position
                                    </label>
                                    <FormSelect
                                        v-model="verificationForm.position_id"
                                        label="Pilih Position (opsional)"
                                        :items="positionItems"
                                        :taggable="true"
                                        labelKey="name"
                                        searchKey="name"
                                        @update:modelValue="handlePositionChange"
                                    />
                                    <div
                                        v-if="creatingPosition"
                                        class="mt-1 text-xs text-blue-600"
                                    >
                                        Menyimpan position baru...
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Jatah Cuti, Gaji Pokok, Shift -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Jatah Cuti (hari/tahun) <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        v-model="verificationForm.leave_quota_per_year"
                                        required
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="12"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Jatah Piutang (Rp)
                                    </label>
                                    <input
                                        type="number"
                                        v-model="verificationForm.loan_quota"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Masukkan jatah piutang (opsional)"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Gaji Pokok
                                    </label>
                                    <input
                                        type="number"
                                        v-model="verificationForm.salary"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Masukkan Gaji Pokok (opsional)"
                                    />
                                </div>
                            </div>

                            <!-- Row 4: Shift, Tanggal Mulai Bekerja -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Shift Kerja <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="verificationForm.shift_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    >
                                        <option :value="null">Pilih Shift Kerja</option>
                                        <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                                            {{ shift.name }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tanggal Mulai Bekerja <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        v-model="verificationForm.working_start_date"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                            </div>

                            <!-- Row 5: Upload Files -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Upload Foto Karyawan <span class="text-red-500">*</span>
                                    </label>

                                    <!-- Preview existing photo -->
                                    <div v-if="selectedItem?.profile_photo_url && !verificationForm.photo" class="mb-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                        <div class="flex items-center gap-3">
                                            <img :src="selectedItem.profile_photo_url" alt="Current photo" class="w-16 h-16 rounded-lg object-cover" />
                                            <div class="flex-1">
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Foto saat ini tersimpan</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-500">Upload foto baru untuk menggantinya</p>
                                            </div>
                                        </div>
                                    </div>

                                    <input
                                        type="file"
                                        @change="verificationForm.photo = $event.target.files[0]"
                                        accept="image/*"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                    <p v-if="selectedItem?.profile_photo_url && !verificationForm.photo" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Opsional - Biarkan kosong jika tidak ingin mengubah foto
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Upload Tanda Tangan <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="file"
                                        @change="verificationForm.signature = $event.target.files[0]"
                                        accept="image/*"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Identitas & Kontak -->
                        <div>
                            <h3 class="text-md font-semibold text-gray-800 dark:text-white mb-3">Identitas & Kontak</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        No. Telepon
                                    </label>
                                    <input
                                        type="text"
                                        v-model="verificationForm.contact"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Masukkan nomor telepon"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Jenis Kelamin <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-4 items-center px-3 py-2 border border-gray-300 rounded-lg dark:border-gray-600">
                                        <label class="inline-flex items-center">
                                            <input
                                                type="radio"
                                                v-model="verificationForm.gender"
                                                value="Laki-laki"
                                                class="mr-2"
                                            />
                                            <span class="text-gray-700 dark:text-gray-300">Laki-laki</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input
                                                type="radio"
                                                v-model="verificationForm.gender"
                                                value="Perempuan"
                                                class="mr-2"
                                            />
                                            <span class="text-gray-700 dark:text-gray-300">Perempuan</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Status Kerja <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-2 items-center px-3 py-2 border border-gray-300 rounded-lg dark:border-gray-600">
                                        <label class="inline-flex items-center">
                                            <input
                                                type="radio"
                                                v-model="verificationForm.status"
                                                value="Tetap"
                                                class="mr-2"
                                            />
                                            <span class="text-gray-700 dark:text-gray-300">Tetap</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input
                                                type="radio"
                                                v-model="verificationForm.status"
                                                value="Kontrak"
                                                class="mr-2"
                                            />
                                            <span class="text-gray-700 dark:text-gray-300">Kontrak</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input
                                                type="radio"
                                                v-model="verificationForm.status"
                                                value="Magang"
                                                class="mr-2"
                                            />
                                            <span class="text-gray-700 dark:text-gray-300">Magang</span>
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tempat Lahir
                                    </label>
                                    <input
                                        type="text"
                                        v-model="verificationForm.birthplace"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Masukkan tempat lahir"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Agama
                                    </label>
                                    <select
                                        v-model="verificationForm.religion"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    >
                                        <option value="">Pilih Agama</option>
                                        <option value="Islam">Islam</option>
                                        <option value="Kristen">Kristen</option>
                                        <option value="Katolik">Katolik</option>
                                        <option value="Hindu">Hindu</option>
                                        <option value="Buddha">Buddha</option>
                                        <option value="Konghucu">Konghucu</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tanggal Lahir <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        v-model="verificationForm.birthdate"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                                <div class="md:col-span-3">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Alamat
                                    </label>
                                    <textarea
                                        v-model="verificationForm.address"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Masukkan alamat lengkap"
                                    ></textarea>
                                </div>
                                <!-- Provinsi, Kota, Kecamatan, Kelurahan -->
                                <div class="md:col-span-3">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Provinsi
                                            </label>
                                            <select
                                                v-model="verificationForm.provinsi"
                                                @change="onProvinsiChange"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            >
                                                <option value="">Pilih Provinsi</option>
                                                <option v-for="prov in provinsiOptions" :key="prov.id" :value="prov.name">
                                                    {{ prov.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Kota
                                            </label>
                                            <select
                                                v-model="verificationForm.kota"
                                                @change="onKotaChange"
                                                :disabled="!verificationForm.provinsi"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <option value="">{{ verificationForm.provinsi ? 'Pilih Kota' : 'Pilih Provinsi terlebih dahulu' }}</option>
                                                <option v-for="kota in kotaOptions" :key="kota.id" :value="kota.name">
                                                    {{ kota.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Kecamatan
                                            </label>
                                            <select
                                                v-model="verificationForm.kecamatan"
                                                @change="onKecamatanChange"
                                                :disabled="!verificationForm.kota"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <option value="">{{ verificationForm.kota ? 'Pilih Kecamatan' : 'Pilih Kota terlebih dahulu' }}</option>
                                                <option v-for="kec in kecamatanOptions" :key="kec.id" :value="kec.name">
                                                    {{ kec.name }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Kelurahan
                                            </label>
                                            <select
                                                v-model="verificationForm.kelurahan"
                                                :disabled="!verificationForm.kecamatan"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                            >
                                                <option value="">{{ verificationForm.kecamatan ? 'Pilih Kelurahan' : 'Pilih Kecamatan terlebih dahulu' }}</option>
                                                <option v-for="kel in kelurahanOptions" :key="kel.id" :value="kel.name">
                                                    {{ kel.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Legal & Administratif -->
                        <div>
                            <h3 class="text-md font-semibold text-gray-800 dark:text-white mb-3">Data Legal & Administratif</h3>
                            <!-- Row 1: NIK, No. KTP, BPJS Kesehatan -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        NIK
                                    </label>
                                    <input
                                        type="text"
                                        v-model="verificationForm.nik"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Masukkan NIK"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        No. KTP
                                    </label>
                                    <input
                                        type="text"
                                        v-model="verificationForm.ktp"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Masukkan no KTP"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        BPJS Kesehatan
                                    </label>
                                    <input
                                        type="text"
                                        v-model="verificationForm.bpjs_kesehatan"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="No BPJS Kesehatan"
                                    />
                                </div>
                            </div>
                            <!-- Row 2: BPJS Ketenagakerjaan, Sertifikat, Kontrak -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        BPJS Ketenagakerjaan
                                    </label>
                                    <input
                                        type="text"
                                        v-model="verificationForm.bpjs_ketenagakerjaan"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="No BPJS Ketenagakerjaan"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Sertifikat
                                    </label>
                                    <input
                                        type="text"
                                        v-model="verificationForm.certificate"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Nama/No Sertifikat (opsional)"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Kontrak
                                    </label>
                                    <input
                                        type="text"
                                        v-model="verificationForm.contract"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                        placeholder="Info kontrak (opsional)"
                                    />
                                </div>
                            </div>
                            <!-- Row 3: Upload Foto KTP, Upload BPJS Kesehatan, Upload BPJS Ketenagakerjaan -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Upload Foto KTP
                                    </label>
                                    <input
                                        type="file"
                                        @change="verificationForm.ktp_file = $event.target.files[0]"
                                        accept="image/*"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Upload Kartu BPJS Kesehatan
                                    </label>
                                    <input
                                        type="file"
                                        @change="verificationForm.bpjs_kesehatan_file = $event.target.files[0]"
                                        accept="image/*"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Upload Kartu BPJS Ketenagakerjaan
                                    </label>
                                    <input
                                        type="file"
                                        @change="verificationForm.bpjs_ketenagakerjaan_file = $event.target.files[0]"
                                        accept="image/*"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                            </div>
                            <!-- Row 4: Upload Sertifikat, Upload Kontrak, Upload Kartu Keluarga -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Upload Sertifikat (opsional)
                                    </label>
                                    <input
                                        type="file"
                                        @change="verificationForm.certificate_file = $event.target.files[0]"
                                        accept="image/*,application/pdf"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Upload Kontrak (opsional)
                                    </label>
                                    <input
                                        type="file"
                                        @change="verificationForm.contract_file = $event.target.files[0]"
                                        accept="image/*,application/pdf"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Upload Kartu Keluarga (opsional)
                                    </label>
                                    <input
                                        type="file"
                                        @change="verificationForm.kk_file = $event.target.files[0]"
                                        accept="image/*"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                            </div>
                            <!-- Row 5: Tanggal Resign - full width -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tanggal Resign (opsional)
                                    </label>
                                    <input
                                        type="date"
                                        v-model="verificationForm.resign_date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                                <!-- Empty placeholders for grid alignment -->
                                <div></div>
                                <div></div>
                            </div>
                        </div>

                        <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                            * Field yang ditandai wajib diisi
                        </div>
                    </div>
                </Modal>

                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus Pengguna"
                    confirmText="Ya, Hapus!"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>

            <Pagination
                v-if="users.data && users.data.length > 0"
                :pagination="users"
            />
        </div>
    </div>
</template>

<script setup>
import CheckIcon from "@/Components/icons/CheckIcon.vue";
import CheckCircleIcon from "@/Components/icons/CheckCircleIcon.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import SearchIcon from "@/Components/icons/SearchIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import FormSelect from "@/Components/form/SelectPemakaian.vue";
import { useAuth } from "@/Composables/useAuth";
import { ref, watch, computed, onMounted } from "vue";
import { useForm, router, Head } from "@inertiajs/vue3";

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    users: Object,
    roles: Object,
    branches: Object,
    departments: Object,
    shifts: Object,
    positions: Object,
    branchPositions: Object,
    search: String,
    role: String,
    status: String,
    branch_id: [String, Number],
    position_id: [String, Number],
    statusCounts: Object,
    sortBy: String,
    sortDirection: String,
});

const { can } = useAuth();

const breadcrumbs = [{ label: "Menu Utama" }, { label: "Pengguna" }];

// Status filter - initialize from props
const statusFilter = ref(props.status || "all");

const formatTime = (date) => {
    if (!date) return "-";
    return new Intl.DateTimeFormat("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(date));
};

const isOnline = (lastSeen) => {
    if (!lastSeen) return false;
    const now = new Date();
    const seen = new Date(lastSeen);
    const diffMinutes = (now - seen) / (1000 * 60);
    return diffMinutes < 2; // kurang dari 2 menit
};

// Get status count for badge display
const getStatusCount = (status) => {
    if (!props.statusCounts) return 0;
    return props.statusCounts[status] || 0;
};

function fetchUsers({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    router.get(
        route("users.index"),
        {
            search: search.value,
            role: roleFilter.value,
            status: statusFilter.value,
            branch_id: branchFilter.value,
            position_id: positionFilter.value,
            sortBy,
            sortDirection,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
}

const search = ref(props.search || "");
const roleFilter = ref(props.role || "");
const branchFilter = ref(props.branch_id || "");
const positionFilter = ref(props.position_id || "");

// Dependent options
const filteredPositions = computed(() => {
    if (!branchFilter.value) return props.positions;

    const validPositionIds = props.branchPositions[branchFilter.value] || [];
    return props.positions.filter(p => validPositionIds.includes(p.id));
});

const filteredBranches = computed(() => {
    if (!positionFilter.value) return props.branches;

    // Reverse lookup: branches that have this position
    const validBranchIds = Object.keys(props.branchPositions).filter(branchId => {
        return props.branchPositions[branchId].includes(Number(positionFilter.value));
    });

    return props.branches.filter(b => validBranchIds.includes(String(b.id)));
});

let timeout = null;
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        fetchUsers();
    }, 400);
});

watch(roleFilter, () => {
    fetchUsers();
});

watch(branchFilter, () => {
    // If branch is changed, we might need to reset position if it's no longer valid
    if (branchFilter.value && positionFilter.value) {
        const validPositionIds = props.branchPositions[branchFilter.value] || [];
        if (!validPositionIds.includes(Number(positionFilter.value))) {
            positionFilter.value = "";
        }
    }
    fetchUsers();
});

watch(positionFilter, () => {
    // If position is changed, we might need to reset branch if it's no longer valid
    if (positionFilter.value && branchFilter.value) {
        const validBranchIds = Object.keys(props.branchPositions).filter(branchId => {
            return props.branchPositions[branchId].includes(Number(positionFilter.value));
        });
        if (!validBranchIds.includes(String(branchFilter.value))) {
            branchFilter.value = "";
        }
    }
    fetchUsers();
});

watch(statusFilter, () => {
    // Reset to page 1 when status filter changes
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.delete('page');
    router.get(
        route("users.index"),
        {
            search: search.value,
            role: roleFilter.value,
            status: statusFilter.value,
            sortBy: props.sortBy,
            sortDirection: props.sortDirection,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
});

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchUsers({ sortBy: column, sortDirection: direction });
}

const isModalOpen = ref(false);
const isEditMode = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    id: null,
    name: "",
    username: "",
    email: "",
    role: "",
    status: "active",
    password: "",
    password_confirmation: "",
});

// Buka modal untuk tambah
function openAddModal() {
    if (!can("users.create")) {
        return;
    }
    form.reset();
    form.password = "";
    form.password_confirmation = "";
    showPassword.value = false;
    showPasswordConfirmation.value = false;
    isEditMode.value = false;
    isModalOpen.value = true;
}

const isVerificationModalOpen = ref(false);
const verificationForm = useForm({
    id: null,
    name: "",
    username: "",
    email: "",
    password: "",
    branch_id: null,
    department_id: null,
    jabatan_id: null,
    position_id: null,
    shift_id: null,
    working_start_date: "",
    leave_quota_per_year: 12,
    loan_quota: null,
    salary: null,
    contact: "",
    gender: "",
    status: "Tetap",
    birthplace: "",
    religion: "",
    birthdate: "",
    address: "",
    provinsi: "",
    kota: "",
    kecamatan: "",
    kelurahan: "",
    nik: "",
    ktp: "",
    bpjs_kesehatan: "",
    bpjs_ketenagakerjaan: "",
    certificate: "",
    contract: "",
    photo: null,
    signature: null,
    ktp_file: null,
    bpjs_kesehatan_file: null,
    bpjs_ketenagakerjaan_file: null,
    certificate_file: null,
    contract_file: null,
    kk_file: null,
    resign_date: "",
});

// Position handling
const localAddedPositions = ref([]);
const creatingPosition = ref(false);

const positionItems = computed(() => {
    return [
        ...props.positions.map(p => ({ id: p.id, name: p.name })),
        ...localAddedPositions.value
    ];
});

async function handlePositionChange(value) {
    // Check if value is a string (new position that needs to be created)
    if (typeof value === 'string' && value && isNaN(value) && value != 'unsigned') {
        // Check if this exact name already exists
        const response = await fetch(route('positions.checkExist', { name: value.trim() }));
        const data = await response.json();
        const exists = data.exists;

        if (!exists && value.trim()) {
            // This is a new position, create it
            await createNewPosition(value);
        } else {
            verificationForm.position_id = data.id;
        }
    } else {
        creatingPosition.value = false;
    }
}

async function createNewPosition(name) {
    creatingPosition.value = true;

    try {
        const response = await fetch(route('positions.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ name: name })
        });

        if (response.ok) {
            const data = await response.json();
            // Add to local added positions
            localAddedPositions.value.push({ id: data.id, name: data.name });
            // Update form value
            verificationForm.position_id = data.id;
            creatingPosition.value = false;
        } else if (response.status === 422) {
            // Validation error - position might already exist
            creatingPosition.value = false;
        } else {
            throw new Error('Failed to create position');
        }
    } catch (error) {
        console.error('Error creating position:', error);
        creatingPosition.value = false;
    }
}

// Wilayah options
const provinsiOptions = ref([]);
const kotaOptions = ref([]);
const kecamatanOptions = ref([]);
const kelurahanOptions = ref([]);

// Load provinsi on mount
const loadProvinsi = async () => {
    try {
        const response = await fetch('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
        const data = await response.json();
        provinsiOptions.value = data.map(p => ({ id: p.id, name: p.name }));
    } catch (error) {
        console.error('Error loading provinsi:', error);
    }
};

// Load kota based on provinsi
const onProvinsiChange = async () => {
    verificationForm.kota = '';
    verificationForm.kecamatan = '';
    verificationForm.kelurahan = '';
    kotaOptions.value = [];
    kecamatanOptions.value = [];
    kelurahanOptions.value = [];

    const selectedProvinsi = provinsiOptions.value.find(p => p.name === verificationForm.provinsi);
    if (selectedProvinsi) {
        try {
            const response = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProvinsi.id}.json`);
            const data = await response.json();
            kotaOptions.value = data.map(k => ({ id: k.id, name: k.name }));
        } catch (error) {
            console.error('Error loading kota:', error);
        }
    }
};

// Load kecamatan based on kota
const onKotaChange = async () => {
    verificationForm.kecamatan = '';
    verificationForm.kelurahan = '';
    kecamatanOptions.value = [];
    kelurahanOptions.value = [];

    const selectedKota = kotaOptions.value.find(k => k.name === verificationForm.kota);
    if (selectedKota) {
        try {
            const response = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${selectedKota.id}.json`);
            const data = await response.json();
            kecamatanOptions.value = data.map(kec => ({ id: kec.id, name: kec.name }));
        } catch (error) {
            console.error('Error loading kecamatan:', error);
        }
    }
};

// Load kelurahan based on kecamatan
const onKecamatanChange = async () => {
    verificationForm.kelurahan = '';
    kelurahanOptions.value = [];

    const selectedKecamatan = kecamatanOptions.value.find(kec => kec.name === verificationForm.kecamatan);
    if (selectedKecamatan) {
        try {
            const response = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${selectedKecamatan.id}.json`);
            const data = await response.json();
            kelurahanOptions.value = data.map(kel => ({ id: kel.id, name: kel.name }));
        } catch (error) {
            console.error('Error loading kelurahan:', error);
        }
    }
};

// Load initial data on mount
onMounted(() => {
    loadProvinsi();
});

function openVerificationModal(user) {
    verificationForm.name = user.name;
    verificationForm.username = user.username;
    verificationForm.email = user.email;

    // Pre-fill IDs and dates from user level (from UserResource/Controller transform)
    verificationForm.branch_id = user.branch_id || null;
    verificationForm.department_id = user.department_id || null;
    verificationForm.shift_id = user.shift_id || null;

    // Auto-fill dates if exists
    if (user.working_start_date) {
        verificationForm.working_start_date = user.working_start_date;
    }
    if (user.birthdate) {
        verificationForm.birthdate = user.birthdate;
    }

    // Pre-fill from employee if exists
    if (user.employee) {
        verificationForm.contact = user.employee.contact || '';
        verificationForm.address = user.employee.address || '';
        verificationForm.position_id = user.employee.position_id || null;
        verificationForm.gender = user.employee.gender || '';
        verificationForm.status = user.employee.status || 'Tetap';
        verificationForm.birthplace = user.employee.birthplace || '';
        verificationForm.religion = user.employee.religion || '';
        verificationForm.provinsi = user.employee.provinsi || '';
        verificationForm.kota = user.employee.kota || '';
        verificationForm.kecamatan = user.employee.kecamatan || '';
        verificationForm.kelurahan = user.employee.kelurahan || '';
        verificationForm.nik = user.employee.nik || '';
        verificationForm.ktp = user.employee.ktp || '';
        verificationForm.bpjs_kesehatan = user.employee.bpjs_kesehatan || '';
        verificationForm.bpjs_ketenagakerjaan = user.employee.bpjs_ketenagakerjaan || '';
    }

    // Set default working start date to today only if not already set
    if (!verificationForm.working_start_date) {
        verificationForm.working_start_date = new Date().toISOString().split('T')[0];
    }

    selectedItem.value = user;
    isVerificationModalOpen.value = true;
}

function closeVerificationModal() {
    isVerificationModalOpen.value = false;
    selectedItem.value = null;
    verificationForm.reset();
    verificationForm.clearErrors();
}

function submitVerification() {
    verificationForm.post(route("users.verify", verificationForm.id), {
        _method: 'PUT',
        forceFormData: true,
        onSuccess: () => {
            closeVerificationModal();
        },
        onError: (errors) => {
            console.error('Verification errors:', errors);
        },
    });
}

// Simpan (otomatis create/update)
function saveUser() {
    if (isEditMode.value) {
        if (!can("users.edit")) {
            return;
        }
        form.put(route("users.update", form.id), {
            onSuccess: closeModal,
        });
    } else {
        if (!can("users.create")) {
            return;
        }
        form.post(route("users.store"), {
            onSuccess: closeModal,
        });
    }
}

// Buka modal untuk edit
function openEditModal(user) {
    if (!can("users.edit")) {
        return;
    }
    form.id = user.id;
    form.name = user.name;
    form.username = user.username;
    form.email = user.email;
    form.role = user.roles[0].name;
    form.status = user.status || "active";
    form.password = "";
    form.password_confirmation = "";
    showPassword.value = false;
    showPasswordConfirmation.value = false;
    selectedItem.value = user;
    isEditMode.value = true;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    selectedItem.value = null;
    form.reset();
    form.clearErrors();
    showPassword.value = false;
    showPasswordConfirmation.value = false;
}

// destroy
const selectedItem = ref(null);

const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    if (!can("users.delete")) {
        return;
    }
    selectedItem.value = item;
    isConfirmModalOpen.value = true;
};
const closeConfirmModal = () => {
    selectedItem.value = null;
    isConfirmModalOpen.value = false;
};
const destroyData = () => {
    if (!can("users.delete")) {
        return;
    }
    router.delete(route("users.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        preserveScroll: true,
    });
};
</script>
