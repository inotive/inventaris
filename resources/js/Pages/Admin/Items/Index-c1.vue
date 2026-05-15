<template>
  <div class="px-6 py-6">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold text-gray-800">Data Barang</h1>
      <Button severity="info" @click="openCreate">
        <i class="mr-2 lni lni-plus"></i>
        Tambah Data Barang
      </Button>
    </div>

    <div class="bg-white rounded-lg border border-gray-200">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h1 class="text-xl font-semibold text-gray-800">Daftar Seluruh Barang</h1>
        <div class="flex gap-3 items-center">
          <div class="relative">
            <IconField>
              <InputIcon>
                <i class="lni lni-search-alt" />
              </InputIcon>
              <InputText v-model="search" placeholder="Pencarian kata kunci" />
            </IconField>
          </div>
          <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Group by</label>
            <select v-model="groupBy" class="h-9 px-2 rounded border border-gray-300 text-sm">
              <option :value="null">Tidak digrup</option>
              <option value="branch">Cabang</option>
              <option value="unit">Satuan</option>
              <option value="status">Status</option>
            </select>
          </div>
        </div>
      </div>

      <div class="py-0">
        <div style="overflow-x: auto">
          <Datatable
            :config="{
              title: 'Daftar Seluruh Barang',
              data: rows,
              totalItems: total,
              currentPage: currentPage,
              perPage: perPage,
              search: search,
              loading: loading,
              columns: [
                { key: 'no', label: 'No', sortable: false, style: 'width:70px' },
                { key: 'name', label: 'Nama Barang', sortable: true },
                { key: 'code', label: 'Kode', sortable: true },
                { key: 'branch', label: 'Cabang', sortable: true },
                { key: 'unit', label: 'Satuan', sortable: true },
                { key: 'min_stock', label: 'Minimal Stok', sortable: true, style: 'text-align:right' },
                { key: 'stock', label: 'Stok Saat Ini', sortable: true, style: 'text-align:right' },
                { key: 'price', label: 'Harga', sortable: true, style: 'text-align:right' },
                { key: 'status', label: 'Status', sortable: true },
                { key: 'actions', label: 'Aksi', sortable: false, style: 'width:150px;text-align:center' },
              ],
            }"
            @sort-change="({ key, asc }) => onSort({ sortField: key, sortOrder: asc ? 1 : -1 })"
            @search-change="(val) => (search.value = val)"
            @page-changed="(page) => onPage({ page: page - 1, rows: perPage.value })"
            @per-page-changed="(rows) => onPage({ page: 0, rows })"
          >
            <template #addFilter>
              <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Kelompokkan Berdasarkan</label>
                <select v-model="groupBy" class="h-9 px-3 rounded-xl border border-gray-300 text-sm">
                  <option :value="null">Tidak digrup</option>
                  <option value="branch">Cabang</option>
                  <option value="unit">Satuan</option>
                  <option value="status">Status</option>
                </select>
              </div>
            </template>

            <template #filterHeader>
              <th></th>
              <th>
                <input v-model="filters.name" placeholder="Cari Nama" class="px-3 h-9 w-full text-sm bg-white rounded-xl border border-gray-300" />
              </th>
              <th>
                <input v-model="filters.code" placeholder="Kode" class="px-3 h-9 w-full text-sm bg-white rounded-xl border border-gray-300" />
              </th>
              <th>
                <input v-model="filters.branch" placeholder="Cabang" class="px-3 h-9 w-full text-sm bg-white rounded-xl border border-gray-300" />
              </th>
              <th>
                <input v-model="filters.unit" placeholder="Satuan" class="px-3 h-9 w-full text-sm bg-white rounded-xl border border-gray-300" />
              </th>
              <th>
                <input v-model.number="filters.min_from" placeholder="Min ≥" class="px-3 h-9 w-full text-sm bg-white rounded-xl border border-gray-300 text-right" />
              </th>
              <th>
                <input v-model.number="filters.stock_from" placeholder="Stok ≥" class="px-3 h-9 w-full text-sm bg-white rounded-xl border border-gray-300 text-right" />
              </th>
              <th>
                <input v-model.number="filters.price_from" placeholder="Harga ≥" class="px-3 h-9 w-full text-sm bg-white rounded-xl border border-gray-300 text-right" />
              </th>
              <th>
                <select v-model="filters.status" class="px-3 h-9 w-full text-sm bg-white rounded-xl border border-gray-300">
                  <option :value="null">Semua</option>
                  <option value="aktif">Aktif</option>
                  <option value="nonaktif">Nonaktif</option>
                </select>
              </th>
              <th></th>
            </template>

            <template #body>
              <tr v-for="(row, idx) in rows" :key="row.id" class="hover:bg-gray-50">
                <td class="px-6 py-3 text-center">{{ first + idx + 1 }}</td>
                <td class="px-6 py-3">{{ row.name }}</td>
                <td class="px-6 py-3">{{ row.code }}</td>
                <td class="px-6 py-3">{{ row.branch?.name || '-' }}</td>
                <td class="px-6 py-3">{{ row.unit || '-' }}</td>
                <td class="px-6 py-3 text-right">{{ row.min_stock ?? '-' }}</td>
                <td class="px-6 py-3 text-right"><span :class="stockBadgeClass(row)">{{ row.stock ?? 0 }}</span></td>
                <td class="px-6 py-3 text-right">Rp{{ new Intl.NumberFormat('id-ID').format(row.price || 0) }}</td>
                <td class="px-6 py-3">{{ row.status || '-' }}</td>
                <td class="px-6 py-3">
                  <div class="flex gap-2 justify-center">
                    <button type="button" class="px-2 h-8 text-xs bg-white border rounded-xl" @click="() => openDetail(row)">Lihat</button>
                    <button type="button" class="w-8 h-8 text-white bg-indigo-500 rounded-xl" @click="() => openEdit(row)"><i class="lni lni-pencil-1"></i></button>
                    <button type="button" class="w-8 h-8 text-white bg-red-500 rounded-xl" @click="() => destroyRow(row)"><i class="lni lni-trash-3"></i></button>
                  </div>
                </td>
              </tr>
            </template>
          </Datatable>
        </div>
      </div>
    </div>

    <Dialog v-model:visible="showDialog" modal :header="dialogTitle" :style="{ width: '42rem' }">
      <form @submit.prevent="submit" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Nama Barang</label>
            <InputText v-model="form.name" placeholder="Nama" class="w-full" />
            <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Kode</label>
            <InputText v-model="form.code" placeholder="Kode" class="w-full" />
            <small v-if="form.errors.code" class="text-red-500">{{ form.errors.code }}</small>
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Departemen</label>
            <select v-model.number="form.department_id" class="w-full rounded border-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
              <option :value="null" disabled>Pilih departemen</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <small v-if="form.errors.department_id" class="text-red-500">{{ form.errors.department_id }}</small>
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Satuan</label>
            <InputText v-model="form.unit" placeholder="Satuan (sak, pcs)" class="w-full" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Harga</label>
            <InputText v-model.number="form.price" placeholder="Harga" class="w-full" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Stok</label>
            <InputText v-model.number="form.stock" placeholder="Stok" class="w-full" />
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Keterangan</label>
            <Textarea v-model="form.description" rows="3" autoResize placeholder="Keterangan" class="w-full" />
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button type="submit" label="Simpan" icon="pi pi-check" :loading="processing" />
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
import ColumnGroup from 'primevue/columngroup'
import Row from 'primevue/row'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Textarea from 'primevue/textarea'
// removed FilterMatchMode; now server-side filtering

defineOptions({ layout: AppLayout })

const props = defineProps({ items: Object, departments: Array, filters: Object })

const rows = computed(() => props.items?.data ?? [])
const total = computed(() => props.items?.total ?? 0)
const perPage = computed(() => props.items?.per_page ?? 10)
const currentPage = computed(() => props.items?.current_page ?? 1)
const first = computed(() => (currentPage.value - 1) * perPage.value)
const sortField = ref(props.filters?.sort_field ?? 'id')
const sortOrder = ref(props.filters?.sort_order ?? 1)
const search = ref(props.filters?.q ?? '')
const groupBy = ref(props.filters?.groupBy ?? null)

// Per-column filters state
const filters = ref({
  name: props.filters?.name ?? '',
  code: props.filters?.code ?? '',
  branch: props.filters?.branch ?? '',
  unit: props.filters?.unit ?? '',
  min_from: props.filters?.min_from ?? null,
  stock_from: props.filters?.stock_from ?? null,
  price_from: props.filters?.price_from ?? null,
  status: props.filters?.status ?? null,
})

const departments = computed(() => props.departments ?? [])
const showDialog = ref(false)
const editingId = ref(null)
const loading = ref(false)

const form = useForm({
  name: '', code: '', unit: '', price: null, stock: null, description: '', department_id: null
})

// server-side search/sort/pagination

const dialogTitle = computed(() => (editingId.value ? 'Ubah Barang' : 'Tambah Barang'))

function openCreate() {
  editingId.value = null
  form.reset()
  form.department_id = departments.value[0]?.id ?? null
  showDialog.value = true
}

function openEdit(row) {
  editingId.value = row.id
  form.name = row.name
  form.code = row.code
  form.unit = row.unit
  form.price = row.price
  form.stock = row.stock
  form.department_id = row.department?.id ?? null
  form.description = row.description
  showDialog.value = true
}

function closeDialog() { showDialog.value = false }

const processing = computed(() => form.processing)

function submit() {
  if (editingId.value) {
    form.put(`/items/${editingId.value}`, { onSuccess: () => closeDialog() })
  } else {
    form.post('/items', { onSuccess: () => closeDialog() })
  }
}

function destroyRow(row) {
  if (!confirm('Yakin ingin menghapus?')) return
  router.delete(`/items/${row.id}`)
}

function onPage(event) {
  router.get(route('items.index'), {
    page: event.page + 1,
    per_page: event.rows,
    q: search.value,
    groupBy: groupBy.value,
    sort_field: sortField.value,
    sort_order: sortOrder.value,
    ...filters.value,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

function onSort(event) {
  sortField.value = event.sortField
  sortOrder.value = event.sortOrder
  router.get(route('items.index'), {
    page: 1,
    per_page: perPage.value,
    q: search.value,
    groupBy: groupBy.value,
    sort_field: sortField.value,
    sort_order: sortOrder.value,
    ...filters.value,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

let t
watch(search, (val) => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(route('items.index'), {
      page: 1,
      per_page: perPage.value,
      q: val,
      groupBy: groupBy.value,
      sort_field: sortField.value,
      sort_order: sortOrder.value,
      ...filters.value,
    }, { preserveState: true, preserveScroll: true, replace: true })
  }, 400)
})

// Watch groupBy and column filters (debounced)
watch([groupBy, filters], () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(route('items.index'), {
      page: 1,
      per_page: perPage.value,
      q: search.value,
      groupBy: groupBy.value,
      sort_field: sortField.value,
      sort_order: sortOrder.value,
      ...filters.value,
    }, { preserveState: true, preserveScroll: true, replace: true })
  }, 400)
}, { deep: true })

function openDetail(row){
  router.get(route('items.show', row.id))
}

function stockBadgeClass(data){
  const stock = Number(data?.stock ?? 0)
  const min = Number(data?.min_stock ?? 0)
  if (isNaN(stock) || isNaN(min)) return 'inline-block px-2 py-0.5 rounded text-xs'
  if (stock < min) return 'inline-block px-2 py-0.5 rounded text-xs bg-red-100 text-red-700'
  if (stock === min) return 'inline-block px-2 py-0.5 rounded text-xs bg-yellow-100 text-yellow-700'
  return 'inline-block px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700'
}
</script>

<style scoped>
:deep(.p-datatable .p-datatable-tbody > tr > td){ padding:1rem .8rem!important; font-size:14px!important; color:#1f2937!important; font-weight:500!important; letter-spacing:.02em!important; line-height:1.25rem!important }
:deep(.p-datatable .p-datatable-thead > tr > th){ padding:1rem!important; background-color:#f9fafb!important; color:#6b7280!important; text-transform:uppercase; font-size:13px!important; font-weight:600!important; letter-spacing:.02em!important }
:deep(.p-datatable .p-datatable-thead){ border-bottom:1px solid #e5e7eb!important }
:deep(.p-datatable .p-datatable-tbody > tr:hover){ background-color:#fafafa!important }
</style>
