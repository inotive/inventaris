<template>
    <Head title="Karyawan" />

    <div class="flex flex-col gap-4 px-4 h-full">
        <div class="flex justify-between items-center">
            <Breadcrumb :items="breadcrumbs" />
            <div class="flex gap-4">
                <Link
                    :href="route('employees.create')"
                    class="flex gap-2 items-center px-3 py-2 text-white bg-blue-500 rounded-md"
                >
                    <PlusSquareIcon class="w-4 h-4" />
                    <span class="hidden text-sm md:block">Tambah Karyawan</span>
                </Link>
                <a :href="route('employees.export')" target="_blank" rel="noopener">
                    <Button
                        class="px-2 py-1 bg-white border border-gray-200 text-dark"
                        icon="pi pi-file-excel"
                        label="Unduh Data Karyawan"
                        severity="success"
                        outlined
                        text
                        type="button"
                    />
                </a>
            </div>
        </div>

        <div
            class="h-[90%] grid-cols-12 gap-4 md:gap-6 overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col gap-2 px-8 py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Daftar Pengguna
                </div>
                <div v-if="successMessage" class="flex gap-3 justify-between items-center px-4 py-1 bg-green-100 rounded-lg border border-green-300">
                    <svg width="34" height="34" viewBox="0 0 34 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path opacity="0.3" d="M32.618 14.553L30.305 12.2056C30.1438 12.0475 30.0163 11.8582 29.9305 11.6493C29.8447 11.4403 29.8023 11.2161 29.8058 10.9902V7.66055C29.8037 7.20439 29.7114 6.75315 29.5344 6.33275C29.3574 5.91236 29.0992 5.5311 28.7745 5.21087C28.4497 4.89063 28.065 4.63775 27.6423 4.46673C27.2196 4.29571 26.7673 4.20993 26.3114 4.21433H22.9834C22.7576 4.21788 22.5335 4.17543 22.3247 4.08957C22.1159 4.00371 21.9267 3.87622 21.7687 3.71487L19.4391 1.36744C18.7901 0.718824 17.9103 0.354492 16.993 0.354492C16.0756 0.354492 15.1958 0.718824 14.5469 1.36744L12.2006 3.68158C12.0426 3.84292 11.8534 3.97041 11.6446 4.05627C11.4357 4.14214 11.2116 4.18458 10.9859 4.18103H7.65787C7.20193 4.1832 6.75092 4.27548 6.33073 4.45256C5.91055 4.62965 5.52948 4.88805 5.20941 5.21292C4.88934 5.53779 4.63658 5.92274 4.46564 6.34563C4.29471 6.76853 4.20898 7.22105 4.21337 7.6772V11.0069C4.21692 11.2328 4.1745 11.457 4.08867 11.6659C4.00285 11.8748 3.87543 12.0641 3.71416 12.2222L1.36791 14.553C0.719618 15.2023 0.355469 16.0826 0.355469 17.0003C0.355469 17.9181 0.719618 18.7983 1.36791 19.4476L3.68088 21.7951C3.84215 21.9532 3.96957 22.1424 4.05539 22.3514C4.14122 22.5603 4.18364 22.7845 4.18009 23.0104V26.3401C4.18226 26.7963 4.27449 27.2475 4.45149 27.6679C4.62848 28.0883 4.88675 28.4696 5.21146 28.7898C5.53617 29.11 5.92092 29.3629 6.34361 29.5339C6.76629 29.7049 7.21859 29.7907 7.67451 29.7863H11.0025C11.2283 29.7828 11.4524 29.8252 11.6612 29.9111C11.8701 29.9969 12.0592 30.1244 12.2173 30.2858L14.5635 32.6332C15.2125 33.2818 16.0923 33.6462 17.0096 33.6462C17.9269 33.6462 18.8067 33.2818 19.4557 32.6332L21.7853 30.3191C21.9433 30.1577 22.1325 30.0302 22.3413 29.9444C22.5502 29.8585 22.7743 29.8161 23 29.8196H26.328C27.246 29.8196 28.1264 29.4548 28.7754 28.8054C29.4245 28.156 29.7892 27.2752 29.7892 26.3567V23.0271C29.7856 22.8012 29.8281 22.577 29.9139 22.368C29.9997 22.1591 30.1271 21.9698 30.2884 21.8117L32.6346 19.4643C32.9571 19.1414 33.2125 18.7579 33.3862 18.3359C33.56 17.9138 33.6487 17.4616 33.6471 17.0052C33.6456 16.5488 33.5538 16.0971 33.3772 15.6763C33.2006 15.2555 32.9426 14.8737 32.618 14.553Z" fill="#17C653"/>
                        <path d="M14.8514 22.458C14.6828 22.4557 14.5165 22.4199 14.3619 22.3528C14.2073 22.2856 14.0675 22.1885 13.9507 22.067L9.92306 17.9863C9.7992 17.8683 9.70033 17.7266 9.63235 17.5696C9.56437 17.4126 9.52867 17.2435 9.52738 17.0724C9.52609 16.9013 9.55922 16.7317 9.62482 16.5736C9.69042 16.4156 9.78713 16.2724 9.9092 16.1526C10.0313 16.0327 10.1762 15.9387 10.3353 15.8761C10.4945 15.8134 10.6646 15.7835 10.8356 15.788C11.0066 15.7925 11.1749 15.8314 11.3305 15.9024C11.4861 15.9733 11.6259 16.0749 11.7414 16.201L14.8684 19.3805L22.3118 12.1034C22.5508 11.8646 22.8747 11.7305 23.2125 11.7305C23.5502 11.7305 23.8742 11.8646 24.1132 12.1034C24.3519 12.3425 24.4859 12.6666 24.4859 13.0045C24.4859 13.3425 24.3519 13.6666 24.1132 13.9057L15.7521 22.101C15.633 22.2171 15.4921 22.3085 15.3375 22.3698C15.1829 22.431 15.0176 22.461 14.8514 22.458Z" fill="#17C653"/>
                    </svg>
                    <div class="text-start">
                        <p class="text-md">Perubahan Data Berhasil</p>
                        <p class="text-sm text-green-600">{{successMessage}}</p>
                    </div>
                </div>
            </div>

            <Datatable
                :config="datatableConfig"
                @sort-change="changeSort"
                @search-change="changeSearch($event)"
                @page-changed="changePage"
                @per-page-changed="perPageChanged"
            >
                <template #addFilter>
                <div class="flex gap-3 items-center">
                    <div class="relative">
                        <button
                            type="button"
                            @click="isOpenFilter = !isOpenFilter"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
                        >
                            <SettingIcon />
                            Filter
                        </button>
                        <div
                            v-if="isOpenFilter"
                            class="absolute right-0 z-50 p-4 mt-2 w-96 bg-white rounded-lg border border-gray-200 shadow-lg"
                            @click.stop
                        >
                            <!-- Popover content goes here -->
                            <div class="mb-2 font-semibold text-gray-700 border-b border-gray-200 dark:text-gray-200">
                                <h1 class="pb-2 text-lg font-semibold">Filter Karyawan</h1>
                            </div>
                            <!-- Example filter fields -->
                            <div class="mb-3">
                                <label class="block mb-1 text-sm text-gray-600 dark:text-gray-300">Cabang</label>
                                <Select
                                    v-model="filter.branch_id"
                                    :options="branchOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih cabang"
                                    class="w-full !bg-white !text-black !border !border-gray-300"
                                />
                            </div>
                            <div class="mb-3">
                                <label class="block mb-1 text-sm text-gray-600 dark:text-gray-300">Departemen</label>
                                <Select
                                    v-model="filter.department_id"
                                    :options="departmentOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih departemen"
                                    class="w-full !bg-white !text-black !border !border-gray-300"
                                />
                            </div>
                            <div class="mb-3">
                                <label class="block mb-1 text-sm text-gray-600 dark:text-gray-300">Agama</label>
                                <Select
                                    v-model="filter.religion"
                                    :options="religionOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih agama"
                                    class="w-full !bg-white !text-black !border !border-gray-300"
                                />
                            </div>
                            <div class="mb-3">
                                <label class="block mb-1 text-sm text-gray-600 dark:text-gray-300">No. Telepon</label>
                                <input
                                    v-model="filter.contact"
                                    type="text"
                                    placeholder="Cari nomor telepon"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                            <div class="mb-3">
                                <label class="block mb-1 text-sm text-gray-600 dark:text-gray-300">Tempat Lahir</label>
                                <input
                                    v-model="filter.birthplace"
                                    type="text"
                                    placeholder="Cari tempat lahir"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                            <div class="flex gap-4 justify-end">
                                <button type="button" class="px-4 py-2 text-sm text-gray-700 bg-gray-200 rounded hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600" @click="resetFilter">Reset</button>
                                <button type="button" class="px-4 py-2 text-sm text-white bg-blue-500 rounded hover:bg-blue-600" @click="applyFilter">Terapkan</button>
                            </div>
                        </div>
                    </div>

                </div>
                </template>
                <template #body>
                    <tr
                        v-for="(context, index) in datatableConfig.data"
                        :key="context.id"
                        class="border-b divide-x divide-y"
                    >
                        <!-- No -->
                        <td class="px-2 py-2 text-center">{{ (datatableConfig.currentPage - 1) * datatableConfig.perPage + index + 1 }}</td>

                        <!-- Avatar (Blue male, Pink female) -->
                        <td class="px-3 py-3">
                            <div
                                :class="['w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold', avatarClass(context?.gender)]"
                                :title="context?.name || '-'"
                            >
                                {{ initials(context?.name) }}
                            </div>
                        </td>

                        <!-- Nama & Username -->
                        <td class="px-3 py-3">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-800">{{ context?.name || '-' }}</span>
                                <span class="text-xs text-gray-500">@{{ context?.username || '-' }}</span>
                            </div>
                        </td>

                        <!-- Departemen -->
                        <td class="px-3 py-3">{{ context?.department?.name || '-' }}</td>

                           <!-- Cabang -->
                        <td class="px-3 py-3">{{ context?.branch?.name || '-' }}</td>
                        <!-- Divisi -->
                        <td class="px-3 py-3">{{ context?.division?.name || context?.position?.name || '-' }}</td>



                        <!-- Umur -->
                        <td class="px-3 py-3">{{ computeAge(context) }}</td>

                        <!-- Aksi -->
                        <td class="px-2 py-2 text-center">
                            <div class="flex gap-3 justify-center whitespace-nowrap">
                                <Link :href="route('employees.show', context.id)">
                                    <ShowIcon class="text-blue-400" />
                                </Link>
                                <Link :href="route('employees.edit', context.id)">
                                    <EditIcon class="text-yellow-500" />
                                </Link>
                                <button @click="openConfirmModal(context)">
                                    <TrashIcon class="text-red-500" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>

            </Datatable>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <ConfirmModal
        :show="isConfirmModalOpen"
        :question="`Yakin ingin menghapus karyawan`"
        :selected="`${selectedItem?.name || 'ini'}`"
        title="Hapus Karyawan"
        confirmText="Ya, Hapus!"
        maxWidth="md"
        @close="closeConfirmModal"
        @confirm="deleteData"
    />
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import ShowIcon from "@/Components/icons/ShowIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import Datatable from "@/Components/common/Datatable.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import SettingIcon from "@/Components/icons/SettingIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Button from "primevue/button";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Select from "primevue/select";
import { ref, watch, computed, onMounted } from "vue";
import { useForm, router, usePage, Link } from "@inertiajs/vue3";
import { Head } from "@inertiajs/vue3";

const breadcrumbs = [{ label: "Menu Utama" }, { label: "Pengguna" }];



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

const deleteData = () => {
    router.delete(route("employees.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        preserveScroll: true,
    });
};

const props = defineProps({
    employees: Object,
    sort_by: String,
    sort_direction: String,
    search: String,
    departments: Array,
    branches: Array,
});

// Initialize datatableConfig
const datatableConfig = ref({
    data: props.employees?.data || [],
    totalItems: props.employees?.total || 0,
    currentPage: props.employees?.current_page || 1,
    perPage: props.employees?.per_page || 10,
    search: props.search || "",
    sortBy: props.sort_by || "name",
    sortDirection: props.sort_direction || "asc",
    loading: false,
    columns: [],
});

// Initialize filter
const filter = ref({
    department_id: null,
    branch_id: null,
    religion: null,
    contact: null,
    birthplace: null,
});

const isOpenFilter = ref(false);
const openFilter = () => {
    isOpenFilter.value = true;
};
const closeFilter = () => {
    isOpenFilter.value = false;
};

const branchOptions = computed(() => {
    return props.branches.map((branch) => ({
        label: branch.name,
        value: branch.id,
    }));
});

const departmentOptions = computed(() => {
    return props.departments.map((department) => ({
        label: department.name,
        value: department.id,
    }));
});

// Religion options
const religionOptions = [
    { label: "Semua", value: null },
    { label: "Islam", value: "ISLAM" },
    { label: "Kristen", value: "Kristen" },
    { label: "Katolik", value: "Katolik" },
    { label: "Hindu", value: "Hindu" },
    { label: "Buddha", value: "Buddha" },
    { label: "Konghucu", value: "Konghucu" },
];

const page = usePage();

const successMessage = computed(() => page.props.flash.success);

const search = ref(props.search || "");

// Initialize datatableConfig and setup click outside handler on mount
onMounted(() => {
    if (props.employees) {
        datatableConfig.value.data = props.employees.data || [];
        datatableConfig.value.totalItems = props.employees.total || 0;
        datatableConfig.value.currentPage = props.employees.current_page || 1;
        datatableConfig.value.perPage = props.employees.per_page || 10;
    }

    // Close filter when clicking outside
    const handleClickOutside = (event) => {
        if (isOpenFilter.value && !event.target.closest('.relative')) {
            isOpenFilter.value = false;
        }
    };
    document.addEventListener('click', handleClickOutside);

    // Cleanup on unmount
    return () => {
        document.removeEventListener('click', handleClickOutside);
    };
});


function fetchEmployees({ page = 1, perPage = datatableConfig.value.perPage, search = datatableConfig.value.search, sortBy = datatableConfig.value.sortBy, sortDirection = datatableConfig.value.sortDirection, onSuccess, onFinish } = {}) {
    datatableConfig.value.loading = true;
    router.get(
        route("employees.index"),
        {
            page,
            per_page: perPage,
            search,
            sort_by: sortBy,
            sort_direction: sortDirection,
            department_id: filter.value.department_id,
            branch_id: filter.value.branch_id,
            religion: filter.value.religion,
            contact: filter.value.contact,
            birthplace: filter.value.birthplace,
        },
        {
            preserveScroll: true,
            preserveState: false,
            replace: true,
            onSuccess: (page) => {
                datatableConfig.value.data = page.props.employees.data;
                datatableConfig.value.totalItems = page.props.employees.total;
                datatableConfig.value.currentPage = page.props.employees.current_page;
                datatableConfig.value.loading = false;
                if (onSuccess) onSuccess(page);
            },
            onFinish: () => {
                datatableConfig.value.loading = false;
                if (onFinish) onFinish();
            },
        }
    );
}

function perPageChanged(newPerPage) {
    datatableConfig.value.perPage = newPerPage;
    datatableConfig.value.currentPage = 1;
    fetchEmployees({ page: 1, perPage: newPerPage });
}
function changeSort(col) {
    const key = typeof col === "string" ? col : col?.key;
    const direction =
        datatableConfig.value.sortBy === key && datatableConfig.value.sortDirection === "asc"
            ? "desc"
            : "asc";
    datatableConfig.value.sortBy = key;
    datatableConfig.value.sortDirection = direction;
    fetchEmployees({ page: datatableConfig.value.currentPage, sortBy: key, sortDirection: direction });
}
function changeSearch(value) {
    console.log(value);
    datatableConfig.value.search = value;
    datatableConfig.value.currentPage = 1;
    fetchEmployees({ page: 1, search: value });
}
function changePage(page) {
    datatableConfig.value.currentPage = page;
    fetchEmployees({ page });
}

function resetFilter() {
    filter.value.department_id = null;
    filter.value.branch_id = null;
    filter.value.religion = null;
    filter.value.contact = null;
    filter.value.birthplace = null;
}
function applyFilter() {
    datatableConfig.value.currentPage = 1;
    fetchEmployees({ page: 1 });
    closeFilter();
}



defineOptions({
    layout: AppLayout,
});

// Helpers
function initials(name){
    if(!name) return '-';
    try {
        const parts = String(name).trim().split(/\s+/).slice(0,2);
        return parts.map(w=>w.charAt(0)).join('').toUpperCase();
    } catch { return '-'; }
}

function avatarClass(gender){
    const g = (gender || '').toString().toLowerCase();
    if(g === 'male' || g === 'l' || g === 'm' || g === 'pria') return 'bg-blue-500';
    if(g === 'female' || g === 'p' || g === 'wanita' || g === 'perempuan') return 'bg-pink-400';
    return 'bg-gray-400';
}

function computeAge(context){
    const dob = context?.birth_date || context?.dob || context?.date_of_birth;
    if(!dob) return '-';
    try {
        const d = new Date(dob);
        if(isNaN(d)) return '-';
        const now = new Date();
        let age = now.getFullYear() - d.getFullYear();
        const m = now.getMonth() - d.getMonth();
        if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--;
        return age + ' th';
    } catch { return '-'; }
}

</script>
