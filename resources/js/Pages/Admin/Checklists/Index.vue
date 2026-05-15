<template>
    <Head title="Manajemen Checklist" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center">
            <Breadcrumb :items="breadcrumbs" />
            <Link
                v-if="can('checklists.create')"
                :href="route('checklists.create')"
                class="flex gap-2 items-center px-3 py-2 text-white bg-blue-500 rounded"
            >
                <PlusSquareIcon class="w-4 h-4" />
                <span class="hidden text-sm md:block">Tambah Checklist</span>
            </Link>
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
                    Manajemen Checklist
                </div>

                <div class="flex flex-wrap gap-3 items-center">
                    <!-- Search -->
                    <div class="relative py-2">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari checklist"
                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[200px]"
                        />
                    </div>

                    <!-- Category Filter -->
                    <select
                        v-model="categoryFilter"
                        class="px-3 py-2 h-10 text-sm text-gray-800 bg-white rounded-lg border border-gray-200 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90"
                    >
                        <option value="">Semua Kategori</option>
                        <option
                            v-for="cat in categories"
                            :key="cat.id"
                            :value="cat.id"
                        >
                            {{ cat.name }}
                        </option>
                    </select>

                    <!-- Branch Filter -->
                    <select
                        v-model="branchFilter"
                        class="px-3 py-2 h-10 text-sm text-gray-800 bg-white rounded-lg border border-gray-200 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90"
                    >
                        <option value="">Semua Cabang</option>
                        <option
                            v-for="branch in branches"
                            :key="branch.id"
                            :value="branch.id"
                        >
                            {{ branch.name }}
                        </option>
                    </select>

                    <!-- Department Filter (filtered by branch) -->
                    <select
                        v-model="departmentFilter"
                        class="px-3 py-2 h-10 text-sm text-gray-800 bg-white rounded-lg border border-gray-200 shadow-theme-xs focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-800 dark:bg-gray-900 dark:text-white/90"
                    >
                        <option value="">Semua Departemen</option>
                        <option
                            v-for="dept in filteredDepartments"
                            :key="dept.id"
                            :value="dept.id"
                        >
                            {{ dept.name }}
                        </option>
                    </select>

                    <!-- Reset Button -->
                    <button
                        @click="resetFilters"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="p-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
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
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center cursor-pointer"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Nama Checklist
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
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Kategori
                                    </p>
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Departemen
                                    </p>
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Deskripsi
                                    </p>
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Jumlah Pertanyaan
                                    </p>
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex justify-center items-center">
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Jenis
                                    </p>
                                </div>
                            </th>
                            <th
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
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
                                v-if="
                                    can('checklists.view') ||
                                    can('checklists.edit') ||
                                    can('checklists.delete')
                                "
                                class="p-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
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
                        <tr
                            v-if="checklists.data && checklists.data.length > 0"
                            v-for="(checklist, index) in checklists.data"
                            :key="checklist.id"
                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <td
                                class="p-2.5 border-gray-200 border-y dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center items-center whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{
                                            (checklists.current_page - 1) *
                                                checklists.per_page +
                                            index +
                                            1
                                        }}.
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 px-8 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center whitespace-nowrap"
                                >
                                    <div class="flex flex-col leading-tight">
                                        <p
                                            class="font-medium text-gray-800 dark:text-white/90"
                                        >
                                            {{ checklist.name }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center items-center px-3 whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ checklist.category?.name || "-" }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center items-center px-3 whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ checklist.department?.name || "-" }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center items-center px-3 whitespace-nowrap"
                                >
                                    <p
                                        class="text-gray-500 dark:text-gray-400 truncate max-w-[320px]"
                                        :title="checklist.description"
                                    >
                                        {{ checklist.description || "-" }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center items-center whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ checklist.questions_count ?? 0 }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center items-center whitespace-nowrap"
                                >
                                    <p class="text-gray-500 dark:text-gray-400">
                                        {{ checklist.type }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="p-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center items-center whitespace-nowrap"
                                >
                                    <span
                                        :class="
                                            statusBadgeClass(checklist.status)
                                        "
                                        class="px-2 py-0.5 text-xs font-medium rounded-full"
                                    >
                                        {{ checklist.status }}
                                    </span>
                                </div>
                            </td>
                            <td
                                v-if="
                                    can('checklists.view') ||
                                    can('checklists.edit') ||
                                    can('checklists.delete')
                                "
                                class="px-8 py-2.5 border-gray-200 border-y dark:border-gray-600"
                            >
                                <div
                                    class="flex gap-3 justify-center whitespace-nowrap"
                                >
                                    <Link
                                        v-if="can('checklists.view')"
                                        :href="
                                            route(
                                                'checklists.show',
                                                checklist.id
                                            )
                                        "
                                        title="Lihat Detail"
                                    >
                                        <ShowIcon class="text-blue-400" />
                                    </Link>
                                    <Link
                                        v-if="can('checklists.edit')"
                                        :href="
                                            route(
                                                'checklists.edit',
                                                checklist.id
                                            )
                                        "
                                        title="Edit"
                                    >
                                        <EditIcon class="text-yellow-500" />
                                    </Link>
                                    <button
                                        v-if="can('checklists.delete')"
                                        @click="openConfirmModal(checklist)"
                                        title="Hapus"
                                    >
                                        <TrashIcon class="text-red-500" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-else>
                            <td
                                :colspan="
                                    can('checklists.view') ||
                                    can('checklists.edit') ||
                                    can('checklists.delete')
                                        ? 9
                                        : 8
                                "
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Modal & ConfirmModal milik halaman ini tetap (tidak berkaitan langsung dengan permission) -->
                <Modal
                    :show="isModalOpen"
                    :title="
                        isEditMode ? `Edit ${form?.name}` : 'Tambah checklist'
                    "
                    confirmText="Simpan"
                    maxWidth="lg"
                    @close="closeModal"
                    @confirm="saveChecklist"
                >
                    <div class="space-y-5">
                        <div class="space-y-1 text-sm">
                            <label
                                for="name"
                                class="font-semibold text-gray-900 dark:text-white"
                                >Nama Kategori<span class="text-red-500"
                                    >*</span
                                ></label
                            >
                            <input
                                id="name"
                                class="w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="Masukkan kategori checklist"
                            />
                            <div
                                v-if="form.errors.name"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.name[0] }}
                            </div>
                        </div>
                        <div class="flex gap-x-5 justify-between text-sm">
                            <label
                                for="start_time"
                                class="font-semibold text-gray-900 dark:text-white"
                            >
                                Waktu Masuk
                                <div class="relative w-full">
                                    <ClockIcon
                                        class="absolute right-3 top-1/2 w-5 h-5 text-blue-500 -translate-y-1/2"
                                    />
                                    <input
                                        id="start_time"
                                        type="time"
                                        v-model="form.start_time"
                                        class="pr-6 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                    />
                                </div>
                                <div
                                    v-if="form.errors.start_time"
                                    class="text-sm text-red-500"
                                >
                                    {{ form.errors.start_time[0] }}
                                </div>
                            </label>
                            <label
                                for="late_tolerance"
                                class="font-semibold text-center text-gray-900 dark:text-white"
                            >
                                Waktu Toleransi
                                <div class="relative w-full">
                                    <ClockIcon
                                        class="absolute right-3 top-1/2 w-5 h-5 text-blue-500 -translate-y-1/2"
                                    />
                                    <input
                                        id="late_tolerance"
                                        type="time"
                                        v-model="form.late_tolerance"
                                        class="pr-6 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                    />
                                </div>
                                <div
                                    v-if="form.errors.late_tolerance"
                                    class="text-sm text-red-500"
                                >
                                    {{ form.errors.late_tolerance[0] }}
                                </div>
                            </label>
                            <label
                                for="end_time"
                                class="font-semibold text-gray-900 dark:text-white text-end"
                            >
                                Waktu Selesai
                                <div class="relative w-full">
                                    <ClockIcon
                                        class="absolute right-3 top-1/2 w-5 h-5 text-blue-500 -translate-y-1/2"
                                    />
                                    <input
                                        id="end_time"
                                        type="time"
                                        v-model="form.end_time"
                                        class="pr-6 w-full text-sm font-medium placeholder-gray-500 text-gray-600 rounded-lg border-gray-400 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                    />
                                </div>
                                <div
                                    v-if="form.errors.end_time"
                                    class="text-sm text-red-500"
                                >
                                    {{ form.errors.end_time[0] }}
                                </div>
                            </label>
                        </div>
                    </div>
                </Modal>

                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus checklist"
                    confirmText="Ya, Hapus!"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>

            <Pagination
                v-if="checklists.data && checklists.data.length > 0"
                :pagination="checklists"
            />
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import SearchIcon from "@/Components/icons/SearchIcon.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import SettingIcon from "@/Components/icons/SettingIcon.vue";
import ClockIcon from "@/Components/icons/ClockIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { ref, watch, computed } from "vue";
import { useForm, router, Head, Link } from "@inertiajs/vue3";
import { useAuth } from "@/Composables/useAuth";

const { can } = useAuth();

const breadcrumbs = [
    { label: "Konfigurasi" },
    { label: "Manajemen Checklist" },
];

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    checklists: Object,
    categories: Array,
    departments: Array,
    branches: Array,
    search: String,
    sortBy: String,
    sortDirection: String,
    filters: Object,
});

const search = ref(props.filters?.search || "");
const categoryFilter = ref(props.filters?.category_id || "");
const branchFilter = ref(props.filters?.branch_id || "");
const departmentFilter = ref(props.filters?.department_id || "");

// Filter departments based on selected branch
const filteredDepartments = computed(() => {
    if (!branchFilter.value) {
        // Show all departments if no branch selected
        return props.departments || [];
    }
    // Filter departments by selected branch
    return (props.departments || []).filter(
        (dept) => dept.branch_id === parseInt(branchFilter.value)
    );
});

// Watch branch filter to reset department if needed
watch(branchFilter, (newBranch, oldBranch) => {
    if (newBranch !== oldBranch && departmentFilter.value) {
        // Check if current department belongs to new branch
        const currentDept = props.departments?.find(
            (d) => d.id === parseInt(departmentFilter.value)
        );
        if (currentDept && currentDept.branch_id !== parseInt(newBranch)) {
            // Reset department if it doesn't belong to new branch
            departmentFilter.value = "";
        }
    }
});

function fetchChecklist({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    router.get(
        route("checklists.index"),
        {
            search: search.value || undefined,
            category_id: categoryFilter.value || undefined,
            branch_id: branchFilter.value || undefined,
            department_id: departmentFilter.value || undefined,
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

function resetFilters() {
    search.value = "";
    categoryFilter.value = "";
    branchFilter.value = "";
    departmentFilter.value = "";
    fetchChecklist();
}

let timeout = null;
watch([search, categoryFilter, branchFilter, departmentFilter], () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        fetchChecklist();
    }, 400);
});

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchChecklist({ sortBy: column, sortDirection: direction });
}

// modal dummy (tetap)
const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    name: "",
    start_time: "",
    late_tolerance: "",
    end_time: "",
});

function openAddModal() {
    form.reset();
    isEditMode.value = false;
    isModalOpen.value = true;
}

function openEditModal(checklist) {
    form.id = checklist.id;
    form.name = checklist.name;
    form.start_time = checklist.start_time;
    form.late_tolerance = checklist.late_tolerance;
    form.end_time = checklist.end_time;
    isEditMode.value = true;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    form.reset();
}

function saveChecklist() {
    if (isEditMode.value) {
        form.put(route("checklists.update", form.id), {
            onSuccess: closeModal,
        });
    } else {
        form.post(route("checklists.store"), {
            onSuccess: closeModal,
        });
    }
}

// destroy (protected by permission)
const selectedItem = ref(null);

const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    if (!can("checklists.delete")) return;
    selectedItem.value = item;
    isConfirmModalOpen.value = true;
};
const closeConfirmModal = () => {
    selectedItem.value = null;
    isConfirmModalOpen.value = false;
};
const destroyData = () => {
    if (!can("checklists.delete")) return;
    router.delete(route("checklists.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        preserveScroll: true,
    });
};

// Helpers
function typeLabel(type) {
    if (type === "multiple") return "Banyak Orang";
    return "Perorang"; // default single
}

function statusBadgeClass(status) {
    switch ((status || "").toLowerCase()) {
        case "active":
            return "bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300";
        case "inactive":
            return "bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300";
        case "draft":
        default:
            return "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300";
    }
}
</script>
