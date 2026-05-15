<template>
    <div class="px-6 py-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">
                Manajemen Kendaraan
            </h1>
            <Button severity="info" @click="openCreate">
                <i class="mr-2 lni lni-plus"></i>
                Tambah Data Kendaraan
            </Button>
        </div>

        <div class="bg-white rounded-lg border border-gray-200">
            <div class="flex justify-between items-center px-4 py-3 border-b">
                <h1 class="text-xl font-semibold text-gray-800">
                    Daftar Kendaraan
                </h1>
                <div class="flex gap-2 items-center">
                    <IconField>
                        <InputIcon>
                            <i class="lni lni-search-alt" />
                        </InputIcon>
                        <InputText
                            v-model="search"
                            placeholder="Pencarian kata kunci"
                        />
                    </IconField>
                </div>
            </div>

            <div class="py-0">
                <div style="overflow-x: auto">
                    <DataTable
                        :value="rows"
                        dataKey="id"
                        responsiveLayout="scroll"
                        :paginator="true"
                        :lazy="true"
                        :totalRecords="total"
                        :first="first"
                        :rows="perPage"
                        :sortField="sortField"
                        :sortOrder="sortOrder"
                        class="w-full p-datatable-sm"
                        :rowHover="true"
                        :showGridlines="false"
                        :rowsPerPageOptions="[10, 25, 50, 100]"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                        :pt="{
                            thead: { class: 'bg-gray-50' },
                            headerRow: { class: 'text-left' },
                            headerCell: {
                                class: 'bg-gray-50 text-gray-500 uppercase text-[11px] tracking-wide px-2',
                            },
                            bodyRow: { class: 'text-gray-800' },
                            bodyCell: { class: 'text-gray-800 px-2' },
                        }"
                        :ptOptions="{ mergeSections: true, mergeProps: true }"
                        :loading="loading"
                        :tableStyle="{ minWidth: 'max-content', width: '100%' }"
                        @page="onPage"
                        @sort="onSort"
                    >
                        <template #empty>
                            Data kendaraan tidak ditemukan.
                        </template>
                        <template #loading> Memuat data kendaraan... </template>

                        <Column
                            header="No"
                            style="width: 80px"
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-center px-2"
                            bodyClass="text-gray-700 px-2 text-center"
                        >
                            <template #body="{ index }">{{
                                first + index + 1
                            }}</template>
                        </Column>

                        <Column
                            field="name"
                            header="Nama Kendaraan"
                            sortable
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="brand"
                            header="Merk"
                            sortable
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="asset_code"
                            header="Kode Asset"
                            sortable
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="plate_number"
                            header="No. Polisi"
                            sortable
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="type"
                            header="Jenis"
                            sortable
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        >
                            <template #body="{ data }">
                                <span
                                    class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-xs font-medium"
                                    >{{ data.type }}</span
                                >
                            </template>
                        </Column>
                        <Column
                            field="department"
                            header="Departemen"
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="branch"
                            header="Cabang"
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="status"
                            header="Status"
                            sortable
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        >
                            <template #body="{ data }">
                                <span
                                    :class="{
                                        'rounded-full px-2 py-0.5 text-xs font-medium': true,
                                        'bg-green-50 text-green-600':
                                            data.status === 'active',
                                        'bg-red-50 text-red-600':
                                            data.status === 'inactive',
                                    }"
                                    >{{
                                        data.status === "active"
                                            ? "Aktif"
                                            : "Tidak Aktif"
                                    }}</span
                                >
                            </template>
                        </Column>

                        <Column
                            header="Aksi"
                            style="width: 140px"
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-center px-2"
                            bodyClass="px-2 text-center"
                        >
                            <template #body="{ data }">
                                <div
                                    class="flex gap-2 justify-center items-center"
                                >
                                    <button
                                        type="button"
                                        aria-label="Edit"
                                        class="inline-flex justify-center items-center w-8 h-8 text-white bg-indigo-500 rounded hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                        @click="() => openEdit(data)"
                                    >
                                        <i class="text-sm lni lni-pencil-1"></i>
                                    </button>
                                    <button
                                        type="button"
                                        aria-label="Hapus"
                                        class="inline-flex justify-center items-center w-8 h-8 text-white bg-red-500 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300"
                                        @click="() => destroyRow(data)"
                                    >
                                        <i class="text-sm lni lni-trash-3"></i>
                                    </button>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>
    </div>

    <Dialog
        v-model:visible="showDialog"
        modal
        :header="dialogTitle"
        :style="{ width: '44rem' }"
    >
        <form @submit.prevent="submit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1"
                        >Nama Kendaraan</label
                    >
                    <InputText
                        v-model="form.name"
                        placeholder="Nama"
                        class="w-full"
                    />
                    <small v-if="form.errors.name" class="text-red-500">{{
                        form.errors.name
                    }}</small>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Merk</label>
                    <InputText
                        v-model="form.brand"
                        placeholder="Honda / Hino"
                        class="w-full"
                    />
                    <small v-if="form.errors.brand" class="text-red-500">{{
                        form.errors.brand
                    }}</small>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1"
                        >Kode Asset</label
                    >
                    <InputText
                        v-model="form.asset_code"
                        placeholder="ASSET-00001"
                        class="w-full"
                    />
                    <small v-if="form.errors.asset_code" class="text-red-500">{{
                        form.errors.asset_code
                    }}</small>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1"
                        >No. Polisi</label
                    >
                    <InputText
                        v-model="form.plate_number"
                        placeholder="B 1234 CD"
                        class="w-full"
                    />
                    <small
                        v-if="form.errors.plate_number"
                        class="text-red-500"
                        >{{ form.errors.plate_number }}</small
                    >
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1"
                        >Jenis Kendaraan</label
                    >
                    <Dropdown
                        class="w-full"
                        v-model="form.type"
                        :options="typeOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Pilih Jenis"
                    />
                    <small v-if="form.errors.type" class="text-red-500">{{
                        form.errors.type
                    }}</small>
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1"
                        >Status</label
                    >
                    <Dropdown
                        class="w-full"
                        v-model="form.status"
                        :options="statusOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Pilih Status"
                    />
                    <small v-if="form.errors.status" class="text-red-500">{{
                        form.errors.status
                    }}</small>
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1"
                        >Departemen</label
                    >
                    <Dropdown
                        class="w-full"
                        v-model="form.department_id"
                        :options="departmentOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Pilih Departemen"
                    />
                    <small
                        v-if="form.errors.department_id"
                        class="text-red-500"
                        >{{ form.errors.department_id }}</small
                    >
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1"
                        >Cabang</label
                    >
                    <Dropdown
                        class="w-full"
                        v-model="form.branch_id"
                        :options="branchOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Pilih Cabang"
                    />
                    <small v-if="form.errors.branch_id" class="text-red-500">{{
                        form.errors.branch_id
                    }}</small>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-600 mb-1"
                        >Keterangan</label
                    >
                    <Textarea
                        v-model="form.description"
                        rows="3"
                        autoResize
                        placeholder="Keterangan"
                        class="w-full"
                    />
                    <small
                        v-if="form.errors.description"
                        class="text-red-500"
                        >{{ form.errors.description }}</small
                    >
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <Button
                    type="submit"
                    label="Simpan"
                    icon="pi pi-check"
                    :loading="processing"
                />
                <Button
                    type="button"
                    label="Batal"
                    severity="secondary"
                    icon="pi pi-times"
                    @click="closeDialog"
                />
            </div>
        </form>
    </Dialog>
</template>

<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Dialog from "primevue/dialog";
import Dropdown from "primevue/dropdown";
import Textarea from "primevue/textarea";

defineOptions({ layout: AppLayout });

const props = defineProps({
    vehicles: Object,
    filters: Object,
    branches: Array,
    departments: Array,
});

const rows = computed(() => props.vehicles?.data ?? []);
const total = computed(() => props.vehicles?.total ?? 0);
const perPage = computed(() => props.vehicles?.per_page ?? 10);
const currentPage = computed(() => props.vehicles?.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * perPage.value);
const sortField = ref(props.filters?.sort_field ?? "id");
const sortOrder = ref(props.filters?.sort_order ?? 1);
const search = ref(props.filters?.q ?? "");
const loading = ref(false);

const showDialog = ref(false);
const editingId = ref(null);
const form = useForm({
    name: "",
    brand: "",
    asset_code: "",
    plate_number: "",
    type: "Pickup",
    branch_id: null,
    department_id: null,
    status: "active",
    description: "",
});

const typeOptions = [
    { label: "Pickup", value: "Pickup" },
    { label: "Motor", value: "Motor" },
    { label: "Truck", value: "Truck" },
];
const statusOptions = [
    { label: "Aktif", value: "active" },
    { label: "Tidak Aktif", value: "inactive" },
];

const branchOptions = computed(() =>
    (props.branches ?? []).map((b) => ({ label: b.name, value: b.id }))
);
const departmentOptions = computed(() =>
    (props.departments ?? []).map((d) => ({ label: d.name, value: d.id }))
);

const dialogTitle = computed(() =>
    editingId.value ? "Ubah Kendaraan" : "Tambah Kendaraan"
);

function openCreate() {
    editingId.value = null;
    form.reset();
    form.type = "Pickup";
    form.status = "active";
    showDialog.value = true;
}

function openEdit(row) {
    editingId.value = row.id;
    form.name = row.name;
    form.brand = row.brand;
    form.asset_code = row.asset_code;
    form.plate_number = row.plate_number;
    form.type = row.type;
    form.status = row.status;
    form.description = row.description;
    form.branch_id = row.branch_id ?? null;
    form.department_id = row.department_id ?? null;
    showDialog.value = true;
}

function closeDialog() {
    showDialog.value = false;
}

const processing = computed(() => form.processing);

function submit() {
    if (editingId.value) {
        form.put(`/vehicles/${editingId.value}`, {
            onSuccess: () => closeDialog(),
        });
    } else {
        form.post("/vehicles", { onSuccess: () => closeDialog() });
    }
}

function destroyRow(row) {
    if (!confirm("Yakin ingin menghapus?")) return;
    router.delete(`/vehicles/${row.id}`);
}

function onPage(event) {
    router.get(
        route("vehicles.index"),
        {
            page: event.page + 1,
            per_page: event.rows,
            q: search.value,
            sort_field: sortField.value,
            sort_order: sortOrder.value,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
}

function onSort(event) {
    sortField.value = event.sortField;
    sortOrder.value = event.sortOrder;
    router.get(
        route("vehicles.index"),
        {
            page: 1,
            per_page: perPage.value,
            q: search.value,
            sort_field: sortField.value,
            sort_order: sortOrder.value,
        },
        { preserveState: true, preserveScroll: true, replace: true }
    );
}

let t;
watch(search, (val) => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get(
            route("vehicles.index"),
            {
                page: 1,
                per_page: perPage.value,
                q: val,
                sort_field: sortField.value,
                sort_order: sortOrder.value,
            },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 400);
});
</script>

<style scoped>
:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 1rem 0.8rem !important;
    font-size: 14px !important;
    color: #1f2937 !important;
    font-weight: 500 !important;
    letter-spacing: 0.02em !important;
    line-height: 1.25rem !important;
}
:deep(.p-datatable .p-datatable-thead > tr > th) {
    padding: 1rem !important;
    background-color: #f9fafb !important;
    color: #6b7280 !important;
    text-transform: uppercase;
    font-size: 13px !important;
    font-weight: 600 !important;
    letter-spacing: 0.02em !important;
}
:deep(.p-datatable .p-datatable-thead) {
    border-bottom: 1px solid #e5e7eb !important;
}
:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #fafafa !important;
}
</style>
