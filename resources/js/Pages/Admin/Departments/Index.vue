<template>
    <Head title="Departemen" />

    <div class="flex flex-col h-full gap-3 px-4">
        <div class="flex items-center justify-between">
            <Breadcrumb :items="breadcrumbs" />
            <button
                v-if="can('departments.create')"
                @click="openAddModal"
                type="button"
                class="flex items-center gap-2 px-3 py-2 text-white bg-blue-500 rounded"
            >
                <PlusSquareIcon class="w-4 h-4" />
                <span class="hidden text-sm md:block">Tambah Departemen</span>
            </button>
        </div>

        <div
            class="h-[90%] grid-cols-12 gap-3 md:gap-6 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col gap-2 px-8 py-1 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Daftar Departemen
                </div>
                <div class="flex items-center gap-3">
                    <form class="relative py-2">
                        <button
                            type="button"
                            class="absolute -translate-y-1/2 left-4 top-1/2"
                        >
                            <SearchIcon class="text-gray-400" />
                        </button>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari departemen"
                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[200px]"
                        />
                    </form>
                </div>
            </div>

            <div class="w-full overflow-auto" data-simplebar>
                <table class="min-w-full overflow-auto text-sm">
                    <thead>
                        <tr>
                            <th class="py-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex items-center justify-center">
                                    <p class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        No.
                                    </p>
                                </div>
                            </th>
                            <!-- Cabang -->
                            <th @click="changeSort('branch')" class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Cabang
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="['-mb-1', sortBy === 'branch' && sortDirection === 'asc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                        <DownIcon :class="['-mt-1', sortBy === 'branch' && sortDirection === 'desc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                    </div>
                                </div>
                            </th>
                            <th @click="changeSort('name')" class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Nama Departemen
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="['-mb-1', sortBy === 'name' && sortDirection === 'asc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                        <DownIcon :class="['-mt-1', sortBy === 'name' && sortDirection === 'desc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                    </div>
                                </div>
                            </th>
                            <th @click="changeSort('employees_count')" class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Jumlah Karyawan
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="['-mb-1', sortBy === 'employees_count' && sortDirection === 'asc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                        <DownIcon :class="['-mt-1', sortBy === 'employees_count' && sortDirection === 'desc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                    </div>
                                </div>
                            </th>
                            <th @click="changeSort('verification')" class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Status
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="['-mb-1', sortBy === 'verification' && sortDirection === 'asc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                        <DownIcon :class="['-mt-1', sortBy === 'verification' && sortDirection === 'desc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                    </div>
                                </div>
                            </th>
                            <th class="py-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex items-center justify-center">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                        Aksi
                                    </p>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-if="departments.data && departments.data.length > 0">
                            <tr
                                v-for="(department, index) in departments.data"
                                :key="department.id"
                                class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <td class="py-2.5 border-gray-200 border-y dark:border-gray-600">
                                    <div class="flex items-center justify-center px-3 whitespace-nowrap">
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ (departments.current_page - 1) * departments.per_page + index + 1 }}.
                                        </p>
                                    </div>
                                </td>
                                <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center whitespace-nowrap">
                                        <div class="flex flex-col px-8 leading-tight">
                                            <p class="font-medium text-gray-800 dark:text-white/90">
                                                {{ department.branch?.name || '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center whitespace-nowrap">
                                        <div class="flex flex-col px-8 leading-tight">
                                            <p class="font-medium text-gray-800 dark:text-white/90">
                                                {{ department.name }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-center px-3 whitespace-nowrap">
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ department.employees_count ?? 0 }}
                                        </p>
                                    </div>
                                </td>
                                <td class="py-2.5 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-center px-3 whitespace-nowrap">
                                        <p class="text-red-500 dark:text-gray-400">
                                            <span v-if="department.status === true" class="p-1 font-normal text-green-600 bg-green-100 border-2 border-green-300 rounded-lg">
                                                Aktif
                                            </span>
                                            <span v-else class="p-1 font-normal text-red-600 bg-red-100 border-2 border-red-300 rounded-lg">
                                                Non Aktif
                                            </span>
                                        </p>
                                    </div>
                                </td>
                                <td class="py-2.5 border-gray-200 border-y dark:border-gray-600">
                                    <div class="flex justify-center gap-3 px-4 whitespace-nowrap sm:px-0">
                                        <Link
                                            v-if="can('departments.view')"
                                            :href="route('departments.show', department.id)"
                                        >
                                            <ShowIcon class="text-blue-400" />
                                        </Link>
                                        <button v-if="can('departments.edit')" @click="openEditModal(department)">
                                            <EditIcon class="text-yellow-500" />
                                        </button>
                                        <button v-if="can('departments.delete')" @click="openConfirmModal(department)">
                                            <TrashIcon class="text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-else>
                            <td colspan="6" class="py-6 font-medium text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>

                <Modal
                    :show="isModalOpen"
                    :title="isEditMode ? 'Edit Departemen' : 'Tambah Departemen'"
                    :confirmText="isLoading ? 'Menyimpan...' : 'Simpan'"
                    :disabled="isLoading"
                    maxWidth="lg"
                    @close="closeModal"
                    @confirm="saveDepartment"
                >
                    <template #confirmButton>
                        <div class="flex items-center gap-2">
                            <RefreshIcon v-if="isLoading" class="w-4 h-4 animate-spin" />
                            <span>{{ isLoading ? 'Menyimpan...' : 'Simpan' }}</span>
                        </div>
                    </template>
                    <div class="space-y-3">
                        <div class="space-y-1 text-sm">
                            <label for="name" class="font-semibold text-gray-900 dark:text-white">
                                Nama Departemen <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="Masukkan nama departemen"
                            />
                            <div v-if="form.errors.name" class="text-sm text-red-500">
                                {{ form.errors.name[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="branch_id" class="font-semibold text-gray-900 dark:text-white">Cabang</label>
                            <select
                                id="branch_id"
                                v-model="form.branch_id"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                            >
                                <option value="">Pilih Cabang</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                    {{ branch.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.branch_id" class="text-sm text-red-500">
                                {{ form.errors.branch_id[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="description" class="font-semibold text-gray-900 dark:text-white">
                                Deskripsi
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                placeholder="Masukkan deskripsi departemen"
                            ></textarea>
                            <div v-if="form.errors.description" class="text-sm text-red-500">
                                {{ form.errors.description[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="form.status"
                                    class="text-blue-600 border-gray-300 rounded shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                />
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Status Aktif
                                </span>
                            </label>
                            <div v-if="form.errors.status" class="text-sm text-red-500">
                                {{ form.errors.status[0] }}
                            </div>
                        </div>
                    </div>
                </Modal>

                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus '${selectedItem?.name}' ?`"
                    title="Hapus Departemen"
                    :confirmText="isLoading ? 'Menghapus...' : 'Ya, Hapus!'"
                    :disabled="isLoading"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="deleteData"
                />
            </div>

            <Pagination v-if="departments.data && departments.data.length > 0" :pagination="departments" />
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
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import { useAuth } from "@/Composables/useAuth";

const { can } = useAuth();

const breadcrumbs = [{ label: "Konfigurasi" }, { label: "Departemen" }];

const isModalOpen = ref(false);
const isEditMode = ref(false);
const isLoading = ref(false);

const form = useForm({
    id: null,
    name: "",
    branch_id: "",
    description: "",
    status: true,
});

function openAddModal() {
    if (!can('departments.create')) return;
    form.reset();
    isEditMode.value = false;
    isLoading.value = false;
    isModalOpen.value = true;
}

function openEditModal(department) {
    if (!can('departments.edit')) return;
    form.id = department.id;
    form.name = department.name;
    form.branch_id = department.branch_id || "";
    form.description = department.description || "";
    form.status = department.status || false;
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

function saveDepartment() {
    if (isLoading.value) return; // Prevent multiple requests
    isLoading.value = true;

    if (isEditMode.value) {
        if (!can('departments.edit')) { isLoading.value = false; return; }
        form.put(route("departments.update", form.id), {
            onSuccess: () => { closeModal(); },
            onFinish: () => { isLoading.value = false; },
        });
    } else {
        if (!can('departments.create')) { isLoading.value = false; return; }
        form.post(route("departments.store"), {
            onSuccess: () => { closeModal(); },
            onFinish: () => { isLoading.value = false; },
        });
    }
}

const selectedItem = ref(null);
const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    if (!can('departments.delete')) return;
    selectedItem.value = item;
    isConfirmModalOpen.value = true;
};
const closeConfirmModal = () => {
    selectedItem.value = null;
    isConfirmModalOpen.value = false;
    isLoading.value = false;
};

const deleteData = () => {
    if (isLoading.value) return; // Prevent multiple requests
    if (!can('departments.delete')) return;

    isLoading.value = true;
    router.delete(route("departments.destroy", selectedItem.value.id), {
        onSuccess: () => { closeConfirmModal(); },
        onFinish: () => { isLoading.value = false; },
        preserveScroll: true,
    });
};

const props = defineProps({
    departments: Object,
    branches: Array,
    sortBy: String,
    sortDirection: String,
    search: String,
});

const search = ref(props.search || "");

let timeout = null;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        router.get(
            route("departments.index"),
            {
                search: value,
                sortBy: props.sortBy,
                sortDirection: props.sortDirection,
            },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            }
        );
    }, 400);
});

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }

    router.get(
        route("departments.index"),
        {
            search: search.value,
            sortBy: column,
            sortDirection: direction,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

defineOptions({ layout: AppLayout });
</script>
