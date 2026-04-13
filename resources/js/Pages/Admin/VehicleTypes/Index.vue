<template>
    <Head title="Daftar Tipe Kendaraan" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
            <button
                v-if="can('vehicle_categories.create')"
                @click="openAddModal"
                type="button"
                class="flex items-center gap-2 px-3 py-2 text-white bg-blue-500 rounded hover:bg-blue-600"
            >
                <PlusSquareIcon />
                <span class="hidden text-sm md:block"
                    >Tambah Tipe Kendaraan</span
                >
            </button>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col gap-2 px-8 py-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Daftar Tipe Kendaraan
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative py-2">
                        <div class="absolute -translate-y-1/2 left-4 top-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari tipe kendaraan"
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
                                class="py-2.5 border-y border-gray-200 dark:border-gray-600 bg-gray-100 dark:bg-gray-800"
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
                                @click="changeSort('category')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Kategori
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'category' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'category' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('total')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Total
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'total' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'total' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('updated_at')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Terakhir diperbarui
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
                                v-if="can('vehicle_categories.edit') || can('vehicle_categories.delete')"
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
                        <template v-if="vehicleTypes.data && vehicleTypes.data.length > 0">
                            <tr
                                v-for="(vehicle, index) in vehicleTypes.data"
                                :key="vehicle.id"
                                class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <td
                                    class="py-2.5 border-y border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex items-center justify-center whitespace-nowrap"
                                    >
                                        <p
                                            class="px-3 text-gray-500 dark:text-gray-400"
                                        >
                                            {{
                                                (vehicleTypes.current_page - 1) *
                                                    vehicleTypes.per_page +
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
                                        class="flex items-center justify-center whitespace-nowrap"
                                    >
                                        <p
                                            class="font-medium text-gray-800 dark:text-white/90"
                                        >
                                            {{ vehicle.name }}
                                        </p>
                                    </div>
                                </td>
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex items-center justify-center px-3 whitespace-nowrap"
                                    >
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ vehicle.category }}
                                        </p>
                                    </div>
                                </td>
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex items-center justify-center px-3 whitespace-nowrap"
                                    >
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ vehicle.total }} kendaraan
                                        </p>
                                    </div>
                                </td>
                                <td
                                    class="py-2.5 border border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex items-center justify-center px-3 whitespace-nowrap"
                                    >
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ formatTime(vehicle.updated_at) }}
                                        </p>
                                    </div>
                                </td>
                                <td
                                    v-if="can('vehicle_categories.edit') || can('vehicle_categories.delete')"
                                    class="py-2.5 border-y border-gray-200 dark:border-gray-600"
                                >
                                    <div
                                        class="flex justify-center gap-3 px-4 whitespace-nowrap"
                                    >
                                        <button 
                                            v-if="can('vehicle_categories.edit')"
                                            @click="openEditModal(vehicle)"
                                            class="text-yellow-500 hover:text-yellow-600"
                                            title="Edit"
                                        >
                                            <EditIcon />
                                        </button>
                                        <button 
                                            v-if="can('vehicle_categories.delete')"
                                            @click="openConfirmModal(vehicle)"
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
                                :colspan="can('vehicle_categories.edit') || can('vehicle_categories.delete') ? 6 : 5"
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada tipe kendaraan ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <Modal
                    :show="isModalOpen"
                    :title="
                        isEditMode
                            ? `Edit ${selectedItem?.name}`
                            : 'Tambah Tipe Kendaraan'
                    "
                    :confirmText="isLoading ? 'Menyimpan...' : 'Simpan'"
                    :disabled="isLoading"
                    maxWidth="lg"
                    @close="closeModal"
                    @confirm="saveVehicleType"
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
                                >Nama<span class="text-red-500">*</span></label
                            >
                            <input
                                id="name"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="Masukkan nama kendaraan"
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
                                for="category"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Kategori<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <input
                                id="category"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.category"
                                required
                                placeholder="Masukkan kategori kendaraan"
                            />
                            <div
                                v-if="form.errors.category"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.category }}
                            </div>
                        </div>
                    </div>
                </Modal>
                
                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus Tipe Kendaraan"
                    :confirmText="isLoading ? 'Menghapus...' : 'Ya, Hapus!'"
                    :disabled="isLoading"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>

            <Pagination
                v-if="vehicleTypes.data && vehicleTypes.data.length > 0"
                :pagination="vehicleTypes"
            />
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import SearchIcon from "@/Components/icons/SearchIcon.vue";
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
import { useForm, router, Head } from "@inertiajs/vue3";

const { can } = useAuth();

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [
    { label: "Konfigurasi" },
    { label: "Manajemen Kendaraan" },
];

const props = defineProps({
    vehicleTypes: Object,
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
        hour: "2-digit",
        minute: "2-digit",
    });
}

function fetchVehicleTypes({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    router.get(
        route("vehicle-types.index"),
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
        fetchVehicleTypes();
    }, 400);
});

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchVehicleTypes({ sortBy: column, sortDirection: direction });
}

const isModalOpen = ref(false);
const isEditMode = ref(false);
const isLoading = ref(false);

const form = useForm({
    id: null,
    name: "",
    category: "",
});

// Buka modal untuk tambah
function openAddModal() {
    if (!can('vehicle_categories.create')) {
        return;
    }
    form.reset();
    isEditMode.value = false;
    isLoading.value = false;
    isModalOpen.value = true;
}

// Buka modal untuk edit
function openEditModal(vehicle) {
    if (!can('vehicle_categories.edit')) {
        return;
    }
    form.id = vehicle.id;
    form.name = vehicle.name;
    form.category = vehicle.category;
    selectedItem.value = vehicle;
    isEditMode.value = true;
    isLoading.value = false;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    isLoading.value = false;
    selectedItem.value = null;
    form.reset();
    form.clearErrors();
}

// Simpan (otomatis create/update)
function saveVehicleType() {
    if (isLoading.value) return;

    isLoading.value = true;

    if (isEditMode.value) {
        if (!can('vehicle_categories.edit')) {
            isLoading.value = false;
            return;
        }
        form.put(route("vehicle-types.update", form.id), {
            onSuccess: () => {
                closeModal();
            },
            onFinish: () => {
                isLoading.value = false;
            },
        });
    } else {
        if (!can('vehicle_categories.create')) {
            isLoading.value = false;
            return;
        }
        form.post(route("vehicle-types.store"), {
            onSuccess: () => {
                closeModal();
            },
            onFinish: () => {
                isLoading.value = false;
            },
        });
    }
}

// destroy
const selectedItem = ref(null);
const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    if (!can('vehicle_categories.delete')) {
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
    if (!can('vehicle_categories.delete')) {
        return;
    }

    isLoading.value = true;
    router.delete(route("vehicle-types.destroy", selectedItem.value.id), {
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