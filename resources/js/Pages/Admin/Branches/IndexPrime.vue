<template>
    <div class="px-6 py-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Data Cabang</h1>
            <Button severity="info" @click="openCreate">
                <i class="mr-2 lni lni-plus"></i>
                Tambah Data Cabang
            </Button>
        </div>

        <div class="bg-white rounded-lg border border-gray-200">
            <div class="flex justify-between items-center px-4 py-3 border-b">
                <h1 class="text-xl font-semibold text-gray-800">
                    Daftar Seluruh Cabang
                </h1>
                <div class="flex gap-2 items-center">
                    <IconField>
                        <InputIcon>
                            <i class="lni lni-search-alt" />
                        </InputIcon>
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Pencarian kata kunci"
                        />
                    </IconField>
                </div>
            </div>

            <div class="py-0">
                <div style="overflow-x: auto">
                    <DataTable
                        v-model:filters="filters"
                        :value="rows"
                        dataKey="id"
                        responsiveLayout="scroll"
                        :paginator="true"
                        :rows="10"
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
                        :globalFilterFields="['name', 'region', 'head_name']"
                        :loading="loading"
                        :tableStyle="{ minWidth: 'max-content', width: '100%' }"
                    >
                        <!-- <template #paginatorstart>
                            <div class="flex gap-2 items-center text-gray-500">
                                Show
                            </div>
                        </template>
                        <template #paginatorend>
                            <span class="ml-2 text-gray-500">per page</span>
                        </template> -->
                        <template #empty>
                            Data cabang tidak ditemukan.
                        </template>
                        <template #loading> Memuat data cabang... </template>
                        <Column
                            header="No"
                            style="width: 80px"
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-center px-2"
                            bodyClass="text-gray-800 px-2 text-center"
                        >
                            <template #body="{ index }">{{
                                index + 1
                            }}</template>
                        </Column>
                        <Column
                            field="name"
                            header="Nama Kantor"
                            sortable
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="region"
                            header="Cabang"
                            sortable
                            :filter="true"
                            filterPlaceholder="Cari cabang"
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="head_name"
                            header="Kepala Cabang"
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="employees_count"
                            header="Jumlah Pegawai"
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-center px-2"
                            bodyClass="text-gray-700 px-2 text-center"
                        />
                        <Column
                            header="Aksi"
                            style="width: 120px"
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

        <Dialog
            v-model:visible="showDialog"
            :modal="true"
            :header="dialogTitle"
            class="w-full md:w-2/3 lg:w-1/2"
        >
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block mb-1 text-sm font-medium"
                            >Nama Kantor</label
                        >
                        <InputText
                            v-model="form.name"
                            class="w-full"
                            required
                        />
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium"
                            >Cabang</label
                        >
                        <InputText v-model="form.region" class="w-full" />
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium"
                            >Kepala Cabang</label
                        >
                        <InputText v-model="form.head_name" class="w-full" />
                    </div>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-1 text-sm font-medium"
                                >Email Resmi Cabang</label
                            >
                            <InputText v-model="form.email" class="w-full" />
                        </div>
                        <div>
                            <label class="block mb-1 text-sm font-medium"
                                >No Telepon Resmi Cabang</label
                            >
                            <InputText v-model="form.phone" class="w-full" />
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium"
                            >Alamat Cabang</label
                        >
                        <Textarea
                            v-model="form.address"
                            autoResize
                            rows="3"
                            class="w-full"
                        />
                    </div>
                </div>

                <div class="flex gap-3 items-center mt-6">
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
    </div>
</template>

<script setup>
import { router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
defineOptions({ layout: AppLayout });

// PrimeVue components
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import { FilterMatchMode } from "@primevue/core/api";
import Textarea from "primevue/textarea";

const props = defineProps({
    branches: Object,
});

const rows = computed(() => props.branches?.data ?? []);

const showDialog = ref(false);
const editingId = ref(null);
const loading = ref(false);

const form = useForm({
    name: "",
    region: "",
    head_name: "",
    // employees_count diisi dari server saja, tidak perlu input manual
    email: "",
    phone: "",
    address: "",
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    region: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
    head_name: { value: null, matchMode: FilterMatchMode.STARTS_WITH },
});

const dialogTitle = computed(() =>
    editingId.value ? "Ubah Cabang" : "Tambah Cabang"
);

function openCreate() {
    editingId.value = null;
    form.reset();
    showDialog.value = true;
}

function openEdit(row) {
    editingId.value = row.id;
    form.name = row.name;
    form.region = row.region;
    form.head_name = row.head_name;
    form.email = row.email;
    form.phone = row.phone;
    form.address = row.address;
    showDialog.value = true;
}

function closeDialog() {
    showDialog.value = false;
}

const processing = computed(() => form.processing);

function submit() {
    if (editingId.value) {
        form.put(`/branches/${editingId.value}`, {
            onSuccess: () => closeDialog(),
        });
    } else {
        form.post("/branches", {
            onSuccess: () => closeDialog(),
        });
    }
}

function destroyRow(row) {
    router.delete(`/branches/${row.id}`);
}

// action column handled via template slot
</script>

<style scoped>
/* kecilkan density agar mirip pada screenshot */
:deep(.p-datatable .p-datatable-tbody > tr > td) {
    padding: 1rem 0.8rem !important;
    font-size: 14px !important;
    color: #1f2937 !important;
    font-weight: 500 !important;
    letter-spacing: 0.02em !important;
    line-height: 1.25rem !important;
}
:deep(.p-datatable .p-datatable-thead > tr > th) {
    padding: 1rem !important; /* lebih tinggi */
    background-color: #f9fafb !important; /* bg-gray-50 */
    color: #6b7280 !important; /* text-gray-500 */
    text-transform: uppercase;
    font-size: 13px !important; /* sedikit lebih besar */
    font-weight: 600 !important; /* semi-bold */
    letter-spacing: 0.02em !important; /* tracking-wide-ish */
}

/* besarkan judul kolom saat sortable */
:deep(.p-datatable .p-datatable-thead > tr > th .p-column-title) {
    font-size: 13px !important;
}

/* besarkan ikon sort agar proporsional */
:deep(.p-datatable .p-datatable-thead > tr > th .p-sortable-column-icon) {
    font-size: 0.95rem !important;
}

/* border bawah header agar seperti bootstrap */
:deep(.p-datatable .p-datatable-thead) {
    border-bottom: 1px solid #e5e7eb !important; /* border-gray-200 */
}

/* hover row halus */
:deep(.p-datatable .p-datatable-tbody > tr:hover) {
    background-color: #fafafa !important;
}

/* teks body default */
:deep(.p-datatable .p-datatable-tbody > tr > td) {
    color: #1f2937 !important; /* text-gray-800 */
}
</style>
