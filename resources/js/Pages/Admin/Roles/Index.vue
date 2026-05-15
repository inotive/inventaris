<template>
    <Head title="Daftar Jabatan" />

    <div class="flex flex-col h-full gap-3 px-3 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
            <button
                v-if="can('roles.create')"
                @click="openAddModal"
                class="inline-flex items-center gap-2 text-white hover:text-blue-500 rounded-md border hover:border-gray-300 bg-blue-500 px-3 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            >
                <PlusSquareIcon />
                <span class="hidden text-sm md:block">Tambah Jabatan Baru</span>
            </button>
        </div>

        <div
            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-600 dark:bg-white/[0.03]"
        >
            <div
                class="flex flex-col h-16 gap-2 px-8 sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="font-bold text-gray-700 md:text-xl dark:text-gray-300"
                >
                    Daftar Jabatan
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative py-2">
                        <div class="absolute -translate-y-1/2 left-4 top-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari Jabatan"
                            class="h-10 w-full rounded-lg border border-gray-200 bg-transparent py-2.5 pl-12 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-blue-300 focus:outline-hidden focus:ring-3 focus:ring-blue-500/10 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-gray-400 dark:focus:border-blue-800 xl:w-[220px]"
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
                                        Nama Jabatan
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
                                <div
                                    class="flex items-center justify-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Jatah Cuti
                                    </p>
                                </div>
                            </th>

                            <th
                                class="py-3 bg-gray-100 border border-gray-200 dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Jatah Piutang
                                    </p>
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
                                        Total Pengguna
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
                                @click="changeSort('access')"
                                class="py-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2 px-3"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Jumlah Hak Akses
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'access' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'access' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                v-if="can('roles.edit') || can('roles.delete')"
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
                        <tr
                            v-if="roles.data && roles.data.length > 0"
                            v-for="(role, index) in roles.data"
                            :key="role.id"
                            class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <td
                                class="py-2.5 border-gray-200 border-y dark:border-gray-600"
                            >
                                <div
                                    class="flex items-center justify-center whitespace-nowrap"
                                >
                                    <p
                                        class="px-3 text-gray-500 dark:text-gray-400"
                                    >
                                        {{
                                            (roles.current_page - 1) *
                                                roles.per_page +
                                            index +
                                            1
                                        }}.
                                    </p>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                                @click="can('roles.view') && openStatsModal(role, 'users')"
                            >
                                <div
                                    class="flex items-center justify-center whitespace-nowrap"
                                >
                                    <p
                                        class="font-medium text-gray-800 dark:text-white/90"
                                        :class="{'cursor-pointer hover:text-blue-600': can('roles.view')}"
                                    >
                                        {{ role.name }}
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
                                        {{ (role.leave_quota_per_year ?? 0) }} Hari
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
                                        {{ formatCurrency(role.loan_quota || 0) }}
                                    </p>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                                @click="can('roles.view') && openStatsModal(role, 'users')"
                            >
                                <div
                                    class="flex items-center justify-center px-3 whitespace-nowrap"
                                >
                                    <p
                                        class="cursor-pointer dark:text-blue-400"
                                        :class="can('roles.view') ? 'text-blue-600 hover:underline' : 'text-gray-600'"
                                    >
                                        {{ role.total }} pengguna
                                    </p>
                                </div>
                            </td>
                            <td
                                class="py-2.5 border border-gray-200 dark:border-gray-600"
                                @click="can('roles.view') && openStatsModal(role, 'permissions')"
                            >
                                <div
                                    class="flex items-center justify-center px-3 whitespace-nowrap"
                                >
                                    <p
                                        class="cursor-pointer dark:text-blue-400"
                                        :class="can('roles.view') ? 'text-blue-600 hover:underline' : 'text-gray-600'"
                                    >
                                        {{ role.access }} akses
                                    </p>
                                </div>
                            </td>
                            <td
                                v-if="can('roles.edit') || can('roles.delete')"
                                class="py-2.5 border-gray-200 border-y dark:border-gray-600"
                            >
                                <div
                                    class="flex justify-center gap-3 px-4 whitespace-nowrap sm:px-0"
                                >
                                    <Link
                                        v-if="can('roles.edit')"
                                        :href="route('roles.edit', role.id)"
                                        class="text-blue-500 hover:text-blue-600"
                                        title="Kelola Hak Akses"
                                    >
                                        <GearIcon />
                                    </Link>
                                    <button
                                        v-if="can('roles.edit')"
                                        @click="openEditModal(role)"
                                        class="text-yellow-500 hover:text-yellow-600"
                                        title="Edit"
                                    >
                                        <EditIcon />
                                    </button>
                                    <button
                                        v-if="can('roles.delete')"
                                        @click="openConfirmModal(role)"
                                        class="text-red-500 hover:text-red-600"
                                        title="Hapus"
                                    >
                                        <TrashIcon />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-else>
                            <td
                                :colspan="can('roles.edit') || can('roles.delete') ? 7 : 6"
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Stats Modal: Users & Permissions -->
                <Modal
                    :show="isStatsModalOpen"
                    :title="`Rincian ${statsRole?.name || ''}`"
                    confirmText="Tutup"
                    maxWidth="3xl"
                    @close="closeStatsModal"
                    @confirm="closeStatsModal"
                >
                    <div class="space-y-4">
                        <div class="flex gap-2 border-b">
                            <button
                                class="px-3 py-2 text-sm font-medium"
                                :class="activeStatsTab === 'users' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500'"
                                @click="activeStatsTab = 'users'"
                            >
                                Daftar Pengguna ({{ statsUsers.length }})
                            </button>
                            <button
                                class="px-3 py-2 text-sm font-medium"
                                :class="activeStatsTab === 'permissions' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500'"
                                @click="activeStatsTab = 'permissions'"
                            >
                                Daftar Hak Akses ({{ statsPermissions.length }})
                            </button>
                        </div>

                        <div v-if="statsLoading" class="py-8 text-center text-gray-500">
                            Memuat...
                        </div>

                        <div v-else>
                            <!-- Users Table -->
                            <div v-show="activeStatsTab === 'users'">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="p-2 text-left">Nama</th>
                                            <th class="p-2 text-left">Username</th>
                                            <th class="p-2 text-left">Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="statsUsers.length === 0">
                                            <td colspan="3" class="p-4 text-center text-gray-500">Tidak ada pengguna</td>
                                        </tr>
                                        <tr v-for="u in statsUsers" :key="u.id" class="border-t">
                                            <td class="p-2">{{ u.name }}</td>
                                            <td class="p-2">{{ u.username || '-' }}</td>
                                            <td class="p-2">{{ u.email || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Permissions Table -->
                            <div v-show="activeStatsTab === 'permissions'">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="p-2 text-left">Hak Akses</th>
                                            <th class="p-2 text-left">Grup</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="statsPermissions.length === 0">
                                            <td colspan="2" class="p-4 text-center text-gray-500">Tidak ada hak akses</td>
                                        </tr>
                                        <tr v-for="p in statsPermissions" :key="p.id" class="border-t">
                                            <td class="p-2">{{ formatPermissionName(p.display_name || p.name) }}</td>
                                            <td class="p-2">{{ p.group_name || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </Modal>

                <Modal
                    :show="isModalOpen"
                    :title="
                        isEditMode
                            ? `Edit ${selectedItem?.name}`
                            : 'Tambah Jabatan'
                    "
                    confirmText="Simpan"
                    maxWidth="lg"
                    @close="closeModal"
                    @confirm="saveRole"
                >
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
                                placeholder="Masukkan nama jabatan"
                            />
                            <div
                                v-if="form.errors.name"
                                class="text-sm text-red-500"
                            >
                                {{ form.errors.name }}
                            </div>
                        </div>

                        <div class="space-y-1 text-sm">
                            <label for="leave_quota_per_year" class="font-semibold text-gray-900 dark:text-white">Jatah Cuti (hari/tahun)</label>
                            <input
                                id="leave_quota_per_year"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="number"
                                min="0"
                                step="1"
                                v-model.number="form.leave_quota_per_year"
                                placeholder="Contoh: 12"
                            />
                        </div>

                        <div class="space-y-1 text-sm">
                            <label for="loan_quota" class="font-semibold text-gray-900 dark:text-white">Jatah Piutang (Rp)</label>
                            <input
                                id="loan_quota"
                                class="w-full text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                                type="number"
                                min="0"
                                step="1000"
                                v-model.number="form.loan_quota"
                                placeholder="Contoh: 3000000"
                            />
                        </div>
                    </div>
                </Modal>

                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus Jabatan"
                    confirmText="Ya, Hapus!"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>

            <Pagination
                v-if="roles.data && roles.data.length > 0"
                :pagination="roles"
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
import GearIcon from "@/Components/icons/GearIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import Modal from "@/Components/common/Modal.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import { useAuth } from "@/Composables/useAuth";
import { ref, watch } from "vue";
import { useForm, router, Head, Link } from "@inertiajs/vue3";

const { can } = useAuth();

const breadcrumbs = [{ label: "Konfigurasi" }, { label: "Jabatan" }];

defineOptions({
    layout: AppLayout,
});

const props = defineProps({
    roles: Object,
    search: String,
    sortBy: String,
    sortDirection: String,
});

function fetchRoles({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
} = {}) {
    router.get(
        route("roles.index"),
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
        fetchRoles();
    }, 400);
});

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchRoles({ sortBy: column, sortDirection: direction });
}

const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: null,
    name: "",
    leave_quota_per_year: 0,
    loan_quota: 0,
});

// Currency formatter for IDR
function formatCurrency(val){
    try {
        const num = Number(val || 0);
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
    } catch {
        return `Rp ${Number(val||0).toLocaleString('id-ID')}`;
    }
}

// Buka modal untuk tambah
function openAddModal() {
    if (!can('roles.create')) {
        return;
    }
    form.reset();
    form.leave_quota_per_year = 12;
    form.loan_quota = 0;
    isEditMode.value = false;
    isModalOpen.value = true;
}

// Buka modal untuk edit
function openEditModal(role) {
    if (!can('roles.edit')) {
        return;
    }
    form.id = role.id;
    form.name = role.name;
    form.leave_quota_per_year = role.leave_quota_per_year ?? 0;
    form.loan_quota = role.loan_quota ?? 0;
    selectedItem.value = role;
    isEditMode.value = true;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    form.reset();
}

// Simpan (otomatis create/update)
function saveRole() {
    if (isEditMode.value) {
        if (!can('roles.edit')) {
            return;
        }
        form.put(route("roles.update", form.id), {
            onSuccess: closeModal,
        });
    } else {
        if (!can('roles.create')) {
            return;
        }
        form.post(route("roles.store"), {
            onSuccess: closeModal,
        });
    }
}

// destroy
const selectedItem = ref(null);
const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    if (!can('roles.delete')) {
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
    if (!can('roles.delete')) {
        return;
    }
    router.delete(route("roles.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        preserveScroll: true,
    });
};

// ========== Stats Modal (Users & Permissions) ==========
const isStatsModalOpen = ref(false);
const statsLoading = ref(false);
const statsRole = ref(null);
const statsUsers = ref([]);
const statsPermissions = ref([]);
const activeStatsTab = ref('users');

function closeStatsModal() {
    isStatsModalOpen.value = false;
    statsUsers.value = [];
    statsPermissions.value = [];
}

async function openStatsModal(role, tab = 'users') {
    if (!can('roles.view')) {
        return;
    }

    try {
        statsRole.value = role;
        activeStatsTab.value = tab;
        isStatsModalOpen.value = true;
        statsLoading.value = true;

        const url = route('roles.stats', role.id);
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Gagal memuat data');
        const data = await res.json();
        statsUsers.value = Array.isArray(data.users) ? data.users : [];
        statsPermissions.value = Array.isArray(data.permissions) ? data.permissions : [];
    } catch (e) {
        console.error(e);
        isStatsModalOpen.value = false;
    } finally {
        statsLoading.value = false;
    }
}

// Format permission name dengan menambahkan "Bisa" di depan agar lebih user-friendly
function formatPermissionName(displayName) {
    if (!displayName) return '';
    // Jika sudah dimulai dengan "Bisa", return as is
    if (displayName.toLowerCase().startsWith('bisa')) {
        return displayName;
    }
    // Tambahkan "Bisa" di depan
    return `Bisa ${displayName.toLowerCase()}`;
}
</script>
