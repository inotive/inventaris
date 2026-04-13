<template>
    <Head title="Daftar Barang" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
            <button
                v-if="can('items.create')"
                @click="openAddModal"
                type="button"
                class="inline-flex items-center gap-2 text-white hover:text-blue-500 rounded-md border hover:border-gray-300 bg-blue-500 px-3 py-2 text-sm font-medium text-white-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            >
                <PlusSquareIcon />
                <span class="hidden text-sm md:block">Tambah Barang</span>
            </button>
        </div>

        <div class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]">
            <div class="flex flex-col h-16 gap-2 px-8 sm:flex-row sm:items-center sm:justify-between">
                <div class="font-bold text-gray-700 md:text-xl dark:text-gray-300">Daftar Barang</div>
                <div class="flex items-center gap-3">
                    <Select
                        v-model="filtersLocal.branch_id"
                        label="Pilih cabang"
                        class="w-56"
                        :items="branchesWithAll"
                    >
                        <template #item="{ item }">
                            <span>{{ item.name }}</span>
                        </template>
                    </Select>
                    <select
                        v-model="groupByLocal"
                        class="px-3 py-2.5 max-w-xs text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                    >
                        <option value="" class="bg-gray-200 opacity-50">Kelompokan Berdasarkan</option>
                        <option value="item">Group: Nama Barang</option>
                        <option value="category">Group: Kategori Barang</option>
                    </select>
                </div>
            </div>

            <div class="overflow-auto overflow-x-auto" data-simplebar>
                <table class="min-w-full text-sm table-fixed">
                    <thead>
                        <tr>
                            <th style="width: 70px" class="px-2 py-2 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="text-sm font-medium text-gray-600 dark:text-gray-300">No.</div>
                            </th>
                            <th style="width: 70px" class="px-2 py-2 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800 max-w-[120px]">
                                <button @click="changeSort('code')" class="w-full text-sm font-medium text-center text-gray-600 dark:text-gray-300">
                                    Kode Barang
                                    <SortIcon :active="sortBy === 'code'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th style="width: 250px" class="px-2 py-2 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('category')" class="w-full text-sm font-medium text-center text-gray-600 dark:text-gray-300">
                                    Kategori Barang
                                    <SortIcon :active="sortBy === 'category'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th style="width: 120px" class="px-2 py-2 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('unit')" class="w-full text-sm font-medium text-center text-gray-600 dark:text-gray-300">
                                    Satuan Barang
                                    <SortIcon :active="sortBy === 'unit'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th class="px-2 py-2 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('name')" class="w-full text-sm font-medium text-center text-gray-600 dark:text-gray-300">
                                    Foto dan Nama Barang
                                    <SortIcon :active="sortBy === 'name'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th class="px-2 py-2 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="text-sm font-medium text-center text-gray-600 dark:text-gray-300">Cabang</div>
                            </th>
                            <th class="px-2 py-2 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="text-sm font-medium text-center text-gray-600 dark:text-gray-300">Min Stok</div>
                            </th>
                            <th class="px-2 py-2 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <button @click="changeSort('stock')" class="w-full text-sm font-medium text-gray-600 dark:text-gray-300">
                                    Stok
                                    <SortIcon :active="sortBy === 'stock'" :direction="sortDirection" />
                                </button>
                            </th>
                            <th class="px-2 py-2 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800">
                                <div class="text-sm font-medium text-center text-gray-600 dark:text-gray-300">Aksi</div>
                            </th>
                        </tr>
                        <tr>
                            <th class="px-2 py-1 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                            <th class="px-2 py-1 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <input
                                    v-model="filtersLocal.code"
                                    type="text"
                                    placeholder="Kode"
                                    class="w-full px-2 py-1 text-sm text-gray-800 bg-transparent border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:placeholder:text-white/90 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                                />
                            </th>
                            <th class="px-2 py-1 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <select
                                    v-model="filtersLocal.category_id"
                                    class="w-full px-2 py-1 text-sm text-gray-800 bg-transparent border border-gray-200 rounded-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                                >
                                    <option value="" class="bg-gray-200 opacity-50">Kategori</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </th>
                            <th class="px-2 py-1 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <select
                                    v-model="filtersLocal.unit_id"
                                    class="w-full px-2 py-1 text-sm text-gray-800 bg-transparent border border-gray-200 rounded-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                                >
                                    <option value="" class="bg-gray-200 opacity-50">Satuan</option>
                                    <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                                </select>
                            </th>
                            <th class="px-2 py-1 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <input
                                    v-model="filtersLocal.name"
                                    type="text"
                                    placeholder="Nama barang"
                                    class="w-full px-2 py-1 text-sm text-gray-800 bg-transparent border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:placeholder:text-white/90 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                                />
                            </th>
                            <th class="px-2 py-1 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                            <th class="px-2 py-1 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                            <th class="px-2 py-1 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900"></th>
                            <th class="px-2 py-1 border-gray-200 bg-gray-50 border-y dark:border-gray-600 dark:bg-gray-900">
                                <button @click="clearFilters" class="px-2 py-1 text-sm text-white bg-red-500 rounded hover:bg-red-600">
                                    Clear
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!items.data || items.data.length === 0">
                            <td colspan="9" class="py-8 text-center text-gray-500">Tidak ada barang ditemukan.</td>
                        </tr>

                        <template v-else-if="isGrouped">
                            <template v-for="(rows, gkey) in groupedData" :key="gkey">
                                <tr>
                                    <td :colspan="9" class="px-4 py-2 font-semibold text-gray-600 bg-gray-50 dark:bg-gray-800/40 dark:text-gray-300">
                                        <button class="inline-flex items-center gap-2" @click="toggleGroupCollapse(gkey)">
                                            <span class="text-sm">{{ isCollapsed(gkey) ? "▶" : "▼" }}</span>
                                            <span>{{ groupLabel(gkey) }}</span>
                                            <span class="ml-1 text-sm font-medium text-gray-500">({{ rows.length }})</span>
                                        </button>
                                    </td>
                                </tr>
                                <template v-if="!isCollapsed(gkey)">
                                    <tr v-for="(row, idx) in rows" :key="row.id" class="border-b border-gray-200 dark:border-gray-700">
                                        <td class="w-16 px-2 py-2 text-center dark:text-white">{{ idx + 1 }}.</td>
                                        <td class="px-2 py-2 text-center dark:text-white">{{ row.code }}</td>
                                        <td class="px-2 py-2 text-center dark:text-white">
                                            <div class="flex items-center gap-3">
                                                <img v-if="row.image_url" :src="row.image_url" alt="foto" class="object-cover w-8 h-8 border rounded" />
                                                <div class="font-semibold text-gray-900 dark:text-white">
                                                    <span>{{ row.name }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-2 py-2 text-center dark:text-white">{{ parseInt(row.min_stock) }}</td>
                                        <td
                                            class="px-2 py-2 text-center dark:text-white"
                                            :class="Number(row.stock) <= Number(row.min_stock) ? 'text-red-600' : 'text-emerald-600'"
                                        >
                                            {{ row.stock }}
                                        </td>
                                        <td class="px-2 py-2 text-center dark:text-white">
                                            <div class="flex items-center justify-center gap-5">
                                                <Link v-if="can('items.view')" :href="route('items.show', row.id)">
                                                    <ShowIcon class="text-blue-500" />
                                                </Link>
                                                <button v-if="can('items.edit')" @click="openEditModal(row)">
                                                    <EditIcon class="text-yellow-500" />
                                                </button>
                                                <button v-if="can('items.delete')" type="button" @click="openConfirmModal(row)">
                                                    <TrashIcon class="text-red-500" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </template>

                        <template v-else>
                            <tr v-for="(row, idx) in items.data" :key="row.id" class="border-b border-gray-200 dark:border-gray-700">
                                <td class="w-16 px-2 py-2 text-center dark:text-white">
                                    {{ idx + (items.current_page - 1) * items.per_page + 1 }}.
                                </td>
                                <td class="px-2 py-2 text-center dark:text-white">{{ row.code }}</td>
                                <td class="px-2 py-2 text-center dark:text-white">
                                    <span class="inline-flex px-2 py-0.5 text-sm font-medium text-blue-700 bg-blue-100 rounded dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ row.category }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-center dark:text-white">{{ row.unit }}</td>
                                <td>
                                    <div class="flex items-center justify-center whitespace-nowrap dark:text-white">
                                        <span>{{ row.name }}</span>
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-center">
                                    <span v-if="row.branch" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded dark:bg-green-900/30 dark:text-green-300">{{ row.branch }}</span>
                                    <span v-else class="italic text-gray-400">—</span>
                                </td>
                                <td class="px-2 py-2 text-center dark:text-white">{{ row.min_stock }}</td>
                                <td
                                    class="px-2 py-2 text-center dark:text-white"
                                    :class="Number(row.stock) <= Number(row.min_stock) ? 'text-red-600' : 'text-emerald-600'"
                                >
                                    {{ row.stock }}
                                </td>
                                <td class="px-2 py-2 text-center dark:text-white">
                                    <div class="flex items-center justify-center gap-5">
                                        <Link v-if="can('items.view')" :href="route('items.show', row.id)">
                                            <ShowIcon class="text-blue-500" />
                                        </Link>
                                        <button v-if="can('items.edit')" @click="openEditModal(row)">
                                            <EditIcon class="text-yellow-500" />
                                        </button>
                                        <button v-if="can('items.delete')" type="button" @click="openConfirmModal(row)">
                                            <TrashIcon class="text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <Modal
                    :show="isModalOpen"
                    :title="isEditMode ? `Edit Barang` : 'Tambah Barang'"
                    confirmText="Simpan"
                    maxWidth="lg"
                    @close="closeModal"
                    @confirm="saveItem"
                >
                    <div class="space-y-5">
                        <div class="space-y-1 text-sm">
                            <label for="category_id" class="font-semibold text-gray-900 dark:text-white">
                                Kategori Barang <span class="text-red-500">*</span>
                            </label>
                            <Select
                                v-model="form.category_id"
                                label="Pilih kategori barang"
                                :items="categories"
                                :taggable="true"
                                :required="true"
                            >
                                <template #item="{ item }">
                                    <span>{{ item.name }}</span>
                                </template>
                            </Select>
                            <div v-if="form.errors.category_id" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.category_id) ? form.errors.category_id[0] : form.errors.category_id }}
                            </div>
                        </div>

                        <div class="space-y-1 text-sm">
                            <label for="name" class="font-semibold text-gray-900 dark:text-white">
                                Nama Barang <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="name"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="Masukkan nama barang"
                            />
                            <div v-if="form.errors.name" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.name) ? form.errors.name[0] : form.errors.name }}
                            </div>
                        </div>

                        <div class="grid grid-cols-10 text-sm gap-x-3">
                            <label for="code" class="col-span-5 text-gray-900 dark:text-white">
                                <div class="font-semibold">
                                    Kode Barang
                                    <span class="ml-1 text-sm font-normal text-gray-500">(Opsional)</span>
                                </div>
                                <input
                                    id="code"
                                    class="px-3 py-2.5 w-full text-sm text-gray-800 bg-transparent rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 focus:border-blue-300 focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
                                    type="text"
                                    v-model="form.code"
                                    placeholder="Masukkan kode barang (opsional)"
                                    :disabled="isEditMode"
                                />
                                <div v-if="form.errors.code" class="text-sm text-red-500">
                                    {{ Array.isArray(form.errors.code) ? form.errors.code[0] : form.errors.code }}
                                </div>
                            </label>

                            <label for="unit" class="col-span-5 text-gray-900 dark:text-white">
                                <div class="font-semibold">
                                    Satuan Barang <span class="text-red-500">*</span>
                                </div>
                                <select
                                    v-model="form.unit_id"
                                    class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                >
                                    <option value="">Pilih satuan barang</option>
                                    <option v-for="unit in units" :key="unit.id" :value="Number(unit.id)">{{ unit.name }}</option>
                                </select>
                                <div v-if="form.errors.unit" class="text-sm text-red-500">
                                    {{ Array.isArray(form.errors.unit) ? form.errors.unit[0] : form.errors.unit }}
                                </div>
                            </label>
                        </div>

                        <div class="space-y-1 text-sm">
                            <label for="min_stock" class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
                                <span>Min Stok</span>
                                <div class="relative group">
                                    <svg class="w-4 h-4 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="absolute bottom-full left-40 transform -translate-x-1/2 mb-2 px-3 py-2 text-xs text-white bg-gray-900 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                                        Sistem akan memberikan peringatan jika stock sudah menipis
                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </label>
                            <input
                                id="min_stock"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="number"
                                v-model.number="form.min_stock"
                                min="0"
                                step="0.01"
                                placeholder="Masukkan min stok"
                            />
                            <div v-if="form.errors.min_stock" class="text-sm text-red-500">
                                {{ Array.isArray(form.errors.min_stock) ? form.errors.min_stock[0] : form.errors.min_stock }}
                            </div>
                        </div>
                    </div>
                </Modal>

                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus Barang"
                    confirmText="Ya, Hapus!"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>

            <Pagination :pagination="items" @page-change="changePage" @per-page-change="perPageChanged" />
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Select from "@/Components/form/SelectBrng.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { useAuth } from "@/Composables/useAuth";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";

const { user, is, can } = useAuth();

defineOptions({ layout: AppLayout });

const breadcrumbs = [{ label: "Penyimpanan" }, { label: "Daftar Barang" }];

const props = defineProps({
    user: { type: String, required: true },
    items: { type: Array, required: true },
    branches: { type: Array, required: true },
    categories: { type: Array, required: true },
    units: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    sortBy: { type: String, default: "created_at" },
    sortDirection: { type: String, default: "desc" },
    groupBy: { type: String, default: null },
    isSuperadmin: { type: Boolean, default: false },
});

const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    name: "",
    code: "",
    branch_id: "",
    category_id: "",
    unit_id: "",
    min_stock: "",
});

function openAddModal() {
    form.reset();
    if (!is("Superadmin") && user?.employee?.branch?.id) {
        form.branch_id = user.employee.branch.id;
    }
    isEditMode.value = false;
    isModalOpen.value = true;
}

function openEditModal(item) {
    form.id = item.id;
    form.name = item.name;
    form.code = item.code;
    form.branch_id = item.branch_id;
    form.category_id = item.category_id;
    form.unit_id = Number(item.unit_id);
    form.min_stock = item.min_stock || "";
    selectedItem.value = item;
    isEditMode.value = true;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    selectedItem.value = null;
    form.reset();
    form.clearErrors?.();
}

function saveItem() {
    // FE guard untuk UX cepat
    if (!form.category_id) {
        form.setError?.("category_id", "Kategori barang wajib di isi.");
        return;
    }

    if (isEditMode.value) {
        if (!can("items.edit")) return;
        form.put(route("items.update", form.id), { onSuccess: closeModal });
    } else {
        if (!can("items.create")) return;
        form.post(route("items.store"), { onSuccess: closeModal });
    }
}

// Bersihkan error saat user memilih kategori
watch(() => form.category_id, () => {
    if (form.errors?.category_id) form.clearErrors?.("category_id");
});

// destroy
const selectedItem = ref(null);
const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => { selectedItem.value = item; isConfirmModalOpen.value = true; };
const closeConfirmModal = () => { selectedItem.value = null; isConfirmModalOpen.value = false; };
const destroyData = () => {
    if (!can("items.delete")) return;
    router.delete(route("items.destroy", selectedItem.value.id), {
        onSuccess: () => { closeConfirmModal(); },
        preserveScroll: true,
    });
};

const filtersLocal = ref({
    q: props.filters.q ?? "",
    code: props.filters.code ?? "",
    name: props.filters.name ?? "",
    unit_id: props.filters.unit_id ?? "",
    branch_id: props.filters.branch_id ?? (props.isSuperadmin ? null : user?.employee?.branch_id),
    item_id: props.filters.item_id ?? null,
    category_id: props.filters.category_id ?? "",
});

const branchesWithAll = computed(() => [{ id: "", name: "Semua Cabang" }, ...(props.branches || [])]);

const sortBy = ref(props.sortBy || "created_at");
const sortDirection = ref(props.sortDirection || "desc");
function changeSort(field) {
    if (sortBy.value === field) {
        sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
    } else {
        sortBy.value = field;
        sortDirection.value = "asc";
    }
    fetchItems({ sortBy: sortBy.value, sortDirection: sortDirection.value });
}

const groupByLocal = ref(props.groupBy ?? "");
const effectiveGroup = computed(() => groupByLocal.value || (props.groupBy ?? ""));
const isGrouped = computed(() => !!effectiveGroup.value);

const collapsedGroups = ref(new Set());
const groupedData = computed(() => {
    if (!isGrouped.value) return {};
    const data = props.items.data || [];
    const groups = {};
    for (const row of data) {
        const k = keyOf(row);
        if (!groups[k]) groups[k] = [];
        groups[k].push(row);
    }
    return groups;
});

function keyOf(row) {
    switch (effectiveGroup.value) {
        case "category":
            return row.category ?? "Kategori Barang";
        case "item":
            return row.name;
        default:
            return "ungrouped";
    }
}

function groupLabel(key) {
    switch (effectiveGroup.value) {
        case "category":
            return `Tanggal Keluar: ${key}`;
        case "item":
            return `Nama Barang: ${key}`;
        default:
            return key;
    }
}

function isCollapsed(key) { return collapsedGroups.value.has(key); }
function toggleGroupCollapse(key) {
    const s = new Set(collapsedGroups.value);
    if (s.has(key)) s.delete(key); else s.add(key);
    collapsedGroups.value = s;
}

function fetchItems({ sortBy: sb, sortDirection: sd } = {}) {
    router.get(
        route("items.index"),
        { ...filtersLocal.value, groupBy: effectiveGroup.value || null, sortBy: sb ?? sortBy.value, sortDirection: sd ?? sortDirection.value },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            onStart: () => { if (typeof window !== "undefined") window.__suppressProgress = true; },
            onFinish: () => { if (typeof window !== "undefined") window.__suppressProgress = false; },
        }
    );
}

let debounceTimer;
watch(() => [filtersLocal.value.code, filtersLocal.value.name], () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchItems({}), 400);
}, { deep: true });

watch(() => [filtersLocal.value.branch_id, filtersLocal.value.item_id, filtersLocal.value.category_id, filtersLocal.value.unit_id], () => {
    fetchItems({});
});

watch(groupByLocal, () => { fetchItems({}); });
watch(() => props.groupBy, (val) => { if (!groupByLocal.value && val) groupByLocal.value = val; });

function clearFilters() {
    filtersLocal.value = {
        q: "",
        code: "",
        name: "",
        unit_id: "",
        branch_id: is("Superadmin") ? "" : user?.employee?.branch_id || "",
        item_id: null,
        category_id: "",
    };
    groupByLocal.value = "";
    fetchItems({});
}

function changePage(page) {
    router.get(
        route("items.index"),
        { ...filtersLocal.value, groupBy: effectiveGroup.value || null, sortBy: sortBy.value, sortDirection: sortDirection.value, page },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}

function perPageChanged(perPage) {
    router.get(
        route("items.index"),
        { ...filtersLocal.value, groupBy: effectiveGroup.value || null, sortBy: sortBy.value, sortDirection: sortDirection.value, per_page: perPage, page: 1 },
        { preserveScroll: true, preserveState: true, replace: true }
    );
}
</script>

<script>
export default {
    components: {
        SortIcon: {
            props: { active: Boolean, direction: String },
            template: `
                <span class="inline-block ml-2 text-sm">
                    <template v-if="active">
                        {{ direction === 'asc' ? '▲' : '▼' }}
                    </template>
                    <template v-else>
                        ⇅
                    </template>
                </span>
            `,
        },
    },
};
</script>
