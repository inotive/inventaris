<template>
    <div class="px-6 py-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold text-gray-800">
                Manajemen Checklistsss
            </h1>
            <Link
                v-if="can('checklists.create')"
                :href="route('checklists.create')"
                as="button"
                class="inline-flex items-center px-3 py-2 rounded-md p-button p-button-info p-component"
            >
                <i class="mr-2 lni lni-plus"></i>
                Tambah Data Checklist
            </Link>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h1 class="text-xl font-semibold text-gray-800">
                    Daftar Checklist
                </h1>
                <div class="flex items-center gap-2">
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
                            Data checklist tidak ditemukan.
                        </template>
                        <template #loading> Memuat data checklist... </template>

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
                            header="Nama Checklist"
                            sortable
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="code_sop"
                            header="Nomor SOP"
                            sortable
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
                        <Column
                            field="category"
                            header="Kategori"
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2"
                            bodyClass="text-gray-700 px-2"
                        />
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
                                        'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500':
                                            (data.status || '').toLowerCase() === 'active',
                                        'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-500':
                                            (data.status || '').toLowerCase() === 'inactive',
                                        'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-200':
                                            (data.status || '').toLowerCase() === 'draft',
                                    }"
                                >
                                    {{
                                        (data.status || '').toLowerCase() === "active"
                                            ? "Aktif"
                                            : (data.status || '').toLowerCase() === "inactive"
                                                ? "Tidak Aktif"
                                                : "Draft"
                                    }}
                                </span>
                            </template>
                        </Column>

                        <Column
                            v-if="can('checklists.view') || can('checklists.edit') || can('checklists.delete')"
                            header="Aksi"
                            style="width: 160px"
                            headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-center px-2"
                            bodyClass="px-2 text-center"
                        >
                            <template #body="{ data }">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        v-if="can('checklists.view')"
                                        :href="route('checklists.show', data.id)"
                                        class="inline-flex items-center justify-center w-8 h-8 text-white bg-blue-500 rounded hover:bg-blue-600"
                                        aria-label="Lihat"
                                    >
                                        <i class="text-sm lni lni-eye"></i>
                                    </Link>

                                    <button
                                        v-if="can('checklists.edit')"
                                        type="button"
                                        aria-label="Edit"
                                        class="inline-flex items-center justify-center w-8 h-8 text-white bg-indigo-500 rounded hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                        @click="() => openEdit(data)"
                                    >
                                        <i class="text-sm lni lni-pencil-1"></i>
                                    </button>
                                    <button
                                        v-if="can('checklists.delete')"
                                        type="button"
                                        aria-label="Hapus"
                                        class="inline-flex items-center justify-center w-8 h-8 text-white bg-red-500 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300"
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
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label class="block mb-1 text-sm text-gray-600">Nama Checklist</label>
                    <InputText v-model="form.name" placeholder="Nama" class="w-full" />
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
                </div>
                <div>
                    <label class="block mb-1 text-sm text-gray-600">Nomor SOP</label>
                    <InputText v-model="form.code_sop" placeholder="SOP-XXX-YYY" class="w-full" />
                    <small v-if="form.errors.code_sop" class="text-red-500">{{ form.errors.code_sop }}</small>
                </div>

                <div>
                    <label class="block mb-1 text-sm text-gray-600">Kategori</label>
                    <Dropdown class="w-full" v-model="form.category_id" :options="categoryOptions" optionLabel="label" optionValue="value" placeholder="Pilih Kategori" />
                    <small v-if="form.errors.category_id" class="text-red-500">{{ form.errors.category_id }}</small>
                </div>
                <div>
                    <label class="block mb-1 text-sm text-gray-600">Departemen</label>
                    <Dropdown class="w-full" v-model="form.department_id" :options="departmentOptions" optionLabel="label" optionValue="value" placeholder="Pilih Departemen" />
                    <small v-if="form.errors.department_id" class="text-red-500">{{ form.errors.department_id }}</small>
                </div>

                <div>
                    <label class="block mb-1 text-sm text-gray-600">Cabang</label>
                    <Dropdown class="w-full" v-model="form.branch_id" :options="branchOptions" optionLabel="label" optionValue="value" placeholder="Pilih Cabang" />
                    <small v-if="form.errors.branch_id" class="text-red-500">{{ form.errors.branch_id }}</small>
                </div>
                <div>
                    <label class="block mb-1 text-sm text-gray-600">Status</label>
                    <Dropdown class="w-full" v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" placeholder="Pilih Status" />
                    <small v-if="form.errors.status" class="text-red-500">{{ form.errors.status }}</small>
                </div>

                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm text-gray-600">Keterangan</label>
                    <Textarea v-model="form.description" rows="3" autoResize placeholder="Keterangan" class="w-full" />
                    <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <Button type="submit" label="Simpan" icon="pi pi-check" :loading="processing" />
                <Button type="button" label="Batal" severity="secondary" icon="pi pi-times" @click="closeDialog" />
            </div>
        </form>
    </Dialog>
</template>

<script setup>
import { router, useForm, Link } from "@inertiajs/vue3";
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
import { useAuth } from "@/Composables/useAuth";

defineOptions({ layout: AppLayout });

const { can } = useAuth();

const props = defineProps({
    checklists: Object,
    filters: Object,
    categories: Array,
    departments: Array,
    branches: Array,
});

const rows = computed(() => props.checklists?.data ?? []);
const total = computed(() => props.checklists?.total ?? 0);
const perPage = computed(() => props.checklists?.per_page ?? 10);
const currentPage = computed(() => props.checklists?.current_page ?? 1);
const first = computed(() => (currentPage.value - 1) * perPage.value);
const sortField = ref(props.filters?.sort_field ?? "id");
const sortOrder = ref(props.filters?.sort_order ?? 1);
const search = ref(props.filters?.q ?? "");
const loading = ref(false);

const showDialog = ref(false);
const editingId = ref(null);
const form = useForm({
    name: "",
    code_sop: "",
    category_id: null,
    department_id: null,
    branch_id: null,
    status: "draft",
    description: "",
});

const categoryOptions = computed(() =>
    (props.categories ?? []).map((c) => ({ label: c.name, value: c.id }))
);
const departmentOptions = computed(() =>
    (props.departments ?? []).map((d) => ({ label: d.name, value: d.id }))
);
const branchOptions = computed(() =>
    (props.branches ?? []).map((b) => ({ label: b.name, value: b.id }))
);
const statusOptions = [
    { label: "Draft", value: "draft" },
    { label: "Aktif", value: "active" },
    { label: "Nonaktif", value: "inactive" },
];

const dialogTitle = computed(() =>
    editingId.value ? "Ubah Checklist" : "Tambah Checklist"
);

function openCreate() {
    if (!can('checklists.create')) return;
    editingId.value = null;
    form.reset();
    form.status = "draft";
    showDialog.value = true;
}

function openEdit(row) {
    if (!can('checklists.edit')) return;
    editingId.value = row.id;
    form.name = row.name;
    form.code_sop = row.code_sop;
    form.status = row.status;
    form.description = row.description;
    form.category_id = row.category_id ?? null;
    form.department_id = row.department_id ?? null;
    form.branch_id = row.branch_id ?? null;
    showDialog.value = true;
}

function closeDialog() {
    showDialog.value = false;
}

const processing = computed(() => form.processing);

function submit() {
    if (editingId.value) {
        if (!can('checklists.edit')) return;
        form.put(`/checklists/${editingId.value}`, { onSuccess: () => closeDialog() });
    } else {
        if (!can('checklists.create')) return;
        form.post("/checklists", { onSuccess: () => closeDialog() });
    }
}

function destroyRow(row) {
    if (!can('checklists.delete')) return;
    if (!confirm("Yakin ingin menghapus?")) return;
    router.delete(`/checklists/${row.id}`);
}

function onPage(event) {
    router.get(
        route("checklists.index"),
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
        route("checklists.index"),
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
            route("checklists.index"),
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
