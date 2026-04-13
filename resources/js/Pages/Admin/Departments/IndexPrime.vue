<template>
  <div class="px-6 py-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold text-gray-800">Data Departemen</h1>
      <Button severity="info" v-if="can('departments.create')" @click="openCreate">
        <i class="mr-2 lni lni-plus"></i>
        Tambah Data Departemen
      </Button>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg">
      <div class="flex items-center justify-between px-4 py-3 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Daftar Seluruh Departemen</h1>
        <div class="flex items-center gap-2">
          <IconField>
            <InputIcon>
              <i class="lni lni-search-alt" />
            </InputIcon>
            <InputText v-model="search" placeholder="Pencarian kata kunci" />
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
            @page="onPage"
            @sort="onSort"
            class="w-full p-datatable-sm"
            :rowHover="true"
            :showGridlines="false"
            :rowsPerPageOptions="[10, 25, 50, 100]"
            paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
            :pt="{
              thead: { class: 'bg-gray-50' },
              headerRow: { class: 'text-left' },
              headerCell: { class: 'bg-gray-50 text-gray-500 uppercase text-[11px] tracking-wide px-2' },
              bodyRow: { class: 'text-red-800' },
              bodyCell: { class: 'text-red-800 px-2' },
            }"
            :ptOptions="{ mergeSections: true, mergeProps: true }"
            :loading="loading"
            :tableStyle="{ minWidth: 'max-content', width: '100%' }"
          >
            <template #empty> Data departemen tidak ditemukan. </template>
            <template #loading> Memuat data departemen... </template>

            <Column header="No" style="width: 80px" headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-center px-2" bodyClass="text-gray-700 px-2 text-center">
              <template #body="{ index }">{{ first + index + 1 }}</template>
            </Column>

            <Column field="name" header="Nama Departemen" sortable headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2" bodyClass="text-gray-700 px-2" />
            <Column field="code" header="Kode" sortable headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2" bodyClass="text-gray-700 px-2" />
            <Column field="description" header="Keterangan" headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-start px-2" bodyClass="text-gray-700 px-2" />

            <Column header="Aksi" style="width: 120px" headerClass="bg-gray-50 text-gray-500 uppercase text-xs text-center px-2" bodyClass="px-2 text-center">
              <template #body="{ data }">
                <div class="flex items-center justify-center gap-2">
                  <button
                    v-if="can('departments.edit')"
                    type="button" aria-label="Edit"
                    class="inline-flex items-center justify-center w-8 h-8 text-white bg-indigo-500 rounded hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    @click="() => openEdit(data)"
                  >
                    <i class="text-sm lni lni-pencil-1"></i>
                  </button>
                  <button
                    v-if="can('departments.delete')"
                    type="button" aria-label="Hapus"
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

    <Dialog v-model:visible="showDialog" modal :header="dialogTitle" :style="{ width: '38rem' }">
      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
          <div>
            <label class="block mb-1 text-sm text-gray-600">Nama Departemen</label>
            <InputText v-model="form.name" placeholder="Nama" class="w-full" />
            <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
          </div>
          <div>
            <label class="block mb-1 text-sm text-gray-600">Kode</label>
            <InputText v-model="form.code" placeholder="Kode" class="w-full" />
            <small v-if="form.errors.code" class="text-red-500">{{ form.errors.code }}</small>
          </div>
          <div class="md:col-span-2">
            <label class="block mb-1 text-sm text-gray-600">Keterangan</label>
            <Textarea v-model="form.description" rows="3" autoResize placeholder="Keterangan" class="w-full" />
            <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button v-if="can(editingId.value ? 'departments.edit' : 'departments.create')" type="submit" label="Simpan" icon="pi pi-check" :loading="processing" />
          <Button type="button" label="Batal" severity="secondary" icon="pi pi-times" @click="closeDialog" />
        </div>
      </form>
    </Dialog>
  </div>
</template>

<script setup>
import { router, useForm } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Textarea from 'primevue/textarea'
import { useAuth } from '@/Composables/useAuth'

defineOptions({ layout: AppLayout })

const { can } = useAuth()

const props = defineProps({ departments: Object, filters: Object })

const rows = computed(() => props.departments?.data ?? [])
const total = computed(() => props.departments?.total ?? 0)
const perPage = computed(() => props.departments?.per_page ?? 10)
const currentPage = computed(() => props.departments?.current_page ?? 1)
const first = computed(() => (currentPage.value - 1) * perPage.value)
const sortField = ref(props.filters?.sort_field ?? 'id')
const sortOrder = ref(props.filters?.sort_order ?? 1)
const search = ref(props.filters?.q ?? '')
const showDialog = ref(false)
const editingId = ref(null)
const loading = ref(false)

const form = useForm({ name: '', code: '', description: '' })

const dialogTitle = computed(() => (editingId.value ? 'Ubah Departemen' : 'Tambah Departemen'))

function openCreate() {
  if (!can('departments.create')) return
  editingId.value = null
  form.reset()
  showDialog.value = true
}

function openEdit(row) {
  if (!can('departments.edit')) return
  editingId.value = row.id
  form.name = row.name
  form.code = row.code
  form.description = row.description
  showDialog.value = true
}

function closeDialog() { showDialog.value = false }

const processing = computed(() => form.processing)

function submit() {
  if (editingId.value) {
    if (!can('departments.edit')) return
    form.put(`/departments/${editingId.value}`, { onSuccess: () => closeDialog() })
  } else {
    if (!can('departments.create')) return
    form.post('/departments', { onSuccess: () => closeDialog() })
  }
}

function destroyRow(row) {
  if (!can('departments.delete')) return
  if (!confirm('Yakin ingin menghapus?')) return
  router.delete(`/departments/${row.id}`)
}

function onPage(event) {
  router.get(route('departments.index'), { page: event.page + 1, per_page: event.rows, q: search.value, sort_field: sortField.value, sort_order: sortOrder.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

function onSort(event) {
  sortField.value = event.sortField
  sortOrder.value = event.sortOrder
  router.get(route('departments.index'), { page: 1, per_page: perPage.value, q: search.value, sort_field: sortField.value, sort_order: sortOrder.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

let searchTimer
watch(search, (val) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    router.get(route('departments.index'), { page: 1, per_page: perPage.value, q: val, sort_field: sortField.value, sort_order: sortOrder.value }, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 400)
})
</script>

<style scoped>
:deep(.p-datatable .p-datatable-tbody > tr > td){ padding:1rem .8rem!important; font-size:14px!important; color:#1f2937!important; font-weight:500!important; letter-spacing:.02em!important; line-height:1.25rem!important }
:deep(.p-datatable .p-datatable-thead > tr > th){ padding:1rem!important; background-color:#f9fafb!important; color:#6b7280!important; text-transform:uppercase; font-size:13px!important; font-weight:600!important; letter-spacing:.02em!important }
:deep(.p-datatable .p-datatable-thead){ border-bottom:1px solid #e5e7eb!important }
:deep(.p-datatable .p-datatable-tbody > tr:hover){ background-color:#fafafa!important }
</style>
