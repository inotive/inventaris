<template>
    <Head title="Riwayat Inspeksi Kendaraan" />

    <div class="flex overflow-hidden flex-col gap-3 px-3 h-full">
        <div class="flex justify-between items-center h-10">
            <Breadcrumb :items="breadcrumbs" />
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
                    Data Inspeksi Kendaraan
                </div>

                <div class="flex gap-3 items-center">
                    <div class="relative py-2">
                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari inspeksi"
                            class="dark:bg-dark-900 h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-800 dark:bg-gray-900 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-blue-800 xl:w-[200px]"
                        />
                    </div>
                </div>
            </div>

            <div class="overflow-auto" data-simplebar>
                <table class="min-w-full text-sm">
                    <thead>
                        <tr>
                            <th
                                class="p-2.5 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
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
                                        Nama & No. Kendaraan
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
                                @click="changeSort('driver')"
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center cursor-pointer"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Driver
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'driver' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'driver' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('branch')"
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center cursor-pointer"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Cabang
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'branch' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'branch' &&
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
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center cursor-pointer"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Jumlah Pertanyaan
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
                                @click="changeSort('progress')"
                                class="p-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex gap-2 justify-center items-center cursor-pointer"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Progress
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'progress' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'progress' &&
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
                                        Status
                                    </p>
                                </div>
                            </th>
                            <th
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
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center gap-3 ps-5 whitespace-nowrap"
                                >
                                    <img
                                        class="h-10 w-10 rounded-full object-cover"
                                        :src="
                                            checklist.photo
                                                ? `/storage/${checklist.photo}`
                                                : `https://ui-avatars.com/api/?name=${encodeURIComponent(
                                                      checklist.vehicle?.type
                                                          .name
                                                  )}&background=3b82f6&color=fff`
                                        "
                                        alt="checklist photo"
                                        loading="lazy"
                                    />
                                    <div class="flex flex-col leading-tight">
                                        <p
                                            class="font-medium text-gray-800 dark:text-white/90"
                                        >
                                            {{ checklist.vehicle?.license_code }}
                                        </p>
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            {{ checklist.vehicle?.type.name }}
                                        </span>
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
                                        {{ checklist.vehicle?.driver?.name }}
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
                                        {{ checklist.department?.branch?.name }}
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
                                <div class="flex justify-center mb-1">
                                    <span class="text-blue-700 dark:text-white"
                                        >{{ checklist.progress }}%</span
                                    >
                                </div>
                                <div
                                    class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700"
                                >
                                    <div
                                        class="bg-blue-600 h-2 rounded-full"
                                        :style="{
                                            width: checklist.progress + '%',
                                        }"
                                    ></div>
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
                                        class="px-2 py-0.5 rounded-full text-xs font-medium"
                                    >
                                        {{ checklist.status }}
                                    </span>
                                </div>
                            </td>
                            <td
                                class="px-8 py-2.5 border-gray-200 border-y dark:border-gray-600"
                            >
                                <div
                                    class="flex gap-3 justify-center whitespace-nowrap"
                                >
                                    <Link
                                        :href="
                                            route(
                                                'checklists.show',
                                                checklist.id
                                            )
                                        "
                                    >
                                        <ShowIcon class="text-blue-400" />
                                    </Link>
                                </div>
                            </td>
                        </tr>

                        <tr v-else>
                            <td
                                colspan="9"
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>
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
import EditIcon from "@/Components/icons/EditIcon.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import SettingIcon from "@/Components/icons/SettingIcon.vue";
import ClockIcon from "@/Components/icons/ClockIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { ref, watch } from "vue";
import { useForm, router } from "@inertiajs/vue3";

const breadcrumbs = [{ label: "Kendaraan" }, { label: "Riwayat Inspeksi" }];

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    checklists: Object,
    search: String,
    sortBy: String,
    sortDirection: String,
});

function fetchChecklist({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    router.get(
        route("vehicle-inspections.index"),
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

const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    name: "",
    start_time: "",
    late_tolerance: "",
    end_time: "",
});

// Buka modal untuk tambah
function openAddModal() {
    form.reset();
    isEditMode.value = false;
    isModalOpen.value = true;
}

// Buka modal untuk edit
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

// Simpan (otomatis create/update)
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
