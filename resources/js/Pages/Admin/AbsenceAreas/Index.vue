<template>
    <Head title="Wilayah Absen" />

    <div class="flex flex-col h-full gap-3 px-3 py-0.5 overflow-hidden">
        <div class="flex items-center justify-between h-10">
            <Breadcrumb :items="breadcrumbs" />
        </div>

        <div class="z-0 w-full h-80">
            <l-map
                v-model:zoom="zoom"
                :center="[form.latitude, form.longitude]"
                style="height: 100%; width: 100%"
                @click="onMapClick"
            >
                <l-tile-layer
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    layer-type="base"
                    name="OpenStreetMap"
                />
                <l-marker
                    v-if="marker"
                    :lat-lng="[form.latitude, form.longitude]"
                    :draggable="true"
                    @update:lat-lng="onMarkerDrag"
                />
            </l-map>
        </div>

        <div class="flex items-end justify-between gap-4 mt-4">
            <div class="space-y-1 text-sm">
                <label
                    for="branch"
                    class="text-sm font-medium text-gray-900 dark:text-white"
                    >Kantor Cabang<span class="text-red-500">*</span></label
                >
                <Select
                    id="branch"
                    v-model="form.branch_id"
                    label="Pilih kantor cabang"
                    class="w-44"
                    :items="branches"
                />
                <div v-if="form.errors.branch_id" class="text-sm text-red-500">
                    {{ form.errors.branch_id }}
                </div>
            </div>
            <div class="space-y-1 text-sm">
                <label
                    for="name"
                    class="text-sm font-medium text-gray-900 dark:text-white"
                    >Nama Wilayah<span class="text-red-500">*</span></label
                >
                <input
                    id="name"
                    class="text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg w-60 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                    type="text"
                    v-model="form.name"
                    required
                    placeholder="Masukkan nama wilayah absen"
                />
                <div v-if="form.errors.name" class="text-sm text-red-500">
                    {{ form.errors.name }}
                </div>
            </div>
            <div class="space-y-1 text-sm">
                <label
                    for="latitude"
                    class="text-sm font-medium text-gray-900 dark:text-white"
                    >Latitude</label
                >
                <input
                    id="latitude"
                    v-model.number="form.latitude"
                    type="number"
                    step="0.000001"
                    class="text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg w-44 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                />
            </div>
            <div class="space-y-1 text-sm">
                <label
                    for="longitude"
                    class="text-sm font-medium text-gray-900 dark:text-white"
                    >Longitude</label
                >
                <input
                    id="longitude"
                    v-model.number="form.longitude"
                    type="number"
                    step="0.000001"
                    class="text-sm font-medium text-gray-600 placeholder-gray-500 border-gray-400 rounded-lg w-44 placeholder:font-normal dark:border-white dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
                />
            </div>

            <button
                @click="getMyLocation"
                class="p-2 text-white bg-red-500 rounded-lg"
                title="Lokasi saya"
            >
                <LocationIcon />
            </button>

            <div class="flex items-center gap-2">
                <button
                    v-if="isEditMode"
                    @click="cancelEdit"
                    type="button"
                    class="p-2.5 whitespace-nowrap text-white bg-gray-500 hover:bg-gray-600 rounded flex items-center gap-2"
                >
                    <span class="hidden text-sm md:block">Batal</span>
                </button>
                <button
                    v-if="isEditMode ? can('absence_areas.edit') : can('absence_areas.create')"
                    @click="saveArea"
                    type="button"
                    :class="[
                        'p-2.5 whitespace-nowrap text-white rounded flex items-center gap-2',
                        isEditMode ? 'bg-yellow-500' : 'bg-blue-500',
                    ]"
                >
                    <PlusSquareIcon />
                    <span class="hidden text-sm md:block">
                        {{ isEditMode ? "Update Wilayah Absen" : "Tambah Wilayah Absen" }}
                    </span>
                </button>
            </div>
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
                    Daftar Wilayah Absen
                </div>

                <div class="flex items-center gap-3">
                    <div class="relative py-2">
                        <div class="absolute -translate-y-1/2 left-4 top-1/2">
                            <SearchIcon class="text-gray-400" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari wilayah absen"
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
                                class="p-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div class="flex items-center justify-center">
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
                                    class="flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Nama Wilayah
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
                                @click="changeSort('latitude')"
                                class="p-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Latitude
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'latitude' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'latitude' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                @click="changeSort('longitude')"
                                class="p-3 bg-gray-100 border border-gray-200 cursor-pointer dark:border-gray-600 dark:bg-gray-800"
                            >
                                <div
                                    class="flex items-center justify-center gap-2"
                                >
                                    <p
                                        class="font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                    >
                                        Longitude
                                    </p>
                                    <div class="flex flex-col items-center">
                                        <UpIcon
                                            :class="[
                                                '-mb-1',
                                                sortBy === 'longitude' &&
                                                sortDirection === 'asc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                        <DownIcon
                                            :class="[
                                                '-mt-1',
                                                sortBy === 'longitude' &&
                                                sortDirection === 'desc'
                                                    ? 'text-gray-900 dark:text-gray-200'
                                                    : 'text-gray-400 dark:text-gray-500',
                                            ]"
                                        />
                                    </div>
                                </div>
                            </th>
                            <th
                                v-if="can('absence_areas.edit') || can('absence_areas.delete')"
                                class="p-3 bg-gray-100 border-gray-200 border-y dark:border-gray-600 dark:bg-gray-800"
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
                        <template v-if="areas.data && areas.data.length > 0">
                            <tr
                                v-for="(area, index) in areas.data"
                                :key="area.id"
                                class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                <td class="p-2.5 border-y border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-center whitespace-nowrap">
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{
                                                (areas.current_page - 1) *
                                                    areas.per_page +
                                                index + 1
                                            }}.
                                        </p>
                                    </div>
                                </td>
                                <td class="p-2.5 px-8 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center whitespace-nowrap">
                                        <div class="flex flex-col leading-tight">
                                            <p class="font-medium text-gray-800 dark:text-white/90">
                                                {{ area.name }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-2.5 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-center px-3">
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ area.latitude }}
                                        </p>
                                    </div>
                                </td>
                                <td class="p-2.5 border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-center px-3 whitespace-nowrap">
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ area.longitude }}
                                        </p>
                                    </div>
                                </td>
                                <td
                                    v-if="can('absence_areas.edit') || can('absence_areas.delete')"
                                    class="py-2.5 px-8 border-y border-gray-200 dark:border-gray-600"
                                >
                                    <div class="flex justify-center gap-3 whitespace-nowrap">
                                        <button v-if="can('absence_areas.edit')" @click="editArea(area)" title="Edit">
                                            <EditIcon class="text-yellow-500" />
                                        </button>
                                        <button v-if="can('absence_areas.delete')" @click="openConfirmModal(area)" title="Hapus">
                                            <TrashIcon class="text-red-500" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-else>
                            <td
                                :colspan="(can('absence_areas.edit') || can('absence_areas.delete')) ? 5 : 4"
                                class="py-6 font-medium text-center text-gray-500 dark:text-gray-400"
                            >
                                Tidak ada data ditemukan
                            </td>
                        </tr>
                    </tbody>
                </table>

                <Pagination :pagination="areas" @page-change="changePage"
                        @per-page-change="perPageChanged" class="border-t" />

                <ConfirmModal
                    :show="isConfirmModalOpen"
                    :question="`Yakin ingin menghapus`"
                    :selected="`${selectedItem?.name}`"
                    title="Hapus Wilayah Absen"
                    confirmText="Ya, Hapus!"
                    maxWidth="md"
                    @close="closeConfirmModal"
                    @confirm="destroyData"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import PlusSquareIcon from "@/Components/icons/PlusSquareIcon.vue";
import SearchIcon from "@/Components/icons/SearchIcon.vue";
import EditIcon from "@/Components/icons/EditIcon.vue";
import UpIcon from "@/Components/icons/UpIcon.vue";
import LocationIcon from "@/Components/icons/LocationIcon.vue";
import DownIcon from "@/Components/icons/DownIcon.vue";
import TrashIcon from "@/Components/icons/TrashIcon.vue";
import Breadcrumb from "@/Components/common/Breadcrumb.vue";
import ConfirmModal from "@/Components/common/ConfirmModal.vue";
import Pagination from "@/Components/common/Pagination.vue";
import Select from "@/Components/form/SelectPemakaian.vue";
import { useForm, router, Head } from "@inertiajs/vue3";
import { ref, watch, onMounted } from "vue";
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import L from "leaflet";
import markerIcon from "leaflet/dist/images/marker-icon.png";
import markerIcon2x from "leaflet/dist/images/marker-icon-2x.png";
import markerShadow from "leaflet/dist/images/marker-shadow.png";
import { useAuth } from "@/Composables/useAuth";

const { can } = useAuth();

defineOptions({
    layout: AppLayout,
});

const breadcrumbs = [{ label: "Konfigurasi" }, { label: "Wilayah Absen" }];

const props = defineProps({
    areas: Object,
    branches: Array,
    search: String,
    sortBy: String,
    sortDirection: String,
    perPage: Number,
});

const zoom = ref(15);
const marker = ref(true);

// Klik map → pasang/geser marker
function onMapClick(e) {
    form.latitude = Number(e.latlng.lat.toFixed(8));
    form.longitude = Number(e.latlng.lng.toFixed(8));
    marker.value = true;
}

// Drag marker → update input
function onMarkerDrag(newLatLng) {
    form.latitude = Number(newLatLng.lat.toFixed(8));
    form.longitude = Number(newLatLng.lng.toFixed(8));
}

// Button: get my location
function getMyLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                form.latitude = Number(position.coords.latitude.toFixed(8));
                form.longitude = Number(position.coords.longitude.toFixed(8));
                zoom.value = 15;
                marker.value = true;
            },
            () => {
                alert("Unable to retrieve your location.");
            }
        );
    } else {
        alert("Geolocation is not supported by your browser.");
    }
}

onMounted(() => {
    // Fix for Leaflet default markers
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: markerIcon2x,
        iconUrl: markerIcon,
        shadowUrl: markerShadow,
    });

    getMyLocation();
});

function fetchArea({
    sortBy = props.sortBy,
    sortDirection = props.sortDirection,
    page = null,
} = {}) {
    router.get(
        route("absence-areas.index"),
        {
            search: search.value,
            per_page: perPage.value,
            sortBy,
            sortDirection,
            page: page || props.areas?.current_page || 1,
        },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
}

const search = ref(props.search || "");
const perPage = ref(props.perPage || 10);

function changePage(page) {
    fetchArea({ page });
}

function perPageChanged(newPerPage) {
    perPage.value = newPerPage;
    fetchArea({ page: 1 });
}

let timeout = null;
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        fetchArea();
    }, 400);
});

function changeSort(column) {
    let direction = "asc";
    if (props.sortBy === column && props.sortDirection === "asc") {
        direction = "desc";
    }
    fetchArea({ sortBy: column, sortDirection: direction });
}

const isEditMode = ref(false);

const form = useForm({
    id: null,
    name: "",
    branch_id: "",
    latitude: "",
    longitude: "",
});

watch(
    () => form.branch_id,
    (newVal) => {
        if (!isEditMode.value) {
            const branch = props.branches.find((b) => b.id === newVal);
            form.name = branch ? branch.name : "";
        }
    }
);

// Buka modal untuk edit
function editArea(area) {
    if (!can('absence_areas.edit')) return;
    form.id = area.id;
    form.branch_id = area.branch_id;
    form.name = area.name;
    form.latitude = area.latitude;
    form.longitude = area.longitude;
    selectedItem.value = area;
    isEditMode.value = true;
}

// Batal edit
function cancelEdit() {
    form.reset();
    form.clearErrors();
    isEditMode.value = false;
    selectedItem.value = null;
    getMyLocation();
}

// Simpan (otomatis create/update)
function saveArea() {
    if (isEditMode.value) {
        if (!can('absence_areas.edit')) return;
        form.put(route("absence-areas.update", form.id), {
            onSuccess: () => {
                form.reset();
                isEditMode.value = false;
                getMyLocation();
            },
        });
    } else {
        if (!can('absence_areas.create')) return;
        form.post(route("absence-areas.store"), {
            onSuccess: () => {
                form.reset();
                isEditMode.value = false;
                getMyLocation();
            },
        });
    }
}

// destroy
const selectedItem = ref(null);

const isConfirmModalOpen = ref(false);
const openConfirmModal = (item) => {
    if (!can('absence_areas.delete')) return;
    selectedItem.value = item;
    isConfirmModalOpen.value = true;
};
const closeConfirmModal = () => {
    selectedItem.value = null;
    isConfirmModalOpen.value = false;
};
const destroyData = () => {
    if (!can('absence_areas.delete')) return;
    router.delete(route("absence-areas.destroy", selectedItem.value.id), {
        onSuccess: () => {
            closeConfirmModal();
        },
        preserveScroll: true,
    });
};
</script>
