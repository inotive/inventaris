<template>
    <Head title="Data Cabang" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between">
            <Breadcrumb :items="breadcrumbs" />
            <button
                v-if="can('branches.create')"
                @click="openAddModal"
                type="button"
                class="flex items-center gap-2 px-3 py-2 text-white bg-blue-500 rounded hover:bg-blue-600"
            >
                <PlusSquareIcon />
                <span class="hidden text-sm md:block">Tambah Kantor Cabang</span>
            </button>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col gap-2 px-8 py-1 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Daftar Kantor Cabang
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative py-2">
                        <div class="absolute -translate-y-1/2 left-4 top-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari kantor cabang"
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
                                    class="flex items-center justify-center px-3"
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
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Nama Kantor
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
                                @click="changeSort('region')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Region
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'region' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'region' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('contact')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Nomor Telfon
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'contact' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'contact' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('email')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Email
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'email' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'email' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('address')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Alamat
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'address' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'address' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('employees_count')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Jumlah Pegawai
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'employees_count' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'employees_count' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                v-if="can('branches.view') || can('branches.edit') || can('branches.delete')"
                                class="bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
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
                        <template v-if="branches.data && branches.data.length > 0">
                            <tr
                                v-for="(branch, index) in branches.data"
                                :key="branch.id"
                                class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                            <td
                                class="py-2.5 border-gray-200 border-y dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center justify-center px-3 whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{
                                            (branches.current_page - 1) *
                                                branches.per_page +
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
                                    class="flex items-center whitespace-nowrap"
                                >
                                    <div
                                        class="flex flex-col px-8 leading-tight"
                                    >
                                        <p
                                            class="font-medium text-gray-800 dark:text-white/90"
                                        >
                                            {{ branch.name }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap"
                                >
                                    <div
                                        class="flex flex-col px-8 leading-tight"
                                    >
                                        <p
                                            class="font-medium text-gray-800 dark:text-white/90"
                                        >
                                            {{ branch.region }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap"
                                >
                                    <div
                                        class="flex flex-col px-8 leading-tight"
                                    >
                                        <p
                                            class="text-gray-600 dark:text-gray-400"
                                        >
                                            {{ branch.contact || "-" }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap"
                                >
                                    <div
                                        class="flex flex-col px-8 leading-tight"
                                    >
                                        <p
                                            class="text-gray-600 dark:text-gray-400"
                                        >
                                            {{ branch.email || "-" }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap"
                                >
                                    <div
                                        class="flex flex-col px-8 leading-tight"
                                    >
                                        <p
                                            class="text-gray-600 dark:text-gray-400"
                                        >
                                            {{ branch.address || "-" }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center justify-center px-3 whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ branch.employees_count ?? 0 }}
                                    </p>
                                </div>
                            </td>
                            <td
                                v-if="can('branches.view') || can('branches.edit') || can('branches.delete')"
                                class="py-2.5 border-gray-200 border-y dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center gap-3 px-4 whitespace-nowrap sm:px-0"
                                >
                                    <Link
                                        v-if="can('branches.view')"
                                        :href="route('branches.show', branch.id)"
                                        class="text-blue-400 hover:text-blue-600"
                                        title="Lihat Detail"
                                    >
                                        <ShowIcon />
                                    </Link>
                                    <button
                                        v-if="can('branches.edit')"
                                        @click="openEditModal(branch)"
                                        class="text-yellow-500 hover:text-yellow-600"
                                        title="Edit"
                                    >
                                        <EditIcon />
                                    </button>
                                    <button
                                        v-if="can('branches.delete')"
                                        @click="openConfirmModal(branch)"
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
                                :colspan="can('branches.view') || can('branches.edit') || can('branches.delete') ? 9 : 8"
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <Modal
                    :show="isShowModalOpen"
                    :title="`Detail ${selectedItem?.name}`"
                    closeText="Tutup"
                    maxWidth="lg"
                    @close="closeShowModal"
                >
                    <div class="space-y-5">
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Nama</label
                            >
                            <p class="text-gray-800 dark:text-white/90">
                                {{ selectedItem?.name ?? "-" }}
                            </p>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Cabang</label
                            >
                            <p class="text-gray-800 dark:text-white/90">
                                {{ selectedItem?.region ?? "-" }}
                            </p>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Kepala Cabang</label
                            >
                            <p class="text-gray-800 dark:text-white/90">
                                {{ selectedItem?.head ?? "-" }}
                            </p>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Jumlah Pegawai</label
                            >
                            <p class="text-gray-800 dark:text-white/90">
                                {{ selectedItem?.total ?? "-" }}
                            </p>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Contact</label
                            >
                            <p class="text-gray-800 dark:text-white/90">
                                {{ selectedItem?.contact ?? "-" }}
                            </p>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Email</label
                            >
                            <p class="text-gray-800 dark:text-white/90">
                                {{ selectedItem?.email }}
                            </p>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Alamat</label
                            >
                            <p class="text-gray-800 dark:text-white/90">
                                {{ selectedItem?.address }}
                            </p>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Deskripsi</label
                            >
                            <p class="text-gray-800 dark:text-white/90">
                                {{ selectedItem?.description }}
                            </p>
                        </div>
                    </div>
                </Modal>
                
                <Modal
                    :show="isModalOpen"
                    :title="isEditMode ? `Edit Kantor` : 'Tambah Kantor'"
                    :confirmText="isLoading ? 'Menyimpan...' : 'Simpan'"
                    :disabled="isLoading"
                    maxWidth="lg"
                    @close="closeModal"
                    @confirm="saveBranch"
                >
                    <template #confirmButton>
                        <div class="flex items-center gap-2">
                            <RefreshIcon
                                v-if="isLoading"
                                class="w-4 h-4 animate-spin"
                            />
                            <span>{{ isLoading ? 'Menyimpan...' : 'Simpan' }}</span>
                        </div>
                    </template>
                    <div class="space-y-3">
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Nama <span class="text-red-500">*</span></label
                            >
                            <input
                                id="name"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="Masukkan nama kantor"
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
                                for="region"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Region / Kota
                                <span class="text-red-500">*</span></label
                            >
                            <input
                                id="region"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.region"
                                required
                                placeholder="Masukkan region cabang"
                            />
                            <div
                                v-if="form.errors.region"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.region }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="contact"
                                class="font-semibold text-gray-900 dark:text-white"
                                >No. Telepon</label
                            >
                            <input
                                id="contact"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.contact"
                                required
                                placeholder="Masukkan telepon kantor"
                            />
                            <div
                                v-if="form.errors.contact"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.contact }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="email"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Email</label
                            >
                            <input
                                id="email"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.email"
                                placeholder="Masukkan email kantor"
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
                                for="address"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Alamat
                                <span class="text-red-500">*</span></label
                            >
                            <textarea
                                id="address"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                v-model="form.address"
                                placeholder="Masukkan alamat kantor"
                            ></textarea>
                            <div
                                v-if="form.errors.address"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.address }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label
                                for="description"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Deskripsi
                                <span class="text-red-500">*</span></label
                            >
                            <textarea
                                id="description"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                v-model="form.description"
                                placeholder="Masukkan deskripsi kantor"
                            ></textarea>
                            <div
                                v-if="form.errors.description"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.description }}
                            </div>
                        </div>
                    </div>
                </Modal>
                
                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus Kantor"
                    :confirmText="isLoading ? 'Menghapus...' : 'Ya, Hapus!'"
                    :disabled="isLoading"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>

            <Pagination
                v-if="branches.data && branches.data.length > 0"
                :pagination="branches"
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
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import RefreshIcon from "@/Components/icons/RefreshIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { useAuth } from "@/Composables/useAuth";
import { ref, watch } from "vue";
import { useForm, router, Head, Link } from "@inertiajs/vue3";

const { can } = useAuth();

const breadcrumbs = [{ label: "Konfigurasi" }, { label: "Kantor Cabang" }];

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    branches: Object,
    search: String,
    sortBy: String,
    sortDirection: String,
});

function fetchBranches({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    router.get(
        route("branches.index"),
        {
            search: search.value,
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

let timeout = null;
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        fetchBranches();
    }, 400);
});

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchBranches({ sortBy: column, sortDirection: direction });
}

const isModalOpen = ref(false);
const isEditMode = ref(false);
const isLoading = ref(false);

const form = useForm({
    id: null,
    name: "",
    region: "",
    contact: "",
    email: "",
    address: "",
    description: "",
});

// Buka modal untuk tambah
function openAddModal() {
    if (!can('branches.create')) {
        return;
    }
    form.reset();
    isEditMode.value = false;
    isLoading.value = false;
    isModalOpen.value = true;
}

// Buka modal untuk edit
function openEditModal(branch) {
    if (!can('branches.edit')) {
        return;
    }
    form.id = branch.id;
    form.name = branch.name;
    form.region = branch.region;
    form.contact = branch.contact;
    form.email = branch.email;
    form.address = branch.address;
    form.description = branch.description;
    isEditMode.value = true;
    isLoading.value = false;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    isLoading.value = false;
    form.reset();
    form.clearErrors();
}

// Simpan (otomatis create/update)
function saveBranch() {
    if (isLoading.value) return;

    isLoading.value = true;

    if (isEditMode.value) {
        if (!can('branches.edit')) {
            isLoading.value = false;
            return;
        }
        form.put(route("branches.update", form.id), {
            onSuccess: () => {
                closeModal();
            },
            onFinish: () => {
                isLoading.value = false;
            },
        });
    } else {
        if (!can('branches.create')) {
            isLoading.value = false;
            return;
        }
        form.post(route("branches.store"), {
            onSuccess: () => {
                closeModal();
            },
            onFinish: () => {
                isLoading.value = false;
            },
        });
    }
}

const selectedItem = ref(null);

// show
const isShowModalOpen = ref(false);
const openShowModal = (item) => {
    if (!can('branches.view')) {
        return;
    }
    selectedItem.value = item;
    isShowModalOpen.value = true;
};
const closeShowModal = () => {
    selectedItem.value = null;
    isShowModalOpen.value = false;
};

// destroy
const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    if (!can('branches.delete')) {
        return;
    }
    selectedItem.value = item;
    isConfirmModalOpen.value = true;
};
const closeConfirmModal = () => {
    selectedItem.value = null;
    isConfirmModalOpen.value = false;
    isLoading.value = false;
};
const destroyData = () => {
    if (isLoading.value) return;
    if (!can('branches.delete')) {
        return;
    }

    isLoading.value = true;
    router.delete(route("branches.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        onFinish: () => {
            isLoading.value = false;
        },
        preserveScroll: true,
    });
};
</script>