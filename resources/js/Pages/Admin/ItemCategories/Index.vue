<template>
    <Head title="Kategori Barang" />

    <div class="flex flex-col h-full gap-3 px-3 py-0.5 overflow-hidden">
        <div class="flex items-center justify-between">
            <Breadcrumb :items="breadcrumbs" />
            <button
                v-if="can('item_categories.create')"
                @click="openAddModal"
                type="button"
                class="flex items-center gap-2 px-3 py-2 text-white bg-blue-500 rounded"
            >
                <PlusSquareIcon />
                <span class="hidden text-sm md:block">Tambah Kategori Barang</span>
            </button>
        </div>

        <div class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-2 px-8 py-1 sm:flex-row sm:items-center sm:justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">
                    Daftar Kategori Barang
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative py-2">
                        <div class="absolute -translate-y-1/2 left-4 top-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari kategori barang"
                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[200px]"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="p-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex items-center justify-center">
                                    <p class="flex flex-col items-center font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">No.</p>
                                </div>
                            </th>

                            <th @click="changeSort('name')" class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2 cursor-pointer">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">Nama kategori</p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="['-mb-1', sortBy==='name' && sortDirection==='asc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                        <DownIcon :class="['-mt-1', sortBy==='name' && sortDirection==='desc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                    </div>
                                </div>
                            </th>

                            <th @click="changeSort('description')" class="p-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">Deskripsi kategori</p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="['-mb-1', sortBy==='description' && sortDirection==='asc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                        <DownIcon :class="['-mt-1', sortBy==='description' && sortDirection==='desc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                    </div>
                                </div>
                            </th>

                            <th @click="changeSort('updated_at')" class="p-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800">
                                <div class="flex items-center justify-center gap-2">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">Tanggal diperbarui</p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon :class="['-mb-1', sortBy==='updated_at' && sortDirection==='asc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                        <DownIcon :class="['-mt-1', sortBy==='updated_at' && sortDirection==='desc' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 dark:text-gray-500']" />
                                    </div>
                                </div>
                            </th>

                            <th
                                v-if="can('item_categories.edit') || can('item_categories.delete')"
                                class="p-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
                                    <p class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">Aksi</p>
                                </div>
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <template v-if="itemCategories.data && itemCategories.data.length > 0">
                            <tr
                                v-for="(category, index) in itemCategories.data"
                                :key="category.id"
                                class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <td class="p-2.5 border-y border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-center whitespace-nowrap">
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ (itemCategories.current_page - 1) * itemCategories.per_page + index + 1 }}.
                                        </p>
                                    </div>
                                </td>

                                <td class="p-2.5 px-8 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center whitespace-nowrap">
                                        <div class="flex flex-col leading-tight">
                                            <p class="font-medium text-gray-800 dark:text-white/90">{{ category.name }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="p-2.5 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-center px-3">
                                        <p class="text-gray-500 dark:text-gray-400">{{ category.description }}</p>
                                    </div>
                                </td>

                                <td class="p-2.5 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-center px-3 whitespace-nowrap">
                                        <p class="text-gray-500 dark:text-gray-400">{{ formatTime(category.updated_at) }}</p>
                                    </div>
                                </td>

                                <td
                                    v-if="can('item_categories.edit') || can('item_categories.delete')"
                                    class="py-2.5 px-8 border-y border-gray-200 dark:border-gray-600"
                                >
                                    <div class="flex justify-center gap-3 whitespace-nowrap">
                                        <button v-if="can('item_categories.edit')" @click="openEditModal(category)" title="Edit">
                                            <EditIcon class="text-yellow-500" />
                                        </button>
                                        <button v-if="can('item_categories.delete')" @click="openConfirmModal(category)" title="Hapus">
                                            <TrashIcon class="text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-else>
                            <td
                                :colspan="(can('item_categories.edit') || can('item_categories.delete')) ? 5 : 4"
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>

                <Modal
                    :show="isModalOpen"
                    :title="isEditMode ? `Edit  Kategori Barang` : 'Tambah Kategori Barang'"
                    :confirmText="isLoading ? 'Menyimpan...' : 'Simpan'"
                    :disabled="isLoading"
                    maxWidth="md"
                    @close="closeModal"
                    @confirm="saveCategory"
                >
                    <div class="space-y-5">
                        <div class="space-y-1 text-sm">
                            <label for="name" class="font-semibold text-gray-900 dark:text-white">
                                Nama Kategori<span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="Masukkan nama kategori barang"
                            />
                            <div v-if="form.errors.name" class="text-sm text-red-500">
                                {{ form.errors.name[0] }}
                            </div>
                        </div>
                        <div class="space-y-1 text-sm">
                            <label for="description" class="font-semibold text-gray-900 dark:text-white">
                                Deskripsi Kategori
                            </label>
                            <textarea
                                id="description"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                v-model="form.description"
                                placeholder="Masukkan deskripsi kategori barang"
                            ></textarea>
                            <div v-if="form.errors.description" class="text-sm text-red-500">
                                {{ form.errors.description[0] }}
                            </div>
                        </div>
                    </div>
                </Modal>

                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus Kategori Barang"
                    confirmText="Ya, Hapus!"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>

            <Pagination
                v-if="itemCategories.data && itemCategories.data.length > 0"
                :pagination="itemCategories"
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
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { ref, watch } from "vue";
import { useForm, router, Head } from "@inertiajs/vue3";
import { useAuth } from "@/Composables/useAuth";

const { can } = useAuth();

const breadcrumbs = [{ label: "Konfigurasi" }, { label: "Kategori Barang" }];

defineOptions({ layout: AppLayout });

const props = defineProps({
    itemCategories: Object,
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

function fetchCategory({ sortBy = props.sortBy, sortDirection = props.sortDirection } = {}) {
    router.get(
        route("item-categories.index"),
        { search: search.value, sortBy, sortDirection },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

const search = ref(props.search || "");
let timeout = null;
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => fetchCategory(), 400);
});

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchCategory({ sortBy: column, sortDirection: direction });
}

const isModalOpen = ref(false);
const isEditMode = ref(false);
const isLoading = ref(false);

const form = useForm({
    id: null,
    name: "",
    description: "",
});

// Buka modal untuk tambah
function openAddModal() {
    if (!can('item_categories.create')) return;
    form.reset();
    isEditMode.value = false;
    isModalOpen.value = true;
}

// Buka modal untuk edit
function openEditModal(category) {
    if (!can('item_categories.edit')) return;
    form.id = category.id;
    form.name = category.name;
    form.description = category.description;
    isEditMode.value = true;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
    isLoading.value = false;
}

// Simpan (otomatis create/update) dengan pengecekan izin
function saveCategory() {
    if (isLoading.value) return;
    isLoading.value = true;

    if (isEditMode.value) {
        if (!can('item_categories.edit')) { isLoading.value = false; return; }
        form.put(route("item-categories.update", form.id), {
            onSuccess: closeModal,
            onFinish: () => { isLoading.value = false; },
        });
    } else {
        if (!can('item_categories.create')) { isLoading.value = false; return; }
        form.post(route("item-categories.store"), {
            onSuccess: closeModal,
            onFinish: () => { isLoading.value = false; },
        });
    }
}

// destroy
const selectedItem = ref(null);

const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    if (!can('item_categories.delete')) return;
    selectedItem.value = item;
    isConfirmModalOpen.value = true;
};
const closeConfirmModal = () => {
    selectedItem.value = null;
    isConfirmModalOpen.value = false;
};
const destroyData = () => {
    if (!can('item_categories.delete')) return;
    router.delete(route("item-categories.destroy", selectedItem.value.id), {
        onSuccess: closeConfirmModal,
        preserveScroll: true,
    });
};
</script>
